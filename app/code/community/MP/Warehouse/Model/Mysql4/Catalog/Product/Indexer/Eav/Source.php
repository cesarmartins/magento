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
 * EAV indexer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Catalog_Product_Indexer_Eav_Source 
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Eav_Source
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
     * Get version helper
     * 
     * @return MP_Warehouse_Helper_Core_Version
     */
    protected function getVersionHelper()
    {
        return $this->getWarehouseHelper()->getVersionHelper();
    }
    /**
     * Prepare data index for indexable select attributes
     *
     * @param array $entityIds the entity ids limitation
     * @param int $attributeId the attribute id limitation
     * 
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source
     */
    protected function _prepareSelectIndex($entityIds = null, $attributeId = null)
    {
        $helper             = $this->getWarehouseHelper();
        $isMultipleMode     = $helper->isMultipleMode();
        
        $adapter            = $this->_getWriteAdapter();
        $idxTable           = $this->getIdxTable();
        if (is_null($attributeId)) {
            $attrIds            = $this->_getIndexableAttributes(false);
        } else {
            $attrIds            = array($attributeId);
        }

        if (!$attrIds) {
            return $this;
        }
        
        if ($this->getVersionHelper()->isGe1600()) {
            $subSelect          = $adapter->select()
                ->from(array('s' => $this->getTable('core/store')), array('store_id', 'website_id'))
                ->joinLeft(
                    array('d' => $this->getValueTable('catalog/product', 'int')), 
                    '1 = 1 AND d.store_id = 0', 
                    array('entity_id', 'attribute_id', 'value')
                )->where('s.store_id != 0');
            
            if ($this->getVersionHelper()->isGe1900()) {
                $statusCond = $adapter->quoteInto(' = ?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                $this->_addAttributeToSelect($subSelect, 'status', 'd.entity_id', 's.store_id', $statusCond);
            }
            
            if ($this->getVersionHelper()->isGe1700()) {
                if (!is_null($entityIds)) {
                    $subSelect->where('d.entity_id IN(?)', $entityIds);
                }
            }

            $select = $adapter->select()
                ->from(array('pid' => new Zend_Db_Expr(sprintf('(%s)', $subSelect->assemble()))), array())
                ->joinLeft(
                    array('pis' => $this->getValueTable('catalog/product', 'int')), 
                    implode(
                        ' AND ', array(
                        'pis.entity_id = pid.entity_id', 
                        'pis.attribute_id = pid.attribute_id', 
                        'pis.store_id = pid.store_id'
                        )
                    ), 
                    array()
                );
            
            $stockJoinCondition = ($isMultipleMode) ? 
                "cis.stock_id = {$adapter->quote($helper->getDefaultStockId())}" : 
                '';
            $select->join(
                array(
                'cis' => $this->getTable('cataloginventory/stock')), 
                $stockJoinCondition, 
                array()
            );
            $stockId            = 'cis.stock_id';
            
            $select->columns(
                array(
                'pid.entity_id', 
                'pid.attribute_id', 
                'pid.store_id', 
                $stockId, 
                'value' => $adapter->getIfNullSql('pis.value', 'pid.value'), 
                )
            )->where('pid.attribute_id IN(?)', $attrIds);
            $select->where(Mage::getResourceHelper('catalog')->getIsNullNotNullCondition('pis.value', 'pid.value'));
        } else {
            $select = $adapter->select()
                ->from(
                    array('pid' => $this->getValueTable('catalog/product', 'int')),
                    array('entity_id', 'attribute_id')
                )
                ->join(array('cs' => $this->getTable('core/store')), '', array('store_id'));
            
            $stockJoinCondition = ($isMultipleMode) ? 
                "cis.stock_id = {$adapter->quote($helper->getDefaultStockId())}" : 
                '';
            $select->join(
                array(
                'cis' => $this->getTable('cataloginventory/stock')), 
                $stockJoinCondition, 
                array()
            );
            $stockId            = 'cis.stock_id';
            
            $select->columns(array($stockId));
            $select->joinLeft(
                array('pis' => $this->getValueTable('catalog/product', 'int')), 
                implode(
                    ' AND ', array(
                        'pis.entity_id = pid.entity_id', 
                        'pis.attribute_id = pid.attribute_id', 
                        'pis.store_id=cs.store_id', 
                    )
                ), 
                array('value' => new Zend_Db_Expr('IF(pis.value_id > 0, pis.value, pid.value)'))
            )
                ->where('pid.store_id=?', 0)
                ->where('cs.store_id!=?', 0)
                ->where('pid.attribute_id IN(?)', $attrIds)
                ->where('IF(pis.value_id > 0, pis.value, pid.value) IS NOT NULL');
            $statusCond = $adapter->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            $this->_addAttributeToSelect($select, 'status', 'pid.entity_id', 'cs.store_id', $statusCond);
        }
        
        if (!$this->getVersionHelper()->isGe1700()) {
            if (!is_null($entityIds)) {
                $select->where('pid.entity_id IN(?)', $entityIds);
            }
        }
        
        if ($this->getVersionHelper()->isGe1600()) {
            Mage::dispatchEvent(
                'prepare_catalog_product_index_select', array(
                'select'            => $select,
                'entity_field'      => new Zend_Db_Expr('pid.entity_id'), 
                'website_field'     => new Zend_Db_Expr('pid.website_id'), 
                'store_field'       => new Zend_Db_Expr('pid.store_id'), 
                'stock_field'       => new Zend_Db_Expr($stockId), 
                )
            );
        } else {
            Mage::dispatchEvent(
                'prepare_catalog_product_index_select', array(
                'select'            => $select,
                'entity_field'      => new Zend_Db_Expr('pid.entity_id'),
                'website_field'     => new Zend_Db_Expr('cs.website_id'),
                'store_field'       => new Zend_Db_Expr('cs.store_id'), 
                'stock_field'       => new Zend_Db_Expr($stockId), 
                )
            );
        }
        
        $query              = $select->insertFromSelect($idxTable);
        $adapter->query($query);
        return $this;
    }
    /**
     * Prepare data index for indexable multiply select attributes
     *
     * @param array $entityIds      the entity ids limitation
     * @param int $attributeId      the attribute id limitation
     * 
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source
     */
    protected function _prepareMultiselectIndex($entityIds = null, $attributeId = null)
    {
        $helper             = $this->getWarehouseHelper();
        $isMultipleMode     = $helper->isMultipleMode();
        
        $adapter            = $this->_getWriteAdapter();
        if (is_null($attributeId)) {
            $attrIds            = $this->_getIndexableAttributes(true);
        } else {
            $attrIds            = array($attributeId);
        }

        if (!$attrIds) {
            return $this;
        }

        $options            = array();
        $select             = $adapter->select()
            ->from($this->getTable('eav/attribute_option'), array('attribute_id', 'option_id'))
            ->where('attribute_id IN(?)', $attrIds);
        $query              = $select->query();
        while ($row = $query->fetch()) {
            $options[$row['attribute_id']][$row['option_id']] = true;
        }
        
        if ($this->getVersionHelper()->isGe1600()) {
            $productValueExpression = $adapter->getCheckSql('pvs.value_id > 0', 'pvs.value', 'pvd.value');
        } else {
            $productValueExpression = new Zend_Db_Expr('IF(pvs.value_id>0, pvs.value, pvd.value)');
        }

        $select             = $adapter->select()
            ->from(
                array('pvd' => $this->getValueTable('catalog/product', 'varchar')),
                array('entity_id', 'attribute_id')
            )
            ->join(array('cs' => $this->getTable('core/store')), '', array('store_id'));
        
        $stockJoinCondition = ($isMultipleMode) ? 
            "cis.stock_id = {$adapter->quote($helper->getDefaultStockId())}" : 
            '';
        $select->join(
            array(
            'cis' => $this->getTable('cataloginventory/stock')), 
            $stockJoinCondition, 
            array()
        );
        $stockId            = 'cis.stock_id';
        
        $select->columns(array($stockId));
        
        if ($this->getVersionHelper()->isGe1600()) {
            $defaultStoreId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        } else {
            $defaultStoreId = 0;
        }
        
        $select->joinLeft(
            array('pvs' => $this->getValueTable('catalog/product', 'varchar')), 
            implode(
                ' AND ', array(
                    'pvs.entity_id = pvd.entity_id', 
                    'pvs.attribute_id = pvd.attribute_id', 
                    'pvs.store_id=cs.store_id', 
                )
            ), 
            array('value' => $productValueExpression)
        )
            ->where('pvd.store_id=?', $defaultStoreId)
            ->where('cs.store_id!=?', $defaultStoreId)
            ->where('pvd.attribute_id IN(?)', $attrIds);

        $statusCond         = $adapter->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'pvd.entity_id', 'cs.store_id', $statusCond);
        if (!is_null($entityIds)) {
            $select->where('pvd.entity_id IN(?)', $entityIds);
        }

        Mage::dispatchEvent(
            'prepare_catalog_product_index_select', array(
            'select'            => $select,
            'entity_field'      => new Zend_Db_Expr('pvd.entity_id'),
            'website_field'     => new Zend_Db_Expr('cs.website_id'),
            'store_field'       => new Zend_Db_Expr('cs.store_id'), 
            'stock_field'       => new Zend_Db_Expr($stockId), 
            )
        );
        $i                  = 0;
        $data               = array();
        $query              = $select->query();
        while ($row = $query->fetch()) {
            $values             = explode(',', $row['value']);
            foreach ($values as $valueId) {
                if (isset($options[$row['attribute_id']][$valueId])) {
                    $data[] = array(
                        $row['entity_id'], 
                        $row['attribute_id'], 
                        $row['store_id'], 
                        $row['stock_id'], 
                        $valueId, 
                    );
                    $i ++;
                    if ($i % 10000 == 0) {
                        $this->_saveIndexData($data);
                        $data = array();
                    }
                }
            }
        }

        $this->_saveIndexData($data);
        unset($options);
        unset($data);
        return $this;
    }
    /**
     * Save a data to temporary source index table
     * 
     * @param array $data
     * 
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source
     */
    protected function _saveIndexData(array $data)
    {
        if (!$data) {
            return $this;
        }

        $adapter = $this->_getWriteAdapter();
        $adapter->insertArray(
            $this->getIdxTable(), 
            array('entity_id', 'attribute_id', 'store_id', 'stock_id', 'value', ), 
            $data
        );
        return $this;
    }
    /**
     * Prepare data index for product relations
     *
     * @param array $parentIds  the parent entity ids limitation
     * 
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Abstract
     */
    protected function _prepareRelationIndex($parentIds = null)
    {
        $helper             = $this->getWarehouseHelper();
        $isMultipleMode     = $helper->isMultipleMode();
        
        $adapter            = $this->_getWriteAdapter();
        $idxTable           = $this->getIdxTable();
        $select = $adapter->select()
            ->from(array('l' => $this->getTable('catalog/product_relation')), 'parent_id')
            ->join(array('cs' => $this->getTable('core/store')), '', array());
        
        $stockJoinCondition = ($isMultipleMode) ? 
            "cis.stock_id = {$adapter->quote($helper->getDefaultStockId())}" : 
            '';
        $select->join(
            array(
            'cis' => $this->getTable('cataloginventory/stock')), 
            $stockJoinCondition, 
            array()
        );
        $stockId            = 'cis.stock_id';
        
        $select->join(
            array('i' => $idxTable), 
            implode(
                ' AND ', array(
                    'l.child_id = i.entity_id', 
                    'cs.store_id = i.store_id', 
                    "{$stockId} = i.stock_id", 
                )
            ), 
            array('attribute_id', 'store_id', 'stock_id', 'value')
        )
            ->group(
                array(
                'l.parent_id', 'i.attribute_id', 'i.store_id', 'i.stock_id', 'i.value'
                )
            );
        if (!is_null($parentIds)) {
            $select->where('l.parent_id IN(?)', $parentIds);
        }

        Mage::dispatchEvent(
            'prepare_catalog_product_index_select', array(
            'select'            => $select, 
            'entity_field'      => new Zend_Db_Expr('l.parent_id'), 
            'website_field'     => new Zend_Db_Expr('cs.website_id'), 
            'store_field'       => new Zend_Db_Expr('cs.store_id'), 
            'stock_field'       => new Zend_Db_Expr('i.stock_id'), 
            )
        );
        
        if ($this->getVersionHelper()->isGe1600()) {
            $query = $adapter->insertFromSelect($select, $idxTable, array(), Varien_Db_Adapter_Interface::INSERT_IGNORE);
        } else {
            $query = $select->insertIgnoreFromSelect($idxTable);
        }
        
        $adapter->query($query);
        return $this;
    }
}
