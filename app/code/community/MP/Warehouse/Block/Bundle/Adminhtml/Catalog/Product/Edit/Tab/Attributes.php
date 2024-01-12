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
 * Product attributes tab
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Bundle_Adminhtml_Catalog_Product_Edit_Tab_Attributes 
    extends Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Attributes
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
    public function getProduct()
    {
        return Mage::registry('product');
    }
    /**
     * Prepare form before rendering HTML
     *
     * @return self
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $group              = $this->getGroup();
        if (!$group) {
            return $this;
        }

        $form               = $this->getForm();
        $fieldset           = $form->getElement('group_fields'.$group->getId());
        if (!$fieldset) {
            return $this;
        }

        $helper             = $this->getWarehouseHelper();
        $product            = $this->getProduct();
        $blockTypePrefix    = 'warehouse/adminhtml_catalog_product_edit_tab';
        if ($form->getElement('price')) {
            $fieldset->addField(
                'batch_prices', 
                'text', 
                array(
                    'name'      => 'batch_prices', 
                    'label'     => $helper->__('Batch Price'), 
                    'title'     => $helper->__('Batch Price'), 
                    'required'  => false, 
                    'value'     => $product->getBatchPrices(), 
                ), 
                'price'
            )->setRenderer(
                $this->getLayout()->createBlock($blockTypePrefix.'_batchprice_renderer')
            );
        }

        if ($form->getElement('tax_class_id')) {
            $fieldset->addField(
                'stock_tax_class_ids', 
                'text', 
                array(
                    'name'      => 'stock_tax_class_ids', 
                    'label'     => $helper->__('Warehouse Tax Class'), 
                    'title'     => $helper->__('Warehouse Tax Class'), 
                    'required'  => false, 
                    'value'     => $product->getStockTaxClassIds(), 
                ), 
                'tax_class_id'
            )->setRenderer(
                $this->getLayout()->createBlock($blockTypePrefix.'_stock_tax_class_renderer')
            );
        }

        return $this;
    }
}
