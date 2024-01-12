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
 * Catalog inventory observer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Cataloginventory_Observer 
    extends Mage_CatalogInventory_Model_Observer
{
    /**
     * Quote item quantities
     * 
     * @var array
     */
    protected $_qtys = array();
    /**
     * Product qty's checked
     * 
     * @var array
     */
    protected $_checkedQuoteItems2 = array();
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
     * Get version helper
     * 
     * @return MP_Warehouse_Helper_Core_Version
     */
    protected function getVersionHelper()
    {
        return $this->getWarehouseHelper()->getVersionHelper();
    }
    /**
     * Get catalog inventory helper
     * 
     * @return MP_Warehouse_Helper_Cataloginventory
     */
    protected function getCatalogInventoryHelper()
    {
        return $this->getWarehouseHelper()->getCatalogInventoryHelper();
    }
    /**
     * Get product helper
     * 
     * @return MP_Warehouse_Helper_Catalog_Product
     */
    protected function getProductHelper()
    {
        return $this->getWarehouseHelper()->getProductHelper();
    }
    /**
     * Throw exception
     * 
     * @param string $message
     * @param string $helper
     */
    protected function throwException($message, $helper = 'cataloginventory')
    {
        Mage::throwException(Mage::helper($helper)->__($message));
    }
    /**
     * Get predefined stock identifier
     * 
     * @param MP_Warehouse_Model_Sales_Quote $quote
     * 
     * @return int
     */
    protected function getStockId($quote = null)
    {
        return $this->getWarehouseHelper()
            ->getAssignmentMethodHelper()
            ->getQuoteStockId($quote);
    }
    /**
     * Get product qty includes information from all quote items
     * 
     * @param int   $productId
     * @param int   $stockId
     * @param int   $quoteItemId
     * @param float $itemQty
     * 
     * @return int
     */
    protected function _getQuoteItemQtyForCheck2($productId, $stockId, $quoteItemId, $itemQty)
    {
        $qty = $itemQty;
        if (!$stockId) {
            $stockId = 0;
        }

        if (isset($this->_checkedQuoteItems2[$productId]) && 
            isset($this->_checkedQuoteItems2[$productId][$stockId]) && 
            isset($this->_checkedQuoteItems2[$productId][$stockId]['qty']) && 
            !in_array($quoteItemId, $this->_checkedQuoteItems2[$productId][$stockId]['items'])
        ) {
            $qty += $this->_checkedQuoteItems2[$productId][$stockId]['qty'];
        }

        $this->_checkedQuoteItems2[$productId][$stockId]['qty'] = $qty;
        $this->_checkedQuoteItems2[$productId][$stockId]['items'][] = $quoteItemId;
        return $qty;
    }
    /**
     * Add stock information to product
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Observer
     */
    public function addInventoryData($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if (!($product instanceof Mage_Catalog_Model_Product)) {
            return $this;
        }

        $helper     = $this->getWarehouseHelper();
        $config     = $helper->getConfig();
        $stockId = null;
        if ($config->isMultipleMode()) {
            $stockId = null;
        } else  {
            $stockId = $this->getStockId($product->getQuote());
        }

        $this->getWarehouseHelper()
            ->getProductPriceHelper()
            ->loadPrices($product);
        $stockItem = $this->getCatalogInventoryHelper()
            ->getStockItemCached(intval($product->getId()), $stockId);
        if ($stockId) {
            $stockItem->assignProduct($product);
        } else {
            $stockItem->assignAvailableProduct($product);
        }

        return $this;
    }
    /**
     * Remove stock information
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Observer
     */
    public function removeInventoryData($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if (!($product instanceof Mage_Catalog_Model_Product) || !$product->getId()) {
            return $this;
        }

        $this->getCatalogInventoryHelper()->unsetStockItemCached(intval($product->getId()));
        return $this;
    }
    /**
     * Add stock status to collection
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Observer
     */
    public function addStockStatusToCollection($observer) 
    {
        $collection     = $observer->getEvent()->getCollection();
        if ($collection->hasFlag('ignore_stock_items')) {
            return $this;
        }

        $stockId        = $this->getStockId();
        $this->getWarehouseHelper()
            ->getProductPriceHelper()
            ->loadCollectionPrices($collection);
        if ($collection->hasFlag('require_stock_items')) {
            $this->getCatalogInventoryHelper()
                ->getStock($stockId)
                ->addItemsToProducts($collection);
        } else {
            $this->getCatalogInventoryHelper()
                ->getStockStatus($stockId)
                ->addStockStatusToProducts($collection, null, $stockId);
        }

        return $this;
    }
    /**
     * Add stock items to collection
     *
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Observer
     */
    public function addInventoryDataToCollection($observer)
    {
        $collection = $observer->getEvent()->getProductCollection();
        if (!count($collection)) {
            return $this;
        }

        $stockId = $this->getStockId();
        $this->getWarehouseHelper()
            ->getProductPriceHelper()
            ->loadCollectionPrices($collection);
        $this->getCatalogInventoryHelper()
            ->getStock($stockId)
            ->addItemsToProducts($collection);
        return $this;
    }
    /**
     * Add stock status limitation to catalog product select
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function prepareCatalogProductIndexSelect(Varien_Event_Observer $observer)
    {
        $select   = $observer->getEvent()->getSelect();
        $entity   = $observer->getEvent()->getEntityField();
        $website  = $observer->getEvent()->getWebsiteField();
        $stock    = $observer->getEvent()->getStockField();
        $this->getCatalogInventoryHelper()
            ->getStockStatusSingleton()
            ->prepareCatalogProductIndexSelect2($select, $entity, $website, $stock);
        return $this;
    }
    /**
     * Add stock status filter to select
     *
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function addStockStatusFilterToSelect(Varien_Event_Observer $observer)
    {
        $event          = $observer->getEvent();
        $select         = $event->getSelect();
        $entity         = $event->getEntityField();
        $website        = $event->getWebsiteField();
        $stock          = $event->getStockField();
        
        if (($entity === null) || ($website === null)) {
            return $this;
        }
        
        if (!($entity instanceof Zend_Db_Expr)) {
            $entity     = new Zend_Db_Expr($entity);
        }

        if (!($website instanceof Zend_Db_Expr)) {
            $website    = new Zend_Db_Expr($website);
        }

        if (!($stock instanceof Zend_Db_Expr)) {
            $stock      = new Zend_Db_Expr($stock);
        }

        $this->getCatalogInventoryHelper()
            ->getStockStatusSingleton()
            ->prepareCatalogProductIndexSelect2($select, $entity, $website, $stock);
        
        return $this;
    }
    /**
     * Apply stock items for quote
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return Mage_CatalogInventory_Model_Observer
     */
    protected function applyQuoteStockItems($quote)
    {
        $quote->applyStocks();
        return $this;
    }
    /**
     * Whether quote item needs to be checked or not
     * 
     * @param $quoteItem MP_Warehouse_Model_Sales_Quote_Item
     * 
     * @return bool
     */
    protected function isCheckQuoteItemQty($quoteItem)
    {
        if (!$quoteItem || 
            !$quoteItem->getProductId() || 
            !$quoteItem->getQuote() || 
            $quoteItem->getQuote()->getIsSuperMode()
        ) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * Check product inventory data with qty options
     * 
     * @param  $quoteItem MP_Warehouse_Model_Sales_Quote_Item
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Observer
     */
    protected function checkQuoteItemQtyWithOptions($quoteItem)
    {
        $helper     = $this->getWarehouseHelper();
        $quote      = $quoteItem->getQuote();
        $stockItem  = $quoteItem->getStockItem();
        $product    = $quoteItem->getProduct();
        $options    = $quoteItem->getQtyOptions();
        $qty        = $product->getTypeInstance(true)->prepareQuoteItemQty($quoteItem->getQty(), $product);
        $quoteItem->setData('qty', $qty);
        if ($stockItem) {
            $result = $stockItem->checkQtyIncrements($qty);
            if ($result->getHasError()) {
                $quoteItem->setHasError(true)->setMessage($result->getMessage());
                $quote->setHasError(true)->addMessage($result->getQuoteMessage(), $result->getQuoteMessageIndex());
            }
        }

        foreach ($options as $option) {
            if ($stockItem) {
                $option->setStockId($stockItem->getStockId());
            }

            $optionQty = $qty * $option->getValue();
            $increaseOptionQty = ($quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty) * $option->getValue();
            $option->unsetStockItem();
            $stockItem = $option->getStockItem();
            
            if ($this->getVersionHelper()->isGe1700()) {
                if ($quoteItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    $stockItem->setProductName($quoteItem->getName());
                }
            }
            
            if (!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                $this->throwException('The stock item for Product in option is not valid.');
            }

            $stockItem->setOrderedItems(0);
            $stockItem->setIsChildItem(true);
            $stockItem->setSuppressCheckQtyIncrements(true);
            $qtyForCheck = $this->_getQuoteItemQtyForCheck2(
                $option->getProduct()->getId(), $stockItem->getStockId(), $quoteItem->getId(), $increaseOptionQty
            );
            if ($qtyForCheck > $optionQty) {
                $qtyForCheck = $optionQty;
            }

            $result = $stockItem->checkQuoteItemQty($optionQty, $qtyForCheck, $option->getValue());
            if (!is_null($result->getItemIsQtyDecimal())) {
                $option->setIsQtyDecimal($result->getItemIsQtyDecimal());
            }

            if ($result->getHasQtyOptionUpdate()) {
                $option->setHasQtyOptionUpdate(true);
                $quoteItem->updateQtyOption($option, $result->getOrigQty());
                $option->setValue($result->getOrigQty());
                $quoteItem->setData('qty', intval($qty));
            }

            if (!is_null($result->getMessage())) {
                $option->setMessage($result->getMessage());
                
                if ($this->getVersionHelper()->isGe1700()) {
                    $quoteItem->setMessage($result->getMessage());
                }
            }

            if (!is_null($result->getItemBackorders())) {
                $option->setBackorders($result->getItemBackorders());
            }

            if ($result->getHasError()) {
                $option->setHasError(true);
                
                if ($helper->getVersionHelper()->isGe1800()) {
                    $quoteItem->setHasError(true)->setMessage($result->getMessage());
                } else {
                    $quoteItem->setHasError(true)->setMessage($result->getQuoteMessage());
                }
                
                $quote->setHasError(true)->addMessage($result->getQuoteMessage(), $result->getQuoteMessageIndex());
            }

            $stockItem->unsIsChildItem();
        }

        return $this;
    }
    /**
     * Check product inventory data without qty options
     * 
     * @param $quoteItem MP_Warehouse_Model_Sales_Quote_Item
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Observer
     */
    protected function checkQuoteItemQtyWithoutOptions($quoteItem)
    {
        $helper     = $this->getWarehouseHelper();
        $quote      = $quoteItem->getQuote();
        $stockItem  = $quoteItem->getStockItem();
        $product    = $quoteItem->getProduct();
        $qty        = $quoteItem->getQty();
        if (!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
            $this->throwException('The stock item for Product is not valid.');
        }

        if ($quoteItem->getParentItem()) {
            $rowQty = $quoteItem->getParentItem()->getQty() * $qty;
            $qtyForCheck = $this->_getQuoteItemQtyForCheck2($product->getId(), $stockItem->getStockId(), $quoteItem->getId(), 0);
        } else {
            $increaseQty = $quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty;
            $rowQty = $qty;
            $qtyForCheck = $this->_getQuoteItemQtyForCheck2($product->getId(), $stockItem->getStockId(), $quoteItem->getId(), $increaseQty);
        }

        $productTypeCustomOption = $product->getCustomOption('product_type');
        if (!is_null($productTypeCustomOption)) {
            if ($productTypeCustomOption->getValue() == Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE) {
                if ($helper->getVersionHelper()->isGe1800()) {
                    $stockItem->setProductName($quoteItem->getProduct()->getName());
                }
                
                $stockItem->setIsChildItem(true);
            }
        }

        if ($qtyForCheck > $rowQty) {
            $qtyForCheck = $rowQty;
        }

        $result = $stockItem->checkQuoteItemQty($rowQty, $qtyForCheck, $qty);
        if ($stockItem->hasIsChildItem()) {
            $stockItem->unsIsChildItem();
        }

        if (!is_null($result->getItemIsQtyDecimal())) {
            $quoteItem->setIsQtyDecimal($result->getItemIsQtyDecimal());
            if ($quoteItem->getParentItem()) {
                $quoteItem->getParentItem()->setIsQtyDecimal($result->getItemIsQtyDecimal());
            }
        }

        if ($result->getHasQtyOptionUpdate() && (!$quoteItem->getParentItem() || 
            $quoteItem->getParentItem()->getProduct()->getTypeInstance(true)
                ->getForceChildItemQtyChanges($quoteItem->getParentItem()->getProduct()))) {
            $quoteItem->setData('qty', $result->getOrigQty());
        }

        if (!is_null($result->getItemUseOldQty())) {
            $quoteItem->setUseOldQty($result->getItemUseOldQty());
        }

        if (!is_null($result->getMessage())) {
            $quoteItem->setMessage($result->getMessage());
            if ($quoteItem->getParentItem()) {
                $quoteItem->getParentItem()->setMessage($result->getMessage());
            }
        }

        if (!is_null($result->getItemBackorders())) {
            $quoteItem->setBackorders($result->getItemBackorders());
        }

        if ($result->getHasError()) {
            $quoteItem->setHasError(true);
            $quote->setHasError(true)->addMessage($result->getQuoteMessage(), $result->getQuoteMessageIndex());
        }

        return $this;
    }
    /**
     * Check parent quote item qty
     * 
     * @param $quoteItem MP_Warehouse_Model_Sales_Quote_Item
     * 
     * @return Mage_CatalogInventory_Model_Observer
     */
    public function checkParentQuoteItemQty($quoteItem)
    {
        $quote              = $quoteItem->getQuote();
        $stockItem          = $quoteItem->getStockItem();
        $parentStockItem    = false;
        if ($quoteItem->getParentItem()) {
            $parentStockItem = $quoteItem->getParentItem()->getStockItem();
        }

        if ($stockItem) {
            if (!$stockItem->getIsInStock() || ($parentStockItem && !$parentStockItem->getIsInStock())) {
                $quoteItem->addErrorInfo(
                    'cataloginventory',
                    Mage_CatalogInventory_Helper_Data::ERROR_QTY,
                    Mage::helper('cataloginventory')->__('This product is currently out of stock.')
                );
                $quote->addErrorInfo(
                    'stock',
                    'cataloginventory',
                    Mage_CatalogInventory_Helper_Data::ERROR_QTY,
                    Mage::helper('cataloginventory')->__('Some of the products are currently out of stock.')
                );
                return $this;
            } else {
                $this->_removeErrorsFromQuoteAndItem($quoteItem, Mage_CatalogInventory_Helper_Data::ERROR_QTY);
            }
        }

        return $this;
    }
    /**
     * Check product inventory data
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return Mage_CatalogInventory_Model_Observer
     */
    public function checkQuoteItemQty($observer)
    {
        $helper     = $this->getWarehouseHelper();
        $quoteItem  = $observer->getEvent()->getItem();
        if (!$this->isCheckQuoteItemQty($quoteItem)) {
            return $this;
        }

        $this->applyQuoteStockItems($quoteItem->getQuote());
        if ($quoteItem->isDeleted()) {
            return $this;
        }

        if ($helper->getVersionHelper()->isGe1800()) {
            $this->checkParentQuoteItemQty($quoteItem);
        }

        if ($quoteItem->getQtyOptions() && ($quoteItem->getQty() > 0)) {
            $this->checkQuoteItemQtyWithOptions($quoteItem);
        } else {
            $this->checkQuoteItemQtyWithoutOptions($quoteItem);
        }

        return $this;
    }
    /**
     * Saving product inventory data. Product qty calculated dynamically.
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Observer
     */
    public function saveInventoryData($observer)
    {
        /*
        foreach (Mage::helper('cataloginventory')->getConfigItemOptions() as $option) {
            if (isset($inventoryData[$option]) && !isset($inventoryData['use_config_' . $option])) {
                $inventoryData['use_config_' . $option] = 0;
            }
        }
        */
        
        /*
        $item->addData($product->getStockData())
            ->setProduct($product)
            ->setProductId($product->getId())
            ->setStockId($item->getStockId());
        if (!is_null($product->getData('stock_data/min_qty'))
            && is_null($product->getData('stock_data/use_config_min_qty'))) {
            $item->setData('use_config_min_qty', false);
        }
        if (!is_null($product->getData('stock_data/min_sale_qty'))
            && is_null($product->getData('stock_data/use_config_min_sale_qty'))) {
            $item->setData('use_config_min_sale_qty', false);
        }
        if (!is_null($product->getData('stock_data/max_sale_qty'))
            && is_null($product->getData('stock_data/use_config_max_sale_qty'))) {
            $item->setData('use_config_max_sale_qty', false);
        }
        if (!is_null($product->getData('stock_data/backorders'))
            && is_null($product->getData('stock_data/use_config_backorders'))) {
            $item->setData('use_config_backorders', false);
        }
        if (!is_null($product->getData('stock_data/notify_stock_qty'))
            && is_null($product->getData('stock_data/use_config_notify_stock_qty'))) {
            $item->setData('use_config_notify_stock_qty', false);
        }
        $originalQty = $product->getData('stock_data/original_inventory_qty');
        if (strlen($originalQty)>0) {
            $item->setQtyCorrection($item->getQty()-$originalQty);
        }
        if (!is_null($product->getData('stock_data/enable_qty_increments'))
            && is_null($product->getData('stock_data/use_config_enable_qty_inc'))) {
            $item->setData('use_config_enable_qty_inc', false);
        }
        if (!is_null($product->getData('stock_data/qty_increments'))
            && is_null($product->getData('stock_data/use_config_qty_increments'))) {
            $item->setData('use_config_qty_increments', false);
        }
        */
        
        $inventoryHelper        = $this->getCatalogInventoryHelper();
        $product                = $observer->getEvent()
            ->getProduct();
        if (is_null($product->getStocksData())) {
            if ($product->getIsChangedWebsites() || $product->dataHasChangedFor('status')) {
                foreach ($inventoryHelper->getStockIds() as $stockId) {
                    $inventoryHelper->getStockStatusSingleton($stockId)->updateStatus($product->getId());
                }
            }

            return $this;
        }

        $data                   = $product->getStocksData();
        if (!count($data)) {
            return $this;
        }

        $keys                   = $inventoryHelper->getConfigItemOptions();
        foreach ($inventoryHelper->getStockIds() as $stockId) {
            $item                   = $inventoryHelper->getStockItem($stockId)
                ->loadByProduct($product);
            $isEmpty                = true;
            foreach ($data as $dataItem) {
                if(isset($dataItem['use_config_manage_stock']) && $dataItem['use_config_manage_stock']){
                    continue;
                }

                if (isset($dataItem['stock_id']) && ($stockId == (int) $dataItem['stock_id'])) {
                    foreach ($keys as $key) {
                        $useConfigKey           = 'use_config_'.$key;
                        if (isset($dataItem[$useConfigKey])) {
                            if ($dataItem[$useConfigKey]) {
                                $dataItem[$useConfigKey] = 1;
                            } else {
                                $dataItem[$useConfigKey] = 0;
                            }
                        } else {
                            $dataItem[$useConfigKey] = 0;
                        }

                        if (!isset($dataItem[$key])) {
                            $dataItem[$useConfigKey] = 1;
                        }
                    }

                    $item->addData($dataItem);
                    $isEmpty                = false;
                    break;
                }
            }

            if ($isEmpty) {
                continue;
            }

            $item->setProduct($product);
            foreach ($keys as $key) {
                if (is_null($item->getData($key))) {
                    $item->setData('use_config_'.$key, 1);
                }
            }

            $originalQty            = $item->getData('original_inventory_qty');
            if (strlen($originalQty) > 0) {
                $item->setQtyCorrection($item->getQty() - $originalQty);
            }

            $item->save();
        }

        return $this;
    }
    /**
     * Update items stock status and low stock date.
     *
     * @param Varien_Event_Observer $observer
     * 
     * @return  MP_Warehouse_Model_Cataloginventory_Observer
     */
    public function updateItemsStockUponConfigChange($observer)
    {
        $inventoryHelper = $this->getCatalogInventoryHelper();
        foreach ($inventoryHelper->getStockIds() as $stockId) {
            $stockResourceSingleton = $inventoryHelper->getStockResource($stockId);
            $stockResourceSingleton->updateSetOutOfStock();
            $stockResourceSingleton->updateSetInStock();
            $stockResourceSingleton->updateLowStockDate();
        }

        return $this;
    }
    /**
     * Cancel order item
     *
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Observer
     */
    public function cancelOrderItem($observer)
    {
        $item       = $observer->getEvent()->getItem();
        $children   = $item->getChildrenItems();
        $qty        = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();
        if ($item->getId() && ($productId = $item->getProductId()) && empty($children) && $qty) {
            $this->getCatalogInventoryHelper()->getStockSingleton($item->getStockId())->backItemQty($productId, $qty);
        }

        return $this;
    }
    /**
     * Return creditmemo items qty to stock
     *
     * @param Varien_Event_Observer $observer
     */
    public function refundOrderInventory($observer)
    {
        $inventoryHelper        = $this->getCatalogInventoryHelper();
        $creditmemo             = $observer->getEvent()->getCreditmemo();
        $items                  = array();
        $isAutoReturnEnabled    = Mage::helper('cataloginventory')->isAutoReturnEnabled();
        foreach ($creditmemo->getAllItems() as $item) {
            $return = false;
            if ($item->hasBackToStock()) {
                if ($item->getBackToStock() && $item->getQty()) {
                    $return = true;
                }
            } elseif ($isAutoReturnEnabled) {
                $return = true;
            }

            if ($return) {
                $orderItem = $item->getOrderItem();
                $productId = $item->getProductId();
                $stockId = ($orderItem) ? $orderItem->getStockId() : $inventoryHelper->getDefaultStockId();
                
                if ($this->getVersionHelper()->isGe1700()) {
                    $parentOrderId = $item->getOrderItem()->getParentItemId();
                    $parentItem = $parentOrderId ? $creditmemo->getItemByOrderId($parentOrderId) : false;
                    $qty = $parentItem ? ($parentItem->getQty() * $item->getQty()) : $item->getQty();
                    if (isset($items[$productId]) && isset($items[$productId][$stockId])) {
                        $items[$productId][$stockId]['qty'] += $qty;
                    } else {
                        $items[$productId][$stockId] = array(
                            'qty'   => $qty, 
                            'item'  => null, 
                        );
                    }
                } else {
                    if (isset($items[$productId]) && isset($items[$productId][$stockId])) {
                        $items[$productId][$stockId]['qty'] += $item->getQty();
                    } else {
                        $items[$productId][$stockId] = array(
                            'qty'   => $item->getQty(), 
                            'item'  => null, 
                        );
                    }
                }
            }
        }

        $inventoryHelper->getStockSingleton()->revertProductsSale($items);
        return $this;
    }
    /**
     * Adds stock item qty to $items
     *
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * 
     * @param array &$items
     */
    protected function _addItemToQtyArray($quoteItem, &$items)
    {
        $productId = $quoteItem->getProductId();
        if (!$productId) return;
        $stockItem = null;
        if ($quoteItem->getProduct()) {
            $stockItem = $quoteItem->getStockItem();
        }

        $stockId = ($stockItem) ? $stockItem->getStockId() : 0;
        if (isset($items[$productId]) && isset($items[$productId][$stockId])) {
            $items[$productId][$stockId]['qty'] += $quoteItem->getTotalQty();
        } else {
            $items[$productId][$stockId] = array(
                'item'  => $stockItem, 
                'qty'   => $quoteItem->getTotalQty(), 
            );
        }

        return $this;
    }
    /**
     * Update Only product status observer
     *
     * @deprecated
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Observer
     */
    public function productStatusUpdate(Varien_Event_Observer $observer)
    {
        $inventoryHelper = $this->getCatalogInventoryHelper();
        $productId = $observer->getEvent()->getProductId();
        foreach ($inventoryHelper->getStockIds() as $stockId) {
            $inventoryHelper->getStockStatusSingleton($stockId)->updateStatus($productId);
        }

        return $this;
    }
    /**
     * Catalog Product website update
     *
     * @deprecated
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Observer
     */
    public function catalogProductWebsiteUpdate(Varien_Event_Observer $observer)
    {
        $inventoryHelper = $this->getCatalogInventoryHelper();
        $websiteIds     = $observer->getEvent()->getWebsiteIds();
        $productIds     = $observer->getEvent()->getProductIds();
        foreach ($websiteIds as $websiteId) {
            foreach ($productIds as $productId) {
                foreach ($inventoryHelper->getStockIds() as $stockId) {
                    $inventoryHelper->getStockStatusSingleton($stockId)->updateStatus($productId, null, $websiteId);
                }
            }
        }

        return $this;
    }
    /**
     * Add stock status to prepare index select
     * 
     * @deprecated
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Observer
     */
    public function addStockStatusToPrepareIndexSelect(Varien_Event_Observer $observer) 
    {
        $inventoryHelper = $this->getCatalogInventoryHelper();
        $website        = $observer->getEvent()->getWebsite();
        $select         = $observer->getEvent()->getSelect();
        $inventoryHelper->getStockStatusSingleton($this->getStockId())->addStockStatusToSelect($select, $website);
        return $this;
    }
}
