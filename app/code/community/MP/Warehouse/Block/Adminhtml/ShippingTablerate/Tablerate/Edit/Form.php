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
 * Table rate edit form
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Shippingtablerate_Tablerate_Edit_Form 
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Form
{
    /**
     * Form field name suffix
     *
     * @var string
     */
    protected $_formFieldNameSuffix = 'shippingtablerate';
    /**
     * Form HTML identifier prefix
     *
     * @var string
     */
    protected $_formHtmlIdPrefix = 'shippingtablerate_';
    /**
     * Form field set identifier
     *
     * @var string
     */
    protected $_formFieldsetId = 'shippingtablerate_fieldset';
    /**
     * Form field set legend
     *
     * @var string
     */
    protected $_formFieldsetLegend = '';
    /**
     * Model name
     *
     * @var string
     */
    protected $_modelName = 'shippingtablerate';
    /**
     * Retrieve shipping table rate helper
     *
     * @return MP_Warehouse_Helper_ShippingTablerate_Data
     */
    protected function getShippingTablerateHelper()
    {
        return Mage::helper('warehouse/shippingTablerate_data');
    }
    /**
     * Retrieve text helper
     *
     * @return MP_Warehouse_Helper_ShippingTablerate_Data
     */
    public function getTextHelper()
    {
        return $this->getShippingTablerateHelper();
    }
    /**
     * Check is allowed action
     *
     * @param   string $action
     * @return  bool
     */
    protected function isAllowedAction($action)
    {
        return $this->getAdminSession()->isAllowed('sales/shipping/tablerates/'.$action);
    }
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
        $regions            = array(array('value' => '', 'label' => '*'));
        $model              = $this->getModel();
        $destCountryId      = $model->getDestCountryId();
        if ($destCountryId) {
            $regionCollection = Mage::getModel('directory/region')
                ->getCollection()
                ->addCountryFilter($destCountryId);
            $regions = $regionCollection->toOptionArray();
            if (isset($regions[0])) {
                $regions[0]['label'] = '*';
            }
        }

        return $regions;
    }
    /**
     * Get condition name values
     *
     * @return array
     */
    protected function getConditionNameValues()
    {
        return Mage::getModel('adminhtml/system_config_source_shipping_tablerate')->toOptionArray();
    }
    /**
     * Get zip value
     *
     * @return string
     */
    protected function getZipValue()
    {
        $model = $this->getModel();
        $destZip = $model->getDestZip();
        return (($destZip == '*') || ($destZip == '')) ? '*' : $destZip;
    }
    /**
     * Prepare form before rendering HTML
     *
     * @return MP_Warehouse_Block_Adminhtml_ShippingTablerate_Tablerate_Edit_Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $helper                 = $this->getWarehouseHelper();
        $textHelper         = $this->getTextHelper();
        $model              = $this->getModel();
        $isElementDisabled  = ($this->isSaveAllowed()) ? false : true;
        $form               = $this->getForm();
        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getData('action'));
        $form->setMethod('post');
        $fieldset = $this->getFieldset();
        if ($model->getId()) {
            $fieldset->addField(
                'pk', 'hidden', array(
                'name'      => 'pk',
                'value'     => $model->getId(),
                )
            );
        }

        $fieldset->addField(
            'website_id', 'hidden', array(
            'name'       => 'website_id',
            'value'      => $model->getWebsiteId(),
            )
        );
        $fieldset->addField(
            'warehouse_id', 'select', array(
            'name'          => 'warehouse_id',
            'label'         => $textHelper->__('Warehouse'),
            'title'         => $textHelper->__('Warehouse'),
            'required'      => false,
            'value'         => $model->getWarehouseId(),
            'values'        => $this->getWarehouseValues(),
            'disabled'      => $isElementDisabled,
            )
        );
        $fieldset->addField(
            'dest_country_id', 'select', array(
            'name'       => 'dest_country_id',
            'label'      => $textHelper->__('Dest Country'),
            'title'      => $textHelper->__('Dest Country'),
            'required'   => false,
            'value'         => $model->getDestCountryId(),
            'values'     => $this->getCountryValues(),
            'disabled'   => $isElementDisabled,
            )
        );
        $fieldset->addField(
            'dest_region_id', 'select', array(
            'name'       => 'dest_region_id',
            'label'      => $textHelper->__('Dest Region/State'),
            'title'      => $textHelper->__('Dest Region/State'),
            'required'   => false,
            'value'         => $model->getDestRegionId(),
            'values'     => $this->getRegionValues(),
            'disabled'   => $isElementDisabled,
            )
        );
        $fieldset->addField(
            'dest_zip', 'text', array(
            'name'       => 'dest_zip',
            'label'      => $textHelper->__('Dest Zip/Postal Code'),
            'title'      => $textHelper->__('Dest Zip/Postal Code'),
            'note'       => $textHelper->__('* or blank - matches any'),
            'required'   => false,
            'value'         => $this->getZipValue(),
            'disabled'   => $isElementDisabled,
            )
        );
        $fieldset->addField(
            'condition_name', 'select', array(
            'name'       => 'condition_name',
            'label'      => $textHelper->__('Condition Name'),
            'title'      => $textHelper->__('Condition Name'),
            'required'   => true,
            'value'         => $model->getConditionName(),
            'values'     => $this->getConditionNameValues(),
            'disabled'   => $isElementDisabled,
            )
        );
        $fieldset->addField(
            'condition_value', 'text', array(
            'name'       => 'condition_value',
            'label'      => $textHelper->__('Condition Value'),
            'title'      => $textHelper->__('Condition Value'),
            'required'   => true,
            'value'         => floatval($model->getConditionValue()),
            'disabled'   => $isElementDisabled,
            )
        );
        $fieldset->addField(
            'method_id', 'select', array(
            'name'          => 'method_id',
            'label'         => $textHelper->__('Method'),
            'title'         => $textHelper->__('Method'),
            'required'      => true,
            'value'         => $model->getMethodId(),
            'values'        => $this->getMethodValues(),
            'disabled'      => $isElementDisabled,
            )
        );
        $fieldset->addField(
            'price', 'text', array(
            'name'       => 'price',
            'label'      => $textHelper->__('Price'),
            'title'      => $textHelper->__('Price'),
            'required'   => true,
            'value'         => floatval($model->getPrice()),
            'disabled'   => $isElementDisabled,
            )
        );
        $fieldset->addField(
            'cost', 'text', array(
            'name'       => 'cost',
            'label'      => $textHelper->__('Cost'),
            'title'      => $textHelper->__('Cost'),
            'required'   => true,
            'value'         => floatval($model->getCost()),
            'disabled'   => $isElementDisabled,
            )
        );
        $fieldset->addField(
            'note', 'textarea', array(
            'name'       => 'note',
            'label'      => $textHelper->__('Notes'),
            'title'      => $textHelper->__('Notes'),
            'required'   => false,
            'value'         => $model->getNote(),
            'disabled'   => $isElementDisabled,
            )
        );
        $this->dispatchPrepareFormEvent();
        return $this;
    }
    /**
     * Retrieve warehouse helper
     *
     * @return MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Get warehouse values
     * 
     * @return array
     */
    protected function getWarehouseValues()
    {
        return $this->getWarehouseHelper()
            ->getWarehousesOptions(false, '*', '0');
    }
    /**
     * Get warehouse values
     * 
     * @return array
     */
    protected function getMethodValues()
    {
        return $this->getWarehouseHelper()
            ->getShippingTablerateMethodsOptions(true);
    }
}
