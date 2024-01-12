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
 * Address helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Address
    extends Mage_Core_Helper_Abstract
{
    /**
     * Countries
     * 
     * @var array
     */
    protected $_countries;
    /**
     * Regions
     * 
     * @var array
     */
    protected $_regions;
    /**
     * Grouped regions 
     * 
     * @var array
     */
    protected $_regionsGrouped;
    /**
     * Get countries
     * 
     * @return array
     */
    public function getCountries()
    {
        if (is_null($this->_countries)) {
            $countries = array();
            $collection = Mage::getResourceModel('directory/country_collection');
            foreach ($collection as $country) {
                $countries[strtoupper($country->getId())] = $country;
            }

            $this->_countries = $countries;
        }

        return $this->_countries;
    }
    /**
     * Get country
     * 
     * @return Mage_Directory_Model_Country
     */
    public function getCountry()
    {
        return Mage::getModel('directory/country');
    }
    /**
     * Get country by identifier
     * 
     * @param string $countryId
     * 
     * @return Mage_Directory_Model_Country
     */
    public function getCountryById($countryId)
    {
        $this->getCountries();
        if (!isset($this->_countries[$countryId])) {
            return $this->getCountry()->load($countryId);
        } else {
            return $this->_countries[$countryId];
        }
    }
    /**
     * Get country iso2 code by id
     * 
     * @param string $countryId
     * 
     * @return string
     */
    public function getCountryIso2CodeById($countryId)
    {
        return $this->getCountryById($countryId)->getDataUsingMethod('iso2_code');
    }
    /**
     * Get country identifier by identifier
     * 
     * @param string $countryId
     * 
     * @return string
     */
    public function getCountryIdById($countryId)
    {
        $countryId = strtoupper($countryId);
        $countries = $this->getCountries();
        if (isset($countries[$countryId])) {
            return $countryId;
        } else {
            return null;
        }
    }
    /**
     * Get country by attribute
     * 
     * @param string $attribute
     * @param string $value
     * 
     * @return Mage_Directory_Model_Country
     */
    public function getCountryByAttribute($attribute, $value)
    {
        $country = null;
        $value = strtoupper($value);
        $countries = $this->getCountries();
        foreach ($countries as $_country) {
            if ($value == strtoupper($_country->getDataUsingMethod($attribute))) { 
                $country = $_country;
                break;
            }
        }

        return $country;
    }
    /**
     * Get country identifier by name
     * 
     * @param string $name
     * 
     * @return string
     */
    public function getCountryIdByName($name)
    {
        $country = $this->getCountryByAttribute('name', $name);
        if ($country) {
            return $country->getId();
        } else {
            return null;
        }
    }
    /**
     * Get country identifier by iso2 code
     * 
     * @param string $code
     * 
     * @return string
     */
    public function getCountryIdByIso2Code($code)
    {
        $country = $this->getCountryByAttribute('iso2_code', $code);
        if ($country) {
            return $country->getId();
        } else {
            return null;
        }
    }
    /**
     * Get country identifier by iso3 code
     * 
     * @param string $code
     * 
     * @return string
     */
    public function getCountryIdByIso3Code($code)
    {
        $country = $this->getCountryByAttribute('iso3_code', $code);
        if ($country) {
            return $country->getId();
        } else {
            return null;
        }
    }
    /**
     * Cast country identifier
     * 
     * @param string $countryId
     * 
     * @return string
     */
    public function castCountryId($countryId)
    {
        $castedCountryId = null;
        $_countryId = $this->getCountryIdById($countryId);
        if (!$_countryId) {
            $_countryId = $this->getCountryIdByName($countryId);
            if (!$_countryId) {
                $_countryId = $this->getCountryIdByIso3Code($countryId);
                if (!$_countryId) {
                    $_countryId = $this->getCountryIdByIso2Code($countryId);
                }
            }
        }

        if ($_countryId) {
            $castedCountryId = $_countryId;
        }

        return $castedCountryId;
    }
    /**
     * Get regions
     * 
     * @return array
     */
    public function getRegions()
    {
        if (is_null($this->_regions)) {
            $regions = array();
            $collection = Mage::getResourceModel('directory/region_collection');
            foreach ($collection as $region) {
                $regions[$region->getId()] = $region;
            }

            $this->_regions = $regions;
        }

        return $this->_regions;
    }
    /**
     * Get region
     * 
     * @return Mage_Directory_Model_Region
     */
    public function getRegion()
    {
        return Mage::getModel('directory/region');
    }
    /**
     * Get region by identifier
     * 
     * @param string $regionId
     * 
     * @return Mage_Directory_Model_Region
     */
    public function getRegionById($regionId)
    {
        $this->getRegions();
        if (!isset($this->_regions[$regionId])) {
            return $this->getRegion()->load($regionId);
        } else {
            return $this->_regions[$regionId];
        }
    }
     /**
     * Get region code by id
     * 
     * @param string $regionId
     * 
     * @return string
     */
    public function getRegionCodeById($regionId)
    {
        return $this->getRegionById($regionId)
            ->getDataUsingMethod('code');
    }
    /**
     * Get regions grouped
     * 
     * @return array
     */
    public function getRegionsGrouped()
    {
        if (is_null($this->_regionsGrouped)) {
            $regionsGrouped = array();
            foreach ($this->getRegions() as $region) {
                $countryId  = strtoupper($region->getCountryId());
                $regionId   = $region->getRegionId();
                $regionsGrouped[$countryId][$regionId] = $region;
            }

            $this->_regionsGrouped = $regionsGrouped;
        }

        return $this->_regionsGrouped;
    }
    /**
     * Get regions by country identifier
     * 
     * @param string $countryId
     * 
     * @return string
     */
    public function getRegionsByCountryId($countryId)
    {
        $regionsGrouped = $this->getRegionsGrouped();
        if ($countryId && isset($regionsGrouped[$countryId])) {
            return $regionsGrouped[$countryId];
        } else {
            return array();
        }
    }
    /**
     * Get region identifier
     * 
     * @param string $countryId
     * @param string $regionId
     * 
     * @return string
     */
    public function getRegionIdById($countryId, $regionId)
    {
        $regions = array();
        if ($countryId) {
            $regions = $this->getRegionsByCountryId($countryId);
        } else {
            $regions = $this->getRegions();
        }

        if (isset($regions[$regionId])) {
            return $regionId;
        } else {
            return null;
        }
    }
    /**
     * Get region by attribute
     * 
     * @param string $attribute
     * @param string $countryId
     * @param string $value
     * 
     * @return Mage_Directory_Model_Region
     */
    public function getRegionByAttribute($attribute, $countryId, $value)
    {
        $region = null;
        $value = strtoupper($value);
        if ($countryId) {
            $regions = $this->getRegionsByCountryId($countryId);
        } else {
            $regions = $this->getRegions();
        }

        foreach ($regions as $_region) {
            if ($value == strtoupper($_region->getDataUsingMethod($attribute))) { 
                $region = $_region;
                break; 
            }
        }

        return $region;
    }
    /**
     * Get region identifier by name
     * 
     * @param string $countryId
     * @param string $name
     * 
     * @return string
     */
    public function getRegionIdByName($countryId, $name)
    {
        $region = $this->getRegionByAttribute('name', $countryId, $name);
        if ($region) {
            return $region->getId();
        } else {
            return null;
        }
    }
    /**
     * Get region identifier by code
     * 
     * @param string $countryId
     * @param string $code
     * 
     * @return string
     */
    public function getRegionIdByCode($countryId, $code)
    {
        $region = $this->getRegionByAttribute('code', $countryId, $code);
        if ($region) {
            return $region->getId();
        } else {
            return null;
        }
    }
    /**
     * Get region name by identifier
     * 
     * @param string $countryId
     * @param string $regionId
     * 
     * @return string
     */
    public function getRegionNameById($countryId, $regionId)
    {
        $region = $this->getRegionByAttribute('id', $countryId, $regionId);
        if ($region) {
            return $region->getName();
        } else {
            return null;
        }
    }
    /**
     * Get region identifier
     * 
     * @param string $countryId
     * @param string $regionId
     * 
     * @return string
     */
    public function castRegionId($countryId, $regionId)
    {
        $castedRegionId = null;
        $_regionId = $this->getRegionIdById($countryId, $regionId);
        if (!$_regionId) {
            $_regionId = $this->getRegionIdByName($countryId, $regionId);
            if (!$_regionId) {
                $_regionId = $this->getRegionIdByCode($countryId, $regionId);
            }
        }

        if ($_regionId) {
            $castedRegionId = $_regionId;
        }

        return $castedRegionId;
    }
    /**
     * Cast address
     * 
     * @param Varien_Object $address
     * 
     * @return Varien_Object
     */
    public function cast($address)
    {
        $castedAddress = new Varien_Object();
        if ($address->getCountryId() || $address->getCountry()) {
            $countryId = ($address->getCountryId()) ? $address->getCountryId() : $address->getCountry();
            $castedAddress->setCountryId($this->castCountryId($countryId));
        } else {
            $castedAddress->setCountryId(null);
        }

        if ($address->getRegionId() || $address->getRegion()) {
            $regionId = ($address->getRegionId()) ? $address->getRegionId() : $address->getRegion();
            $regionId = $this->castRegionId($castedAddress->getCountryId(), $regionId);
            $castedAddress->setRegionId($regionId);
            if ($regionId) {
                $castedAddress->setRegion($this->getRegionNameById($castedAddress->getCountryId(), $regionId));
            } else {
                $castedAddress->setRegion($address->getRegion());
            }
        } else {
            $castedAddress->setRegionId(null);
            $castedAddress->setRegion(null);
        }

        $castedAddress->setCity($address->getCity());
        $castedAddress->setPostcode($address->getPostcode());
        $street = $address->getStreet();
        $street = is_array($street) ? $street : explode("\n", $street);
        $castedAddress->setStreet($street);
        return $castedAddress;
    }
    /**
     * Format address
     * 
     * @param Varien_Object $address
     * 
     * @return string
     */
    public function format(Varien_Object $address)
    {
        $formattedAddress = null;
        $pieces = array();
        if ($address->getStreet()) {
            $street = $address->getStreet();
            if (is_array($street)) {
                $street = implode(' ', $street);
            }

            array_push($pieces, (string) $street);
        }

        if ($address->getCity()) {
            array_push($pieces, (string) $address->getCity());
        }

        if ($address->getRegionId() || $address->getRegion() || $address->getPostcode()) {
            $regionAndPostalCodePieces = array();
            $regionId = ($address->getRegion()) ? $address->getRegion() : $address->getRegionId();
            if (is_numeric($regionId)) {
                $region = $this->getRegionById($regionId);
                if ($region) {
                    array_push($regionAndPostalCodePieces, (string) $region->getName());
                }
            } else {
                array_push($regionAndPostalCodePieces, (string) $regionId);
            }

            if ($address->getPostcode()) {
                array_push($regionAndPostalCodePieces, (string) trim(str_replace('-', '', $address->getPostcode())));
            }

            if (count($regionAndPostalCodePieces)) {
                array_push($pieces, implode(' ', $regionAndPostalCodePieces));
            }
        }

        if ($address->getCountryId() || $address->getCountry()) {
            if ($address->getCountry()) {
                array_push($pieces, (string) $address->getCountry());
            } else if ($address->getCountryId()) {
                $country = $this->getCountryById($address->getCountryId());
                if ($country) {
                    array_push($pieces, (string) $country->getName());
                }
            }
        }

        if (count($pieces)) {
            $formattedAddress = implode(', ', $pieces);
        }

        return $formattedAddress;
    }
    /**
     * Check if address is empty
     * 
     * @param Varien_Object $address
     * 
     * @return bool
     */
    public function isEmpty($address)
    {
        if ($address->getCountryId() || $address->getRegionId() || $address->getRegion() || 
            $address->getCity() || $address->getPostcode() || $this->getStreet($address, 1)
        ) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * Copy address
     * 
     * @param Varien_Object $srcAddress
     * @param Varien_Object $dstAddress
     * 
     * @return self
     */
    public function copy($srcAddress, $dstAddress)
    {
        $dstAddress->setCountryId($srcAddress->getCountryId());
        $dstAddress->setRegionId($srcAddress->getRegionId());
        $dstAddress->setRegion($srcAddress->getRegion());
        $dstAddress->setCity($srcAddress->getCity());
        $dstAddress->setPostcode($srcAddress->getPostcode());
        $dstAddress->setStreet($srcAddress->getStreet());
        return $this;
    }
    /**
     * Compares address
     * 
     * @param Varien_Object $address1
     * @param Varien_Object $address2
     * 
     * @return boolean
     */
    public function compare($address1, $address2)
    {
        $address1 = $this->cast($address1);
        $address2 = $this->cast($address2);
        if ($address1->getCountryId() != $address2->getCountryId()) {
            return false;
        }

        if ($address1->getRegionId() != $address2->getRegionId()) {
            return false;
        }

        if ($address1->getRegion() != $address2->getRegion()) {
            return false;
        }

        if ($address1->getCity() != $address2->getCity()) {
            return false;
        }

        if ($address1->getPostcode() != $address2->getPostcode()) {
            return false;
        }

        if ($address1->getStreet() != $address2->getStreet()) {
            return false;
        }

        return true;
    }
    /**
     * Get street
     * 
     * @param Varien_Object $address
     * @param int $line
     * 
     * @return string
     */
    public function getStreet($address, $line = 0)
    {
        $street = $address->getStreet();
        if (-1 === $line) {
            return $street;
        } else {
            $array = is_array($street) ? $street : explode("\n", $street);
            if (0 === $line || $line === null) {
                return $array;
            } elseif (isset($array[$line - 1])) {
                return $array[$line - 1];
            } else {
                return '';
            }
        }
    }
    /**
     * Get hash
     * 
     * @param Varien_Object $address
     * 
     * @return string
     */
    public function getHash($address)
    {
        return md5($this->format($address));
    }
}
