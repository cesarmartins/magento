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
 * Shipping total row renderer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Sales_Order_Create_Totals_Shipping 
    extends Mage_Adminhtml_Block_Sales_Order_Create_Totals_Shipping
{
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
     * Get quote
     * 
     * @return MP_Warehouse_Model_Sales_Quote
     */
    protected function _getQuote()
    {
        return $this->getTotal()->getAddress()->getQuote();
    }
    /**
     * Get shipping amount include tax
     *
     * @return float
     */
    public function getShippingIncludeTax()
    {
        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
            $shippingAmount = 0;
            foreach ($this->_getQuote()->getAllShippingAddresses() as $address) {
                $shippingAmount += $address->getShippingAmount() + $address->getShippingTaxAmount();
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
        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
            $shippingAmount = 0;
            foreach ($this->_getQuote()->getAllShippingAddresses() as $address) {
                $shippingAmount += $address->getShippingAmount();
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
        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
            $labels = array();
            foreach ($this->_getQuote()->getAllShippingAddresses() as $address) {
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
        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
            $labels = array();
            foreach ($this->_getQuote()->getAllShippingAddresses() as $address) {
                $labels[$address->getShippingMethod()] = $address->getShippingDescription();
            }

            return $this->escapeHtml($this->helper('tax')->__('Shipping Excl. Tax (%s)', implode(' & ', $labels)));
        } else {
            return parent::getExcludeTaxLabel();
        }
    }
}
