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
 * EAV decimal indexer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Catalog_Product_Indexer_Eav_Decimal 
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Eav_Decimal
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
     * Prepare data index for indexable attributes
     *
     * @param array $entityIds      the entity ids limitation
     * @param int $attributeId      the attribute id limitation
     * 
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Decimal
     */
    protected function _prepareIndex($entityIds = null, $attributeId = null)
    {
        $helper             = $this->getWarehouseHelper();
        $isMultipleMode     = $helper->isMultipleMode();
        
        $adapter            = $this->_getWriteAdapter();
        $idxTable           = $this->getIdxTable();
        if (is_null($attributeId)) {
            $attrIds            = $this->_getIndexableAttributes();
        } else {
            $attrIds            = array($attributeId);
        }

        if (!$attrIds) {
            return $this;
        }
        
        if ($this->getVersionHelper()->isGe1600()) {
            $productValueExpression = $adapter->getCheckSql('pds.value_id > 0', 'pds.value', 'pdd.value');
        } else {
            $productValueExpression = new Zend_Db_Expr('IF(pds.value_id > 0, pds.value, pdd.value) IS NOT NULL');
        }
        
        $select = $adapter->select()
            ->from(
                array('pdd' => $this->getValueTable('catalog/product', 'decimal')),
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
            array('pds' => $this->getValueTable('catalog/product', 'decimal')), 
            implode(
                ' AND ', array(
                    'pds.entity_id = pdd.entity_id', 
                    'pds.attribute_id = pdd.attribute_id', 
                    'pds.store_id=cs.store_id', 
                )
            ), 
            array('value' => $productValueExpression)
        )
            ->where('pdd.store_id=?', $defaultStoreId)
            ->where('cs.store_id!=?', $defaultStoreId)
            ->where('pdd.attribute_id IN(?)', $attrIds)
            ->where("{$productValueExpression} IS NOT NULL");

        $statusCond = $adapter->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'pdd.entity_id', 'cs.store_id', $statusCond);
        if (!is_null($entityIds)) {
            $select->where('pdd.entity_id IN(?)', $entityIds);
        }

        Mage::dispatchEvent(
            'prepare_catalog_product_index_select', array(
            'select'            => $select,
            'entity_field'      => new Zend_Db_Expr('pdd.entity_id'),
            'website_field'     => new Zend_Db_Expr('cs.website_id'),
            'store_field'       => new Zend_Db_Expr('cs.store_id'), 
            'stock_field'       => new Zend_Db_Expr($stockId), 
            )
        );
        $query              = $select->insertFromSelect($idxTable);
        $adapter->query($query);
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
