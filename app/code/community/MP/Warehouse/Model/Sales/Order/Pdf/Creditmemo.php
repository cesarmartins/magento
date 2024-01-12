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
 * Creditmemo PDF model
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Sales_Order_Pdf_Creditmemo 
    extends Mage_Sales_Model_Order_Pdf_Creditmemo
{
    /**
     * Get warehouse helper
     *
     * @return  MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Draw table header for product items
     *
     * @param  Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(Zend_Pdf_Page $page)
    {
        $helper = $this->getWarehouseHelper();
        
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 30);
        $this->y -= 10;
        $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));

        //columns headers
        $lines[0][] = array(
            'text' => Mage::helper('sales')->__('Products'),
            'feed' => 35,
        );

        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split(Mage::helper('sales')->__('SKU'), 12, true, true),
            'feed'  => 215,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split(Mage::helper('sales')->__('Total (ex)'), 12, true, true),
            'feed'  => 280,
            'align' => 'right',
            //'width' => 50,
        );

        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split(Mage::helper('sales')->__('Discount'), 12, true, true),
            'feed'  => 320,
            'align' => 'right',
            //'width' => 50,
        );

        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split(Mage::helper('sales')->__('Qty'), 12, true, true),
            'feed'  => 375,
            'align' => 'right',
            //'width' => 30,
        );

        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split(Mage::helper('sales')->__('Tax'), 12, true, true),
            'feed'  => 415,
            'align' => 'right',
            //'width' => 45,
        );

        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split(Mage::helper('sales')->__('Total (inc)'), 12, true, true),
            'feed'  => 475,
            'align' => 'right'
        );
        
        
        $lines[0][] = array(
            'text'  => $helper->__('Warehouse'), 
            'feed'  => 545,
            'align' => 'right'
        );

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 10
        );

        $this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }
    
}
