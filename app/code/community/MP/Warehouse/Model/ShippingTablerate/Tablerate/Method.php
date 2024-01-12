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
 * Table rate method model
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_ShippingTablerate_Tablerate_Method
    extends MP_Warehouse_Model_Core_Abstract
{
    /**
     * Prefix of model events names
     * 
     * @var string
     */
    protected $_eventPrefix = 'shippingtablerate_tablerate_method';
    /**
     * Parameter name in event
     * 
     * In observe method you can use $observer->getEvent()->getItem() in this case
     * 
     * @var string
     */
    protected $_eventObject = 'tablerate_method';
    /**
     * Model cache tag for clear cache in after save and after delete
     * 
     * When you use true - all cache will be clean
     * 
     * @var string || true
     */
    protected $_cacheTag = 'shippingtablerate_tablerate_method';
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('warehouse/shippingTablerate_tablerate_method');
    }
    /**
     * Get warehouse helper
     *
     * @return  MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Get shipping table rate helper
     *
     * @return MP_Warehouse_Helper_Data
     */
    protected function getTextHelper()
    {
        return $this->getWarehouseHelper();
    }
    /**
     * Get filters
     * 
     * @return array
     */
    protected function getFilters()
    {
        return array(
            'code'              => $this->getTextFilter(), 
            'name'              => $this->getTextFilter(), 
        );
    }
    /**
     * Get code validator
     * 
     * @return Zend_Validate
     */
    protected function getCodeValidator()
    {
        $helper         = $this->getTextHelper();
        $validator      = new Zend_Validate_Regex(array('pattern' => '/^[a-z]+[a-z0-9_]*$/'));
        $validator->setMessage(
            $helper->__(
                'Method code may only contain letters (a-z), numbers (0-9) or underscore(_), the first character must be a letter'
            ), 
            Zend_Validate_Regex::NOT_MATCH
        );
        return $this->getTextValidator(true, 0, 32)->addValidator($validator);
    }
    /**
     * Get validators
     * 
     * @return array
     */
    protected function getValidators()
    {
        return array(
            'code'              => $this->getCodeValidator(), 
            'name'              => $this->getTextValidator(true, 0, 128), 
        );
    }
    /**
     * Get model
     * 
     * @return MP_Warehouse_Model_Shippingtablerate_Tablerate_Method
     */
    protected function _getModel()
    {
        return Mage::getModel('warehouse/shippingTablerate_tablerate_method');
    }
    /**
     * Validate method
     *
     * @throws Mage_Core_Exception
     * 
     * @return bool
     */
    public function validate()
    {
        $helper = $this->getTextHelper();
        parent::validate();
        $errorMessages      = array();
        $tablerateMethod    = $this->_getModel()->loadByCode($this->getCode(), $this->getId());
        if ($tablerateMethod->getId()) {
            array_push($errorMessages, $helper->__('Method with the same code already exists.'));
        }

        $tablerateMethod    = $this->_getModel()->loadByName($this->getName(), $this->getId());
        if ($tablerateMethod->getId()) {
            array_push($errorMessages, $helper->__('Method with the same name already exists.'));
        }

        if (count($errorMessages)) {
            Mage::throwException(join("\n", $errorMessages));
        }

        return true;
    }
    /**
     * Get title
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->getName();
    }
    /**
     * Processing object before delete data
     * 
     * @return MP_Warehouse_Model_ShippingTablerate_Tablerate_Method
     */
    protected function _beforeDelete()
    {
        if (1 == $this->getId()) {
            $helper = $this->getTextHelper();
            Mage::throwException($helper->__('The default method can\'t be deleted.'));
        }

        parent::_beforeDelete();
        return $this;
    }
    /**
     * Load method by code
     * 
     * @param string $code
     * @param int $exclude
     * 
     * @return MP_Warehouse_Model_ShippingTablerate_Tablerate_Method
     */
    public function loadByCode($code, $exclude = null)
    {
        $this->_getResource()->loadByCode($this, $code, $exclude);
        $this->setOrigData();
        return $this;
    }
    /**
     * Load method by name
     * 
     * @param string $name
     * @param int $exclude
     * 
     * @return MP_Warehouse_Model_ShippingTablerate_Tablerate_Method
     */
    public function loadByName($name, $exclude = null)
    {
        $this->_getResource()->loadByName($this, $name, $exclude);
        $this->setOrigData();
        return $this;
    }
}
