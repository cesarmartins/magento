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
 * Tax helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Tax extends Mage_Tax_Helper_Data
{
    
    /**
     * Get product price with all tax settings processing
     *
     * @param Mage_Catalog_Model_Product $product
     * @param float $price
     * @param bool $includingTax
     * @param null|Mage_Customer_Model_Address $shippingAddress
     * @param null|Mage_Customer_Model_Address $billingAddress
     * @param null|int $ctc
     * @param null|Mage_Core_Model_Store $store
     * @param bool $priceIncludesTax
     * @param bool $roundPrice
     * @return float
     */
    public function getPrice(
        $product, 
        $price, 
        $includingTax = null, 
        $shippingAddress = null, 
        $billingAddress = null,
        $ctc = null, 
        $store = null, 
        $priceIncludesTax = null, 
        $roundPrice = true
    ) {
        if (!$price) {
            return $price;
        }

        $store = Mage::app()->getStore($store);

        if (!$this->needPriceConversion($store)) {
            return $price;
        }

        return parent::getPrice(
            $product, 
            $price, 
            $includingTax, 
            $shippingAddress, 
            $billingAddress, 
            $ctc, 
            $store, 
            $priceIncludesTax, 
            $roundPrice
        );
    }
}
