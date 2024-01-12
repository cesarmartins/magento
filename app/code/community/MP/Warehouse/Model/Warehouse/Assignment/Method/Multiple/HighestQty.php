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
 * Highest qty multiple assignment method
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Warehouse_Assignment_Method_Multiple_HighestQty 
    extends MP_Warehouse_Model_Warehouse_Assignment_Method_Multiple_Abstract
{
    /**
     * Apply quote stock items
     * 
     * @param MP_Warehouse_Model_Sales_Quote $quote
     * 
     * @return $this
     */
    public function applyQuoteStockItems($quote = null)
    {
        if (is_null($quote)) {
            $quote                  = $this->getQuote();
        }

        if (!$quote) {
            return $this;
        }

        $helper                 = $this->getWarehouseHelper();
        $quoteHelper            = $helper->getQuoteHelper();
        $stockData              = $quoteHelper->getStockData($quote);
        if (($stockData === null) || !count($stockData)) {
            return $this;
        }

        $combination            = array();
        $productHelper          = $helper->getProductHelper();
        foreach ($stockData as $itemKey => $itemStockData) {
            $itemStockId            = null;
            if ($itemStockData->getIsInStock()) {
                if (!$itemStockData->getSessionStockId()) {
                    $stockIds               = array();
                    foreach ($itemStockData->getStockItems() as $stockItem) {
                        $stockId                = (int) $stockItem->getStockId();
                        if ($stockId) {
                            array_push($stockIds, $stockId);
                        }
                    }

                    $itemStockId            = $productHelper->getQuoteMaxQtyStockId(
                        $itemStockData->getProduct(), 
                        $stockIds, 
                        $itemStockData->getBuyRequest()
                    );
                } else {
                    $itemStockId            = $itemStockData->getSessionStockId();
                }
            }

            if ($itemStockId === null) {
                $combination            = null;
                break;
            }

            $combination[$itemKey]  = $itemStockId;
        }

        if (!is_null($combination)) {
            $quote->applyStockItemsCombination($stockData, $combination);
        }

        return $this;
    }
    /**
     * Get product stock id
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return integer
     */
    protected function _getProductStockId($product)
    {
        $helper                 = $this->getWarehouseHelper();
        $productHelper          = $helper->getProductHelper();
        $stockIds               = $productHelper->getQuoteInStockStockIds($product);
        $stockId                = $productHelper->getQuoteMaxQtyStockId($product, $stockIds);
        return ($stockId) ? 
            $stockId : 
            $helper->getDefaultStockId();
    }
}
