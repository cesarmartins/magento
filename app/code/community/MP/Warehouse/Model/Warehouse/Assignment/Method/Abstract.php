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
 * Abstact warehouse assignment method
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Warehouse_Assignment_Method_Abstract 
    extends Varien_Object
{
    /**
     * Quote
     * 
     * @var MP_Warehouse_Model_Sales_Quote
     */
    protected $_quote;
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
     * Get quote
     * 
     * @return MP_Warehouse_Model_Sales_Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }
    /**
     * Set quote
     * 
     * @param MP_Warehouse_Model_Sales_Quote $quote
     * 
     * @return MP_Warehouse_Model_Warehouse_Assignment_Method_Multiple_Abstract
     */
    public function setQuote($quote)
    {
        $this->_quote = $quote;
        return $this;
    }
    /**
     * Check if assignment method is active
     * 
     * @return bool
     */
    public function isActive()
    {
        $flag = $this->getData('active');
        if (!empty($flag) && ($flag !== 'false')) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Check if assignment method is based on shipping
     * 
     * @return bool
     */
    public function isBasedOnShipping()
    {
        $flag = $this->getData('based_on_shipping');
        if (!empty($flag) && ($flag !== 'false')) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Get title
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->getWarehouseHelper()->__($this->getData('title'));
    }
    /**
     * Get description
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->getWarehouseHelper()->__($this->getData('description'));
    }
    /**
     * Get store
     * 
     * @return Mage_Core_Model_Store
     */
    protected function getStore()
    {
        $store = null;
        $quote = $this->getQuote();
        if ($quote) {
            $store = $quote->getStore();
        }

        if (!$store) {
            $store = Mage::app()->getStore();
        }

        return $store;
    }
    /**
     * Get store identifier
     * 
     * @return int
     */
    protected function getStoreId()
    {
        return $this->getStore()->getId();
    }
    /**
     * Get customer group id
     * 
     * @return int
     */
    protected function getCustomerGroupId()
    {
        $customerGroupId = null;
        $quote = $this->getQuote();
        if ($quote) {
            $customerGroupId = $quote->getCustomerGroupId();
        }

        if (!$customerGroupId) {
            $customerGroupId = $this->getWarehouseHelper()
                ->getCustomerHelper()
                ->getCustomerGroupId();
        }

        return $customerGroupId;
    }
    /**
     * Get currency code
     * 
     * @return string
     */
    protected function getCurrencyCode()
    {
        $currencyCode = null;
        $quote = $this->getQuote();
        if ($quote) {
            $currencyCode = $quote->getQuoteCurrencyCode();
        }

        if (!$currencyCode) {
            $currencyCode = $this->getWarehouseHelper()
                ->getCurrencyHelper()
                ->getCurrentCode();
        }

        return $currencyCode;
    }
    /**
     * Get customer address
     * 
     * @return Varien_Object
     */
    protected function getCustomerAddress()
    {
        $helper         = $this->getWarehouseHelper();
        $address        = null;
        $addressHelper  = $helper->getAddressHelper();
        $quote          = $this->getQuote();
        if ($quote) {
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingAddress && !$addressHelper->isEmpty($shippingAddress)) {
                $address = $addressHelper->cast($shippingAddress);
            }
        }

        if (!$address || $addressHelper->isEmpty($address)) {
            $customerLocatorHelper = $helper->getCustomerLocatorHelper();
            $customerAddress = $customerLocatorHelper->getCustomerAddress();
            $address = $addressHelper->cast($customerAddress);
        }

        return $address;
    }
    /**
     * Apply quote stock items
     * 
     * @param MP_Warehouse_Model_Sales_Quote $quote
     * 
     * @return MP_Warehouse_Model_Warehouse_Assignment_Method_Abstract
     */
    public function applyQuoteStockItems($quote = null)
    {
        return $this;
    }
    /**
     * Get stock identifier
     * 
     * @return int|null
     */
    public function getStockId()
    {
        return null;
    }
    /**
     * Get product stock identifier
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return int|null
     */
    public function getProductStockId($product)
    {
        return null;
    }
}
