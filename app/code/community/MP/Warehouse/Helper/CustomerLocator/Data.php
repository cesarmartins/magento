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
 * Customer locator helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_CustomerLocator_Data
    extends MP_Warehouse_Helper_Core_Abstract
{
    /**
     * Get geo ip helper
     * 
     * @return MP_Warehouse_Helper_GeoIp_Data
     */
    public function getGeoIpHelper()
    {
        return Mage::helper('warehouse/geoIp_data');
    }
    /**
     * Get geo coder helper
     * 
     * @return MP_Warehouse_Helper_GeoCoder_Data
     */
    public function getGeoCoderHelper()
    {
        return Mage::helper('warehouse/geoCoder_data');
    }
    /**
     * Get address helper
     * 
     * @return MP_Warehouse_Helper_Core_Address
     */
    public function getAddressHelper()
    {
        return $this->getCoreHelper()->getAddressHelper();
    }
    /**
     * Get checkout helper
     * 
     * @return MP_Warehouse_Helper_Core_Checkout
     */
    public function getCheckoutHelper()
    {
        return $this->getCoreHelper()->getCheckoutHelper();
    }
    /**
     * Get customer helper
     * 
     * @return MP_Warehouse_Helper_Core_Customer
     */
    public function getCustomerHelper()
    {
        return $this->getCoreHelper()->getCustomerHelper();
    }
    /**
     * Get session
     * 
     * @return MP_Warehouse_Model_CustomerLocator_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('warehouse/customerLocator_session');
    }
    /**
     * Get config
     * 
     * @return MP_Warehouse_Model_CustomerLocator_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('warehouse/customerLocator_config');
    }
    /**
     * Get attributes
     * 
     * @return array
     */
    public function getAttributes()
    {
        $config = $this->getConfig();
        return array(
            $config->getCountryAttribute()   => $this->__('Country'), 
            $config->getRegionAttribute()    => $this->__('Region / State'), 
            $config->getPostcodeAttribute()  => $this->__('Zip / Postal Code'), 
            $config->getCityAttribute()      => $this->__('City'), 
            $config->getStreetAttribute()    => $this->__('Street Address'), 
        );
    }
    /**
     * Get customer address
     * 
     * @return Varien_Object
     */
    public function getCustomerAddress()
    {
        return $this->getSession()->getAddress();
    }
    /**
     * Check if customer address is set
     * 
     * @return boolean
     */
    public function hasCustomerAddress()
    {
        return $this->getSession()->hasAddress();
    }
    /**
     * Set customer address
     * 
     * @param Varien_Object $address
     * 
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    public function setCustomerAddress($address)
    {
        $this->getSession()->setAddress($address);
        return $this;
    }
    /**
     * Set customer address identifier
     * 
     * @param int $addressId
     * 
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    public function setCustomerAddressId($addressId)
    {
        $this->getSession()->setAddressId($addressId);
        return $this;
    }
    /**
     * Set customer coordinates
     * 
     * @param Varien_Object $coordinates
     * 
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    public function setCustomerCoordinates($coordinates)
    {
        $this->getSession()->setCoordinates($coordinates);
        return $this;
    }
    /**
     * Unset customer address
     * 
     * @param Varien_Object $address
     * 
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    public function unsetCustomerAddress()
    {
        $this->getSession()->unsetAddress();
        return $this;
    }
    /**
     * Apply customer address to quote address
     * 
     * @param Mage_Sales_Model_Quote_Address $quoteAddress
     * @param boolean $overwrite
     * 
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    public function applyCustomerAddressToQuoteAddress($quoteAddress, $overwrite = false)
    {
        $coreHelper         = $this->getCoreHelper();
        $addressHelper      = $this->getAddressHelper();

        if ($coreHelper->isAdmin()
            || (!$addressHelper->isEmpty($quoteAddress) && !$overwrite)
            || !$this->hasCustomerAddress()) {
            return $this;
        }

        $customerAddress  = $this->getCustomerAddress();

        $addressHelper->copy($customerAddress, $quoteAddress);

        $customerAddressId  = $coreHelper->getCustomerHelper()->getAddressIdByAddress($customerAddress);
        if ($customerAddressId) {
            $quoteAddress->setCustomerAddressId($customerAddressId);
        }

        return $this;
    }
    /**
     * Apply quote address to customer address
     * 
     * @param Mage_Sales_Model_Quote_Address $quoteAddress
     * 
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    public function applyQuoteAddressToCustomerAddress($quoteAddress)
    {
        $quote      = $quoteAddress->getQuote();
        $coreHelper = $this->getCoreHelper();
        if ($coreHelper->isAdmin() ||
            (!$quote) || 
            (!$this->getConfig()->isAdjustAddressOnCheckout($quote->getStore())) || 
            ($quote->isVirtual() && ($quoteAddress->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)) || 
            (!$quote->isVirtual() && ($quoteAddress->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_BILLING)) || 
            (!in_array(
                $this->getCoreHelper()->getFullControllerName(), 
                $this->getCheckoutHelper()->getFullControllerNames()
            ))
        ) {
            return $this;
        }

        $addressHelper      = $coreHelper->getAddressHelper();
        $customerAddress    = $this->getCustomerAddress();
        if (!$addressHelper->isEmpty($quoteAddress) && 
            !$addressHelper->compare($customerAddress, $quoteAddress)
        ) {
            $this->setCustomerAddress($quoteAddress);
        }

        return $this;
    }
    /**
     * Get store
     * 
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return $this->getCoreHelper()->getStore();
    }
    /**
     * Check if coordinates geolocator is enabled
     * 
     * @return boolean
     */
    public function isCoordinatesGeolocatorEnabled()
    {
        return (
            !$this->getCoreHelper()->isAdmin() && 
            $this->useCoordinatesGeolocation() && 
            !(
                $this->useDefaultShippingAddress() &&  
                $this->getCustomerHelper()->getCustomer()->getDefaultShippingAddress()
            ) && !$this->getSession()->getCoordinates()
        ) ? true : false;
    }
    /**
     * Get attributes options
     * 
     * @return array
     */
    public function getAttributesOptions()
    {
        $options = array();
        $attributes = $this->getAttributes();
        foreach ($attributes as $attribute => $name) {
            array_push($options, array('value' => $attribute, 'label' => $name));
        }

        return $options;
    }
    /**
     * Get attribute name
     * 
     * @param string $attribute
     * 
     * @return string
     */
    public function getAttributeName($attribute)
    {
        $attributes = $this->getAttributes();
        if (isset($attributes[$attribute])) {
            return $attributes[$attribute];
        } else {
            return null;
        }
    }
    /**
     * Check if customer can change current address
     * 
     * @return bool
     */
    public function isAllowModification()
    {
        return $this->getConfig()->isAllowModification($this->getStore());
    }
    /**
     * Check if default shipping address should be applied
     * 
     * @return bool
     */
    public function useDefaultShippingAddress()
    {
        return $this->getConfig()->useDefaultShippingAddress($this->getStore());
    }
    /**
     * Check if coordinates geolocation should be applied
     * 
     * @return boolean
     */
    public function useCoordinatesGeolocation()
    {
        return $this->getConfig()->useCoordinatesGeolocation($this->getStore());
    }
    /**
     * Check if IP geolocation should be applied
     * 
     * @return boolean
     */
    public function useIpGeolocation()
    {
        return $this->getConfig()->useIpGeolocation($this->getStore());
    }
    /**
     * Check if customer address should be adjusted according to checkout address
     * 
     * @return boolean
     */
    public function isAdjustAddressOnCheckout()
    {
        return $this->getConfig()->isAdjustAddressOnCheckout($this->getStore());
    }
    /**
     * Check if country is allowed
     *
     * @return bool
     */
    public function isCountryAllowed()
    {
        return $this->getConfig()->isCountryAllowed($this->getStore());
    }
    /**
     * Check if region is allowed
     *
     * @return bool
     */
    public function isRegionAllowed()
    {
        return $this->getConfig()->isRegionAllowed($this->getStore());
    }
    /**
     * Check if city is allowed
     * 
     * @return bool
     */
    public function isCityAllowed()
    {
        return $this->getConfig()->isCityAllowed($this->getStore());
    }
    /**
     * Check if postal code is allowed
     *
     * @return bool
     */
    public function isPostcodeAllowed()
    {
        return $this->getConfig()->isPostcodeAllowed($this->getStore());
    }
    /**
     * Check if street is allowed
     *
     * @return bool
     */
    public function isStreetAllowed()
    {
        return $this->getConfig()->isStreetAllowed($this->getStore());
    }
    /**
     * Check if country is required
     *
     * @return bool
     */
    public function isCountryRequired()
    {
        return $this->getConfig()->isCountryRequired($this->getStore());
    }
    /**
     * Check if region is required
     *
     * @return bool
     */
    public function isRegionRequired()
    {
        return $this->getConfig()->isRegionRequired($this->getStore());
    }
    /**
     * Check if city is required
     * 
     * @return bool
     */
    public function isCityRequired()
    {
        return $this->getConfig()->isCityRequired($this->getStore());
    }
    /**
     * Check if postal code is required
     * 
     * @return bool
     */
    public function isPostcodeRequired()
    {
        return $this->getConfig()->isPostcodeRequired($this->getStore());
    }
    /**
     * Check if street is required
     * 
     * @return bool
     */
    public function isStreetRequired()
    {
        return $this->getConfig()->isStreetRequired($this->getStore());
    }
    /**
     * Get default address
     * 
     * @return Varien_Object
     */
    public function getDefaultAddress()
    {
        return $this->getConfig()->getDefaultAddress($this->getStore());
    }
}
