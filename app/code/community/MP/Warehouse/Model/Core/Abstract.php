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
 * Abstract model
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Core_Abstract
    extends Mage_Core_Model_Abstract
{
    /**
     * Get core helper
     * 
     * @return MP_Warehouse_Helper_Core_Data
     */
    protected function getCoreHelper()
    {
        return Mage::helper('warehouse/core_data');
    }
    /**
     * Create filter chain
     * 
     * @return Zend_Filter
     */
    protected function createFilterChain()
    {
        return new Zend_Filter();
    }
    /**
     * Create validator chain
     * 
     * @return Zend_Validate
     */
    protected function createValidatorChain()
    {
        return new Zend_Validate();
    }
    /**
     * Get text filter
     * 
     * @return Zend_Filter
     */
    protected function getTextFilter()
    {
        return $this->createFilterChain()
                ->appendFilter(new Zend_Filter_StringTrim())
                ->appendFilter(new Zend_Filter_StripNewlines())
                ->appendFilter(new Zend_Filter_StripTags());
    }
    /**
     * Filter float
     * 
     * @param mixed $value
     * 
     * @return float
     */
    public function filterFloat($value)
    {
        return (float) ((string) $value);
    }
    /**
     * Filter integer
     * 
     * @param mixed $value
     * 
     * @return integer
     */
    public function filterInteger($value)
    {
        return (integer) ((string) $value);
    }
    /**
     * Get float filter
     * 
     * @return Zend_Filter
     */
    protected function getFloatFilter()
    {
        return $this->getTextFilter()->appendFilter(
            new Zend_Filter_Callback(
                array(
                'callback' => array($this, 'filterFloat'), 
                )
            )
        );
    }
    /**
     * Get integer filter
     * 
     * @return Zend_Filter
     */
    protected function getIntegerFilter()
    {
        return $this->getTextFilter()->appendFilter(
            new Zend_Filter_Callback(
                array(
                'callback' => array($this, 'filterInteger'), 
                )
            )
        );
    }
    /**
     * Validate range
     * 
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     * 
     * @return boolean
     */
    public function validateRange($value, $min = null, $max = null)
    {
        if ((strval($value) !== '')) {
            if (!is_null($min)) {
                if ($value < $min) return false; 
            }

            if (!is_null($max)) {
                if ($value > $max) return false; 
            }
        }

        return true;
    }
    /**
     * Get text validator
     * 
     * @param boolean $isRequired
     * @param int $minLength
     * @param int $maxLength
     * 
     * @return Zend_Validate
     */
    protected function getTextValidator($isRequired = false, $minLength = null, $maxLength = null)
    {
        $validator = $this->createValidatorChain();
        if ($isRequired) {
            $validator->addValidator(new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::STRING), true);
        }

        if (!is_null($minLength) || !is_null($maxLength)) {
            $options = array();
            if (!is_null($minLength)) {
                $options['min'] = $minLength;
            }

            if (!is_null($maxLength)) {
                $options['max'] = $maxLength;
            }

            $validator->addValidator(new Zend_Validate_StringLength($options), true);
        }

        return $validator;
    }
    /**
     * Get integer validator
     * 
     * @param boolean $isRequired
     * @param int $min
     * @param int $max
     * 
     * @return Zend_Validate
     */
    protected function getIntegerValidator($isRequired = false, $min = null, $max = null)
    {
        $validator = $this->createValidatorChain();
        if ($isRequired) {
            $validator->addValidator(new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::INTEGER), true);
        }

        $validator->addValidator(new Zend_Validate_Int(), true);
        if (!is_null($min) || !is_null($max)) {
            $validator->addValidator(
                new Zend_Validate_Callback(
                    array(
                    'callback' => array($this, 'validateRange'), 'options' => array($min, $max), 
                    )
                ), true
            );
        }

        return $validator;
    }
    /**
     * Get float validator
     * 
     * @param boolean $isRequired
     * @param int $min
     * @param int $max
     * 
     * @return Zend_Validate
     */
    protected function getFloatValidator($isRequired = false, $min = null, $max = null)
    {
        $validator = $this->createValidatorChain();
        if ($isRequired) {
            $validator->addValidator(new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::FLOAT), true);
        }

        $validator->addValidator(new Zend_Validate_Float(), true);
        if (!is_null($min) || !is_null($max)) {
            $validator->addValidator(
                new Zend_Validate_Callback(
                    array(
                    'callback' => array($this, 'validateRange'), 'options' => array($min, $max), 
                    )
                ), true
            );
        }

        return $validator;
    }
    /**
     * Get filters
     * 
     * @return array
     */
    protected function getFilters()
    {
        return array();
    }
    /**
     * Filter model
     *
     * @throws Mage_Core_Exception
     * 
     * @return MP_Warehouse_Model_Core_Abstract
     */
    public function filter()
    {
        $filters = $this->getFilters();
        foreach ($filters as $field => $filter) {
            $this->setData($field, $filter->filter($this->getData($field)));
        }

        return $this;
    }
    /**
     * Get validators
     * 
     * @return array
     */
    protected function getValidators()
    {
        return array();
    }
    /**
     * Validate model
     * 
     * @throws Mage_Core_Exception
     * 
     * @return bool
     */
    public function validate()
    {
        $validators = $this->getValidators();
        $errorMessages = array();
        foreach ($validators as $field => $validator) {
            if (!$validator->isValid($this->getData($field))) {
                $errorMessages = array_merge($errorMessages, $validator->getMessages());
            }
        }

        if (count($errorMessages)) {
            Mage::throwException(join("\n", $errorMessages));
        }

        return true;
    }
    /**
     * Processing object before save data
     *
     * @return MP_Warehouse_Model_Core_Abstract
     */
    protected function _beforeSave()
    {
        $this->filter();
        $this->validate();
        parent::_beforeSave();
        return $this;
    }
    /**
     * Add data
     * 
     * @param array $arr
     * 
     * @return MP_Warehouse_Model_Core_Abstract
     */
    public function addData(array $arr)
    {
        Mage::dispatchEvent(
            $this->_eventPrefix.'_add_data_before', 
            array_merge(
                $this->_getEventData(), 
                array('array' => $arr)
            )
        );
        parent::addData($arr);
        Mage::dispatchEvent(
            $this->_eventPrefix.'_add_data_after', 
            array_merge(
                $this->_getEventData(), 
                array('array' => $arr)
            )
        );
        return $this;
    }
}
