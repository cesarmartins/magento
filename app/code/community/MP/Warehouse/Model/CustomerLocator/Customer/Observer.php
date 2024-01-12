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
 * Customer observer
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_CustomerLocator_Customer_Observer
{
    /**
     * Get customer locator helper
     *
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    protected function getCustomerLocatorHelper()
    {
        return Mage::helper('warehouse/customerLocator_data');
    }
    /**
     * Customer after login handler
     * 
     * @param     Varien_Event_Observer $observer
     * 
     * @return     MP_Warehouse_Model_CustomerLocator_Observer_Customer
     */
    public function loginAfter(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getModel();
        if ($customer && ($customer instanceof Mage_Customer_Model_Customer)) {
            $this->getCustomerLocatorHelper()->unsetCustomerAddress();
        }

        return $this;
    }
    /**
     * Customer after logout handler
     * 
     * @param     Varien_Event_Observer $observer
     * 
     * @return     MP_Warehouse_Model_CustomerLocator_Observer_Customer
     */
    public function logoutAfter(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if ($customer && ($customer instanceof Mage_Customer_Model_Customer)) {
            $this->getCustomerLocatorHelper()->unsetCustomerAddress();
        }

        return $this;
    }
}
