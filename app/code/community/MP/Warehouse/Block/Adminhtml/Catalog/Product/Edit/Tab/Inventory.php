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
 * Product inventory tab
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Catalog_Product_Edit_Tab_Inventory 
    extends Mage_Adminhtml_Block_Widget_Form
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
     * Retrieve registered product model
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function getProduct()
    {
        return Mage::registry('product');
    }
    /**
     * Prepare form before rendering HTML
     *
     * @return MP_Warehouse_Block_Adminhtml_Catalog_Product_Edit_Tab_Inventory
     */
    protected function _prepareForm() 
    {
        $helper     = $this->getWarehouseHelper();
        $product    = $this->getProduct();
        $form       = new Varien_Data_Form();
        $form->setHtmlIdPrefix('product_');
        $form->setFieldNameSuffix('product');
        $fieldset   = $form->addFieldset('multipleinventory', array('legend' => $helper->__('Inventory'), ));
        $stocksDataElement = $fieldset->addField(
            'stocks_data', 'text', array(
            'name'      => 'stocks_data', 
            'label'     => $helper->__('Inventory'), 
            'title'     => $helper->__('Inventory'), 
            'required'  => true, 
            'class'     => 'requried-entry', 
            'value'     => $product->getStocksItems(), 
            )
        );
        $stocksDataElement->setRenderer(
            $this->getLayout()->createBlock('warehouse/adminhtml_catalog_product_edit_tab_inventory_renderer')
        );
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
