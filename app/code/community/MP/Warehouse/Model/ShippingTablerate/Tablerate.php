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
 * Shipping table rate
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_ShippingTablerate_Tablerate
    extends MP_Warehouse_Model_Core_Area_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'shippingtablerate_tablerate';
    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getItem() in this case
     *
     * @var string
     */
    protected $_eventObject = 'tablerate';
    /**
     * Model cache tag for clear cache in after save and after delete
     *
     * When you use true - all cache will be clean
     *
     * @var string || true
     */
    protected $_cacheTag = 'shippingtablerate_tablerate';
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('warehouse/shippingTablerate_tablerate');
    }
    /**
     * Retrieve shipping table rate helper
     *
     * @return MP_Warehouse_Helper_ShippingTablerate_Data
     */
    protected function getTextHelper()
    {
        return Mage::helper('warehouse/shippingTablerate_data');
    }
    /**
     * Get shortened notes
     *
     * @param int $maxLength
     *
     * @return string
     */
    public function getShortNote($maxLength = 50)
    {
        $string = Mage::helper('core/string');
        $note = $this->getData('note');
        return ($string->strlen($note) > $maxLength) ? $string->substr($note, 0, $maxLength).'...' : $note;
    }
    /**
     * Filter condition name
     *
     * @param mixed $value
     *
     * @return string
     */
    public function filterConditionName($value)
    {
        $values = Mage::getSingleton('shipping/carrier_tablerate')->getCode('condition_name');
        return (isset($values[$value])) ? $value : null;
    }
    /**
     * Get condition name filter
     *
     * @return Zend_Filter
     */
    protected function getConditionNameFilter()
    {
        return $this->getTextFilter()->appendFilter(
            new Zend_Filter_Callback(
                array(
                'callback' => array($this, 'filterConditionName'),
                )
            )
        );
    }
    /**
     * Get condition value filter
     *
     * @return Zend_Filter
     */
    protected function getConditionValueFilter()
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
     * Get price filter
     *
     * @return Zend_Filter
     */
    protected function getPriceFilter()
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
     * Get cost filter
     *
     * @return Zend_Filter
     */
    protected function getCostFilter()
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
     * Get filters
     *
     * @return array
     */
    protected function getFilters()
    {
        return array(
            'dest_country_id'     => $this->getCountryFilter(),
            'dest_region_id'      => $this->getRegionFilter('dest_country_id'),
            'dest_zip'            => $this->getZipFilter(),
            'condition_name'      => $this->getConditionNameFilter(),
            'condition_value'     => $this->getConditionValueFilter(),
            'price'               => $this->getPriceFilter(),
            'cost'                => $this->getCostFilter(),
            'note'                => $this->getTextFilter(),
            'warehouse_id'        => $this->getWarehouseFilter(),
            'method_id'           => $this->getMethodFilter()
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
            'dest_country_id'     => $this->getTextValidator(false, 0, 4),
            'dest_region_id'      => $this->getIntegerValidator(false, 0),
            'dest_zip'            => $this->getTextValidator(false, 0, 10),
            'condition_name'      => $this->getTextValidator(true, 0, 30),
            'condition_value'     => $this->getFloatValidator(false, 0),
            'price'               => $this->getFloatValidator(false, 0),
            'cost'                => $this->getFloatValidator(false, 0),
            'note'                => $this->getTextValidator(false, 0, 512),
            'warehouse_id'        => $this->getIntegerValidator(false, 0),
            'method_id'           => $this->getIntegerValidator(true, 0)
        );
    }
    /**
     * Validate catalog inventory stock
     *
     * @throws Mage_Core_Exception
     *
     * @return bool
     */
    public function validate()
    {
        $helper = $this->getTextHelper();
        parent::validate();
        $errorMessages = array();
        $tablerate = Mage::getModel('warehouse/shippingTablerate_tablerate')->loadByRequest($this);
        if ($tablerate->getId()) {
            array_push($errorMessages, $helper->__('Duplicate rate.'));
        }

        if (count($errorMessages)) Mage::throwException(join("\n", $errorMessages));
        return true;
    }
    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        $title = parent::getTitle();
        $conditionNames = Mage::getSingleton('shipping/carrier_tablerate')->getCode('condition_name');
        $conditionName = $this->getConditionName();
        $conditionName = (isset($conditionNames[$conditionName])) ? $conditionNames[$conditionName] : '';
        $conditionValue = $this->getConditionValue();
        $title = implode(
            ', ', array(
            $title,
            (($conditionName) ? $conditionName : ''),
            (($conditionValue) ? floatval($conditionValue) : '0'),
            )
        );

        $helper     = $this->getWarehouseHelper();
        $pieces     = array($title);
        if ($this->getWarehouseId()) {
            $warehouse = $helper->getWarehouse($this->getWarehouseId());
        } else {
            $warehouse = null;
        }

        array_push($pieces, ($warehouse) ? $warehouse->getTitle() : '*');
        if ($this->getMethodId()) {
            $tablerateMethod = $helper->getShippingTablerateMethod($this->getMethodId());
        } else {
            $tablerateMethod = null;
        }

        array_push($pieces, ($tablerateMethod) ? $tablerateMethod->getName() : '');
        return implode(', ', $pieces);
        
        return $title;
    }
    /**
     * Load table rate by request
     *
     * @param Varien_Object $request
     *
     * @return MP_Warehouse_Model_ShippingTablerate_Tablerate
     */
    public function loadByRequest(Varien_Object $request)
    {
        $this->_getResource()->loadByRequest($this, $request);
        $this->setOrigData();
        return $this;
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
     * Filter warehouse
     * 
     * @param mixed $value
     * 
     * @return string
     */
    public function filterWarehouse($value)
    {
        $helper = $this->getWarehouseHelper();
        if ($value && ($value != '*')) {
            $warehouses = $helper->getWarehousesHash();
            if (isset($warehouses[$value])) {
                $value = $value;
            } else if (in_array($value, $warehouses)) {
                $value = array_search($value, $warehouses);
            } else $value = '0';
        } else $value = '0';
        return $value;
    }
    /**
     * Filter method
     * 
     * @param mixed $value
     * 
     * @return string
     */
    public function filterMethod($value)
    {
        $helper             = $this->getWarehouseHelper();
        $tablerateMethods   = $helper->getShippingTablerateMethodsHash();
        if (isset($tablerateMethods[$value])) {
            $value = $value;
        } else if (in_array($value, $tablerateMethods)) {
            $value = array_search($value, $tablerateMethods);
        } else {
            $value = null;
        }

        return $value;
    }
    /**
     * Get warehouse filter
     * 
     * @return Zend_Filter
     */
    protected function getWarehouseFilter()
    {
        return $this->getTextFilter()->appendFilter(
            new Zend_Filter_Callback(
                array(
                'callback' => array($this, 'filterWarehouse'), 
                )
            )
        );
    }
    /**
     * Get method filter
     * 
     * @return Zend_Filter
     */
    protected function getMethodFilter()
    {
        return $this->getTextFilter()->appendFilter(
            new Zend_Filter_Callback(
                array(
                'callback' => array($this, 'filterMethod'), 
                )
            )
        );
    }
}
