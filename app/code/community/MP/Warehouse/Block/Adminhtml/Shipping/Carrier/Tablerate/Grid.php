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
 * Shipping carrier table rate grid
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Shipping_Carrier_Tablerate_Grid 
    extends Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid
{
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
     * Prepare page
     * 
     * @return MP_Warehouse_Block_Adminhtml_Shipping_Carrier_Tablerate_Grid
     */
    protected function _preparePage()
    {
        $this->getCollection()->getSelect()->order(
            array(
            'warehouse_id', 
            'dest_country_id', 
            'dest_region_id', 
            'dest_zip', 
            'condition_value', 
            'method_id', 
            'price', 
            )
        );
        parent::_preparePage();
        return $this;
    }
    /**
     * Prepare table columns
     *
     * @return MP_Warehouse_Block_Adminhtml_Shipping_Carrier_Tablerate_Grid
     */
    protected function _prepareColumns()
    {
        $helper = $this->getWarehouseHelper();
        $this->addColumn(
            'warehouse_id', array(
            'header'        => $this->getWarehouseHelper()->__('Warehouse'), 
            'index'         => 'warehouse_id', 
            'default'       => '*', 
            )
        );
        parent::_prepareColumns();
        $this->addColumnAfter(
            'method_id', array(
            'header'        => $helper->__('Method'), 
            'align'         => 'left', 
            'index'         => 'method_id', 
            ), 'condition_value'
        );
        $this->sortColumnsByOrder();
        return $this;
    }
}
