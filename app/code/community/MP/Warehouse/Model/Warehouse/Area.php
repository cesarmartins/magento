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
 * Warehouse area
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Warehouse_Area 
    extends MP_Warehouse_Model_Core_Area_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('warehouse/warehouse_area');
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
     * Get filters
     * 
     * @return array
     */
    protected function getFilters()
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        $filters                = array(
            'country_id'            => $this->getCountryFilter(), 
            'region_id'             => $this->getRegionFilter('country_id'), 
            'is_zip_range'          => $this->getIntegerFilter(), 
            'zip'                   => $this->getZipFilter(), 
            'from_zip'              => $this->getTextFilter(), 
            'to_zip'                => $this->getTextFilter(), 
        );
        if ($config->isMultipleMode()) {
            $filters['priority']    = $this->getIntegerFilter();
        }

        return $filters;
    }
    /**
     * Get validators
     * 
     * @return array
     */
    protected function getValidators()
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        $validators             = array(
            'country_id'            => $this->getTextValidator(false, 0, 4), 
            'region_id'             => $this->getIntegerValidator(false, 0), 
            'is_zip_range'          => $this->getIntegerValidator(false, 0), 
        );
        $isZipRange             = $this->getIsZipRange();
        if ($isZipRange) {
            $maxZipValue            = 9999999999;
            $fromZip                = (int) $this->getFromZip();
            $validators['from_zip'] = $this->getIntegerValidator(true, 1, $maxZipValue);
            $validators['to_zip']   = $this->getIntegerValidator(true, $fromZip, $maxZipValue);
        } else {
            $validators['zip']      = $this->getTextValidator(false, 0, 10);
        }

        if ($config->isMultipleMode()) {
            $validators['priority'] = $this->getIntegerValidator(true, 0);
        }

        return $validators;
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
        $helper                 = $this->getWarehouseHelper();
        if (parent::validate()) {
            $isZipRange             = $this->getIsZipRange();
            if ($isZipRange) {
                $this->setZip($this->getFromZip().'-'.$this->getToZip());
            } else {
                $this->setFromZip(null);
                $this->setToZip(null);
            }

            $errorMessages          = array();
            $warehouseArea          = Mage::getModel('warehouse/warehouse_area')
                ->loadByRequest($this);
            if ($warehouseArea->getId()) {
                array_push($errorMessages, $helper->__('Duplicate area.'));
            }

            if (count($errorMessages)) {
                Mage::throwException(join("\n", $errorMessages));
            }

            return true;
        } else {
            return false;
        }
    }
    /**
     * Processing object before save data
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        $this->filter();
        $this->validate();
        parent::_beforeSave();
        return $this;
    }
    /**
     * Load warehouse area by request
     * 
     * @param Varien_Object $request
     * 
     * @return $this
     */
    public function loadByRequest(Varien_Object $request)
    {
        $this->_getResource()
            ->loadByRequest($this, $request);
        $this->setOrigData();
        return $this;
    }
}
