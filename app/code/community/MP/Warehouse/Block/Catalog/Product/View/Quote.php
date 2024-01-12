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
 * Product quote block
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Catalog_Product_View_Quote 
    extends Mage_Core_Block_Template
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
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('warehouse/catalog/product/view/quote.phtml');
    }
    /**
     * Get product
     * 
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        $key = 'product';
        if (!$this->hasData($key)) {
            $this->setData($key, Mage::registry('product'));
        }

        return $this->getData($key);
    }
    /**
     * Get product name
     * 
     * @return string
     */
    public function getProductName()
    {
        return $this->getProduct()->getName();
    }
    /**
     * Convert price
     * 
     * @param float $price
     * 
     * @return float
     */
    public function convertPrice($price)
    {
        return $this->getProduct()->getStore()->convertPrice($price, true, false);
    }
    /**
     * Format price
     * 
     * @param float $price
     * 
     * @return float
     */
    public function formatPrice($price)
    {
        return $this->getProduct()->getStore()->formatPrice($price, false);
    }
    /**
     * Check if product is virtual
     * 
     * @return boolean
     */
    public function isVirtual()
    {
        $productHelper  = $this->getWarehouseHelper()->getProductHelper();
        $product        = $this->getProduct();
        return ($productHelper->isVirtual($product) || $productHelper->isDownloadable($product)) ? true : false;
    }
    /**
     * Get buy request
     * 
     * @return Varien_Object
     */
    public function getBuyRequest()
    {
        $key = 'buy_request';
        if (!$this->hasData($key)) {
            $buyRequest = $this->getWarehouseHelper()
                ->getProductHelper()
                ->getBuyRequest($this->getProduct());
            $this->setData($key, $buyRequest);
        }

        return $this->getData($key);
    }
    /**
     * Get stock ids
     * 
     * @return array
     */
    public function getStockIds()
    {
        $key = 'stock_ids';
        if (!$this->hasData($key)) {
            $stockIds   = array();
            $helper     = $this->getWarehouseHelper();
            $config     = $helper->getConfig();
            if (!$config->isCatalogOutOfStockVisible()) {
                $stockIds = $helper->getProductHelper()->getQuoteInStockStockIds($this->getProduct(), $this->getBuyRequest());
            } else {
                $stockIds = $helper->getStockIds();
            }

            $this->setData($key, $stockIds);
        }

        return $this->getData($key);
    }
    /**
     * Get current stock identifier
     * 
     * @return int
     */
    public function getCurrentStockId()
    {
        return $this->getWarehouseHelper()
            ->getProductHelper()
            ->getCurrentStockId($this->getProduct());
    }
    /**
     * Check if adjustment is allowed
     * 
     * @return bool
     */
    public function isAllowAdjustment()
    {
        $key = 'is_allow_adjustment';
        if (!$this->hasData($key)) {
            $helper         = $this->getWarehouseHelper();
            $config         = $helper->getConfig();
            $stockIds       = $this->getStockIds();
            $isAllowAdjustment = (
                $config->isAllowAdjustment() && $config->isMultipleMode() && (count($stockIds) > 1)
            ) ? true : false;
            $this->setData($key, $isAllowAdjustment);
        }

        return $this->getData($key);
    }
    /**
     * Get default quote configuration options
     * 
     * @return array
     */
    public function getDefaultQuoteConfigurationOptions()
    {
        return $this->getWarehouseHelper()
            ->getProductHelper()
            ->getDefaultQuoteConfigurationOptions($this->getProduct(), $this->getBuyRequest());
    }
    /**
     * Get formated configuration option value
     * 
     * @param string $optionValue
     * 
     * @return string
     */
    public function getFormatedConfigurationOptionValue($optionValue)
    {
        return $this->getWarehouseHelper()
            ->getProductHelper()
            ->getFormatedConfigurationOptionValue($optionValue);
    }
    /**
     * Get quote
     * 
     * @param int $stockId
     * 
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote($stockId)
    {
        return $this->getWarehouseHelper()
            ->getProductHelper()
            ->getQuote($this->getProduct(), $stockId, $this->getBuyRequest());
    }
    /**
     * Get quote shipping rates
     * 
     * @param int $stockId
     * 
     * @return Mage_Sales_Model_Quote
     */
    public function getQuoteShippingRates($stockId)
    {
        return $this->getWarehouseHelper()
            ->getQuoteHelper()
            ->getGroupedShippingRates($this->getQuote($stockId));
    }
    /**
     * Get quote is in stock
     * 
     * @param int $stockId
     * 
     * @return boolean
     */
    public function getQuoteIsInStock($stockId)
    {
        return $this->getWarehouseHelper()
            ->getProductHelper()
            ->getQuoteIsInStock($this->getProduct(), $stockId, $this->getBuyRequest());
    }
    /**
     * Get quote max qty
     * 
     * @param int $stockId
     * 
     * @return float|null
     */
    public function getQuoteMaxQty($stockId)
    {
        return $this->getWarehouseHelper()
            ->getProductHelper()
            ->getQuoteMaxQty($this->getProduct(), $stockId, $this->getBuyRequest());
    }
    /**
     * Get quote subtotal
     * 
     * @param int $stockId
     * 
     * @return float|null
     */
    public function getQuoteSubtotal($stockId)
    {
        return $this->getWarehouseHelper()
            ->getProductHelper()
            ->getQuoteSubtotal($this->getProduct(), $stockId, $this->getBuyRequest());
    }
    /**
     * Get quote tax amount
     * 
     * @param int $stockId
     * 
     * @return float|null
     */
    public function getQuoteTaxAmount($stockId)
    {
        return $this->getWarehouseHelper()
            ->getProductHelper()
            ->getQuoteTaxAmount($this->getProduct(), $stockId, $this->getBuyRequest());
    }
    /**
     * Get customer address stock distance string
     * 
     * @param int $stockId
     * 
     * @return string
     */
    public function getCustomerAddressStockDistanceString($stockId)
    {
        return $this->getWarehouseHelper()
            ->getCustomerAddressStockDistanceString($stockId);
    }
}
