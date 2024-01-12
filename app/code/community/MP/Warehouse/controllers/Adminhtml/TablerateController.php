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
 * Table rates controller
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Adminhtml_TablerateController 
    extends MP_Warehouse_Controller_Adminhtml_Action
{
    /**
     * Model names
     *
     * @var array
     */
    protected $_modelNames = array(
        'shippingtablerate' => 'warehouse/shippingTablerate_tablerate',
    );
    /**
     * Retrieve shipping table rate helper
     *
     * @return MP_Warehouse_Helper_ShippingTablerate_Data
     */
    protected function getShippingTablerateHelper()
    {
        return Mage::helper('warehouse/shippingTablerate_data');
    }
    /**
     * Get website id
     *
     * @return integer
     */
    protected function getWebsiteId()
    {
        return $this->getShippingTablerateHelper()->getWebsiteId();
    }
    /**
     * Set redirect into responce
     *
     * @param   string $path
     * @param   array $arguments
     *
     * @return MP_Warehouse_Adminhtml_TablerateController
     */
    protected function _redirect($path, $arguments = array())
    {
        $arguments = array_merge(array('website' => $this->getWebsiteId()), $arguments);
        parent::_redirect($path, $arguments);
        return $this;
    }
    /**
     * Get model
     *
     * @param string $type
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _getModel($type)
    {
        $model = parent::_getModel($type);
        $model->setWebsiteId($this->getWebsiteId());
        return $model;
    }
    /**
     * Check is allowed action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->getAdminSession()->isAllowed('sales/shipping/tablerates/tablerates');
    }
    /**
     * Index action
     */
    public function indexAction()
    {
        $helper = $this->getShippingTablerateHelper();
        $this->_indexAction(
            'shippingtablerate', false, 'sales/shipping/tablerates', array(
            $helper->__('Sales'),
            $helper->__('Shipping'),
            $helper->__('Shipping Table Rates'),
            )
        );
    }
    /**
     * Grid action
     */
    public function gridAction()
    {
        $this->_gridAction('shippingtablerate', true);
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
        $helper = $this->getShippingTablerateHelper();
        $this->_editAction(
            'shippingtablerate', false, 'sales/shipping/tablerates', 'tablerate_id', '',
            $helper->__('New Rate'), $helper->__('Edit Rate'),
            array(
                $helper->__('Sales'),
                $helper->__('Shipping'),
                $helper->__('Shipping Table Rates'),
            ),
            $helper->__('This rate no longer exists.')
        );
    }
    /**
     * Save action
     */
    public function saveAction()
    {
        $helper = $this->getShippingTablerateHelper();
        $this->_saveAction(
            'shippingtablerate', false, 'tablerate_id', '', 'edit',
            $helper->__('The rate has been saved.'),
            $helper->__('An error occurred while saving the rate.')
        );
    }
    /**
     * Delete action
     */
    public function deleteAction()
    {
        $helper = $this->getShippingTablerateHelper();
        $this->_deleteAction(
            'shippingtablerate', false, 'tablerate_id', '', 'edit',
            $helper->__('Unable to find a rate to delete.'),
            $helper->__('The rate has been deleted.')
        );
    }
    /**
     * Mass delete action
     */
    public function massDeleteAction()
    {
        $helper = $this->getShippingTablerateHelper();
        $this->_massDeleteAction(
            'shippingtablerate', false, 'tablerate_id', '',
            $helper->__('Please select rate(s).'),
            $helper->__('Total of %d record(s) have been deleted.')
        );
    }
    /**
     * Export rates to CSV format
     */
    public function exportCsvAction()
    {
        $this->_exportCsvAction('shipping_table_rates.csv', 'warehouse/adminhtml_shippingTablerate_tablerate_grid');
    }
    /**
     * Export rates to XML format
     */
    public function exportXmlAction()
    {
        $this->_exportXmlAction('shipping_table_rates.xml', 'warehouse/adminhtml_shippingTablerate_tablerate_grid');
    }
}
