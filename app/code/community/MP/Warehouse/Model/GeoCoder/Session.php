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
 * Geo coder session
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_GeoCoder_Session
    extends MP_Warehouse_Model_Core_Session_Abstract
{
    /**
     * Namespace
     * 
     * @var string
     */
    protected $_namespace = 'mp_geocoder';
    /**
     * Get key by string
     * 
     * @param string $string
     * 
     * @return string
     */
    protected function getKeyByString($string)
    {
        return 'hash'.md5($string);
    }
    /**
     * Set coordinates
     * 
     * @param string $addressString
     * @param Varien_Object $coordinates
     * 
     * @return MP_Warehouse_Model_GeoCoder_Session
     */
    public function setCoordinates($addressString, $coordinates)
    {
        $this->setData($this->getKeyByString($addressString), $coordinates);
        return $this;
    }
    /**
     * Get coordinates
     * 
     * @param string $addressString
     * 
     * @return Varien_Object
     */
    public function getCoordinates($addressString)
    {
        return $this->getData($this->getKeyByString($addressString));
    }
    /**
     * Set address
     * 
     * @param string $coordinatesString
     * @param Varien_Object $address
     * 
     * @return MP_Warehouse_Model_GeoCoder_Session
     */
    public function setAddress($coordinatesString, $address)
    {
        $this->setData($this->getKeyByString($coordinatesString), $address);
        return $this;
    }
    /**
     * Get address
     * 
     * @param string $coordinatesString
     * 
     * @return Varien_Object
     */
    public function getAddress($coordinatesString)
    {
        return $this->getData($this->getKeyByString($coordinatesString));
    }
}
