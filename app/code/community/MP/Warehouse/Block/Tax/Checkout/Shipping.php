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
 * Shipping row renderer
 * 
 * @category    MP
 * @package     MP_Warehouse
 * @author      Mage Plugins Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Tax_Checkout_Shipping 
    extends Mage_Tax_Block_Checkout_Shipping
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
     * Get shipping amount include tax
     *
     * @return float
     */
    public function getShippingIncludeTax()
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        $quote                  = $this->getQuote();
        $storeId                = $helper->getCurrentStoreId($quote);
        if ($config->isMultipleMode($storeId) && 
            $config->isSplitOrderEnabled($storeId)
        ) {
            $shippingAmount         = 0;
            foreach ($quote->getAllShippingAddresses() as $address) {
                $shippingAmount         += $address->getShippingInclTax();
            }

            return $shippingAmount;
        } else {
            return parent::getShippingIncludeTax();
        }
    }
    /**
     * Get shipping amount exclude tax
     *
     * @return float
     */
    public function getShippingExcludeTax()
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        $quote                  = $this->getQuote();
        $storeId                = $helper->getCurrentStoreId($quote);
        if ($config->isMultipleMode($storeId) && 
            $config->isSplitOrderEnabled($storeId)
        ) {
            $shippingAmount         = 0;
            foreach ($quote->getAllShippingAddresses() as $address) {
                $shippingAmount         += $address->getShippingAmount();
            }

            return $shippingAmount;
        } else {
            return parent::getShippingExcludeTax();
        }
    }
    /**
     * Get label for shipping include tax
     *
     * @return float
     */
    public function getIncludeTaxLabel()
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        $quote                  = $this->getQuote();
        $storeId                = $helper->getCurrentStoreId($quote);
        if ($config->isMultipleMode($storeId) && 
            $config->isSplitOrderEnabled($storeId)
        ) {
            $labels                 = array();
            foreach ($quote->getAllShippingAddresses() as $address) {
                $labels[$address->getShippingMethod()] = $address->getShippingDescription();
            }

            return $this->escapeHtml($this->helper('tax')->__('Shipping Incl. Tax (%s)', implode(' & ', $labels)));
        } else {
            return parent::getIncludeTaxLabel();
        }
    }
    /**
     * Get label for shipping exclude tax
     * 
     * @return float
     */
    public function getExcludeTaxLabel()
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        $quote                  = $this->getQuote();
        $storeId                = $helper->getCurrentStoreId($quote);
        if ($config->isMultipleMode($storeId) && 
            $config->isSplitOrderEnabled($storeId)
        ) {
            $labels                 = array();
            foreach ($quote->getAllShippingAddresses() as $address) {
                $labels[$address->getShippingMethod()] = $address->getShippingDescription();
            }

            return $this->escapeHtml($this->helper('tax')->__('Shipping Excl. Tax (%s)', implode(' & ', $labels)));
        } else {
            return parent::getExcludeTaxLabel();
        }
    }
}
