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
 * Quote address item
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Sales_Quote_Address_Item
    extends Mage_Sales_Model_Quote_Address_Item
{
    /**
     * Stock item model
     *
     * @var Mage_CatalogInventory_Model_Stock_Item
     */
    protected $_stockItem;
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
     * Get default stock identifier
     */
    public function getDefaultStockId()
    {
        return $this->getWarehouseHelper()->getDefaultStockId();
    }
    /**
     * Retrieve stock identifier
     * 
     * @return int
     */
    public function getStockId()
    {
        $quote = $this->getQuote();
        if ($quote) {
            $address = $quote->getAddress();
            if ($address && $address->getStockId()) {
                return $address->getStockId();
            } else {
                return $this->getDefaultStockId();
            }
        } else {
            return $this->getDefaultStockId();
        }
    }
    /**
     * Get stock item
     * 
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    public function getStockItem()
    {
        if (!$this->_stockItem) {
            $this->_stockItem = $this->getWarehouseHelper()->getCatalogInventoryHelper()->getStockItem($this->getStockId());
            $this->_stockItem->assignProduct($this->getProduct());
        }

        return $this->_stockItem;
    }
}
