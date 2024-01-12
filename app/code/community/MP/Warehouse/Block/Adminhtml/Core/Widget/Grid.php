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
 * Adminhtml grid
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Core_Widget_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Object identifier
     * 
     * @var string
     */
    protected $_objectId;
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
     * Get object identifier
     * 
     * @return string
     */
    public function getObjectId()
    {
        return $this->_objectId;
    }
    /**
     * Prepare collection object
     *
     * @return Varien_Data_Collection
     */
    protected function __prepareCollection()
    {
        return null;
    }
    /**
     * Prepare collection object
     *
     * @return MP_Warehouse_Block_Adminhtml_Core_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->__prepareCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }
    /**
     * Get row URL
     * 
     * @param   Varien_Object $row
     * 
     * @return  string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array($this->getObjectId() => $row->getId()));
    }
    /**
     * Get grid URL
     * 
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
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
     * Check if view action allowed
     * 
     * @return bool
     */
    public function isViewAllowed()
    {
        return $this->isAllowedAction('view');
    }
}
