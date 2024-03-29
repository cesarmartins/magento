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
 * Editable grid container block
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Editable_Container
    extends Mage_Adminhtml_Block_Widget
{
    /**
     * Grid block type
     * 
     * @var string
     */
    protected $_gridBlockType;
    /**
     * Form block type
     * 
     * @var string
     */
    protected $_formBlockType;
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mp/core/widget/grid/editable/container.phtml');
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
     * Get text helper
     * 
     * @return Varien_Object
     */
    public function getTextHelper()
    {
        return $this;
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
     * Check if edit function enabled
     * 
     * @return bool
     */
    protected function canEdit()
    {
        return true;
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
     * Get grid block type
     * 
     * @return string
     */
    protected function getGridBlockType()
    {
        return $this->_gridBlockType;
    }
    /**
     * Get form block type
     * 
     * @return string
     */
    protected function getFormBlockType()
    {
        return $this->_formBlockType;
    }
    /**
     * Prepare Layout data
     * 
     * @return MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Editable_Container
     */
    protected function _prepareLayout()
    {
        $layout = $this->getLayout();
        if ($this->canEdit() && !$this->hasForm()) {
            $this->setChild('form', $layout->createBlock($this->getFormBlockType()));
        }

        if (!$this->hasGrid()) {
            $this->setChild('grid', $layout->createBlock($this->getGridBlockType()));
        }

        return parent::_prepareLayout();
    }
    /**
     * Get grid
     * 
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    public function getGrid()
    {
        return $this->getChild('grid');
    }
    /**
     * Check if grid exists
     * 
     * @return bool
     */
    public function hasGrid()
    {
        $grid = $this->getGrid();
        if (!empty($grid)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Retrieve grid HTML
     *
     * @return string
     */
    public function getGridHtml()
    {
        if ($this->hasGrid()) {
            return $this->getChildHtml('grid');
        } else {
            return null;
        }
    }
    /**
     * Get js object name
     * 
     * @return string
     */
    public function getGridJsObjectName()
    {
        if ($this->hasGrid()) {
            return $this->getGrid()->getJsObjectName();
        } else {
            return null;
        }
    }
    /**
     * Get form
     * 
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    public function getForm()
    {
        return $this->getChild('form');
    }
    /**
     * Check if form exists
     * 
     * @return bool
     */
    public function hasForm()
    {
        $form = $this->getForm();
        if (!empty($form)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Retrieve form HTML
     * 
     * @return string
     */
    public function getFormHtml()
    {
        if ($this->hasForm()) {
            return $this->getChildHtml('form');
        } else {
            return null;
        }
    }
    /**
     * Get form html id prefix
     * 
     * @return string
     */
    public function getFormHtmlIdPrefix()
    {
        if ($this->hasForm()) {
            return $this->getForm()->getFormHtmlIdPrefix();
        } else {
            return null;
        }
    }
    /**
     * Get form html identifier
     * 
     * @return string
     */
    public function getFormHtmlId()
    {
        if ($this->hasForm()) {
            return $this->getForm()->getHtmlId();
        } else {
            return null;
        }
    }
    /**
     * Get form field names
     * 
     * @return array
     */
    public function getFormFieldNames()
    {
        if ($this->hasForm()) {
            return $this->getForm()->getFieldNames();
        } else {
            return array();
        }
    }
    /**
     * Get form field names json
     * 
     * @return string
     */
    public function getFormFieldNamesJson()
    {
        return $this->getCoreHelper()->getCoreHelper()->jsonEncode($this->getFormFieldNames());
    }
    /**
     * Get form defaults
     * 
     * @return array
     */
    public function getFormDefaults()
    {
        if ($this->hasForm()) {
            return $this->getForm()->getDefaults();
        } else {
            return array();
        }
    }
    /**
     * Get form defaults json
     * 
     * @return string
     */
    public function getFormDefaultsJson()
    {
        return $this->getCoreHelper()->getCoreHelper()->jsonEncode($this->getFormDefaults());
    }
    /**
     * Get form js object name
     * 
     * @return string
     */
    public function getFormJsObjectName()
    {
        if ($this->hasForm()) {
            return $this->getForm()->getJsObjectName();
        } else {
            return null;
        }
    }
    /**
     * Escape JavaScript string
     * 
     * @param string $string
     * 
     * @return string
     */
    public function escapeJs($string)
    {
        return addcslashes($string, "'\r\n\\");
    }
}
