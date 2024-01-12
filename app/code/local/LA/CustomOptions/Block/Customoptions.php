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
 * Customoptions Block
 * 
 * @category    Magestore
 * @package     Magestore_CustomOptions
 * @author      Magestore Developer
 */
class LA_CustomOptions_Block_Customoptions extends Mage_Catalog_Block_Product_View_Options_Abstract
{
    /**
     * prepare block's layout
     *
     * @return LA_CustomOptions_Block_Customoptions
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getFormatedPriceCustomName()
    {
        $priceName = Mage::helper('customoptions')->getSettingPriceName();
        return Mage::helper('core')->currency($priceName, true, false);
    }

    public function getFormatedPriceCustomNumber()
    {
        $priceNumber = Mage::helper('customoptions')->getSettingPriceNumber();
        return Mage::helper('core')->currency($priceNumber, true, false);
    }

    public function checkAttributeProduct($productId = null){
        $check = false;

        $isSettingEnable = Mage::helper('customoptions')->isSettingEnble();
        $isModuleEnabled = Mage::helper('customoptions')->isModuleEnabled();
        if (empty($isSettingEnable) || !$isModuleEnabled){
            return $check;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        $valueAttribute = $product->getData('product_option');
        if (!empty($valueAttribute)) {
            $check = true;
        }
        return $check;
    }
}