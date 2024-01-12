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
 * Product priority tab
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Catalog_Product_Edit_Tab_Priority 
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
     * @return MP_Warehouse_Block_Adminhtml_Catalog_Product_Edit_Tab_Priority
     */
    protected function _prepareForm()
    {
        $product    = $this->getProduct();
        $helper     = $this->getWarehouseHelper();
        $form       = new Varien_Data_Form();
        $form->setHtmlIdPrefix('product_');
        $form->setFieldNameSuffix('product');
        $fieldset   = $form->addFieldset('priority', array('legend' => $helper->__('Priority'), ));
        $stockPrioritiesElement = $fieldset->addField(
            'stock_priorities', 'text', array(
            'name'      => 'stock_priorities', 
            'label'     => $helper->__('Warehouse Priority'), 
            'title'     => $helper->__('Warehouse Priority'), 
            'required'  => false, 
            'value'     => $product->getStockPriorities(), 
            )
        );
        $stockPrioritiesElement->setRenderer(
            $this->getLayout()->createBlock('warehouse/adminhtml_catalog_product_edit_tab_stock_priority_renderer')
        );
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
