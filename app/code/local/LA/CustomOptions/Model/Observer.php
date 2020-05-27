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
 * CustomOptions Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_CustomOptions
 * @author      Magestore Developer
 */
class LA_CustomOptions_Model_Observer
{
    /**
     * process controller_action_predispatch event
     *
     * @return LA_CustomOptions_Model_Observer
     */
    public function addCustomproduct($observer)
    {
        $isSettingEnable = Mage::helper('customoptions')->isSettingEnble();
        $isModuleEnabled = Mage::helper('customoptions')->isModuleEnabled();
        if (empty($isSettingEnable) || !$isModuleEnabled){
            return $this;
        }

        try{
            $dataOption = Mage::app()->getRequest()->getParams();
            if (!$dataOption['options_name'] && !$dataOption['options_number']) {
                return $this;
            }
            $quoteItem = $observer->getQuoteItem();
            $product_id = $quoteItem->getProduct()->getId();
            $priceName = 0;
            $priceNumber = 0;
            $value = array();
            if ($dataOption['options_name']) {
                $priceName = Mage::helper('customoptions')->getSettingPriceName();
                $nameOption = array(
                    'label'                    => 'Nome',
                    'option_value'             => $dataOption['options_name'],
                    'value'                    => $dataOption['options_name'].' +'.Mage::helper('core')->currency($priceName, true, false),
                );
                $value['name_options'] = $nameOption;
            }
            if ($dataOption['options_number']) {
                $priceNumber = Mage::helper('customoptions')->getSettingPriceNumber();
                $numberOption = array(
                    'label'                    => 'Número',
                    'option_value'             => $dataOption['options_number'],
                    'value'                    => $dataOption['options_number'].' +'.Mage::helper('core')->currency($priceNumber, true, false),
                );
                $value['number_options'] = $numberOption;
            }

            $value = serialize($value);
            $quoteItem->addOption(array('code'=> 'additional_options', 'product_id'=> $product_id, 'value'=> $value));

            //add price option to product
            $extraPrice = $priceName + $priceNumber;
            $customPrice = $quoteItem->getProduct()->getFinalPrice() + $extraPrice;
            $quoteItem->setOriginalCustomPrice($customPrice);
            $quoteItem->setCustomPrice($customPrice);
        }catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;

    }

    public function salesConvertQuoteItemToOrderItem(Varien_Event_Observer $observer){
        $isSettingEnable = Mage::helper('customoptions')->isSettingEnble();
        $isModuleEnabled = Mage::helper('customoptions')->isModuleEnabled();
        if (empty($isSettingEnable) || !$isModuleEnabled){
            return $this;
        }

        try{
            $quoteItem = $observer->getItem();
            if ($additionalOptions = $quoteItem->getOptionByCode('additional_options'))
            {
                $orderItem = $observer->getOrderItem();
                $options = $orderItem->getProductOptions();
                $options['additional_options'] = unserialize($additionalOptions->getValue());
                $orderItem->setProductOptions($options);
            }
        }catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;

    }
}