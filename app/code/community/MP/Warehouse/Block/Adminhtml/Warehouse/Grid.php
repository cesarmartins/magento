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
 * Warehouses grid
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Warehouse_Grid 
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Grid
{
    /**
     * Object identifier
     * 
     * @var string
     */
    protected $_objectId = 'warehouse_id';
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
        $this->setId('warehouseGrid');
        $this->setDefaultSort('priority');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setEmptyText($this->getWarehouseHelper()->__('No warehouses found'));
    }
    /**
     * Prepare collection object
     *
     * @return Varien_Data_Collection
     */
    protected function __prepareCollection()
    {
        return Mage::getModel('warehouse/warehouse')->getCollection();
    }
    /**
     * Prepare columns
     *
     * @return MP_Warehouse_Block_Adminhtml_Warehouse_Grid
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $helper = $this->getWarehouseHelper();
        $this->addColumn(
            'warehouse_id', array(
            'header'    => $helper->__('ID'), 
            'width'     => '80', 
            'align'     => 'left', 
            'index'     => 'warehouse_id',
            )
        );
        $config = $helper->getConfig();
        if ($config->isPriorityEnabled()) {
            $this->addColumn(
                'priority', array(
                'header'    => $helper->__('Priority'), 
                'width'     => '80', 
                'align'     => 'left', 
                'index'     => 'priority', 
                )
            );
        }

        $this->addColumn(
            'code', array(
            'header'    => $helper->__('Code'), 
            'align'     => 'left', 
            'index'     => 'code',
            )
        );
        $this->addColumn(
            'title', array(
            'header'    => $helper->__('Title'), 
            'align'     => 'left', 
            'index'     => 'title',
            )
        );
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'marca'); //"color" is the attribute_code
        $allOptions = $attribute->getSource()->getAllOptions(false, false);
        $options = [];
        foreach($allOptions as $op){
            $options[$op['value']] = $op['label'];

        }
        $this->addColumn(
            'marca', array(
            'header'    => $helper->__('Marca'), 
            'width'     => '100', 
            'type'      => 'options', 
            'index'     => 'marca',
            'options'    => $options
            )
        );

        $this->addColumn(
            'origin_region', array(
            'header'    => $helper->__('Origin Region/State'), 
            'width'     => '100', 
            'index'     => 'origin_region', 
            )
        );
        $this->addColumn(
            'origin_postcode', array(
            'header'    => $helper->__('Origin Postal Code'), 
            'width'     => '100', 
            'index'     => 'origin_postcode', 
            )
        );
        $this->addColumn(
            'origin_city', array(
            'header'    => $helper->__('Origin City'), 
            'width'     => '100', 
            'index'     => 'origin_city', 
            )
        );
        $this->addColumn(
            'action', array(
            'header'    =>  $helper->__('Action'), 
            'width'     => '100', 
            'type'      => 'action', 
            'getter'    => 'getId', 
            'actions'   => array(
                array(
                    'caption'   => $helper->__('Edit'), 
                    'url'       => array('base' => '*/*/edit'), 
                    'field'     => 'warehouse_id', 
                ), 
            ),
            'filter'    => false, 
            'sortable'  => false, 
            'is_system' => true, 
            )
        );
        return $this;
    }
}
