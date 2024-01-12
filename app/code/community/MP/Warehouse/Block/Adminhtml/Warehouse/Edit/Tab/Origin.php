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
 * Warehouse origin tab
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Warehouse_Edit_Tab_Origin 
    extends MP_Warehouse_Block_Adminhtml_Warehouse_Edit_Tab_Abstract
{
    /**
     * Form field set identifier
     * 
     * @var string
     */
    protected $_formFieldsetId = 'origin_fieldset';
    /**
     * Form field set legend
     * 
     * @var string
     */
    protected $_formFieldsetLegend = 'Origin';
    /**
     * Tab title
     * 
     * @var string
     */
    protected $_title = 'Origin';
    /**
     * Retrieve countries values
     * 
     * @return  array
     */
    protected function getCountryValues()
    {
        $source                 = new Mage_Adminhtml_Model_System_Config_Source_Country();
        return $source->toOptionArray(false);
    }
    /**
     * Prepare form before rendering HTML
     *
     * @return self
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $helper                 = $this->getWarehouseHelper();
        $model                  = $this->getModel();
        $isElementDisabled      = ($this->isSaveAllowed()) ? false : true;
        $fieldset               = $this->getFieldset();
        $fieldset->addField(
            'origin_country_id', 'select', array(
            'name'                  => 'origin_country_id', 
            'label'                 => $helper->__('Country'), 
            'title'                 => $helper->__('Country'), 
            'required'              => true, 
            'disabled'              => $isElementDisabled, 
            'values'                => $this->getCountryValues(), 
            'class'                 => 'origin_country_id', 
            'value'                 => $model->getOriginCountryId(), 
            )
        );
        $fieldset->addField(
            'origin_region_id', 'text', array(
            'name'                  => 'origin_region_id', 
            'label'                 => $helper->__('Region/State'), 
            'title'                 => $helper->__('Region/State'), 
            'required'              => true, 
            'disabled'              => $isElementDisabled, 
            'class'                 => 'origin_region_id', 
            'value'                 => (($model->getOriginRegionId()) ? 
                $model->getOriginRegionId() : 
                $model->getOriginRegion()), 
            )
        );
        $fieldset->addField(
            'origin_postcode', 'text', array(
            'name'                  => 'origin_postcode', 
            'label'                 => $helper->__('ZIP/Postal Code'), 
            'title'                 => $helper->__('ZIP/Postal Code'), 
            'required'              => true, 
            'disabled'              => $isElementDisabled, 
            'value'                 => $model->getOriginPostcode(), 
            )
        );
        $fieldset->addField(
            'origin_city', 'text', array(
            'name'                  => 'origin_city', 
            'label'                 => $helper->__('City'), 
            'title'                 => $helper->__('City'), 
            'required'              => true, 
            'disabled'              => $isElementDisabled, 
            'value'                 => $model->getOriginCity(), 
            )
        );
        $fieldset->addField(
            'origin_street1', 'text', array(
            'name'                  => 'origin_street1', 
            'label'                 => $helper->__('Street Address'), 
            'title'                 => $helper->__('Street Address'), 
            'required'              => true, 
            'disabled'              => $isElementDisabled, 
            'value'                 => $model->getOriginStreet1(), 
            )
        );
        $fieldset->addField(
            'origin_street2', 'text', array(
            'name'                  => 'origin_street2', 
            'label'                 => $helper->__('Street Address 2'), 
            'title'                 => $helper->__('Street Address 2'), 
            'required'              => false, 
            'disabled'              => $isElementDisabled, 
            'value'                 => $model->getOriginStreet2(), 
            )
        );

        $fieldset->addField(
            'prazo_envio', 'text', array(
            'name'                  => 'prazo_envio', 
            'label'                 => $helper->__('Prazo de envio'), 
            'title'                 => $helper->__('Prazo de envio'), 
            'required'              => false,
            'class'                 => 'validate-digits',
            'disabled'              => $isElementDisabled, 
            'value'                 => $model->getPrazoEnvio(), 
            )
        );




        $this->dispatchPrepareFormEvent();
        return $this;
    }
}
