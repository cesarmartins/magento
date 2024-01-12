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
 * Product inventory tab renderer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Catalog_Product_Edit_Tab_Inventory_Renderer 
    extends MP_Warehouse_Block_Adminhtml_Catalog_Product_Edit_Tab_Renderer_Abstract 
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Stocks items
     * 
     * @var array
     */
    protected $_stocksItems;
    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->setTemplate('warehouse/catalog/product/edit/tab/inventory/renderer.phtml');
    }
    /**
     * Is readonly stock
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getProduct()->getInventoryReadonly();
    }
    /**
     * Get stocks items
     * 
     * @return array
     */
    public function getStocksItems()
    {
        if (is_null($this->_stocksItems)) {
            if (!$this->isNew()) {
                $stocksItems = $this->getWarehouseHelper()
                    ->getCatalogInventoryHelper()
                    ->getStockItemCollection($this->getProductId());
                $this->_stocksItems = array();
                foreach ($stocksItems as $stockItem) {
                    $this->_stocksItems[$stockItem->getStockId()] = $stockItem;
                }
            } else $this->_stocksItems = array();
        }

        return $this->_stocksItems;
    }
    /**
     * Get stock item
     * 
     * @param int $stockId
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Stock_Item | null
     */
    public function getStockItem($stockId)
    {
        $stocksItems = $this->getStocksItems();
        return isset($stocksItems[$stockId]) ? $stocksItems[$stockId] : null;
    }
    /**
     * Get default stock item field value
     * 
     * @param string $field
     * 
     * @return mixed
     */
    public function getDefaultConfigValue($field)
    {
        return Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_ITEM.$field);
    }
    /**
     * Get stock item field value
     * 
     * @param MP_Warehouse_Model_Cataloginventory_Stock_Item $stockItem
     * @param string $field
     * 
     * @return mixed
     */
    public function getFieldValue($stockItem = null, $field)
    {
        if ($stockItem) {
            return $stockItem->getDataUsingMethod($field);
        }

        return $this->getDefaultConfigValue($field);
    }
    /**
     * Sort values function
     *
     * @param mixed $a
     * @param mixed $b
     * 
     * @return int
     */
    protected function sortValues($a, $b)
    {
        if ($a['stock_id'] != $b['stock_id']) {
            return $a['stock_id'] < $b['stock_id'] ? -1 : 1;
        }

        return 0;
    }
    /**
     * Get values
     * 
     * @return array
     */
    public function getValues()
    {
        $values     = array();
        $data       = array();
        $stockIds   = $this->getWarehouseHelper()->getCatalogInventoryHelper()->getStockIds();
        if (count($stockIds)) {
            $keys = Mage::helper('cataloginventory/data')->getConfigItemOptions();
            foreach ($stockIds as $stockId) {
                $stockItem = $this->getStockItem($stockId);
                $item = array(
                    'stock_id'               => $stockId, 
                    'qty'                    => $this->getFieldValue($stockItem, 'qty') * 1, 
                    'original_inventory_qty' => $this->getFieldValue($stockItem, 'qty') * 1, 
                    'is_qty_decimal'         => ($stockItem) ? $this->getFieldValue($stockItem, 'is_qty_decimal') * 1 : 0, 
                    'is_in_stock'            => ($stockItem) ? $this->getFieldValue($stockItem, 'is_in_stock') * 1 : 0, 
                );
                foreach ($keys as $key) {
                    $useConfigKey = 'use_config_'.$key;
                    $item[$key] = floatval($this->getFieldValue($stockItem, $key)) * 1;
                    $item[$useConfigKey] = (!$stockItem || $this->getFieldValue($stockItem, $useConfigKey)) ? 1 : 0;
                }

                array_push($data, $item);
            }
        }

        if (is_array($data)) {
            usort($data, array($this, 'sortValues')); 
            $values = $data;
        }

        return $values;
    }
    /**
     * Get backorders values
     * 
     * @return array
     */
    public function getBackordersValues()
    {
        return Mage::getSingleton('cataloginventory/source_backorders')->toOptionArray();
    }
    /**
     * Get is in stock values
     * 
     * @return array
     */
    public function getIsInStockValues()
    {
        return Mage::getSingleton('cataloginventory/source_stock')->toOptionArray();
    }
}
