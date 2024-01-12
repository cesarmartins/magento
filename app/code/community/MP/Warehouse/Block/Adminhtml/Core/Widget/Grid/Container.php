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
 * Grid container
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Container
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Header label
     * 
     * @var string
     */
    protected $_headerLabel;
    /**
     * Add Label
     * 
     * @var string
     */
    protected $_addLabel;
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
     * Get header label
     * 
     * @return string
     */
    public function getHeaderLabel()
    {
        return $this->_headerLabel;
    }
    /**
     * Get add label
     * 
     * @return string
     */
    public function getAddLabel()
    {
        return $this->_addLabel;
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
     * Check if delete action allowed
     * 
     * @return bool
     */
    public function isDeleteAllowed()
    {
        return $this->isAllowedAction('delete');
    }
    /**
     * Add buttons
     * 
     * @return MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Container
     */
    protected function _addButtons()
    {
        if ($this->isSaveAllowed()) {
            $this->_updateButton('add', 'label', $this->getTextHelper()->__($this->getAddLabel()));
        } else {
            $this->_removeButton('add');
        }

        return $this;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_headerText = $this->getTextHelper()->__($this->getHeaderLabel());
        parent::__construct();
        $this->_addButtons();
    }
}
