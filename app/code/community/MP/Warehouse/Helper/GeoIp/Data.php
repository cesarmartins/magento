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
 * Geo ip helper
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_GeoIp_Data
    extends MP_Warehouse_Helper_Core_Abstract
{
    /**
     * Geo Ip resource
     *
     * @var resource
     */
    protected $_geoIp;
    /**
     * Regions names
     *
     * @var array
     */
    protected $_regionsNames;
    /**
     * If PHP database is enabled
     * 
     * @var boolean 
     */
    protected $_isPhpDatabaseEnabled;
    /**
     * If database is enabled
     * 
     * @var boolean 
     */
    protected $_isDatabaseEnabled;
    /**
     * Constructor
     */
    public function __construct()
    {
        if ($this->isDatabaseEnabled()) {
            include_once $this->getVendorPath().'/geoip.inc';
            include_once $this->getVendorPath().'/geoipcity.inc';
            $geoIp = _geoip_open($this->getDatabaseFilePath(), _GEOIP_STANDARD);
            if ($geoIp) {
                $this->_geoIp = $geoIp;
            } else {
                $this->_geoIp = false;
            }

            include_once $this->getVendorPath().'/geoipregionvars.php';
            $this->_regionsNames = $_GEOIP_REGION_NAME;
        }
    }
    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->_geoIp) {
            _geoip_close($this->_geoIp);
        }
    }
    /**
     * Get string helper
     * 
     * @return Mage_Core_Helper_String
     */
    protected function getStringHelper()
    {
        return $this->getCoreHelper()->getStringHelper();
    }
    /**
     * Get config
     * 
     * @return MP_Warehouse_Model_GeoIp_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('warehouse/geoIp_config');
    }
    /**
     * Check if PHP database is enabled
     * 
     * @return boolean
     */
    protected function isPhpDatabaseEnabled()
    {
        if (is_null($this->_isPhpDatabaseEnabled)) {
            $this->_isPhpDatabaseEnabled = (
                $this->getConfig()->usePhpExtension() && 
                extension_loaded('geoip') && 
                geoip_db_avail(GEOIP_CITY_EDITION_REV0)
            )  ? true : false;
        }

        return $this->_isPhpDatabaseEnabled;
    }
    /**
     * Check if database is enabled
     * 
     * @return boolean
     */
    protected function isDatabaseEnabled()
    {
        if (is_null($this->_isDatabaseEnabled)) {
            $this->_isDatabaseEnabled = (
                !$this->isPhpDatabaseEnabled() && 
                file_exists($this->getDatabaseFilePath())
            ) ? true : false;
        }

        return $this->_isDatabaseEnabled;
    }
    /**
     * Get database file path
     * 
     * @return string
     */
    protected function getDatabaseFilePath()
    {
        $path = trim($this->getConfig()->getDatabaseFile());
        if (substr($path, 0, 1) != '/') {
            $path = Mage::getBaseDir().DS.$path;
        }

        return $path;
    }
    /**
     * Get vendor path
     * 
     * @return string
     */
    protected function getVendorPath()
    {
        return Mage::getModuleDir(null, 'MP_Warehouse').'/Helper'.'/GeoIp'.'/Lib';
    }
    /**
     * Get geo ip resource
     * 
     * @return resource
     */
    protected function getGeoIp()
    {
        return $this->_geoIp;
    }
    /**
     * Get regions names
     * 
     * @return array
     */
    protected function getRegionsNames()
    {
        return $this->_regionsNames;
    }
    /**
     * Get region name
     * 
     * @param string $countryCode
     * @param string $regionCode
     * 
     * @return string
     */
    protected function getRegionName($countryCode, $regionCode)
    {
        if ($this->isPhpDatabaseEnabled()) {
            return @geoip_region_name_by_code($countryCode, $regionCode);
        } else if ($this->isDatabaseEnabled()) {
            return (
                isset($this->_regionsNames[$countryCode]) && 
                isset($this->_regionsNames[$countryCode][$regionCode])
            ) ? $this->_regionsNames[$countryCode][$regionCode] : null;
        } else {
            return null;
        }
    }
    /**
     * Get record by ip adress
     * 
     * @param string $ip
     * 
     * @return stdClass
     */
    protected function getRecordByIp($ip)
    {
        //see if the ip address is ipv4
        $ipv4 = $this->isIpv4($ip);

        $record = null;
        if ($this->isPhpDatabaseEnabled()) {
            $_record = @geoip_record_by_name($ip);

            if ($_record && is_array($_record)) {
                $record = $_record;
            }
        } else if ($this->isDatabaseEnabled()) {
            if($ipv4) {
                //address is ipv4, use lib ipv4 function
                $_record = @_geoip_record_by_addr($this->getGeoIp(), $ip);
            } else {
                //address is ipv6, use lib ipv6 function
                $_record = @_geoip_record_by_addr_v6($this->getGeoIp(), $ip);
            }

            if ($_record && is_object($_record)) {
                $record = get_object_vars($_record);
            }
        }

        if ($record) {
            $stringHelper = $this->getStringHelper();
            foreach ($record as $key => $value) {
                $record[$key] = $stringHelper->cleanString($value);
            }

            $record = new Varien_Object($record);
        }

        return $record;
    }

    /**
     * Determine if ip is ipv4
     *
     * @param $ip
     * @return bool
     */
    protected function isIpv4($ip)
    {
        if(!$ip) {
            //we could not get the ip, default to true for ipv4 type
            return true;
        }

        if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) == false) {
            //address is ipv4, return true
            return true;
        } else {
            //address is ipv6, return false
            return false;
        }
    }

    /**
     * Get address by ip address
     *
     * @param string $ip
     * 
     * @return Varien_Object
     */
    public function getAddressByIp($ip)
    {
        $address = new Varien_Object();
        if (!$ip) {
            return $address;
        }

        $record = $this->getRecordByIp($ip);
        if (!$record) {
            return $address;
        }

        if ($record->getCountryCode()) {
            $address->setCountryId($record->getCountryCode());
        }

        if ($record->getCountryName()) {
            $address->setCountry($record->getCountryName());
        }

        if ($record->getCountryCode() && $record->getRegion()) {
            $address->setRegion($this->getRegionName($record->getCountryCode(), $record->getRegion()));
        }

        if ($record->getCity()) {
            $address->setCity($record->getCity());
        }

        if ($record->getPostalCode()) {
            $address->setPostcode($record->getPostalCode());
        }
        
        return $address;
    }
    /**
     * Get coordinates by ip address
     *
     * @param string $ip
     * 
     * @return Varien_Object
     */
    public function getCoordinatesByIp($ip)
    {
        $coordinates = new Varien_Object();
        if (!$ip) {
            return $coordinates;
        }

        $record = $this->getRecordByIp($ip);
        if (!$record) {
            return $coordinates;
        }

        if ($record->getLatitude() && $record->getLongitude()) {
            $coordinates->setLatitude((float) $record->getLatitude());
            $coordinates->setLongitude((float) $record->getLongitude());
        }

        return $coordinates;
    }
}
