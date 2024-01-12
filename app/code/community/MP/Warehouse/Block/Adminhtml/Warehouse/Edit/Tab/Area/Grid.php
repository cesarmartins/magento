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
 * Warehouse area grid
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Warehouse_Edit_Tab_Area_Grid 
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Editable_Area_Grid
{
    /**
     * Add button label
     * 
     * @var string
     */
    protected $_addButtonLabel = 'Add Area';
    /**
     * Form js object name
     * 
     * @var string
     */
    protected $_formJsObjectName = 'warehouseAreaTabFormJsObject';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('warehouseAreaGrid');
        $this->setDefaultSort('warehouse_area_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
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
     * Retrieve registered warehouse
     * 
     * @return MP_Warehouse_Model_Warehouse
     */
    protected function getWarehouse()
    {
        return Mage::registry('warehouse');
    }
    /**
     * Prepare collection object
     *
     * @return Varien_Data_Collection
     */
    protected function __prepareCollection()
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        $warehouse              = $this->getWarehouse();
        return Mage::getModel('warehouse/warehouse_area')
            ->getCollection()
            ->setWarehouseFilter($warehouse->getId())
            ->setPriorityFilter();
    }
    /**
     * Get country options
     * 
     * @return array
     */
    protected function getCountryOptions()
    {
        $options                = array();
        $countries              = Mage::getModel('adminhtml/system_config_source_country')
            ->toOptionArray(false);
        if (isset($countries[0])) {
            $countries[0]           = array('value' => '0', 'label' => '*', );
        }

        foreach ($countries as $country) { 
            $options[$country['value']] = $country['label']; 
        }

        return $options;
    }
    /**
     * Add columns to grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        if ($config->isMultipleMode() && $config->isAssignedAreaMultipleAssignmentMethod()) {
            $this->addColumn(
                'priority', array(
                'header'                => $helper->__('Priority'), 
                'align'                 => 'left', 
                'index'                 => 'priority', 
                )
            );
        }

        $this->addColumn(
            'action', array(
            'header'                => $helper->__('Action'), 
            'width'                 => '50px', 
            'type'                  => 'action', 
            'getter'                => 'getId', 
            'actions'               => array(
                array(
                    'name'                  => 'edit', 
                    'caption'               => $helper->__('Edit'), 
                    'url'                   => array(
                        'base'                  => '*/*/editArea', 
                        'params'                => $this->getRowUrlParameters(), 
                    ), 
                    'field'                 => 'warehouse_area_id', 
                ), 
                array(
                    'name'                  => 'delete', 
                    'caption'               => $helper->__('Delete'), 
                    'url'                   => array(
                        'base'                  => '*/*/deleteArea', 
                        'params'                => $this->getRowUrlParameters(), 
                    ), 
                    'field'                 => 'warehouse_area_id', 
                    'confirm'               => $helper->__('Are you sure you want to delete area?')
                )
            ), 
            'filter'                => false, 
            'sortable'              => false, 
            )
        );
        parent::_prepareColumns();
        return $this;
    }
    /**
     * Retrieve grid URL
     * 
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url') ? 
            $this->getData('grid_url') : 
            $this->getUrl('*/*/areaGrid', array('_current' => true));
    }
    /**
     * Get row URL parameters
     * 
     * @param Varien_Object|null $row
     * 
     * @return array
     */
    protected function getRowUrlParameters($row = null)
    {
        $params                     = array('warehouse_id' => $this->getWarehouse()->getId());
        if ($row) {
            $params['warehouse_area_id'] = $row->getId();
        }

        return $params;
    }
    /**
     * Get row URL
     * 
     * @param Varien_Object $row
     * 
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/editArea', $this->getRowUrlParameters($row));
    }
}
