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
 * Product edit tab super config simple
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Config_Simple 
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Simple
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
     * Prepare form
     * 
     * @return MP_Warehouse_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Config_Simple
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form       = $this->getForm();
        $fieldset   = $form->getElement('simple_product');
        if (!$fieldset) {
            return $this;
        }

        $fieldset->removeField('simple_product_inventory_qty');
        $fieldset->removeField('simple_product_inventory_is_in_stock');
        $stockHiddenFields = array(
            'use_config_min_qty', 'use_config_min_sale_qty', 'use_config_max_sale_qty', 
            'use_config_backorders', 'use_config_notify_stock_qty', 'is_qty_decimal', 
        );
        foreach ($stockHiddenFields as $fieldName) {
            $fieldset->removeField('simple_product_inventory_'.$fieldName);
        }

        return $this;
    }
}
