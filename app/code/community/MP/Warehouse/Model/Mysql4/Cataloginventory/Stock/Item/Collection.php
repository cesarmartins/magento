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
 * Stock item collection
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Cataloginventory_Stock_Item_Collection
    extends Mage_CatalogInventory_Model_Mysql4_Stock_Item_Collection
{
    /**
     * Get warehouse helper
     *
     * @return MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Join Stock Status to collection
     * 
     * @param int | null $storeId
     * 
     * @return MP_Warehouse_Model_Mysql4_Cataloginventory_Stock_Item_Collection
     */
    public function joinStockStatus($storeId = null)
    {
        if ($this->getWarehouseHelper()->getConfig()->isMultipleMode() && !$this->hasFlag('isStockFilterSet')) {
            $isStockManagedInConfig = (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
            $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
            $connection = $this->getConnection();
            $select = $this->getSelect();
            $select->joinLeft(
                array('status_table' => $this->getTable('cataloginventory/stock_status')), 
                '(main_table.product_id = status_table.product_id) AND '.
                '(main_table.stock_id = status_table.stock_id) AND '.
                '(status_table.website_id = '.$connection->quote($websiteId).')', 
                array('stock_status')
            );
            $conditionSelect = $connection->select();
            $conditionSelect->from(array('item_table' => $this->getTable('cataloginventory/stock_item')), 'item_id');
            $conditionSelect->joinLeft(
                array('item_status_table' => $this->getTable('cataloginventory/stock_status')), 
                '(item_table.product_id=item_status_table.product_id) AND '.
                '(item_table.stock_id=item_status_table.stock_id) AND '.
                '(item_status_table.website_id='.$connection->quote($websiteId).')', 
                array()
            );
            $conditionSelect->order(
                array(
                'item_status_table.stock_status DESC', 
                '(IF(IF(item_table.use_config_manage_stock, '.$connection->quote($isStockManagedInConfig).', item_table.manage_stock), '.
                    'item_table.is_in_stock, 1)) DESC'
                )
            );
            $conditionSelect->where('item_table.product_id = main_table.product_id');
            $conditionSelect->limit(1);
            $select->where('main_table.item_id = ('.$conditionSelect->assemble().')');
            return $this;
        } else {
            return parent::joinStockStatus($storeId);
        }
    }
    /**
     * Add in stock filter
     *
     * @param mixed $isStockManagedInConfig
     * 
     * @return  MP_Warehouse_Model_Mysql4_Cataloginventory_Stock_Item_Collection
     */
    public function addInStockFilter($isStockManagedInConfig)
    {
        $isStockManagedInConfig = (int) $isStockManagedInConfig;
        $this->getSelect()->where(
            'IF(
            IF(use_config_manage_stock, '.$this->getConnection()->quote($isStockManagedInConfig).', manage_stock), is_in_stock, 1
        ) = 1'
        );
        return $this;
    }
    /**
     * Add stock filter to collection
     *
     * @param mixed $stock
     * 
     * @return MP_Warehouse_Model_Mysql4_Cataloginventory_Stock_Item_Collection
     */
    public function addStockFilter($stock)
    {
        parent::addStockFilter($stock);
        $this->setFlag('isStockFilterSet', true);
        return $this;
    }
}
