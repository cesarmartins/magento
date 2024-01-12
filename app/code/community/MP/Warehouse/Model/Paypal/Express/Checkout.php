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
 * Paypal Express checkout
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */

class MP_Warehouse_Model_Paypal_Express_Checkout_Abstract 
        extends Mage_Paypal_Model_Express_Checkout
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
     * Get version helper
     * 
     * @return MP_Warehouse_Helper_Core_Version
     */
    protected function getVersionHelper()
    {
        return $this->getWarehouseHelper()
            ->getVersionHelper();
    }
    /**
     * Initialize checkout
     * 
     * @return Mage_Paypal_Model_Express_Checkout
     */
    protected function _init()
    {
        $quote                  = $this->_quote;
        $quote->reapplyStocks();
        $quote->getShippingAddress()
            ->setCollectShippingRates(true);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        return $this;
    }
    /**
     * Update quote when returned from PayPal
     * rewrite billing address by paypal
     * save old billing address for new customer
     * export shipping address in case address absence
     *
     * @param string $token
     */
    public function returnFromPaypal($token)
    {
        $this->_init();
        return parent::returnFromPaypal($token);
    }
    /**
     * Check whether order review has enough data to initialize
     *
     * @param $token
     * 
     * @throws Mage_Core_Exception
     */
    public function prepareOrderReview($token = null)
    {
        $this->_init();
        return parent::prepareOrderReview($token);
    }

    /**
     * Reserve order ID for specified quote and start checkout on PayPal
     *
     * @param string $returnUrl
     * @param string $cancelUrl
     * @param bool|null $button
     *
     * @return mixed
     */
    public function start($returnUrl, $cancelUrl, $button = null)
    {
        $this->_init();
        return parent::start($returnUrl, $cancelUrl, $button);
    }
}
