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
 * Attribute layer filter resource
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Catalog_Layer_Filter_Attribute 
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
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
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @param int $value
     * 
     * @return Mage_Catalog_Model_Resource_Layer_Filter_Attribute
     */
    public function applyFilterToCollection($filter, $value)
    {
        $collection         = $filter->getLayer()->getProductCollection();
        $attribute          = $filter->getAttributeModel();
        $connection         = $this->_getReadAdapter();
        $tableAlias         = $attribute->getAttributeCode() . '_idx';
        
        $conditions         = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()), 
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()), 
            $connection->quoteInto(
                "{$tableAlias}.stock_id = ?", $this->getWarehouseHelper()
                ->getProductHelper()
                ->getCollectionStockId($collection)
            ), 
            $connection->quoteInto("{$tableAlias}.value = ?", $value)
        );
        $collection
            ->getSelect()
            ->join(array($tableAlias => $this->getMainTable()), implode(' AND ', $conditions), array());
        return $this;
    }
    /**
     * Retrieve array with products counts per attribute option
     * 
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * 
     * @return array
     */
    public function getCount($filter)
    {
        $collection     = $filter->getLayer()->getProductCollection();
        $select         = clone $collection->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $connection     = $this->_getReadAdapter();
        $attribute      = $filter->getAttributeModel();
        $tableAlias     = sprintf('%s_idx', $attribute->getAttributeCode());
        $conditions     = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()), 
            $connection->quoteInto("{$tableAlias}.store_id = ?", $filter->getStoreId()), 
            $connection->quoteInto(
                "{$tableAlias}.stock_id = ?", $this->getWarehouseHelper()
                ->getProductHelper()
                ->getCollectionStockId($collection)
            ), 
        );
        $select
            ->join(
                array($tableAlias => $this->getMainTable()),
                join(' AND ', $conditions),
                array('value', 'count' => new Zend_Db_Expr("COUNT({$tableAlias}.entity_id)"))
            )
            ->group("{$tableAlias}.value");
        return $connection->fetchPairs($select);
    }
}
