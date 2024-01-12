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
 * Editable area grid block
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Editable_Area_Grid
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Editable_Grid
{
    /**
     * Get country options
     * 
     * @return array
     */
    protected function getCountryOptions()
    {
        $options = array();
        $countries = Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(false);
        if (isset($countries[0])) {
            $countries[0] = array('value' => '0', 'label' => '*', );
        }

        foreach ($countries as $country) { 
            $options[$country['value']] = $country['label']; 
        }

        return $options;
    }
    /**
     * Get child block type prefix
     * 
     * @return string
     */
    protected function getAreaChildBlockTypePrefix()
    {
        return 'warehouse/adminhtml_core_widget_grid_area_';
    }
    /**
     * Add columns to grid
     * 
     * @return MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Editable_Area_Grid
     */
    protected function _prepareColumns()
    {
        $helper = $this->getTextHelper();
        $this->addColumn(
            'country_id', array(
            'header'        => $helper->__('Country'), 
            'align'         => 'left', 
            'index'         => 'country_id', 
            'filter_index'  => 'main_table.country_id', 
            'type'          => 'options', 
            'options'       => $this->getCountryOptions(), 
            )
        );
        $this->addColumn(
            'region', array(
            'header'        => $helper->__('Region / State'), 
            'align'         => 'left', 
            'index'         => 'region', 
            'filter_index'  => 'region_table.code', 
            'filter'        => $this->getAreaChildBlockTypePrefix().'column_filter_region', 
            'default'       => '*', 
            )
        );
        $this->addColumn(
            'zip', array(
            'header'        => $helper->__('Zip / Postal Code'), 
            'align'         => 'left', 
            'index'         => 'zip', 
            'filter'        => $this->getAreaChildBlockTypePrefix().'column_filter_zip', 
            'renderer'        => $this->getAreaChildBlockTypePrefix().'column_renderer_zip', 
            'default'       => '*', 
            )
        );
        return $this;
    }
}
