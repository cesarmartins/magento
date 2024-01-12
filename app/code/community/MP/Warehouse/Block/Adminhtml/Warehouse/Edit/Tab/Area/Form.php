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
 * Warehouse area form
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Warehouse_Edit_Tab_Area_Form 
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Editable_Area_Form
{
    /**
     * Form field name suffix
     * 
     * @var string
     */
    protected $_formFieldNameSuffix = 'warehouse_area';
    /**
     * Form HTML identifier prefix
     * 
     * @var string
     */
    protected $_formHtmlIdPrefix = 'warehouse_area_';
    /**
     * Form field set identifier
     * 
     * @var string
     */
    protected $_formFieldsetId = 'warehouse_area_fieldset';
    /**
     * Form field set legend
     * 
     * @var string
     */
    protected $_formFieldsetLegend = 'Area';
    /**
     * Model name
     * 
     * @var string
     */
    protected $_modelName = 'warehouse_area';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('warehouseAreaTabForm');
    }
    /**
     * Get warehouse helper
     * 
     * @return Varien_Object
     */
    public function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Retrieve registered product model
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function getWarehouse()
    {
        return Mage::registry('warehouse');
    }
    /**
     * Check is allowed action
     * 
     * @param   string $action
     * 
     * @return  bool
     */
    protected function isAllowedAction($action)
    {
        return $this->getAdminSession()
            ->isAllowed('catalog/warehouses/'.$action);
    }
    /**
     * Retrieve save URL
     *
     * @return string
     */
    public function getSaveUrl()
    {
        $model                  = $this->getModel();
        $params                 = array('warehouse_id' => $this->getWarehouse()->getId());
        if ($model->getId()) {
            $params['warehouse_area_id'] = $model->getId();
        }

        return $this->getUrl('*/*/saveArea', $params);
    }
    /**
     * Get is zip range options
     * 
     * @return array
     */
    protected function getIsZipRangeOptions()
    {
        $helper                 = $this->getWarehouseHelper();
        return array(
            '0' => $helper->__('No'),
            '1' => $helper->__('Yes'),
        );
    }
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $fieldset               = $this->getFieldset();
        if (!$fieldset) {
            return $this;
        }

        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        $model                  = $this->getModel();
        $isElementDisabled      = !$this->isSaveAllowed();
        $fieldset->addField(
            'warehouse_area_id', 
            'hidden', 
            array(
                'name'                  => 'warehouse_area_id', 
                'value'                 => $model->getId(), 
                'default'               => '', 
            )
        );
        $fieldset->addField(
            'is_zip_range', 
            'select', 
            array(
                'name'                  => 'is_zip_range', 
                'label'                 => $helper->__('Zip/Postal Code is Range'), 
                'title'                 => $helper->__('Zip/Postal Code is Range'), 
                'required'              => false, 
                'options'               => $this->getIsZipRangeOptions(), 
                'value'                 => (($model->getIsZipRange()) ? '1' : '0'), 
                'default'               => '0', 
                'disabled'              => $isElementDisabled, 
            ), 
            'region_id'
        );
        $fieldset->removeField('zip');
        $fieldset->addField(
            'zip', 
            'text', 
            array(
                'name'                  => 'zip', 
                'label'                 => $helper->__('Zip/Postal Code'), 
                'title'                 => $helper->__('Zip/Postal Code'), 
                'note'                  => $helper->__('\'*\' - matches any.'), 
                'required'              => false, 
                'value'                 => $this->getZipValue(), 
                'default'               => '', 
                'disabled'              => $isElementDisabled, 
            ), 
            'is_zip_range'
        );
        $fieldset->addField(
            'from_zip', 
            'text', 
            array(
                'name'                  => 'from_zip', 
                'label'                 => $helper->__('Zip/Postal Code From'), 
                'title'                 => $helper->__('Zip/Postal Code From'), 
                'required'              => true, 
                'value'                 => $model->getFromZip(), 
                'disabled'              => $isElementDisabled, 
                'class'                 => 'validate-digits', 
            ), 
            'zip'
        );
        $fieldset->addField(
            'to_zip', 
            'text', 
            array(
                'name'                  => 'to_zip', 
                'label'                 => $helper->__('Zip/Postal Code To'), 
                'title'                 => $helper->__('Zip/Postal Code To'), 
                'required'              => true, 
                'value'                 => $model->getToZip(), 
                'disabled'              => $isElementDisabled, 
                'class'                 => 'validate-digits', 
            ), 
            'from_zip'
        );
        if ($config->isMultipleMode() && $config->isAssignedAreaMultipleAssignmentMethod()) {
            $fieldset->addField(
                'priority', 
                'text', 
                array(
                    'name'                  => 'priority', 
                    'label'                 => $helper->__('Priority'), 
                    'title'                 => $helper->__('Priority'), 
                    'required'              => true, 
                    'value'                 => $model->getPriority(), 
                    'disabled'              => $isElementDisabled, 
                    'class'                 => 'validate-digits', 
                ), 
                'from_zip'
            );
        }

        $fieldset->addField(
            'submit_button', 'note', array(
            'text'                  => $this->getButtonHtml(
                $helper->__('Submit'), 
                $this->getJsObjectName().'.submit(\''.$this->getSaveUrl().'\');', 
                'save'
            )
            )
        );
        return $this;
    }
}
