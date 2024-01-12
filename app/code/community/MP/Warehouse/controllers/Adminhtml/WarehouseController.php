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
 * Warehouse controller
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Adminhtml_WarehouseController 
    extends MP_Warehouse_Controller_Adminhtml_Action
{
    /**
     * Model names
     * 
     * @var array
     */
    protected $_modelNames = array(
        'warehouse'         => 'warehouse/warehouse', 
        'warehouse_area'    => 'warehouse/warehouse_area', 
    );
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
     * Check is allowed action
     * 
     * @return bool
     */
    protected function _isAllowed()
    {
        $adminSession = $this->getAdminSession();
        switch ($this->getRequest()->getActionName()) {
            case 'new': 
            case 'save': 
                return $adminSession->isAllowed('catalog/warehouses/save'); 
                break;
            case 'delete': 
                return $adminSession->isAllowed('catalog/warehouses/delete'); 
                break;
            default: 
                return $adminSession->isAllowed('catalog/warehouses'); 
                break;
        }
    }
    /**
     * Index action
     */
    public function indexAction()
    {
        $helper = $this->getWarehouseHelper();
        $this->_indexAction(
            'warehouse', false, 'catalog/warehouses', array(
            $helper->__('Catalog'), 
            $helper->__('Manage Warehouses'), 
            )
        );
    }
    /**
     * Grid action
     */
    public function gridAction()
    {
        $this->_gridAction('warehouse', true);
    }
    /**
     * New action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }
    /**
     * Edit action
     */
    public function editAction()
    {
        $helper = $this->getWarehouseHelper();
        $this->_editAction(
            'warehouse', false, 'catalog/warehouses', 'warehouse_id', '', 
            $helper->__('New Warehouse'), $helper->__('Edit Warehouse'), 
            array(
                $helper->__('Catalog'), 
                $helper->__('Manage Warehouses'), 
            ), 
            $helper->__('This warehouse no longer exists.')
        );
    }
    /**
     * Save action
     */
    public function saveAction()
    {
        $helper = $this->getWarehouseHelper();
        $this->_saveAction(
            'warehouse', false, 'warehouse_id', '', 'edit', 
            $helper->__('The warehouse has been saved.'), 
            $helper->__('An error occurred while saving the warehouse.')
        );
    }
    /**
     * Delete action
     */
    public function deleteAction()
    {
        $helper = $this->getWarehouseHelper();
        $this->_deleteAction(
            'warehouse', false, 'warehouse_id', '', 'edit', 
            $helper->__('Unable to find a warehouse to delete.'), 
            $helper->__('The warehouse has been deleted.')
        );
    }
    /**
     * Initialize warehouse
     * 
     * @return MP_Warehouse_Adminhtml_WarehouseController
     */
    protected function _initWarehouse()
    {
        $helper = $this->getWarehouseHelper();
        $this->_initModel('warehouse', true, 'warehouse_id', '', $helper->__('This warehouse no longer exists.'));
        return $this;
    }
    /**
     * Get products grid
     */
    public function productsGridAction()
    {
        $this->_initWarehouse();
        $this->_gridAction('catalog_product', true);
    }
    /**
     * Get sales orders grid
     */
    public function salesOrdersGridAction()
    {
        $this->_initWarehouse();
        $this->_gridAction('sales_order', true);
    }
    /**
     * Get sales invoices grid
     */
    public function salesInvoicesGridAction()
    {
        $this->_initWarehouse();
        $this->_gridAction('sales_invoice', true);
    }
    /**
     * Get sales shipments grid
     */
    public function salesShipmentsGridAction()
    {
        $this->_initWarehouse();
        $this->_gridAction('sales_shipment', true);
    }
    /**
     * Get sales credit memos grid
     */
    public function salesCreditmemosGridAction()
    {
        $this->_initWarehouse();
        $this->_gridAction('sales_creditmemo', true);
    }
    /**
     * Get area grid
     */
    public function areaGridAction()
    {
        $this->_initWarehouse();
        $this->_gridAction('warehouse_area', true);
    }
    /**
     * Edit area action
     */
    public function editAreaAction()
    {
        $helper = $this->getWarehouseHelper();
        $this->_editAction(
            'warehouse_area', true, null, 'warehouse_area_id', null, null, null, array(), 
            $helper->__('This area no longer exists.')
        );
        return $this;
    }
    /**
     * Prepare save
     * 
     * @param string $type
     * @param Mage_Core_Model_Abstract $model
     * 
     * @return MP_Warehouse_Adminhtml_WarehouseController
     */
    protected function _prepareSave($type, $model)
    {
        if ($type == 'warehouse_area') {
            $warehouseId = $this->getRequest()->getParam('warehouse_id');
            $model->setWarehouseId($warehouseId);
        }

        return $this;
    }
    /**
     * Save area action
     */
    public function saveAreaAction()
    {
        $helper = $this->getWarehouseHelper();
        $this->_saveAction(
            'warehouse_area', true, 'warehouse_area_id', null, null, 
            $helper->__('The area has been saved.'), 
            $helper->__('An error occurred while saving the area: %s.')
        );
        return $this;
    }
    /**
     * Delete area action
     */
    public function deleteAreaAction()
    {
        $helper = $this->getWarehouseHelper();
        $this->_deleteAction(
            'warehouse_area', true, 'warehouse_area_id', null, null, 
            $helper->__('This warehouse no longer exists.'), 
            $helper->__('The area has been deleted.')
        );
    }
}
