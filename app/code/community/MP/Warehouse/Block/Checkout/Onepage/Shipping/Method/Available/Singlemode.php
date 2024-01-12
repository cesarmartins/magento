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
class MP_Warehouse_Block_Checkout_Onepage_Shipping_Method_Available_Singlemode 
    extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    /**
     * Get shipping method
     * 
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->getAddressShippingMethod();
    }
    /**
     * Get shipping prices 
     */
    public function getShippingPrices()
    {
        $shippingPrices = array();
        foreach ($this->getShippingRates() as $carrierShippingRates) {
            foreach ($carrierShippingRates as $rate) {
                $shippingMethodCode = $rate->getCode();
                $price = (float) $rate->getPrice();
                $shippingPrices[$shippingMethodCode] = $price;
            }
        }

        return $shippingPrices;
    }
    /**
     * Get shipping prices JSON
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
        $price = null;
        $shippingMethod = $this->getShippingMethod();
        if ($shippingMethod) {
            foreach ($this->getShippingRates() as $carrierShippingRates) {
                foreach ($carrierShippingRates as $rate) {
                    $shippingMethodCode = $rate->getCode();
                    if ($shippingMethodCode == $shippingMethod) {
                        $price = (float) $rate->getPrice();
                        break 2;
                    }
                }
            }
        }

        return $price;
    }
    /**
     * Get current shipping price JS
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
