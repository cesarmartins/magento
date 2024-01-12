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
 * Order create items grid
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Sales_Order_Create_Items_Grid 
    extends Mage_Adminhtml_Block_Sales_Order_Create_Items_Grid
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
     * Get subtotal
     * 
     * @return float
     */
    public function getSubtotal()
    {
        $helper     = $this->getWarehouseHelper();
        $config     = $helper->getConfig();
        if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
            $subtotal = 0;
            foreach ($this->getQuote()->getAllShippingAddresses() as $address) {
                if ($this->displayTotalsIncludeTax()) {
                    if ($helper->getVersionHelper()->isGe1800()) {
                        if ($this->getIsPriceInclTax()) {
                            $subtotal += $address->getSubtotalInclTax();
                        } else {
                            $subtotal += $address->getSubtotal() + $address->getTaxAmount();
                        }
                    } else {
                        if ($address->getSubtotalInclTax()) {
                            $subtotal += $address->getSubtotalInclTax();
                        } else {
                            $subtotal += $address->getSubtotal() + $address->getTaxAmount();
                        }
                    }
                } else {
                    if ($helper->getVersionHelper()->isGe1800()) {
                        if ($this->getIsPriceInclTax()) {
                            return $address->getSubtotalInclTax() - $address->getTaxAmount();
                        } else {
                            return $address->getSubtotal();
                        }
                    } else {
                        $subtotal += $address->getSubtotal();
                    }
                }
            }

            return $subtotal;
        } else {
            return parent::getSubtotal();
        }
    }
    /**
     * Get discount
     * 
     * @return float
     */
    public function getDiscountAmount()
    {
        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
            $discount = 0;
            foreach ($this->getQuote()->getAllShippingAddresses() as $address) {
                $discount += $address->getDiscountAmount();
            }

            return $discount;
        } else {
            return parent::getDiscountAmount();
        }
    }
    /**
     * Get subtotal with discount
     * 
     * @return float
     */
    public function getSubtotalWithDiscount()
    {
        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
            return $this->getSubtotal() + $this->getDiscountAmount();
        } else {
            return parent::getSubtotalWithDiscount();
        }
    }
}
