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
 * Product grid batch prices renderer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Catalog_Product_Grid_Column_Renderer_Batchprices 
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
     * Get currency code
     * 
     * @param array $row
     * 
     * @return string
     */
    protected function _getCurrencyCode($row)
    {
        $code = $this->getColumn()->getCurrencyCode();
        if ($code) {
            return $code;
        }

        $code = $row->getData($this->getColumn()->getCurrency());
        if ($code) {
            return $code;
        }

        return false;
    }
    /**
     * Get rate
     * 
     * @param array $row
     * 
     * @return float
     */
    protected function _getRate($row)
    {
        $rate = $this->getColumn()->getRate();
        if ($rate) {
            return (float) $rate;
        }

        $rate = $row->getData($this->getColumn()->getRateField());
        if ($rate) {
            return (float) $rate;
        }

        return 1;
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
            $currencyCode   = $this->_getCurrencyCode($row);
            $rate           = $this->_getRate($row);
            $output = '<table cellspacing="0" class="batch-prices-table"><col width="100"/><col width="40"/>';
            foreach ($value as $stockId => $price) {
                if ($currencyCode) {
                    $price = sprintf("%f", ((float) $price) * $rate);
                    $price = Mage::app()->getLocale()->currency($currencyCode)->toCurrency($price);
                }

                $output .= '<tr><td>'.$helper->getWarehouseTitleByStockId($stockId).'</td><td>'.$price.'</td></tr>';
            }

            $output .= '</table>';
            return $output;
        }

        return '';
    }
}
