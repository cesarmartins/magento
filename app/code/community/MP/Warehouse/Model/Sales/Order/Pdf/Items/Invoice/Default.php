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
 * Invoice Pdf default items renderer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Sales_Order_Pdf_Items_Invoice_Default 
    extends Mage_Sales_Model_Order_Pdf_Items_Invoice_Default
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
     * Draw item line
     */
    public function draw()
    {
        $helper = $this->getWarehouseHelper();
        
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $lines  = array();

        // draw Product name
        $lines[0] = array(array(
            'text' => Mage::helper('core/string')->str_split($item->getName(), 35, true, true),
            'feed' => 35,
        ));

        // draw SKU
        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split($this->getSku($item), 17),
            'feed'  => 250,
            'align' => 'right'
        );

        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getQty() * 1,
            'feed'  => 375,
            'align' => 'right'
        );
        
        if ($helper->getVersionHelper()->isGe1700()) {
            // draw item Prices
            $i = 0;
            $prices = $this->getItemPricesForDisplay();
            $feedPrice = 340;
            $feedSubtotal = 495;
            foreach ($prices as $priceData){
                if (isset($priceData['label'])) {
                    // draw Price label
                    $lines[$i][] = array(
                        'text'  => $priceData['label'],
                        'feed'  => $feedPrice,
                        'align' => 'right'
                    );
                    // draw Subtotal label
                    $lines[$i][] = array(
                        'text'  => $priceData['label'],
                        'feed'  => $feedSubtotal,
                        'align' => 'right'
                    );
                    $i++;
                }

                // draw Price
                $lines[$i][] = array(
                    'text'  => $priceData['price'],
                    'feed'  => $feedPrice,
                    'font'  => 'bold',
                    'align' => 'right'
                );
                // draw Subtotal
                $lines[$i][] = array(
                    'text'  => $priceData['subtotal'],
                    'feed'  => $feedSubtotal,
                    'font'  => 'bold',
                    'align' => 'right'
                );
                $i++;
            }
        } else {
            // draw Price
            $lines[0][] = array(
                'text'  => $order->formatPriceTxt($item->getPrice()),
                'feed'  => 340,
                'font'  => 'bold',
                'align' => 'right'
            );

            // draw Subtotal
            $lines[0][] = array(
                'text'  => $order->formatPriceTxt($item->getRowTotal()),
                'feed'  => 495,
                'font'  => 'bold',
                'align' => 'right'
            );
        }
        
        // draw Tax
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getTaxAmount()),
            'feed'  => 425,
            'font'  => 'bold',
            'align' => 'right'
        );

        // draw Warehouse
        $warehouse = ($item->getWarehouse()) ? $item->getWarehouseTitle() : 'No warehouse';
        $lines[0][] = array(
            'text'      => Mage::helper('core/string')->str_split($warehouse, 25), 
            'feed'      => 545, 
            'align'     => 'right', 
        );

        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), 40, true, true),
                    'font' => 'italic',
                    'feed' => 35
                );

                if ($option['value']) {
                    if (isset($option['print_value'])) {
                        $_printValue = $option['print_value'];
                    } else {
                        $_printValue = strip_tags($option['value']);
                    }

                    $values = explode(', ', $_printValue);
                    foreach ($values as $value) {
                        $lines[][] = array(
                            'text' => Mage::helper('core/string')->str_split($value, 30, true, true),
                            'feed' => 40
                        );
                    }
                }
            }
        }

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 20
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
}
