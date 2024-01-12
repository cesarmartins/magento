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
 * Warehouse area tab
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Warehouse_Edit_Tab_Area 
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Editable_Area_Container
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Grid block
     * 
     * @var string
     */
    protected $_gridBlockType = 'warehouse/adminhtml_warehouse_edit_tab_area_grid';
    /**
     * Form block
     * 
     * @var string
     */
    protected $_formBlockType = 'warehouse/adminhtml_warehouse_edit_tab_area_form';
    /**
     * Tab title
     * 
     * @var string
     */
    protected $_title = 'Areas';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('warehouseAreaTab');
        $this->setTemplate('warehouse/warehouse/edit/tab/area.phtml');
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
     * Retrieve Tab class
     * 
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }
    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->getWarehouseHelper()
            ->__($this->_title);
    }
    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getWarehouseHelper()
            ->__($this->_title);
    }
    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
    /**
     * Retrieve registered warehouse
     *
     * @return MP_Warehouse_Model_Warehouse
     */
    protected function getWarehouse()
    {
        return Mage::registry('warehouse');
    }
    /**
     * Check is allowed action
     * 
     * @param string $action
     * 
     * @return boolean
     */
    protected function isAllowedAction($action)
    {
        return $this->getAdminSession()
            ->isAllowed('catalog/warehouses/'.$action);
    }
    /**
     * Check if edit function enabled
     * 
     * @return boolean
     */
    protected function canEdit()
    {
        $warehouse              = $this->getWarehouse();
        return ($this->isSaveAllowed() && $warehouse->getId()) ? true : false;
    }
}
