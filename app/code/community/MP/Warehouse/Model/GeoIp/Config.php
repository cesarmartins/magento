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
 * Geo Ip config
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_GeoIp_Config
    extends Varien_Object
{
    /**
     * Config path constants
     */
    const XML_PATH_GEOIP_OPTIONS_USE_PHP_EXTENSION  = 'mp_geoip/options/use_php_extension';
    const XML_PATH_GEOIP_OPTIONS_DATABASE_FILE      = 'mp_geoip/options/database_file';
    const XML_PATH_GEOIP_OPTIONS_GOOGLE_API_KEY     = 'mp_geoip/options/google_api_key';
    /**
     * Check if customer can change current address
     * 
     * @return boolean
     */
    public function usePhpExtension()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_GEOIP_OPTIONS_USE_PHP_EXTENSION);
    }
    /**
     * Get database file
     * 
     * @return array
     */
    public function getDatabaseFile()
    {
        return Mage::getStoreConfig(self::XML_PATH_GEOIP_OPTIONS_DATABASE_FILE);
    }
    /**
     * Get google api key
     *
     * @return array
     */
    public function getGoogleApiKey()
    {
        return Mage::getStoreConfig(self::XML_PATH_GEOIP_OPTIONS_GOOGLE_API_KEY);
    }
}
