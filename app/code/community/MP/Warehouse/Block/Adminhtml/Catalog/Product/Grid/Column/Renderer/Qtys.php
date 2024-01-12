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
 * Product grid quantities renderer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Catalog_Product_Grid_Column_Renderer_Qtys 
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
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
     * Render a grid cell as qtys
     * 
     * @param Varien_Object $row
     * 
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $helper     = $this->getWarehouseHelper();
        $value      = $row->getData($this->getColumn()->getIndex());
        if (is_array($value) && count($value)) {
            $output     = '<table cellspacing="0" class="qty-table"><col width="100"/><col width="40"/>';
            $totalQty   = 0;
            
            foreach ($value as $stockId => $qty) {
                $output .= '<tr><td>'.
                    $helper->getWarehouseTitleByStockId($stockId).'</td><td>'.((!is_null($qty))?$qty:$helper->__('N / A')).
                '</td></tr>';
                $totalQty += floatval($qty);
            }
            
            $output .= '<tr><td><strong>Total</strong></td><td><strong>'.$totalQty.'</strong></td></tr>';
            $output .= '</table>';
            return $output;
        }

        return '';
    }
}
