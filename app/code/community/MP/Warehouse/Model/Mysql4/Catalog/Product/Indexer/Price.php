<?php
/**
 * Mage Plugins, Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mage Plugins Commercial License (MPCL 1.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://mageplugins.net/commercial-license/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mageplugins@gmail.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to http://www.mageplugins.net for more information.
 *
 * @category   MP
 * @package    MP_Warehouse
 * @copyright  Copyright (c) 2017-2018 Mage Plugins, Co. and affiliates (https://mageplugins.net/)
 * @license    https://mageplugins.net/commercial-license/ Mage Plugins Commercial License (MPCL 1.0)
 */

/**
 * Product price indexer resource
 * 
 * @category    MP
 * @package     MP_Warehouse
 * @author      Mage Plugins Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Catalog_Product_Indexer_Price 
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price
{
    /**
     * Get warehouse helper
     * 
     * @return  MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Get price indexer helper
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price_Indexer
     */
    protected function getProductPriceIndexerHelper()
    {
        return $this
            ->getWarehouseHelper()
            ->getProductPriceIndexerHelper();
    }
    /**
     * Get version helper
     * 
     * @return MP_Warehouse_Helper_Core_Version
     */
    protected function getVersionHelper()
    {
        return $this
            ->getProductPriceIndexerHelper()
            ->getVersionHelper();
    }



     #Returns array of pairs (childProductId:childProductType)
    private function getChildIdsByParent($parentId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            #->from(array('pr' => $this->getTable('catalog/product_relation')), array('child_id'))
            ->from(array('p' => $this->getTable('catalog/product')), array('entity_id'))
            ->join(
                #array('p' => $this->getTable('catalog/product')),
                array('pr' => $this->getTable('catalog/product_relation')),
                'pr.child_id=p.entity_id',
                array('p.type_id'))
            ->where('pr.parent_id=?', $parentId);
        return $read->fetchPairs($select);
    }

    private function getProductTypeById($id)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('pr' => $this->getTable('catalog/product_relation')), array('parent_id'))
            ->join(
                array('p' => $this->getTable('catalog/product')),
                'pr.parent_id=p.entity_id',
                array('p.type_id'))
            ->where('pr.parent_id=?', $id);
        $data = $read->fetchRow($select);
        #Mage::log("SCP: getProductTypeById: result is: " . print_r($data, true));
        return $data['type_id'];
    }


    /**
     * Process product save
     *
     * @param Mage_Index_Model_Event $event
     * 
     * @return $this
     */
    public function catalogProductSave(Mage_Index_Model_Event $event)
    {
        $productId = $event->getEntityPk();
        $data = $event->getNewData();
        if (!isset($data['reindex_price'])) {
            return $this;
        }

        $this->clearTemporaryIndexTable();
        $this->_prepareWebsiteDateTable();
        $indexer = $this->_getIndexer($data['product_type_id']);
        $processIds = array($productId);
        if ($indexer->getIsComposite()) {


             #Mage::log("catalogProductSave: " . "saving composite");
            if ($this->getProductTypeById($productId) == 'configurable') {
                #Mage::log("catalogProductSave: " . "saving composite - is configurable");
                $children = $this->getChildIdsByParent($productId);
                $processIds = array_merge($processIds, array_keys($children));
                #Ignore tier price data for actual configurable product
                $tierPriceIds = array_keys($children);
            } else {
                $tierPriceIds = $productId;
            }

            $this->_copyRelationIndexData($productId);
            
            $this->_prepareBatchPriceIndex($productId);
            $this->_prepareBatchSpecialPriceIndex($productId);
            
            $this->_prepareTierPriceIndex($productId);

            if ($this->getVersionHelper()->isGe1700()) {
                $this->_prepareGroupPriceIndex($productId);
            }
            
            $indexer->reindexEntity($productId);


        } else {
            $parentIds = $this->getProductParentsByChild($productId);
            if ($parentIds) {
                $processIds = array_merge($processIds, array_keys($parentIds));
                $this->_copyRelationIndexData(array_keys($parentIds), $productId);
                
                $this->_prepareBatchPriceIndex($processIds);
                $this->_prepareBatchSpecialPriceIndex($processIds);
                
                $this->_prepareTierPriceIndex($processIds);
                
                if ($this->getVersionHelper()->isGe1700()) {
                    $this->_prepareGroupPriceIndex($processIds);
                }
                
                $indexer->reindexEntity($productId);
                $parentByType = array();
                foreach ($parentIds as $parentId => $parentType) {
                    $parentByType[$parentType][$parentId] = $parentId;
                }

                foreach ($parentByType as $parentType => $entityIds) {
                    $this->_getIndexer($parentType)->reindexEntity($entityIds);
                }
            } else {
                $this->_prepareBatchPriceIndex($productId);
                $this->_prepareBatchSpecialPriceIndex($productId);
                
                $this->_prepareTierPriceIndex($productId);
                
                if ($this->getVersionHelper()->isGe1700()) {
                    $this->_prepareGroupPriceIndex($productId);
                }
                
                $indexer->reindexEntity($productId);
            }
        }

        $this->_copyIndexDataToMainTable($processIds);
        return $this;
    }
    /**
     * Rebuild all index data
     *
     * @return $this
     */
    public function reindexAll()
    {
        $this->useIdxTable(true);
        
        if ($this->getVersionHelper()->isGe1620()) {
            $this->beginTransaction();
            try {
                $this->clearTemporaryIndexTable();
                $this->_prepareWebsiteDateTable();

                $this->_prepareBatchPriceIndex();
                $this->_prepareBatchSpecialPriceIndex();

                $this->_prepareTierPriceIndex();

                if ($this->getVersionHelper()->isGe1700()) {
                    $this->_prepareGroupPriceIndex();
                }

                $indexers = $this->getTypeIndexers();
                foreach ($indexers as $indexer) {
                    $indexer->reindexAll();
                }

                $this->syncData();
                $this->commit();
            } catch (Exception $e) {
                $this->rollBack();
                throw $e;
            }
        } else {
            $this->useIdxTable(true);
            $this->clearTemporaryIndexTable();
            $this->_prepareWebsiteDateTable();
            
            $this->_prepareBatchPriceIndex();
            $this->_prepareBatchSpecialPriceIndex();
            
            $this->_prepareTierPriceIndex();
            $indexers = $this->getTypeIndexers();
            foreach ($indexers as $indexer) {
                if ($this->getVersionHelper()->isGe1610()) {
                    if (!$this->_allowTableChanges && is_callable(array($indexer, 'setAllowTableChanges'))) {
                        $indexer->setAllowTableChanges(false);
                    }
                }
                
                $indexer->reindexAll();
                
                if ($this->getVersionHelper()->isGe1610()) {
                    if (!$this->_allowTableChanges && is_callable(array($indexer, 'setAllowTableChanges'))) {
                        $indexer->setAllowTableChanges(true);
                    }
                }
            }

            $this->syncData();
        }

        return $this;
    }
    /**
     * Retrieve catalog_product attribute instance by attribute code
     *
     * @param string $attributeCode
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected function _getAttribute($attributeCode)
    {
        return Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);
    }
    /**
     * Add attribute join condition to select and return Zend_Db_Expr
     * attribute value definition
     * If $condition is not empty apply limitation for select
     *
     * @param Varien_Db_Select $select
     * @param string $attrCode              the attribute code
     * @param string|Zend_Db_Expr $entity   the entity field or expression for condition
     * @param string|Zend_Db_Expr $store    the store field or expression for condition
     * @param Zend_Db_Expr $condition       the limitation condition
     * @param bool $required                if required or has condition used INNER join, else - LEFT
     * 
     * @return Zend_Db_Expr                 the attribute value expression
     */
    protected function _addAttributeToSelect($select, $attrCode, $entity, $store, $condition = null, $required = false)
    {
        $attribute      = $this->_getAttribute($attrCode);
        $attributeId    = $attribute->getAttributeId();
        $attributeTable = $attribute->getBackend()->getTable();
        $adapter        = $this->_getReadAdapter();
        $joinType       = !is_null($condition) || $required ? 'join' : 'joinLeft';
        if ($attribute->isScopeGlobal()) {
            $alias = 'ta_' . $attrCode;
            $select->$joinType(
                array($alias => $attributeTable),
                "{$alias}.entity_id = {$entity} AND {$alias}.attribute_id = {$attributeId}"
                    . " AND {$alias}.store_id = 0",
                array()
            );
            $expression = new Zend_Db_Expr("{$alias}.value");
        } else {
            $dAlias = 'tad_' . $attrCode;
            $sAlias = 'tas_' . $attrCode;
            $select->$joinType(
                array($dAlias => $attributeTable),
                "{$dAlias}.entity_id = {$entity} AND {$dAlias}.attribute_id = {$attributeId}"
                    . " AND {$dAlias}.store_id = 0",
                array()
            );
            $select->joinLeft(
                array($sAlias => $attributeTable),
                "{$sAlias}.entity_id = {$entity} AND {$sAlias}.attribute_id = {$attributeId}"
                    . " AND {$sAlias}.store_id = {$store}",
                array()
            );
            if ($this->getVersionHelper()->isGe1600()) {
                $expression = $adapter->getCheckSql(
                    $adapter->getIfNullSql("{$sAlias}.value_id", -1) . ' > 0',
                    "{$sAlias}.value", "{$dAlias}.value"
                );
            } else {
                $expression = new Zend_Db_Expr("IF({$sAlias}.value_id > 0, {$sAlias}.value, {$dAlias}.value)");
            }    
        }

        if (!is_null($condition)) {
            $select->where("{$expression}{$condition}");
        }

        return $expression;
    }
    
    
    /**
     * Prepare batch price index table
     * 
     * @param integer|array $entityIds
     * @param string $attributeCode
     * @param string $table
     * @param string $indexTable
     * 
     * @return $this
     */
    protected function __prepareBatchPriceIndex(
        $entityIds = null, 
        $attributeCode, 
        $table, 
        $indexTable
    ) {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        $adminStockId           = $helper->getAdminStockId();
        $productPriceHelper     = $helper->getProductPriceHelper();
        $productPriceIndexerHelper = $this->getProductPriceIndexerHelper();
        $adapter                = $this->_getWriteAdapter();
        
        $select                 = $adapter
            ->select()
            ->from(
                array('e' => $this->getTable('catalog/product')), 
                array()
            )
            ->join(
                array('cw' => $this->getTable('core/website')), 
                '', 
                array()
            )
            ->join(
                array('cwd' => $this->getTable('catalog/product_index_website')), 
                '(cw.website_id = cwd.website_id)', 
                array()
            )
            ->join(
                array('csg' => $this->getTable('core/store_group')), 
                '(csg.website_id = cw.website_id) AND (cw.default_group_id = csg.group_id)', 
                array()
            )
            ->join(
                array('cs' => $this->getTable('core/store')), 
                '(csg.default_store_id = cs.store_id) AND (cs.store_id != 0)', 
                array()
            )
            ->join(
                array('pw' => $this->getTable('catalog/product_website')), 
                '(pw.product_id = e.entity_id) AND (pw.website_id = cw.website_id)', 
                array()
            )
            ->join(
                array('cis' => $this->getTable('cataloginventory/stock')), 
                "(cis.stock_id != {$adminStockId})", 
                array()
            );
        $price                  = $this->_addAttributeToSelect($select, $attributeCode, 'e.entity_id', 'cs.store_id');
        $select
            ->joinLeft(
                array('cbgp' => $table), 
                implode(
                    ' AND ', array(
                    '(cbgp.product_id = e.entity_id)', 
                    '(cbgp.stock_id = cis.stock_id)', 
                    '(cbgp.website_id = 0)', 
                    )
                ), 
                array()
            );
        if (!$productPriceIndexerHelper->getPriceHelper()->isGlobalScope()) {
            $select
                ->joinLeft(
                    array('cbp' => $table), 
                    implode(
                        ' AND ', array(
                        '(cbp.product_id = e.entity_id)', 
                        '(cbp.stock_id = cis.stock_id)', 
                        '(cbp.website_id = cw.website_id)', 
                        )
                    ), 
                    array()
                );
        }

        if (!$productPriceIndexerHelper->getPriceHelper()->isGlobalScope()) {
            $price                  = new Zend_Db_Expr(
                "IF (
                cbp.price IS NOT NULL, 
                cbp.price, 
                IF (
                    cbgp.price IS NOT NULL, 
                    ROUND(cbgp.price * cwd.rate, 4), 
                    {$price}
                )
            )"
            );
        } else {
            $price                  = new Zend_Db_Expr(
                "IF (
                cbgp.price IS NOT NULL, 
                ROUND(cbgp.price * cwd.rate, 4), 
                {$price}
            )"
            );
        }

        $select->where(
            implode(
                ' AND ', array(
                '(cw.website_id != 0)', 
                '((cbgp.price IS NOT NULL)'.
                (
                    (!$productPriceHelper->isGlobalScope()) ? 
                        'OR (cbp.price IS NOT NULL)' : 
                        ''
                ).
                ')', 
                )
            )
        );
        
        if (!empty($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        $adapter->delete($indexTable);
        
        if ($config->isMultipleModeInAnyStore()) {
            $_select                = clone $select;
            $query                  = $_select
                ->columns(
                    array(
                    'entity_id'             => new Zend_Db_Expr('e.entity_id'), 
                    'stock_id'              => new Zend_Db_Expr($adminStockId), 
                    'website_id'            => new Zend_Db_Expr('cw.website_id'), 
                    'price'                 => new Zend_Db_Expr("MAX({$price})"), 
                    'min_price'             => new Zend_Db_Expr("MIN({$price})"), 
                    'max_price'             => new Zend_Db_Expr("MAX({$price})"), 
                    )
                )
                ->group(
                    array(
                    'e.entity_id', 
                    'cw.website_id', 
                    )
                )
                ->insertFromSelect($indexTable);
            $adapter->query($query);
        }

        if ($config->isSingleModeInAnyStore()) {
            $_select                = clone $select;
            $query                  = $_select
                ->columns(
                    array(
                    'entity_id'             => new Zend_Db_Expr('e.entity_id'), 
                    'stock_id'              => new Zend_Db_Expr('cis.stock_id'), 
                    'website_id'            => new Zend_Db_Expr('cw.website_id'), 
                    'price'                 => new Zend_Db_Expr($price), 
                    'min_price'             => new Zend_Db_Expr('NULL'), 
                    'max_price'             => new Zend_Db_Expr('NULL'), 
                    )
                )
                ->group(
                    array(
                    'e.entity_id', 
                    'cis.stock_id', 
                    'cw.website_id', 
                    )
                )
                ->insertFromSelect($indexTable);
            $adapter->query($query);
        }

        return $this;
    }
    /**
     * Prepare batch price index table
     * 
     * @param integer|array $entityIds
     * 
     * @return $this
     */
    protected function _prepareBatchPriceIndex($entityIds = null)
    {
        return $this->__prepareBatchPriceIndex(
            $entityIds, 
            'price', 
            $this->getTable('catalog/product_batch_price'), 
            $this
                ->getProductPriceIndexerHelper()
                ->getBatchPriceIndexTable()
        );
    }
    /**
     * Prepare batch special price index table
     *
     * @param integer|array $entityIds
     * 
     * @return $this
     */
    protected function _prepareBatchSpecialPriceIndex($entityIds = null)
    {
        return $this->__prepareBatchPriceIndex(
            $entityIds, 
            'special_price', 
            $this->getTable('catalog/product_batch_special_price'), 
            $this
                ->getProductPriceIndexerHelper()
                ->getBatchSpecialPriceIndexTable()
        );
    }
    /**
     * Prepare group price index table
     *
     * @param int|array $entityIds the entity ids limitation
     * @return $this
     */
    protected function _prepareGroupPriceIndex($entityIds = null)
    {
        $helper         = $this->getWarehouseHelper();
        $isMultipleMode = $helper->isMultipleMode();
        $write          = $this->_getWriteAdapter();
        $table          = $this->_getGroupPriceIndexTable();
        
        $write->delete($table);

        $price = $write->getCheckSql('gp.website_id = 0', 'ROUND(gp.value * cwd.rate, 4)', 'gp.value');

        if ($isMultipleMode) {
            $group = array('gp.entity_id', 'cg.customer_group_id', 'cw.website_id');
        } else {
            $group = array('gp.entity_id', 'cg.customer_group_id', 'cw.website_id', 'cis.stock_id');
        }

        $columns = array(
            'entity_id'         => 'gp.entity_id',
            'customer_group_id' => 'cg.customer_group_id',
            'website_id'        => 'cw.website_id',
            'stock_id'          => new Zend_Db_Expr(
                ($isMultipleMode) ? $helper->getDefaultStockId() : 'cis.stock_id'
            ),
            'price'             => new Zend_Db_Expr("MIN({$price})")
        );

        $select = $write->select()
            ->from(
                array('gp' => $this->getValueTable('catalog/product', 'group_price')),
                array()
            )
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                'gp.all_groups = 1 OR (gp.all_groups = 0 AND gp.customer_group_id = cg.customer_group_id)',
                array()
            )
            ->join(
                array('cw' => $this->getTable('core/website')),
                'gp.website_id = 0 OR gp.website_id = cw.website_id',
                array()
            )
            ->join(
                array('cwd' => $this->_getWebsiteDateTable()),
                'cw.website_id = cwd.website_id',
                array()
            )
            ->join(
                array('cis' => $this->getTable('cataloginventory/stock')),
                '(gp.stock_id IS NULL) OR (gp.stock_id = cis.stock_id)',
                array()
            )
            ->where('cw.website_id != 0')
            ->columns($columns)
            ->group($group);

        if (!empty($entityIds)) {
            $select->where('gp.entity_id IN(?)', $entityIds);
        }
        
        $query = $select->insertFromSelect($table);
        $write->query($query);

        return $this;
    }
    /**
     * Prepare tier price index table
     *
     * @param integer|array $entityIds
     * 
     * @return $this
     */
    protected function _prepareTierPriceIndex($entityIds = null)
    {
        $helper                 = $this->getWarehouseHelper();
        $isMultipleMode         = $helper->isMultipleMode();
        $adapter                = $this->_getWriteAdapter();
        $table                  = $this->_getTierPriceIndexTable();
        $adapter->delete($table);
        if ($this->getVersionHelper()->isGe1600()) {
            $price                  = $adapter->getCheckSql(
                'tp.website_id = 0', 
                'ROUND(tp.value * cwd.rate, 4)', 
                'tp.value'
            );
        } else {
            $price                  = new Zend_Db_Expr(
                "IF (tp.website_id=0, ROUND(tp.value * cwd.rate, 4), tp.value)"
            );
        }

        if ($isMultipleMode) {
            $group                  = array('tp.entity_id', 'cg.customer_group_id', 'cw.website_id');
        } else {
            $group                  = array('tp.entity_id', 'cg.customer_group_id', 'cw.website_id', 'cis.stock_id');
        }

        $columns                = array(
            'entity_id'             => 'tp.entity_id', 
            'customer_group_id'     => 'cg.customer_group_id', 
            'website_id'            => 'cw.website_id', 
            'stock_id'              => new Zend_Db_Expr(
                ($isMultipleMode) ? 
                    $helper->getDefaultStockId() : 
                    'cis.stock_id'
            ), 
            'min_price'             => new Zend_Db_Expr("MIN({$price})"), 
        );
        $select                 = $adapter->select()
            ->from(
                array('tp' => $this->getValueTable('catalog/product', 'tier_price')), 
                array()
            )
            ->join(
                array('cg' => $this->getTable('customer/customer_group')), 
                'tp.all_groups = 1 OR (tp.all_groups = 0 AND tp.customer_group_id = cg.customer_group_id)', 
                array()
            )
            ->join(
                array('cw' => $this->getTable('core/website')),
                'tp.website_id = 0 OR tp.website_id = cw.website_id', 
                array()
            )
            ->join(
                array('cwd' => $this->_getWebsiteDateTable()), 
                'cw.website_id = cwd.website_id', 
                array()
            )
            ->join(
                array('cis' => $this->getTable('cataloginventory/stock')), 
                '(tp.stock_id IS NULL) OR (tp.stock_id = cis.stock_id)', 
                array()
            )
            ->where('cw.website_id != 0')
            ->columns($columns)
            ->group($group);
        if (!empty($entityIds)) {
            $select->where('tp.entity_id IN(?)', $entityIds);
        }

        $query                  = $select->insertFromSelect($table);
        $adapter->query($query);
        return $this;
    }
}
