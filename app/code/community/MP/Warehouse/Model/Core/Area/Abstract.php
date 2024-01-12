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
 * Area abstract model
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Core_Area_Abstract
    extends MP_Warehouse_Model_Core_Abstract
{
    /**
     * Get address helper
     * 
     * @return MP_Warehouse_Helper_Core_Address
     */
    public function getAddressHelper()
    {
        return $this->getCoreHelper()
            ->getAddressHelper();
    }
    /**
     * Filter country
     * 
     * @param mixed $value
     * 
     * @return string
     */
    public function filterCountry($country)
    {
        if ($country) {
            $country = $this->getAddressHelper()->castCountryId($country);
        }

        if ($country) {
            return $country;
        } else {
            return '0';
        }
    }
    /**
     * Get country filter
     * 
     * @return Zend_Filter
     */
    protected function getCountryFilter()
    {
        return $this->getTextFilter()->appendFilter(
            new Zend_Filter_Callback(
                array(
                'callback' => array($this, 'filterCountry'), 
                )
            )
        );
    }
    /**
     * Filter region
     * 
     * @param mixed $value
     * @param string $countryField
     * 
     * @return string
     */
    public function filterRegion($region, $countryField)
    {
        $countryId = $this->filterCountry($this->getData($countryField));
        if ($countryId && $region) {
            $region = $this->getAddressHelper()->castRegionId($countryId, $region);
        }

        if ($region) {
            return $region;
        } else {
            return '0';
        }
    }
    /**
     * Get destination region filter
     * 
     * @param string $countryField
     * 
     * @return Zend_Filter
     */
    protected function getRegionFilter($countryField)
    {
        return $this->getTextFilter()->appendFilter(
            new Zend_Filter_Callback(
                array(
                'callback' => array($this, 'filterRegion'), 
                'options' => array($countryField), 
                )
            )
        );
    }
    /**
     * Filter zip
     * 
     * @param mixed $value
     * 
     * @return string
     */
    public function filterZip($value)
    {
        return ($value == '' || $value == '*') ? '' : $value;
    }
    /**
     * Get zip filter
     * 
     * @return Zend_Filter
     */
    protected function getZipFilter()
    {
        return $this->getTextFilter()->appendFilter(
            new Zend_Filter_Callback(
                array(
                'callback' => array($this, 'filterZip'), 
                )
            )
        );
    }
    /**
     * Get filters
     * 
     * @return array
     */
    protected function getFilters()
    {
        return array(
            'country_id'     => $this->getCountryFilter(), 
            'region_id'      => $this->getRegionFilter('country_id'), 
            'zip'            => $this->getZipFilter(), 
        );
    }
    /**
     * Get validators
     * 
     * @return array
     */
    protected function getValidators()
    {
        return array(
            'country_id'     => $this->getTextValidator(false, 0, 4), 
            'region_id'      => $this->getIntegerValidator(false, 0), 
            'zip'            => $this->getTextValidator(false, 0, 10), 
        );
    }
    /**
     * Get title
     * 
     * @return string
     */
    public function getTitle()
    {
        $addressHelper  = $this->getAddressHelper();
        $country        = null;
        $region         = null;
        if ($this->getCountryId()) {
            $country = $addressHelper->getCountryById($this->getCountryId());
        }

        if ($this->getRegionId()) {
            $region = $addressHelper->getRegionById($this->getRegionId());
        }

        $zip = $this->getZip();
        $title = implode(
            ', ', array(
            (($region) ? $region->getName() : '*'), 
            (($zip) ? $zip : '*'), 
            (($country) ? $country->getName() : '*')
            )
        );
        return $title;
    }
}
