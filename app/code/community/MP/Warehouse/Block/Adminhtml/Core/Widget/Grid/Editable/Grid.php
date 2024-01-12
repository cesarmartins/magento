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
 * Editable grid block
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Editable_Grid
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Grid
{
    /**
     * Add button label
     * 
     * @var string
     */
    protected $_addButtonLabel;
    /**
     * Form js object name
     * 
     * @var string
     */
    protected $_formJsObjectName;
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
     * Get text helper
     * 
     * @return Varien_Object
     */
    public function getTextHelper()
    {
        return $this;
    }
    /**
     * Get add button label
     * 
     * @return string
     */
    public function getAddButtonLabel()
    {
        return $this->getTextHelper()->__($this->_addButtonLabel);
    }
    /**
     * Get form js object name
     * 
     * @return string
     */
    public function getFormJsObjectName()
    {
        return $this->_formJsObjectName;
    }
    /**
     * Prepare layout
     * 
     * @return MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Editable_Grid
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'add_button', 
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
                array(
                'label'     => $this->getAddButtonLabel(), 
                'onclick'   => $this->getFormJsObjectName().'.doAdd()', 
                'class'     => 'task'
                )
            )
        );
        parent::_prepareLayout();
        return $this;
    }
    /**
     * Get main button HTML
     * 
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        return $this->getChildHtml('add_button').$html;
    }
}
