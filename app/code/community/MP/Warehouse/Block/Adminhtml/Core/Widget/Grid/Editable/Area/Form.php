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
 * Editable grid area form block
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Editable_Area_Form
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Editable_Form
{
    /**
     * Get country values
     * 
     * @return array
     */
    protected function getCountryValues()
    {
        $countries = Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(false);
        if (isset($countries[0])) {
            $countries[0]['label'] = '*';
        }

        return $countries;
    }
    /**
     * Get region values
     * 
     * @return array
     */
    protected function getRegionValues()
    {
        $regions    = array(array('value' => '', 'label' => '*'));
        $model      = $this->getModel();
        $countryId  = $model->getCountryId();
        if ($countryId) {
            $regionCollection   = Mage::getModel('directory/region')->getCollection()->addCountryFilter($countryId);
            $regions            = $regionCollection->toOptionArray();
            if (isset($regions[0])) {
                $regions[0]['label'] = '*';
            }
        }

        return $regions;
    }
    /**
     * Get zip value
     * 
     * @return string
     */
    protected function getZipValue()
    {
        $zip = ($this->getModel()) ? $this->getModel()->getZip() : null;
        return (($zip == '*') || ($zip == '')) ? '*' : $zip;
    }
    /**
     * Prepare form before rendering HTML
     *
     * @return MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Editable_Area_Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $fieldset = $this->getFieldset();
        if ($fieldset) {
            $helper     = $this->getTextHelper();
            $model      = $this->getModel();
            $isElementDisabled = !$this->isSaveAllowed();
            $fieldset->addField(
                'country_id', 'select', array(
                'name'       => 'country_id', 
                'label'      => $helper->__('Country'), 
                'title'      => $helper->__('Country'), 
                'required'   => false, 
                'value'         => $model->getCountryId(), 
                'default'    => '0', 
                'values'     => $this->getCountryValues(), 
                'disabled'   => $isElementDisabled, 
                )
            );
            $fieldset->addField(
                'region_id', 'select', array(
                'name'       => 'region_id', 
                'label'      => $helper->__('Region / State'), 
                'title'      => $helper->__('Region / State'), 
                'required'   => false, 
                'value'         => $model->getRegionId(), 
                'default'    => '0', 
                'values'     => $this->getRegionValues(), 
                'disabled'   => $isElementDisabled, 
                )
            );
            $fieldset->addField(
                'zip', 'text', array(
                'name'       => 'zip', 
                'label'      => $helper->__('Zip / Postal Code'), 
                'title'      => $helper->__('Zip / Postal Code'), 
                'note'       => $helper->__('* or blank - matches any'), 
                'required'   => false, 
                'value'         => $this->getZipValue(), 
                'default'    => '', 
                'disabled'   => $isElementDisabled, 
                )
            );
        }

        return $this;
    }
}
