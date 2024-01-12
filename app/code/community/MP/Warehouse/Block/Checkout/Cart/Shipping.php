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
 * Cart shipping block
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Checkout_Cart_Shipping 
    extends Mage_Checkout_Block_Cart_Shipping
{
    /**
     * Shipping rates
     * 
     * @var array
     */
    protected $_rates2;
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
     * Get addresses
     * 
     * @return array of MP_Warehouse_Model_Sales_Quote_Address
     */
    public function getAddresses()
    {
        return $this->getQuote()->getAllShippingAddresses();
    }
    /**
     * Get shipping rates
     * 
     * @param int $stockId
     * 
     * @return array
     */
    public function getShippingRates2($_stockId)
    {
        if (is_null($this->_rates2)) {
            $this->getAddress()->collectShippingRates()->save();
            $rates = array();
            foreach ($this->getAddresses() as $address) {
                $stockId = (int) $address->getStockId();
                if ($stockId) {
                    $rates[$stockId] = $address->getGroupedAllShippingRates();
                }
            }
            $this->_rates2 = $rates;
        }

        if (isset($this->_rates2[$_stockId])) {
            return $this->_rates2[$_stockId];
        } else {
            return array();
        }
    }

    public function isShippingRatesEmpty($stockId)
    {
        $isEmpty = !count($this->getShippingRates2($stockId));
        return $isEmpty;
    }

    /**
     * Get address shipping method
     * 
     * @param int $stockId
     * 
     * @return string
     */
    public function getAddressShippingMethod2($stockId)
    {
        $shippingAddress = $this->getQuote()->getShippingAddress2($stockId);
        if ($shippingAddress) {
            return $shippingAddress->getShippingMethod();
        } else {
            return null;
        }
    }
    /**
     * Get shipping price
     * 
     * @param float $price
     * @param bool $flag
     * 
     * @return float
     */
    public function getShippingPrice2($stockId, $price, $flag)
    {
        $quote = $this->getQuote();
        $shippingAddress = $quote->getShippingAddress2($stockId);
        if ($shippingAddress) {
            $taxHelper = $this->getWarehouseHelper()->getTaxHelper();
            return $quote->getStore()->convertPrice($taxHelper->getShippingPrice($price, $flag, $shippingAddress), true);
        } else {
            return null;
        }
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
