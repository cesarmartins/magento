<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_CustomOptions
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * CustomOptions Helper
 * 
 * @category    Magestore
 * @package     Magestore_CustomOptions
 * @author      Magestore Developer
 */
class LA_CustomOptions_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isModuleEnabled(){
        return Mage::helper('core')->isModuleEnabled('LA_CustomOptions');
    }

    public function isSettingEnble() {
        $store = Mage::app()->getStore(); // store info
        $configValue = Mage::getStoreConfig('customoptions/general/enable', $store);

        return $configValue;
    }

    public function getSettingPriceName() {
        $priceName = 0;
        $store = Mage::app()->getStore(); // store info
        if (Mage::getStoreConfig('customoptions/general/price_name', $store)) {
            $priceName = Mage::getStoreConfig('customoptions/general/price_name', $store);
        }

        return $priceName;
    }

    public function getSettingPriceNumber() {
        $priceName = 0;
        $store = Mage::app()->getStore(); // store info
        if (Mage::getStoreConfig('customoptions/general/price_number', $store)) {
            $priceName = Mage::getStoreConfig('customoptions/general/price_number', $store);
        }

        return $priceName;
    }
}