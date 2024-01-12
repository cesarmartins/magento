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
 * One page checkout multiple mode shipping method
 *
 * @category    MP
 * @package     MP_Warehouse
 * @author      Mage Plugins Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Checkout_Onepage_Shipping_Method_Available_Multiplemode 
    extends Mage_Checkout_Block_Onepage_Abstract
{
    /**
     * Rates
     * @var array 
     */
    protected $_rates;
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
     * Get warehouses
     * 
     * @return array of MP_Warehouse_Model_Warehouse
     */
    public function getWarehouses()
    {
        return $this->getQuote()->getWarehouses();
    }
    /**
     * Get addresses
     * 
     * @return array of MP_Warehouse_Model_Sales_Quote_Address
     */
    public function getAddresses()
    {
        return $this->getQuote()->getAllShippingAddresses();
    }
    /**
     * Get shipping method
     * 
     * @param int $stockId
     * @return string
     */
    public function getShippingMethod($stockId)
    {
        $shippingAddress = $this->getQuote()->getShippingAddress2($stockId);
        if ($shippingAddress) {
            return $shippingAddress->getShippingMethod();
        } else {
            return null;
        }
    }
    /**
     * Get shipping rates
     * 
     * @param int $stockId
     * @return array
     */
    public function getShippingRates($stockId)
    {
        if (is_null($this->_rates)) {
            $this->getQuote()->getShippingAddress()->collectShippingRates()->save();
            $rates = array();
            foreach ($this->getAddresses() as $address) {
                if ($address->getStockId() && !$address->isVirtual()) {
                    $rates[$address->getStockId()] = $address->getGroupedAllShippingRates();
                }
            }

            $this->_rates = $rates;
        }

        if (isset($this->_rates[$stockId])) {
            return $this->_rates[$stockId];
        } else {
            return array();
        }
    }
    /**
     * Get shipping price
     * 
     * @param float $price
     * @param bool $flag
     * @return float
     */
    public function getShippingPrice($stockId, $price, $flag)
    {
        $shippingAddress = $this->getQuote()->getShippingAddress2($stockId);
        if ($shippingAddress) {
            $taxHelper  = Mage::helper('tax');
            $store      = $this->getQuote()->getStore();
            return $store->convertPrice($taxHelper->getShippingPrice($price, $flag, $shippingAddress), true);
        } else {
            return null;
        }
    }
    /**
     * Get carrier name
     * 
     * @param string $carrierCode
     * 
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        return $this->getWarehouseHelper()->getShippingHelper()->getCarrierName($carrierCode);
    }
    /**
     * Get shipping prices
     * 
     * @return array
     */
    public function getShippingPrices()
    {
        $shippingPrices = array();
        foreach ($this->getAddresses() as $shippingAddress) {
            if ($shippingAddress->isVirtual()) {
                continue;
            }

            $stockId = (int) $shippingAddress->getStockId();
            if (!isset($shippingPrices[$stockId])) {
                $shippingRates = $this->getShippingRates($stockId);
                foreach ($shippingRates as $carrierShippingRates) {
                    foreach ($carrierShippingRates as $rate) {
                        $shippingMethodCode = $rate->getCode();
                        $price = (float) $rate->getPrice();
                        $shippingPrices[$stockId][$shippingMethodCode] = $price;
                    }
                }
            }
        }

        return $shippingPrices;
    }
    /**
     * Get shipping prices JSON
     * 
     * @return string
     */
    public function getShippingPricesJSON()
    {
        return Mage::helper('core')->jsonEncode($this->getShippingPrices());
    }
    /**
     * Get current shipping price
     * 
     * @return float
     */
    public function getCurrentShippingPrice()
    {
        $price = array();
        foreach ($this->getAddresses() as $shippingAddress) {
            if ($shippingAddress->isVirtual()) {
                continue;
            }

            $stockId = (int) $shippingAddress->getStockId();
            if (!isset($price[$stockId])) {
                $shippingMethod = $this->getShippingMethod($stockId);
                $shippingRates = $this->getShippingRates($stockId);
                foreach ($shippingRates as $carrierShippingRates) {
                    foreach ($carrierShippingRates as $rate) {
                        $shippingMethodCode = $rate->getCode();
                        if ($shippingMethodCode == $shippingMethod) {
                            $price[$stockId] = (float) $rate->getPrice();
                            break 2;
                        }
                    }
                }
            }
        }

        return $price;
    }
    /**
     * Get current shipping price JS
     * 
     * @return string
     */
    public function getCurrentShippingPriceJS()
    {
        return Mage::helper('core')->jsonEncode($this->getCurrentShippingPrice());
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
        return $this->getWarehouseHelper()->getCustomerAddressStockDistanceString($stockId);
    }
}
