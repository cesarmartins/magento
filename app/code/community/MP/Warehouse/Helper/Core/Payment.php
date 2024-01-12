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
 * Payment helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Payment
    extends MP_Warehouse_Helper_Core_Abstract
{
    /**
     * Method groups
     * 
     * @var array
     */
    protected $_methodGroups;
    /**
     * Get all methods
     * 
     * @return array
     */
    public function getAllMethods()
    {
         return Mage::getSingleton('payment/config')->getAllMethods();
    }
    /**
     * Get methods
     * 
     * @return array
     */
    public function getMethods()
    {
        $methods = array();
        foreach ($this->getAllMethods() as $method) {
            $isActive = (bool)(int) $method->getConfigData('active', null);
            if (!$isActive) {
                continue;
            }

            $methods[$method->getCode()] = $method;
        }

        return $methods;
    }
    /**
     * Get method groups
     * 
     * @return array
     */
    public function getMethodGroups()
    {
        if (is_null($this->_methodGroups)) {
            $this->_methodGroups = Mage::app()
                ->getConfig()
                ->getNode(Mage_Payment_Helper_Data::XML_PATH_PAYMENT_GROUPS)
                ->asCanonicalArray();
        }

        return $this->_methodGroups;
    }
    /**
     * Get method group name by code
     * 
     * @param string $groupCode
     * 
     * @return string
     */
    public function getMethodGroupNameByCode($groupCode)
    {
        $groups = $this->getMethodGroups();
        return (isset($groups[$groupCode])) ? $groups[$groupCode] : null;
    }
    /**
     * Get method group code by method code
     * 
     * @param string $methodCode
     * 
     * @return string
     */
    public function getMethodGroupCodeByMethodCode($methodCode)
    {
        return Mage::getStoreConfig(Mage_Payment_Helper_Data::XML_PATH_PAYMENT_METHODS.'/'.$methodCode.'/group');
    }
    /**
     * Get method options
     * 
     * @param bool $required
     * @param string $emptyLabel
     * @param string $emptyValue
     * 
     * @return array
     */
    public function getMethodOptions($required = true, $emptyLabel = '', $emptyValue = '')
    {
        $options    = array();
        foreach ($this->getMethods() as $method) {
            $methodCode     = $method->getCode();
            $methodName     = $method->getTitle();
            $groupCode      = $this->getMethodGroupCodeByMethodCode($methodCode);
            if ($groupCode) {
                $groupName      = $this->getMethodGroupNameByCode($groupCode);
                if (!isset($options[$groupCode])) {
                    $options[$groupCode] = array(
                        'value' => array(), 
                        'label' => $groupName, 
                    );
                }

                $options[$groupCode]['value'][$methodCode] = array(
                    'value' => $methodCode, 
                    'label' => $methodName, 
                );
            } else {
                $options[$methodCode] = array(
                    'value' => $methodCode, 
                    'label' => $methodName, 
                );
            }
        }

        $this->getCoreHelper()->prepareOptions($options, $required, $emptyLabel, $emptyValue);
        return $options;
    }
    /**
     * Get method codes
     * 
     * @return array
     */
    public function getMethodCodes()
    {
        $methodCodes = array();
        foreach ($this->getMethods() as $method) {
            $methodCode     = $method->getCode();
            array_push($methodCodes, $methodCode);
        }

        return $methodCodes;
    }
}