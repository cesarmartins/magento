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
 * Admin html observer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Adminhtml_Observer
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
     * Add grid column
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function addGridColumn(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (!$block) {
            return $this;
        }

        $adminhtmlHelper = $this->getWarehouseHelper()->getAdminhtmlHelper();
        if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Grid) {
            $adminhtmlHelper->addQtyProductGridColumn($block);
            $adminhtmlHelper->addBatchPriceProductGridColumn($block);
        } else if ($block instanceof Mage_Adminhtml_Block_Report_Product_Lowstock_Grid) {
            $adminhtmlHelper->addQtyProductLowstockGridColumns($block);
        }

        return $this;
    }
    /**
     * Prepare grid
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function prepareGrid(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (!$block) {
            return $this;
        }

        $adminhtmlHelper = $this->getWarehouseHelper()->getAdminhtmlHelper();
        if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Grid) {
            $adminhtmlHelper->prepareProductGrid($block);
        } else if ($block instanceof Mage_Adminhtml_Block_Report_Product_Lowstock_Grid) {
            $adminhtmlHelper->prepareProductLowstockGrid($block);
        }

        return $this;
    }
    /**
     * Before load product collection
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function beforeLoadProductCollection($observer)
    {
        $collection = $observer->getEvent()->getCollection();
        if (!$collection) {
            return $this;
        }

        $adminhtmlHelper = $this->getWarehouseHelper()->getAdminhtmlHelper();
        if ($collection instanceof Mage_Reports_Model_Resource_Product_Lowstock_Collection) {
            $adminhtmlHelper->beforeLoadProductLowstockCollection($collection);
        }

        return $this;
    }
    /**
     * Process order create data
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function processOrderCreateData($observer)
    {
        $event              = $observer->getEvent();
        $orderCreateModel   = $event->getOrderCreateModel();
        $request            = $event->getRequest();
        if (!$orderCreateModel || !$request) {
            return $this;
        }

        if (isset($request['reset_items']) && $request['reset_items']) {
            $orderCreateModel->resetQuoteItems();
        }

        return $this;
    }

    /**
     * Verify if stock was changed
     *
     * @param  Varien_Event_Observer $observer
     * @return $this
     */
    public function salesOrderShipmentSaveBefore(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Sales_Model_Order_Shipment $shipment
         */
        $shipment = $observer->getEvent()->getShipment();
        $params   = Mage::app()->getRequest()->getParams();

        if (!isset($params['shipment']['warehouses'])) {
            return $this;
        }

        $warehouses = $params['shipment']['warehouses'];

        foreach ($shipment->getAllItems() as $item) {
            if (!isset($warehouses[$item->getOrderItemId()])) {
                continue;
            }

            /**
             * Validate if stock was changed
             */
            if ($item->getData('stock_id') == $warehouses[$item->getOrderItemId()]) {
                continue;
            }

            $stockId   = (int) $warehouses[$item->getOrderItemId()];
            $productId = (int) $item->getProductId();

            /**
             * @var MP_Warehouse_Helper_Cataloginventory $inventoryHelper
             */
            $inventoryHelper = Mage::helper('warehouse')->getCatalogInventoryHelper();

            /**
             * Load and calculate new stock
             */
            $newStock   = $inventoryHelper->getStockItem($stockId)->loadByProduct($productId);
            $currentQty = $newStock->getQty();
            $totalQty   = (float) $currentQty - $item->getQty();

            if ($totalQty < 0) {
                Mage::throwException('Error when modifying warehouse, product "' . $item->getName() . '" don\'t have stock.');
            }

            $newStock->setQty($totalQty);
            $newStock->save();

            /**
             * Revert stock
             */
            $inventoryHelper
                ->getStock($item->getStockId())
                ->backItemQty($productId, $item->getQty());
        }

        return $this;
    }
}
