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
 * Warehouse tabs
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Warehouse_Edit_Tabs 
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Tabs
{
    /**
     * Model name
     * 
     * @var string
     */
    protected $_modelName = 'warehouse';
    /**
     * Child block type prefix
     * 
     * @var string
     */
    protected $_childBlockTypePrefix = 'warehouse/adminhtml_warehouse_edit_tab_';
    
    /**
     * Retrieve warehouse helper
     *
     * @return MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('warehouse_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->getWarehouseHelper()->__('Warehouse Information'));
    }
    /**
     * Prepare layout
     * 
     * @return $this
     */
    protected function _prepareLayout()
    {
        $helper                 = $this->getWarehouseHelper();
        $this->addTab(
            'main', array(
            'label'                 => $helper->__('General'), 
            'content'               => $this->getChildBlockContent('main'), 
            )
        );
        $this->addTab(
            'contact', array(
            'label'                 => $helper->__('Contact'), 
            'content'               => $this->getChildBlockContent('contact'), 
            )
        );
        $this->addTab(
            'origin', array(
            'label'                 => $helper->__('Origin'), 
            'content'               => $this->getChildBlockContent('origin'), 
            )
        );
        if ($this->getModel()->getId()) {
            $config                 = $helper->getConfig();
            if ((!$config->isMultipleMode() && $config->isAssignedAreaSingleAssignmentMethod()) || 
                ($config->isMultipleMode() && $config->isAssignedAreaMultipleAssignmentMethod())
            ) {
                $this->addTab(
                    'area', array(
                    'label'                 => $helper->__('Areas'), 
                    'content'               => $this->getChildBlockContent('area'), 
                    )
                );
            }

            if (!$config->isMultipleMode() && $config->isAssignedStoreSingleAssignmentMethod()) {
                $this->addTab(
                    'store', array(
                    'label'                 => $helper->__('Stores'), 
                    'content'               => $this->getChildBlockContent('store'), 
                    )
                );
            }

            if (!$config->isMultipleMode() && $config->isAssignedCustomerGroupSingleAssignmentMethod()) {
                $this->addTab(
                    'customerGroups', array(
                    'label'                 => $helper->__('Customer Groups'), 
                    'content'               => $this->getChildBlockContent('customer_group'), 
                    )
                );
            }

            if (!$config->isMultipleMode() && $config->isAssignedCurrencySingleAssignmentMethod()) {
                $this->addTab(
                    'currencies', array(
                    'label'                 => $helper->__('Currencies'), 
                    'content'               => $this->getChildBlockContent('currency'), 
                    )
                );
            }

            if ($config->isShippingCarrierFilterEnabled()) {
                $this->addTab(
                    'shipping_carrier', array(
                    'label'                 => $helper->__('Shipping Carriers'), 
                    'content'               => $this->getChildBlockContent('shipping_carrier'), 
                    )
                );
            }

            $this->addTab(
                'products', array(
                'label'                 => $helper->__('Products'), 
                'url'                   => $this->getUrl('*/*/productsGrid', array('_current' => true)), 
                'class'                 => 'ajax', 
                )
            );
            $this->addTab(
                'salesOrders', array(
                'label'                 => $helper->__('Orders'), 
                'url'                   => $this->getUrl('*/*/salesOrdersGrid', array('_current' => true)), 
                'class'                 => 'ajax', 
                )
            );
            $this->addTab(
                'salesInvoices', array(
                'label'                 => $helper->__('Invoices'), 
                'url'                   => $this->getUrl('*/*/salesInvoicesGrid', array('_current' => true)), 
                'class'                 => 'ajax', 
                )
            );
            $this->addTab(
                'salesShipments', array(
                'label'                 => $helper->__('Shipments'), 
                'url'                   => $this->getUrl('*/*/salesShipmentsGrid', array('_current' => true)), 
                'class'                 => 'ajax', 
                )
            );
            $this->addTab(
                'salesCreditMemos', array(
                'label'                 => $helper->__('Credit Memos'), 
                'url'                   => $this->getUrl('*/*/salesCreditmemosGrid', array('_current' => true)), 
                'class'                 => 'ajax', 
                )
            );
        }

        parent::_prepareLayout();
        return $this;
    }
}
