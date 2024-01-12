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
 * Checkout helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Checkout
    extends MP_Warehouse_Helper_Core_Abstract
{
    /**
     * Get quote
     * 
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->getCoreHelper()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        } else {
            return Mage::getSingleton('checkout/session')->getQuote();
        }
    }
    /**
     * Get quote address
     * 
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getQuoteAddress()
    {
        $quote = $this->getQuote();
        if ($quote->isVirtual()) {
            return $quote->getBillingAddress();
        } else {
            return $quote->getShippingAddress();
        }
    }
    /**
     * Get full controller names
     * 
     * @return array
     */
    public function getFullControllerNames()
    {
        return array(
            'checkout_onepage', 
            'adminhtml_sales_order_create', 
            'adminhtml_sales_order_edit', 
        );
    }
}
