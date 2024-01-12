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
 * Table rate methods grid
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_ShippingTablerate_Tablerate_Method_Grid
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Area_Grid
{
    /**
     * Object identifier
     * 
     * @var string
     */
    protected $_objectId = 'method_id';
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
     * Get text helper
     *
     * @return MP_Warehouse_Helper_Data
     */
    public function getTextHelper()
    {
        return $this->getWarehouseHelper();
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('tablerateMethodGrid');
        $this->setDefaultSort('code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->_exportPageSize = 10000;
        $this->setEmptyText($this->getTextHelper()->__('No table rate methods found.'));
    }
    /**
     * Prepare collection object
     *
     * @return Varien_Data_Collection
     */
    protected function __prepareCollection()
    {
        return Mage::getModel('warehouse/shippingTablerate_tablerate_method')->getCollection();
    }
    /**
     * Prepare columns
     *
     * @return MP_Warehouse_Block_Adminhtml_Shippingtablerate_Tablerate_Method_Grid
     */
    protected function _prepareColumns()
    {
        $textHelper     = $this->getTextHelper();
        $this->addColumn(
            'method_id', array(
            'header'    => $textHelper->__('ID'), 
            'width'     => '80', 
            'align'     => 'left', 
            'index'     => 'method_id', 
            )
        );
        $this->addColumn(
            'code', array(
            'header'    => $textHelper->__('Code'), 
            'align'     => 'left', 
            'index'     => 'code', 
            )
        );
        $this->addColumn(
            'name', array(
            'header'    => $textHelper->__('Name'), 
            'align'     => 'left', 
            'index'     => 'name', 
            )
        );
        return $this;
    }
    /**
     * Get row URL
     * 
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/edit', 
            array($this->getObjectId() => $row->getId(), )
        );
    }
    /**
     * Prepare mass action
     * 
     * @return MP_Warehouse_Block_Adminhtml_Shippingtablerate_Tablerate_Method_Grid
     */
    protected function _prepareMassaction()
    {
        $textHelper     = $this->getTextHelper();
        $this->setMassactionIdField('method_id');
        $block          = $this->getMassactionBlock();
        $block->setFormFieldName($this->getObjectId());
        $block->addItem(
            'delete', array(
            'label'       => $textHelper->__('Delete'), 
            'url'         => $this->getUrl('*/*/massDelete', array()), 
            'confirm'     => $textHelper->__('Are you sure?')
            )
        );
        return $this;
    }
}
