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
 * Customer locator session
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_CustomerLocator_Session
    extends MP_Warehouse_Model_Core_Session_Abstract
{
    /**
     * Namespace
     * 
     * @var string
     */
    protected $_namespace = 'mp_customerlocator';
    /**
     * Address
     * 
     * @var Varien_Object
     */
    protected $_address;
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
     * Get address helper
     * 
     * @return MP_Warehouse_Helper_Core_Address
     */
    protected function getAddressHelper()
    {
        return $this->getCustomerLocatorHelper()->getAddressHelper();
    }
    /**
     * Get customer helper
     * 
     * @return MP_Warehouse_Helper_Core_Customer
     */
    protected function getCustomerHelper()
    {
        return $this->getCustomerLocatorHelper()->getCustomerHelper();
    }
    /**
     * Get current address
     * 
     * @return Varien_Object
     */
    protected function _getAddress()
    {
        if (is_null($this->_address)) {
            $address = new Varien_Object();
            $address->setCountryId($this->getCountryId());
            $address->setRegionId($this->getRegionId());
            $address->setRegion($this->getRegion());
            $address->setCity($this->getCity());
            $address->setPostcode($this->getPostcode());
            $address->setStreet($this->getStreet());
            $this->_address = $address;
        }

        return $this->_address;
    }
    /**
     * Check if address is empty
     * 
     * @return bool
     */
    public function isAddressEmpty()
    {
        $this->_getAddress();
        return $this->getAddressHelper()->isEmpty($this->_address);
    }
    /**
     * Get ip address
     * 
     * @return string
     */
    public function getIp()
    {
        $ip = $this->getCoreHelper()->getHttpHelper()->getRemoteAddr();
        return ($ip) ? long2ip(ip2long($ip)) : null;
    }
    /**
     * Get geo ip address
     * 
     * @return Varien_Object
     */
    protected function getGeoIpAddress()
    {
        $address        = null;
        $addressHelper  = $this->getAddressHelper();
        $ip             = $this->getIp();
        if (!$ip) {
            return $address;
        }

        $_address = $this->getCustomerLocatorHelper()->getGeoIpHelper()->getAddressByIp($ip);
        if (!$_address) {
            return $address;
        }

        $_address = $addressHelper->cast($_address);
        if (!$addressHelper->isEmpty($_address)) {
            $address = $_address;
        }

        return $address;
    }
    /**
     * Get customer default address
     * 
     * @return Varien_Object
     */
    protected function getCustomerDefaultAddress()
    {
        $address        = null;
        $customerHelper = $this->getCustomerHelper();
        if (!$customerHelper->isLoggedIn()) {
            return $address;
        }

        $_address = $customerHelper->getCustomer()->getDefaultShippingAddress();
        if (!$_address) {
            return $address;
        }

        $addressHelper  = $this->getAddressHelper();
        $_address       = $addressHelper->cast($_address);
        if (!$addressHelper->isEmpty($_address)) {
            $address = $_address;
        }

        return $address;
    }
    /**
     * Get default address
     * 
     * @return Varien_Object
     */
    protected function getDefaultAddress()
    {
        return $this->getAddressHelper()->cast($this->getCustomerLocatorHelper()->getDefaultAddress());
    }
    /**
     * Locate address
     * 
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    protected function locateAddress()
    {
        $helper         = $this->getCustomerLocatorHelper();
        $coreHelper     = $helper->getCoreHelper();
        $address        = null;
        if (!$coreHelper->isAdmin()) {
            if ($helper->useDefaultShippingAddress()) {
                $address        = $this->getCustomerDefaultAddress();
            }

            if (!$address && $helper->useIpGeolocation()) {
                $address        = $this->getGeoIpAddress();
            }
        }

        if (!$address) {
            $address        = $this->getDefaultAddress();
        }

        $this->setAddress($address);
        return $this;
    }
    /**
     * Set shipping address
     * 
     * @param Varien_Object $shippingAddress
     * 
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    public function setAddress($address)
    {
        $address        = $this->getAddressHelper()->cast($address);
        $this->unsetAddress();
        $this->setCountryId($address->getCountryId());
        $this->setRegionId($address->getRegionId());
        $this->setRegion($address->getRegion());
        $this->setCity($address->getCity());
        $this->setPostcode($address->getPostcode());
        $this->setStreet($address->getStreet());
        $this->_address = $address;
        return $this;
    }
    /**
     * Set address identifier
     * 
     * @param int $addressId
     * 
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    public function setAddressId($addressId)
    {
        $coreHelper             = $this->getCoreHelper();
        $customerHelper         = $coreHelper->getCustomerHelper();
        if (!$customerHelper->isLoggedIn()) {
            return $this;
        }

        $address                = $customerHelper->getCustomer()
            ->getAddressById($addressId);
        if (!$address) {
            return $this;
        }

        $addressHelper          = $coreHelper->getAddressHelper();
        $address                = $addressHelper->cast($address);
        if (!$addressHelper->isEmpty($address)) {
            $this->setAddress($address);
        }

        return $this;
    }
    /**
     * Retrieve address
     * 
     * @return Varien_Object
     */
    public function getAddress()
    {
        $this->_getAddress();
        if ($this->isAddressEmpty()) {
            $this->locateAddress();
        }

        return $this->_address;
    }
    /**
     * Check if address is set
     * 
     * @return boolean
     */
    public function hasAddress()
    {
        return (!$this->getAddressHelper()->isEmpty($this->getAddress())) ? true : false;
    }
    /**
     * Unset address
     * 
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    public function unsetAddress()
    {
        $this->setCountryId(null);
        $this->setRegionId(null);
        $this->setRegion(null);
        $this->setCity(null);
        $this->setPostcode(null);
        $this->setStreet(null);
        $this->_address = null;
        return $this;
    }
    /**
     * Set coordinates
     * 
     * @param Varien_Object $coordinates
     * 
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    public function setCoordinates($coordinates)
    {
        $helper         = $this->getCustomerLocatorHelper();
        if ($helper->isCoordinatesGeolocatorEnabled()) {
            $address        = $helper->getGeoCoderHelper()->getAddress($coordinates);
            $addressHelper  = $this->getAddressHelper();
            if (!$addressHelper->isEmpty($address)) {
                $this->setAddress($address);
            }

            $this->setData('coordinates', $coordinates);
        }

        return $this;
    }
}
