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
 * Warehouse
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Warehouse 
    extends MP_Warehouse_Model_Core_Abstract
{
    /**
     * Prefix of model events names
     * 
     * @var string
     */
    protected $_eventPrefix = 'warehouse';
    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getItem() in this case
     * 
     * @var string
     */
    protected $_eventObject = 'warehouse';
    /**
     * Model cache tag for clear cache in after save and after delete
     *
     * When you use true - all cache will be clean
     *
     * @var string || true
     */
    protected $_cacheTag = 'warehouse';
    /**
     * Warehouses
     *
     * @var array
     */
    protected $_warehouses;
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('warehouse/warehouse');
    }
    /**
     * Get warehouse helper
     * 
     * @return MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
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
            $country                = $this->getAddressHelper()
                ->castCountryId($country);
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
        return $this->getTextFilter()
            ->appendFilter(
                new Zend_Filter_Callback(
                    array(
                    'callback' => array($this, 'filterCountry'), 
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
            'code'                  => $this->getTextFilter(), 
            'title'                 => $this->getTextFilter(), 
            'description'           => $this->getTextFilter(), 
            'priority'              => $this->getIntegerFilter(), 
            'notify'                => $this->getIntegerFilter(), 
            'contact_name'          => $this->getTextFilter(), 
            'contact_email'         => $this->getTextFilter(), 
            'origin_country_id'     => $this->getCountryFilter(), 
            'origin_region_id'      => $this->getTextFilter(), 
            'origin_postcode'       => $this->getTextFilter(), 
            'origin_city'           => $this->getTextFilter(), 
            'origin_street1'        => $this->getTextFilter(), 
            'origin_street2'        => $this->getTextFilter(), 
        );
    }
    /**
     * Get code validator
     * 
     * @return Zend_Validate
     */
    protected function getCodeValidator()
    {
        $helper                 = $this->getWarehouseHelper();
        $validator              = new Zend_Validate_Regex(array('pattern' => '/^[a-z]+[a-z0-9_]*$/'));
        $validator->setMessage(
            $helper->__(
                'Warehouse code may only contain letters (a-z), numbers (0-9) or underscore(_), the first character must be a letter'
            ), 
            Zend_Validate_Regex::NOT_MATCH
        );
        return $this->getTextValidator(true, 0, 32)
            ->addValidator($validator);
    }
    /**
     * Get contact email validator
     * 
     * @return Zend_Validate
     */
    protected function getContactEmailValidator()
    {
        $validator              = $this->getTextValidator(false, 0, 64);
        if ($this->getContactEmail()) {
            $validator->addValidator(new Zend_Validate_EmailAddress());
        }

        return $validator;
    }
    /**
     * Get validators
     * 
     * @return array
     */
    protected function getValidators()
    {
        return array(
            'code'                  => $this->getCodeValidator(), 
            'title'                 => $this->getTextValidator(true, 0, 128), 
            'description'           => $this->getTextValidator(false, 0, 512), 
            'priority'              => $this->getIntegerValidator(false, 0), 
            'notify'                => $this->getIntegerValidator(false, 0), 
            'contact_name'          => $this->getTextValidator(false, 0, 64), 
            'contact_email'         => $this->getContactEmailValidator(), 
            'origin_country_id'     => $this->getTextValidator(true, 0, 4), 
            'origin_region_id'      => $this->getTextValidator(true, 0, 100), 
            'origin_postcode'       => $this->getTextValidator(true, 0, 50), 
            'origin_city'           => $this->getTextValidator(true, 0, 100), 
            'origin_street1'        => $this->getTextValidator(true, 0, 255), 
            'origin_street2'        => $this->getTextValidator(false, 0, 255), 
        );
    }
    /**
     * Validate warehouse
     *
     * @throws Mage_Core_Exception
     * 
     * @return bool
     */
    public function validate()
    {
        $helper                 = $this->getWarehouseHelper();
        parent::validate();
        $errorMessages          = array();
        $warehouse              = Mage::getModel('warehouse/warehouse')
            ->loadByCode($this->getCode(), $this->getId());
        if ($warehouse->getId()) {
            array_push(
                $errorMessages, 
                $helper->__('Warehouse with the same code already exists.')
            );
        }

        $warehouse              = Mage::getModel('warehouse/warehouse')
            ->loadByTitle($this->getTitle(), $this->getId());
        if ($warehouse->getId()) {
            array_push(
                $errorMessages, 
                $helper->__('Warehouse with the same title already exists.')
            );
        }

        if (count($errorMessages)) {
            Mage::throwException(join("\n", $errorMessages));
        }

        return true;
    }
    /**
     * Preset stock id
     * 
     * @return self
     */
    protected function presetStockId()
    {
        $stock                  = $this->getWarehouseHelper()
            ->getCatalogInventoryHelper()
            ->getStock();
        $stockId                = $this->getStockId();
        if ($stockId) {
            $stock->load($stockId);
        }

        $stock->setStockName($this->getTitle());
        $stock->save();
        $this->setStockId($stock->getId());
        return $this;
    }
    /**
     * Delete stock
     * 
     * @return self
     */
    protected function deleteStock()
    {
        $stockId                = $this->getStockId();
        if ($stockId) {
            $stock                  = $this->getWarehouseHelper()
                ->getCatalogInventoryHelper()
                ->getStock();
            $stock->load($stockId);
            $stock->delete();
        }

        return $this;
    }
    /**
     * Preset origin region
     *
     * @return self
     */
    protected function presetOriginRegion()
    {
        $regionId               = $this->getOriginRegionId();
        if ($regionId) {
            if (is_numeric($regionId)) {
                $region                 = $this->getAddressHelper()
                    ->getRegionById($regionId);
                $this->setOriginRegionId($region->getId());
                $this->setOriginRegion($region->getName());
            } else {
                $this->setOriginRegionId(null);
                $this->setOriginRegion($regionId);
            }
        } else {
            $this->setOriginRegionId(null);
        }

        return $this;
    }
    /**
     * Before save
     *
     * @return self
     */
    protected function _beforeSave()
    {
        $regionId               = ($this->getOriginRegionId()) ? 
            $this->getOriginRegionId() : 
            $this->getOriginRegion();
        $this->setOriginRegionId($regionId);
        $this->filter();
        $this->validate();
        parent::_beforeSave();
        $this->presetStockId();
        $this->presetOriginRegion();
        return $this;
    }
    /**
     * After save
     * 
     * @return self
     */
    protected function _afterSave()
    {
        parent::_afterSave();
        return $this;
    }
    /**
     * Before delete
     * 
     * @return self
     */
    protected function _beforeDelete()
    {
        if ($this->getWarehouseHelper()->getDefaultStockId() == $this->getStockId()) {
            $helper                 = $this->getWarehouseHelper();
            Mage::throwException($helper->__('The default warehouse can\'t be deleted.'));
        }

        parent::_beforeDelete();
        return $this;
    }
    /**
     * After delete
     * 
     * @return self
     */
    protected function _afterDelete()
    {
        $this->deleteStock();
        return parent::_afterDelete();
    }
    /**
     * Get origin street
     * 
     * @return array|null
     */
    public function getOriginStreet()
    {
        $street                 = array();
        if ($this->getOriginStreet1()) {
            array_push($street, $this->getOriginStreet1());
        }

        if ($this->getOriginStreet2()) {
            array_push($street, $this->getOriginStreet2());
        }

        return (count($street)) ? $street : null;
    }
    /**
     * Check if origin street is set
     * 
     * @return bool
     */
    public function hasOriginStreet()
    {
        return ($this->getOriginStreet() !== null) ? true : false;
    }
    /**
     * Get origin
     * 
     * @return Varien_Object
     */
    public function getOrigin()
    {
        $this->hasData();
        $origin                 = new Varien_Object();
        if ($this->getOriginCountryId()) {
            $origin->setCountryId($this->getOriginCountryId());
        }

        if ($this->getOriginRegionId()) {
            $origin->setRegionId($this->getOriginRegionId());
        }

        if ($this->getOriginRegion()) {
            $origin->setRegion($this->getOriginRegion());
        }

        if ($this->getOriginPostcode()) {
            $origin->setPostcode($this->getOriginPostcode());
        }

        if ($this->getOriginCity()) {
            $origin->setCity($this->getOriginCity());
        }

        if ($this->getOriginStreet1()) {
            $origin->setStreet1($this->getOriginStreet1());
        }

        if ($this->getOriginStreet2()) {
            $origin->setStreet2($this->getOriginStreet2());
        }

        if ($this->getOriginStreet()) {
            $origin->setStreet($this->getOriginStreet());
        }

        return $origin;
    }
    /**
     * Get origin string
     * 
     * @return string
     */
    public function getOriginString()
    {
        if (!$this->hasData('origin_string')) {
            $this->setData(
                'origin_string', 
                $this->getWarehouseHelper()
                    ->getAddressHelper()
                    ->format($this->getOrigin())
            );
        }

        return $this->getData('origin_string');
    }
    /**
     * Check if notification enabled
     * 
     * @return bool
     */
    public function isNotify()
    {
        return ($this->getNotify()) ? true : false;
    }
    /**
     * Check if contact information is set
     * 
     * @return bool
     */
    public function isContactSet()
    {
        return ($this->getContactName() && $this->getContactEmail()) ? true : false;
    }
    /**
     * Load warehouse by code
     * 
     * @param string $code
     * @param int $exclude
     * 
     * @return self
     */
    public function loadByCode($code, $exclude = null)
    {
        $this->_getResource()
            ->loadByCode($this, $code, $exclude);
        $this->setOrigData();
        return $this;
    }
    /**
     * Load warehouse by title
     * 
     * @param string $title
     * @param int $exclude
     * 
     * @return self
     */
    public function loadByTitle($title, $exclude = null)
    {
        $this->_getResource()
            ->loadByTitle($this, $title, $exclude);
        $this->setOrigData();
        return $this;
    }
    /**
     * Get stores identifiers
     * 
     * @return array
     */
    public function getStores()
    {
        if (is_null($this->getData('stores'))) {
            $this->setData('stores', $this->_getResource()->getStores($this));
        }

        return $this->getData('stores');
    }
    /**
     * Get shipping carriers
     * 
     * @return array
     */
    public function getShippingCarriers()
    {
        if (is_null($this->getData('shipping_carriers'))) {
            $this->setData('shipping_carriers', $this->_getResource()->getShippingCarriers($this));
        }

        return $this->getData('shipping_carriers');
    }
}
