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
 * Customer locator config
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_CustomerLocator_Config
    extends Varien_Object
{
    /**
     * Attributes 
     */
    const COUNTRY_ATTRIBUTE                                                = 'country';
    const REGION_ATTRIBUTE                                                 = 'region';
    const POSTCODE_ATTRIBUTE                                               = 'postcode';
    const CITY_ATTRIBUTE                                                   = 'city';
    const STREET_ATTRIBUTE                                                 = 'street';
    /**
     * Config path constants
     */
    const XML_PATH_CUSTOMER_LOCATOR_OPTION_ALLOW_MODIFICATION              = 'mp_customerlocator/options/allow_modification';
    const XML_PATH_CUSTOMER_LOCATOR_OPTION_USE_DEFAULT_SHIPPING_ADDRESS    = 'mp_customerlocator/options/use_default_shipping_address';
    const XML_PATH_CUSTOMER_LOCATOR_OPTION_USE_COORDINATES_GEOLOCATION     = 'mp_customerlocator/options/use_coordinates_geolocation';
    const XML_PATH_CUSTOMER_LOCATOR_OPTION_USE_IP_GEOLOCATION              = 'mp_customerlocator/options/use_ip_geolocation';
    const XML_PATH_CUSTOMER_LOCATOR_OPTION_ADJUST_ADDRESS_ON_CHECKOUT      = 'mp_customerlocator/options/adjust_address_on_checkout';
    const XML_PATH_CUSTOMER_LOCATOR_OPTION_ALLOW_ATTRIBUTES                = 'mp_customerlocator/options/allow_attributes';
    const XML_PATH_CUSTOMER_LOCATOR_OPTION_REQUIRE_ATTRIBUTES              = 'mp_customerlocator/options/require_attributes';
    const XML_PATH_CUSTOMER_LOCATOR_DEFAULT_ADDRESS_COUNTRY_ID             = 'mp_customerlocator/default_address/country_id';
    const XML_PATH_CUSTOMER_LOCATOR_DEFAULT_ADDRESS_REGION_ID              = 'mp_customerlocator/default_address/region_id';
    const XML_PATH_CUSTOMER_LOCATOR_DEFAULT_ADDRESS_POSTCODE               = 'mp_customerlocator/default_address/postcode';
    const XML_PATH_CUSTOMER_LOCATOR_DEFAULT_ADDRESS_CITY                   = 'mp_customerlocator/default_address/city';
    /**
     * Check if customer can change current address
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isAllowModification($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CUSTOMER_LOCATOR_OPTION_ALLOW_MODIFICATION, $store);
    }
    /**
     * Check if default shipping address should be applied
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function useDefaultShippingAddress($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CUSTOMER_LOCATOR_OPTION_USE_DEFAULT_SHIPPING_ADDRESS, $store);
    }
    /**
     * Check if coordinates geolocation should be applied
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function useCoordinatesGeolocation($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CUSTOMER_LOCATOR_OPTION_USE_COORDINATES_GEOLOCATION, $store);
    }
    /**
     * Check if IP geolocation should be applied
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function useIpGeolocation($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CUSTOMER_LOCATOR_OPTION_USE_IP_GEOLOCATION, $store);
    }
    /**
     * Check if customer address should be adjusted according to checkout address
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isAdjustAddressOnCheckout($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CUSTOMER_LOCATOR_OPTION_ADJUST_ADDRESS_ON_CHECKOUT, $store);
    }
    /**
     * Get country attribute
     * 
     * @return string
     */
    public function getCountryAttribute()
    {
        return self::COUNTRY_ATTRIBUTE;
    }
    /**
     * Get region attribute
     * 
     * @return string
     */
    public function getRegionAttribute()
    {
        return self::REGION_ATTRIBUTE;
    }
    /**
     * Get postcode attribute
     * 
     * @return string
     */
    public function getPostcodeAttribute()
    {
        return self::POSTCODE_ATTRIBUTE;
    }
    /**
     * Get city attribute
     * 
     * @return string
     */
    public function getCityAttribute()
    {
        return self::CITY_ATTRIBUTE;
    }
    /**
     * Get street attribute
     * 
     * @return string
     */
    public function getStreetAttribute()
    {
        return self::STREET_ATTRIBUTE;
    }
    /**
     * Get allowed attributes
     * 
     * @param mixed $store
     * 
     * @return array 
     */
    public function getAllowedAttributes($store = null)
    {
        $attributes = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_LOCATOR_OPTION_ALLOW_ATTRIBUTES, $store);
        return explode(',', $attributes);
    }
    /**
     * Get required attributes
     * 
     * @param mixed $store
     * 
     * @return array 
     */
    public function getRequiredAttributes($store = null)
    {
        $attributes = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_LOCATOR_OPTION_REQUIRE_ATTRIBUTES, $store);
        return explode(',', $attributes);
    }
    /**
     * Check if country is allowed
     * 
     * @param mixed $store
     * 
     * @return bool
     */
    public function isCountryAllowed($store = null)
    {
        return (in_array($this->getCountryAttribute(), $this->getAllowedAttributes($store))) ? true : false;
    }
    /**
     * Check if region is allowed
     * 
     * @param mixed $store
     * 
     * @return bool
     */
    public function isRegionAllowed($store = null)
    {
        return (in_array($this->getRegionAttribute(), $this->getAllowedAttributes($store))) ? true : false;
    }
    /**
     * Check if postcode is allowed
     * 
     * @param mixed $store
     * 
     * @return bool
     */
    public function isPostcodeAllowed($store = null)
    {
        return (in_array($this->getPostcodeAttribute(), $this->getAllowedAttributes($store))) ? true : false;
    }
    /**
     * Check if city is allowed
     * 
     * @param mixed $store
     * 
     * @return bool
     */
    public function isCityAllowed($store = null)
    {
        return (in_array($this->getCityAttribute(), $this->getAllowedAttributes($store))) ? true : false;
    }
    /**
     * Check if street is allowed
     * 
     * @param mixed $store
     * 
     * @return bool
     */
    public function isStreetAllowed($store = null)
    {
        return (in_array($this->getStreetAttribute(), $this->getAllowedAttributes($store))) ? true : false;
    }
    /**
     * Check if country is required
     * 
     * @param mixed $store
     * 
     * @return bool
     */
    public function isCountryRequired($store = null)
    {
        return ($this->isCountryAllowed() && in_array($this->getCountryAttribute(), $this->getRequiredAttributes($store))) ? true : false;
    }
    /**
     * Check if region is required
     * 
     * @param mixed $store
     * 
     * @return bool
     */
    public function isRegionRequired($store = null)
    {
        return ($this->isRegionAllowed() && in_array($this->getRegionAttribute(), $this->getRequiredAttributes($store))) ? true : false;
    }
    /**
     * Check if postcode is required
     * 
     * @param mixed $store
     * 
     * @return bool
     */
    public function isPostcodeRequired($store = null)
    {
        return ($this->isPostcodeAllowed() && in_array($this->getPostcodeAttribute(), $this->getRequiredAttributes($store))) ? true : false;
    }
    /**
     * Check if city is required
     * 
     * @param mixed $store
     * 
     * @return bool
     */
    public function isCityRequired($store = null)
    {
        return ($this->isCityAllowed() && in_array($this->getCityAttribute(), $this->getRequiredAttributes($store))) ? true : false;
    }
    /**
     * Check if street is required
     * 
     * @param mixed $store
     * 
     * @return bool
     */
    public function isStreetRequired($store = null)
    {
        return ($this->isStreetAllowed() && in_array($this->getStreetAttribute(), $this->getRequiredAttributes($store))) ? true : false;
    }
    /**
     * Get default country identifier
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function getDefaultCountryId($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_CUSTOMER_LOCATOR_DEFAULT_ADDRESS_COUNTRY_ID, $store);
    }
    /**
     * Get default region identifier
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function getDefaultRegionId($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_CUSTOMER_LOCATOR_DEFAULT_ADDRESS_REGION_ID, $store);
    }
    /**
     * Get default postcode
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function getDefaultPostcode($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_CUSTOMER_LOCATOR_DEFAULT_ADDRESS_POSTCODE, $store);
    }
    /**
     * Get default city
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function getDefaultCity($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_CUSTOMER_LOCATOR_DEFAULT_ADDRESS_CITY, $store);
    }
    /**
     * Get default address
     * 
     * @param mixed $store
     * 
     * @return Varien_Object
     */
    public function getDefaultAddress($store = null)
    {
        $address                = new Varien_Object();
        $address->setCountryId($this->getDefaultCountryId($store))
            ->setRegionId($this->getDefaultRegionId($store))
            ->setPostcode($this->getDefaultPostcode($store))
            ->setCity($this->getDefaultCity($store));
        return $address;
    }
}
