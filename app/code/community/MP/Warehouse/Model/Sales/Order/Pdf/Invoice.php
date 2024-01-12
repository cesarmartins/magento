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
 * Invoice PDF model
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Sales_Order_Pdf_Invoice 
    extends Mage_Sales_Model_Order_Pdf_Invoice
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
     * Draw header for item table
     *
     * @param Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(Zend_Pdf_Page $page)
    {
        $helper = $this->getWarehouseHelper();
        
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y -15);
        $this->y -= 10;
        $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));

        //columns headers
        $lines[0][] = array(
            'text' => Mage::helper('sales')->__('Products'),
            'feed' => 35
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('SKU'),
            'feed'  => 250,
            'align' => 'right'
        );
        
        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Price'),
            'feed'  => 310,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Qty'),
            'feed'  => 375,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Tax'),
            'feed'  => 425,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Subtotal'),
            'feed'  => 485,
            'align' => 'right'
        );
        
        $lines[0][] = array(
            'text'  => $helper->__('Warehouse'), 
            'feed'  => 545,
            'align' => 'right'
        );

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 5
        );

        $this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }
    /**
     * Get pdf
     * 
     * @param array $invoices
     * 
     * @return Zend_Pdf
     */
    public function getPdf($invoices = array())
    {
        $helper = $this->getWarehouseHelper();
        
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
                Mage::app()->setCurrentStore($invoice->getStoreId());
            }
            
            if ($helper->getVersionHelper()->isGe1700()) {
                $page  = $this->newPage();
            } else {
                $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
                $pdf->pages[] = $page;
            }

            $order = $invoice->getOrder();

            /* Add image */
            $this->insertLogo($page, $invoice->getStore());

            /* Add address */
            $this->insertAddress($page, $invoice->getStore());

            /* Add head */
            $this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId()));

            if ($helper->getVersionHelper()->isGe1700()) {
                /* Add document text and number */
                $this->insertDocumentNumber(
                    $page,
                    Mage::helper('sales')->__('Invoice # ') . $invoice->getIncrementId()
                );
            } else {
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
                $this->_setFontRegular($page);
                $page->drawText(Mage::helper('sales')->__('Invoice # ') . $invoice->getIncrementId(), 35, 780, 'UTF-8');
            }

            if ($helper->getVersionHelper()->isGe1700()) {
                /* Add table */
                $this->_drawHeader($page);
            } else {
                /* Add table */
                $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
                $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
                $page->setLineWidth(0.5);

                $page->drawRectangle(25, $this->y, 570, $this->y -15);
                $this->y -=10;

                /* Add table head */
                $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
                $page->drawText(Mage::helper('sales')->__('Products'), 35, $this->y, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('SKU'), 215, $this->y, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Price'), 330, $this->y, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Qty'), 370, $this->y, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Tax'), 410, $this->y, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Subtotal'), 455, $this->y, 'UTF-8');
                $page->drawText($helper->__('Warehouse'), 495, $this->y, 'UTF-8');
                
                $this->y -=15;

                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            }
            

            /* Add body */
            foreach ($invoice->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                if ($helper->getVersionHelper()->isGe1700()) {
                    /* Draw item */
                    $this->_drawItem($item, $page, $order);
                    $page = end($pdf->pages);
                } else {
                    if ($this->y < 15) {
                        $page = $this->newPage(array('table_header' => true));
                    }

                    /* Draw item */
                    $page = $this->_drawItem($item, $page, $order);
                }
            }
            
            if ($helper->getVersionHelper()->isGe1700()) {
                /* Add totals */
                $this->insertTotals($page, $invoice);
            } else {
                /* Add totals */
                $page = $this->insertTotals($page, $invoice);
            }
            

            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
        }

        $this->_afterGetPdf();

        return $pdf;
    }
    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        $helper = $this->getWarehouseHelper();
        
        /* Add new table head */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            if ($helper->getVersionHelper()->isGe1700()) {
                $this->_drawHeader($page);
            } else {
                $this->_setFontRegular($page);
                $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
                $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
                $page->setLineWidth(0.5);
                $page->drawRectangle(25, $this->y, 570, $this->y-15);
                $this->y -=10;

                $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
                $page->drawText(Mage::helper('sales')->__('Products'), 35, $this->y, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('SKU'), 215, $this->y, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Price'), 330, $this->y, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Qty'), 370, $this->y, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Tax'), 410, $this->y, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Subtotal'), 455, $this->y, 'UTF-8');
                $page->drawText($helper->__('Warehouse'), 495, $this->y, 'UTF-8');

                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                $this->y -=20;
            }
        }

        return $page;
    }
}
