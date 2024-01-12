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
 * Warehouse edit main tab
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Warehouse_Edit_Tab_Main
    extends MP_Warehouse_Block_Adminhtml_Warehouse_Edit_Tab_Abstract
{
    /**
     * Form field set identifier
     * 
     * @var string
     */
    protected $_formFieldsetId = 'main_fieldset';
    /**
     * Form field set legend
     * 
     * @var string
     */
    protected $_formFieldsetLegend = 'General';
    /**
     * Tab title
     * 
     * @var string
     */
    protected $_title = 'General';
    /**
     * Prepare form before rendering HTML
     *
     * @return MP_Warehouse_Block_Adminhtml_Warehouse_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $helper = $this->getWarehouseHelper();
        $model = $this->getModel();
        $isElementDisabled = ($this->isSaveAllowed()) ? false : true;
        $fieldset = $this->getFieldset();
        if ($model->getId()) {
            $fieldset->addField(
                'warehouse_id', 'hidden', array(
                'name' => 'warehouse_id', 
                'value' => $model->getId()
                )
            );
        }

        $fieldset->addField(
            'code', 'text', array(
            'name'      => 'code', 
            'label'     => $helper->__('Code'), 
            'title'     => $helper->__('Code'), 
            'required'  => true, 
            'disabled'  => $isElementDisabled, 
            'value'     => $model->getCode(), 
            )
        );
        $fieldset->addField(
            'title', 'text', array(
            'name'      => 'title', 
            'label'     => $helper->__('Title'), 
            'title'     => $helper->__('Title'), 
            'required'  => true, 
            'disabled'  => $isElementDisabled, 
            'value'     => $model->getTitle(), 
            )
        );
        $fieldset->addField(
            'description', 'textarea', array(
            'name'      => 'description', 
            'label'     => $helper->__('Description'), 
            'title'     => $helper->__('Description'), 
            'required'  => false, 
            'disabled'  => $isElementDisabled, 
            'value'     => $model->getDescription(), 
            )
        );

		$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'marca'); //"color" is the attribute_code
		$allOptions = $attribute->getSource()->getAllOptions(true, true);
		$options = [];
		foreach($allOptions as $op){
			$options[$op['value']] = $op['label'];

		}
		$fieldset->addField(
	            'marca', 'select', array(
	            'name'	=> 'marca',
	            'label'     => $helper->__('Selecione a Marca'),
	            'title'     => $helper->__('Marca'),
	            'required'  => true,
	            'disabled'  => $isElementDisabled,
	            'value'     => $model->getMarca(),
		    	'options'	=> $options
	         )
		);

        $config = $helper->getConfig();
        if ($config->isPriorityEnabled()) {
            $fieldset->addField(
                'priority', 'text', array(
                'name'      => 'priority', 
                'label'     => $helper->__('Priority'), 
                'title'     => $helper->__('Priority'), 
                'required'  => false, 
                'disabled'  => $isElementDisabled, 
                'value'     => $model->getPriority(), 
                )
            );
        }

        $this->dispatchPrepareFormEvent();
        return $this;
    }
}