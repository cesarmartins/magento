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
 * Adminhtml form
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Core_Widget_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Form field name suffix
     * 
     * @var string
     */
    protected $_formFieldNameSuffix;
    /**
     * Form HTML identifier prefix
     * 
     * @var string
     */
    protected $_formHtmlIdPrefix;
    /**
     * Form field set identifier
     * 
     * @var string
     */
    protected $_formFieldsetId;
    /**
     * Form field set legend
     * 
     * @var string
     */
    protected $_formFieldsetLegend;
    /**
     * Model name
     * 
     * @var string
     */
    protected $_modelName;
    /**
     * Get text helper
     * 
     * @return Varien_Object
     */
    public function getTextHelper()
    {
        return $this;
    }
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
     * Get admin session
     * 
     * @return Mage_Admin_Model_Session
     */
    protected function getAdminSession()
    {
        return $this->getCoreHelper()->getAdminSession();
    }
    /**
     * Get form field name suffix
     * 
     * @return string
     */
    public function getFormFieldNameSuffix()
    {
        return $this->_formFieldNameSuffix;
    }
    /**
     * Get form html identifier prefix
     * 
     * @return string
     */
    public function getFormHtmlIdPrefix()
    {
        return $this->_formHtmlIdPrefix;
    }
    /**
     * Get form field set identifier
     * 
     * @return string
     */
    public function getFormFieldsetId()
    {
        return $this->_formFieldsetId;
    }
    /**
     * Get form field set legend
     * 
     * @return string
     */
    public function getFormFieldsetLegend()
    {
        return $this->getTextHelper()->__($this->_formFieldsetLegend);
    }
    /**
     * Get model name
     * 
     * @return string
     */
    public function getModelName()
    {
        return $this->_modelName;
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
        return true;
    }
    /**
     * Check if save action allowed
     * 
     * @return bool
     */
    public function isSaveAllowed()
    {
        return $this->isAllowedAction('save');
    }
    /**
     * Retrieve registered model
     *
     * @return Varien_Object
     */
    protected function getModel()
    {
        $model = Mage::registry($this->getModelName());
        if (!$model) {
            $model = new Varien_Object();
        }

        return $model;
    }
    /**
     * Get Js object name
     * 
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getId().'JsObject';
    }
    /**
     * Get fieldset
     * 
     * @return Varien_Data_Form_Element_Fieldset
     */
    public function getFieldset()
    {
        $form = $this->getForm();
        if ($form) {
            return $form->getElement($this->getFormFieldsetId());
        } else {
            return null;
        }
    }
    /**
     * Get fields
     * 
     * @return array of Varien_Data_Form_Element_Abstract
     */
    public function getFields()
    {
        $fields = array();
        $fieldset = $this->getFieldset();
        if ($fieldset) {
            foreach ($fieldset->getElements() as $element) {
                if (!($element instanceof Varien_Data_Form_Element_Button) && 
                    !($element instanceof Varien_Data_Form)
                ) {
                    if ($element->getData('name')) {
                        $fields[$element->getData('name')] = $element;
                    }
                }
            }
        }

        return $fields;
    }
    /**
     * Get field names
     * 
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->getFields());
    }
    /**
     * Get defaults
     * 
     * @return array
     */
    public function getDefaults()
    {
        $defaults = array();
        foreach ($this->getFields() as $name => $field) {
            $defaults[$name] = $field->getData('default');
        }

        return $defaults;
    }
    /**
     * Prepare form before rendering
     *
     * @return MP_Warehouse_Block_Adminhtml_Core_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        if ($this->getFormFieldNameSuffix()) {
            $form->setFieldNameSuffix($this->getFormFieldNameSuffix());
        }

        if ($this->getFormHtmlIdPrefix()) {
            $form->setHtmlIdPrefix($this->getFormHtmlIdPrefix());
        }

        $form->addFieldset(
            $this->getFormFieldsetId(), 
            array('legend' => $this->getFormFieldsetLegend())
        );
        $this->setForm($form);
        return $this;
    }
    /**
     * Dispatch prepare form event
     * 
     * @return MP_Warehouse_Block_Adminhtml_Core_Widget_Form
     */
    protected function dispatchPrepareFormEvent()
    {
        Mage::dispatchEvent($this->getFormHtmlIdPrefix().'_prepare_form', array('form' => $this->getForm()));
        return $this;
    }
}
