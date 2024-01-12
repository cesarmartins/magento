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
 * Table rate method edit form
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_ShippingTablerate_Tablerate_Method_Edit_Form 
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Form
{
    /**
     * Form field name suffix
     * 
     * @var string
     */
    protected $_formFieldNameSuffix = 'shippingtablerate_method';
    /**
     * Form HTML identifier prefix
     * 
     * @var string
     */
    protected $_formHtmlIdPrefix = 'shippingtablerate_method_';
    /**
     * Form field set identifier
     * 
     * @var string
     */
    protected $_formFieldsetId = 'shippingtablerate_method_fieldset';
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
    protected $_modelName = 'shippingtablerate_method';
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
     * Get text helper
     *
     * @return MP_Warehouse_Helper_Data
     */
    public function getTextHelper()
    {
        return $this->getWarehouseHelper();
    }
    /**
     * Check is allowed action
     * 
     * @param string $action
     * 
     * @return bool
     */
    protected function isAllowedAction($action)
    {
        if (($action == 'delete') && (1 == $this->getModel()->getId())) {
            return false;
        }

        return $this->getAdminSession()->isAllowed('sales/shipping/tablerates/methods');
    }
    /**
     * Prepare form before rendering HTML
     *
     * @return MP_Warehouse_Block_Adminhtml_Shippingtablerate_Tablerate_Edit_Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $textHelper             = $this->getTextHelper();
        $model                  = $this->getModel();
        $isElementDisabled      = ($this->isSaveAllowed()) ? false : true;
        $form                   = $this->getForm();
        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getData('action'));
        $form->setMethod('post');
        $fieldset               = $this->getFieldset();
        if ($model->getId()) {
            $fieldset->addField(
                'method_id', 'hidden', array(
                'name'      => 'method_id', 
                'value'     => $model->getId(), 
                )
            );
        }

        $fieldset->addField(
            'code', 'text', array(
            'name'      => 'code', 
            'label'     => $textHelper->__('Code'), 
            'title'     => $textHelper->__('Code'), 
            'required'  => true, 
            'disabled'  => $isElementDisabled, 
            'value'     => $model->getCode(), 
            )
        );
        $fieldset->addField(
            'name', 'text', array(
            'name'      => 'name', 
            'label'     => $textHelper->__('Name'), 
            'title'     => $textHelper->__('Name'), 
            'required'  => true, 
            'disabled'  => $isElementDisabled, 
            'value'     => $model->getName(), 
            )
        );
        $this->dispatchPrepareFormEvent();
        return $this;
    }
}
