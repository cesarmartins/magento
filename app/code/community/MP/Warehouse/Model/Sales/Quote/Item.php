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
 * Quote item
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Sales_Quote_Item 
    extends Mage_Sales_Model_Quote_Item
{
    /**
     * Stock item model
     * 
     * @var Mage_CatalogInventory_Model_Stock_Item
     */
    protected $_stockItem;
    /**
     * Stock items
     * 
     * @var array of MP_Warehouse_Model_Cataloginventory_Stock_Item
     */
    protected $_stockItems;
    /**
     * In stock stock items
     * 
     * @var array of MP_Warehouse_Model_Cataloginventory_Stock_Item
     */
    protected $_inStockStockItems;
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
     * Get catalog inventory helper
     * 
     * @return MP_Warehouse_Helper_Cataloginventory
     */
    protected function getCatalogInventoryHelper()
    {
        return $this->getWarehouseHelper()->getCatalogInventoryHelper();
    }
    /**
     * Clone quote item
     *
     * @return MP_Warehouse_Model_Sales_Quote_Item
     */
    public function __clone()
    {
        parent::__clone();
        $this->_stockItem           = null;
        $this->_stockItems          = null;
        $this->_inStockStockItems   = null;
        if ($this->_getData('product')) {
            $this->setData('product', clone $this->_getData('product'));
        }

        return $this;
    }
    /**
     * Get warehouse
     * 
     * @return MP_Warehouse_Model_Warehouse
     */
    public function getWarehouse()
    {
        $warehouse = null;
        if ($stockId = $this->getStockId()) {
            $warehouse = $this->getWarehouseHelper()->getWarehouseByStockId($stockId);
        }

        return $warehouse;
    }
    /**
     * Get warehouse title
     * 
     * @return string
     */
    public function getWarehouseTitle()
    {
        $warehouse = $this->getWarehouse();
        if ($warehouse) {
            return $warehouse->getTitle();
        } else {
            return null;
        }
    }
    /**
     * Get warehouse description
     * 
     * @return string
     */
    public function getWarehouseDescription()
    {
        $warehouse = $this->getWarehouse();
        if ($warehouse) {
            return $warehouse->getDescription();
        } else {
            return null;
        }
    }
    /**
     * Get product
     * 
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        return $this->_getData('product');
    }
    /**
     * Set product
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Item
     */
    protected function _setProduct($product)
    {
        $this->setData('product', $product);
        return $this;
    }
    /**
     * Get stock identifier
     * 
     * @return int
     */
    protected function _getStockId()
    {
        return $this->_getData('stock_id');
    }
    /**
     * Set stock identifier
     * 
     * @param int $stockId
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Item
     */
    protected function _setStockId($stockId)
    {
        $this->setData('stock_id', $stockId);
        return $this;
    }
    /**
     * Get stock item
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Stock_Item
     */
    protected function _getStockItem()
    {
        return $this->_stockItem;
    }
    /**
     * Set stock item
     * 
     * @param MP_Warehouse_Model_Cataloginventory_Stock_Item $stockItem
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Item
     */
    protected function _setStockItem($stockItem)
    {
        $this->_stockItem = $stockItem;
        return $this;
    }
    /**
     * Unset stock item
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Stock_Item
     */
    protected function _unsetStockItem()
    {
        if (!is_null($this->_stockItem)) {
            $this->_stockItem = null;
        }

        return $this;
    }
    /**
     * Set product
     * 
     * @param   Mage_Catalog_Model_Product $product
     * 
     * @return  MP_Warehouse_Model_Sales_Quote_Item
     */
    public function setProduct($product)
    {
        if ($this->getQuote() && $this->getQuote()->isDuplicatedProductId($product->getId())) {
            $product = clone $product;
        }

        $this->_unsetStockItem();
        $this->_setProduct($product);
        $this->getStockItem();
        parent::setProduct($product);
        return $this;
    }
    /**
     * Set stock identifier
     * 
     * @param int $stockId
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Item
     */
    public function setStockId($stockId)
    {
        $this->_unsetStockItem();
        $this->_setStockId($stockId);
        $this->getStockItem();
        return $this;
    }
    /**
     * Get stock item
     * 
     * @return MP_Warehouse_Model_Cataloginventory_Stock_Item
     */
    public function getStockItem()
    {
        $stockItem = $this->_getStockItem();
        if (!$stockItem) {
            $stockId = $this->_getStockId();
            if (!$stockId) {
                $product = $this->_getProduct();
                if ($product) {
                    $stockItem = $product->getStockItem();
                    if ($stockItem) {
                        $this->setStockItem($stockItem);
                        return $this->_getStockItem();
                    } else {
                        return null;
                    }
                } else {
                    return null;
                }
            } else {
                $stockItem = $this->getCatalogInventoryHelper()->getStockItem($stockId);
                $this->setStockItem($stockItem);
                return $this->_getStockItem();
            }
        } else {
            return $this->_getStockItem();
        }
    }
    /**
     * Set stock item
     * 
     * @param MP_Warehouse_Model_Cataloginventory_Stock_Item $stockItem
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Item
     */
    public function setStockItem($stockItem)
    {
        if ($stockItem && $stockItem->getStockId()) {
            $stockId = $stockItem->getStockId();
            $this->_setStockId($stockId);
            $this->_setStockItem($stockItem);
            $product = $this->_getProduct();
            if ($product && $stockItem && method_exists($stockItem, 'assignProduct')) {
                $stockItem->assignProduct($product);
            }
        }

        return $this;
    }
    /**
     * Check quote item for availability by stock item
     * 
     * @param MP_Warehouse_Model_Cataloginventory_Stock_Item | null $stockItem
     * 
     * @return Varien_Object
     */
    public function checkQty($stockItem = null) 
    {
        $helper     = $this->getWarehouseHelper();
        $result     = new Varien_Object();
        $result->setHasError(false);
        if (!$this->getProductId() || !$this->getQuote()) {
            $result->setHasError(true);
            return $result;
        }

        if ($this->getQuote()->getIsSuperMode()) {
            $result->setHasError(false);
            return $result;
        }

        $product = $this->getProduct();
        if (!$stockItem) {
            $stockItem = $this->getStockItem();
        } else if (!$stockItem->getProduct()) {
            $stockItem->setProduct($product);
        }
        
        if ($helper->getVersionHelper()->isGe1800()) {
            $parentStockItem = false;
            if ($this->getParentItem()) {
                $parentStockItem = $this->getParentItem()->getStockItem();
            }

            if ($stockItem) {
                if (!$stockItem->getIsInStock() || ($parentStockItem && !$parentStockItem->getIsInStock())) {
                    $result->setHasError(true);
                    return $result;
                }
            }
        }
        
        $qty = $this->getQty();
        if (($options = $this->getQtyOptions()) && $qty > 0) {
            $qty = $product->getTypeInstance(true)->prepareQuoteItemQty($qty, $product);
            if ($stockItem) {
                $result = $stockItem->checkQtyIncrements($qty);
                if ($result->getHasError()) {
                    return $result;
                }
            }

            foreach ($options as $option) {
                if ($stockItem) {
                    $option->setStockId($stockItem->getStockId());
                }

                $optionQty = $qty * $option->getValue();
                $increaseOptionQty = ($this->getQtyToAdd() ? $this->getQtyToAdd() : $qty) * $option->getValue();
                $option->unsetStockItem();
                $stockItem = $option->getStockItem();
                if (!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) return false;
                $stockItem->setOrderedItems(0);
                $stockItem->setIsChildItem(true);
                $stockItem->setSuppressCheckQtyIncrements(true);
                $qtyForCheck = $increaseOptionQty;
                $optionResult = $stockItem->checkQuoteItemQty($optionQty, $qtyForCheck, $option->getValue());
                $stockItem->unsIsChildItem();
                if (!$optionResult->getHasError()) {
                    if ($optionResult->getHasQtyOptionUpdate()) {
                        $result->setHasQtyOptionUpdate(true);
                    }

                    if ($optionResult->getItemIsQtyDecimal()) {
                        $result->setItemIsQtyDecimal(true);
                    }

                    if ($optionResult->getItemQty()) {
                        $result->setItemQty(floatval($result->getItemQty()) + $optionResult->getItemQty());
                    }

                    if ($optionResult->getOrigQty()) {
                        $result->setOrigQty(floatval($result->getOrigQty()) + $optionResult->getOrigQty());
                    }

                    if ($optionResult->getItemUseOldQty()) {
                        $result->setItemUseOldQty(true);
                    }

                    if ($optionResult->getItemBackorders()) {
                        $result->setItemBackorders(floatval($result->getItemBackorders()) + $optionResult->getItemBackorders());
                    }
                } else {
                    return $optionResult;
                }
            }
        } else {
            if (!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                $result->setHasError(true);
                return $result;
            }

            $rowQty = $increaseQty = 0;
            if (!$this->getParentItem()) {
                $increaseQty = $this->getQtyToAdd() ? $this->getQtyToAdd() : $qty;
                $rowQty = $qty;
            } else {
                $rowQty = $this->getParentItem()->getQty() * $qty;
            }

            $qtyForCheck = $increaseQty;
            $productTypeCustomOption = $product->getCustomOption('product_type');
            if (!is_null($productTypeCustomOption)) {
                if ($productTypeCustomOption->getValue() == Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE) {
                    $stockItem->setIsChildItem(true);
                }
            }

            $result = $stockItem->checkQuoteItemQty($rowQty, $qtyForCheck, $qty);
            if ($stockItem->hasIsChildItem()) {
                $stockItem->unsIsChildItem();
            }
        }

        return $result;
    }
    /**
     * Get stock items collection
     * 
     * @return Mage_CatalogInventory_Model_Mysql4_Stock_Item_Collection
     */
    protected function getStockItemsCollection()
    {
        return $this->getWarehouseHelper()
            ->getCatalogInventoryHelper()
            ->getStockItemCollection($this->getProductId(), true);
    }
    /**
     * Get stock items
     * 
     * @return array of MP_Warehouse_Model_Cataloginventory_Stock_Item
     */
    public function getStockItems()
    {
        if (is_null($this->_stockItems)) {
            $stockItems = array();
            foreach ($this->getStockItemsCollection() as $stockItem) {
                $stockItems[$stockItem->getStockId()] = $stockItem;
            }

            $this->_stockItems = $stockItems;
        }

        return $this->_stockItems;
    }
    /**
     * Unset stock items
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Item
     */
    public function unsetStockItems()
    {
        $this->_stockItems = null;
    }
    /**
     * Get stock identifiers
     */
    public function getStockIds()
    {
        $stockIds = array();
        foreach ($this->getStockItems() as $stockId => $stockItem) {
            $stockIds[$stockId] = $stockId;
        }

        return $stockIds;
    }
    /**
     * Get shipping address
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Address
     */
    public function getShippingAddress()
    {
        $quote = $this->getQuote();
        if ($quote) {
            return $quote->getShippingAddress2($this->getStockId());
        } else {
            return null;
        }
    }
    /**
     * Get in stock stock items
     * 
     * @return array of MP_Warehouse_Model_Cataloginventory_Stock_Item
     */
    public function getInStockStockItems()
    {
        if ($this->getLastCheckQty() != $this->getQty()) {
            $stockItems = array();
            foreach ($this->getStockItems() as $stockItem) {
                $result = $this->checkQty($stockItem);
                if (!$result->getHasError()) {
                    $stockItem->setItemBackorders($result->getItemBackorders());
                    $stockItems[$stockItem->getStockId()] = $stockItem;
                }
            }

            $this->_inStockStockItems = $stockItems;
            $this->setLastCheckQty($this->getQty());
        }

        return $this->_inStockStockItems;
    }
    /**
     * Unset in stock stock items
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Item
     */
    public function unsetInStockStockItems()
    {
        $this->_inStockStockItems = null;
    }
    /**
     * Get in stock stock identifiers
     * 
     * @return array
     */
    public function getInStockStockIds()
    {
        $stockIds = array();
        foreach ($this->getInStockStockItems() as $stockItem) {
            $stockId = $stockItem->getStockId();
            $stockIds[$stockId] = $stockId;
        }

        return $stockIds;
    }
    /**
     * Get available warehouses
     * 
     * @return array of MP_Warehouse_Model_Warehouse
     */
    public function getInStockWarehouses()
    {
        $warehouses = array();
        $stocksIds = $this->getInStockStockIds();
        if (count($stocksIds)) {
            $warehouses = $this->getWarehouseHelper()->getWarehousesByStockIds($stocksIds);
        }

        return $warehouses;
    }
    /**
     * Clear order object data
     *
     * @param string $key data key
     * 
     * @return MP_Warehouse_Model_Sales_Order
     */
    public function unsetData($key=null)
    {
        parent::unsetData($key);
        if (is_null($key)) {
            $this->_stockItem = null;
            $this->unsetStockItems();
            $this->unsetInStockStockItems();
        }

        return $this;
    }
    /**
     * Check if item is parent
     * 
     * @return bool
     */
    public function isParentItem()
    {
        return (count($this->getChildren())) ? true : false;
    }
    /**
     * Get child item
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Item
     */
    public function getChild()
    {
        if ($this->isParentItem()) {
            $children = $this->getChildren();
            if (count($children)) {
                return current($children);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
    /**
     * Check if stock identifier is static
     * 
     * @return bool
     */
    public function isStockIdStatic()
    {
        return ($this->getIsStockIdStatic()) ? true : false;
    }
    /**
     * Sort stock identifiers
     * 
     * @param int $stockId1
     * @param int $stockId2
     * 
     * @return int
     */
    protected function sortStockIds($stockId1, $stockId2)
    {
        $productHelper = $this->getWarehouseHelper()->getProductHelper();
        $product = $this->getProduct();
        $priority1 = $productHelper->getStockPriority($product, $stockId1);
        $priority2 = $productHelper->getStockPriority($product, $stockId2);
        if ($priority1 != $priority2) {
            return $priority1 < $priority2 ? -1 : 1;
        }

        return 0;
    }
    /**
     * Get complex item splitted stock quantities
     * 
     * @param array $children
     * @param string $childQtyMethod
     * 
     * @return array
     */
    protected function _getContainerItemSplittedStockQtys($children, $childQtyMethod)
    {
        $stockQtys = array();
        $qty = $this->getQty();
        foreach ($children as $childItem) {
            $childProductId = $childItem->getProductId();
            $childQty = $childItem->$childQtyMethod();
            if ($childQty <= 0) {
                $childQty = 1;
            }

            $totalQty = $qty * $childQty;
            foreach ($childItem->getStockItems() as $stockId => $stockItem) {
                $stockQty = $stockItem->getMaxStockQty($totalQty);
                if (($stockQty !== false) && ($stockQty > 0)) {
                    $stockQtys[$stockId][$childProductId] = floor($stockQty / $childQty);
                } else {
                    $stockQtys[$stockId][$childProductId] = null;
                }
            }
        }

        $_stockQtys = $stockQtys;
        $stockQtys = array();
        $stockIds = $this->getStockIds();
        foreach ($_stockQtys as $stockId => $_qtys) {
            $_qty = null;
            if (in_array($stockId, $stockIds)) {
                foreach ($children as $childItem) {
                    $childProductId = $childItem->getProductId();
                    if (!isset($_qtys[$childProductId]) && is_null($_qtys[$childProductId])) {
                        $_qty = null;
                        break;
                    } else {
                        if (is_null($_qty) || ($_qtys[$childProductId] < $_qty)) {
                            $_qty = $_qtys[$childProductId];
                        }
                    }
                }
            }

            $stockQtys[$stockId] = $_qty;
        }

        $_stockQtys = $stockQtys;
        $stockQtys = array();
        $totalQty = $this->getQty();
        $stockIds = array();
        foreach ($_stockQtys as $stockId => $_qty) {
            array_push($stockIds, $stockId);
        }

        usort($stockIds, array($this, 'sortStockIds'));
        foreach ($stockIds as $stockId) {
            if (isset($_stockQtys[$stockId])) {
                $_qty = $_stockQtys[$stockId];
                if (!is_null($_qty)) {
                    if ($totalQty > $_qty) {
                        $stockQtys[$stockId] = $_qty;
                        $totalQty -= $_qty;
                    } else {
                        $stockQtys[$stockId] = $totalQty;
                        $totalQty = 0;
                        break;
                    }
                }
            }
        }

        if ($totalQty > 0) {
            $stockQtys = array();
        }

        return $stockQtys;
    }
    /**
     * Get complex item splitted stock quantities
     * 
     * @return array
     */
    protected function getContainerItemSplittedStockQtys()
    {
        $stockQtys = array();
        if ($this->isParentItem()) {
            $stockQtys = $this->_getContainerItemSplittedStockQtys($this->getChildren(), 'getQty');
        }

        return $stockQtys;
    }
    /**
     * Get simple item splitted stock quantities
     * 
     * @return array
     */
    protected function getSimpleItemSplittedStockQtys()
    {
        $stockQtys = array();
        if (!count($this->getQtyOptions())) {
            $totalQty = $this->getTotalQty();
            $stockItems = $this->getStockItems();
            $stockIds = $this->getStockIds();
            usort($stockIds, array($this, 'sortStockIds'));
            foreach ($stockIds as $stockId) {
                if (isset($stockItems[$stockId])) {
                    $stockItem = $stockItems[$stockId];
                    $stockQty = $stockItem->getMaxStockQty($totalQty);
                    if (($stockQty !== false) && ($stockQty > 0)) {
                        $stockQtys[$stockId] = $stockQty;
                        $totalQty -= $stockQty;
                        if ($totalQty <= 0) {
                            break;
                        }
                    }
                }
            }

            if ($totalQty > 0) {
                $stockQtys = array();
            }
        } else {
            $stockQtys = $this->_getContainerItemSplittedStockQtys($this->getQtyOptions(), 'getValue');
        }

        return $stockQtys;
    }
    /**
     * Get splitted stock quantities
     * 
     * @return array
     */
    protected function getSplittedStockQtys()
    {
        $stockQtys = array();
        if ($this->isParentItem()) {
            $stockQtys = $this->getContainerItemSplittedStockQtys();
        } else {
            $stockQtys = $this->getSimpleItemSplittedStockQtys();
        }

        return $stockQtys;
    }
    /**
     * Get splitted stock data
     * 
     * @return array of Varien_Object
     */
    public function getSplittedStockData()
    {
        $stockData = array();
        $stockQtys = $this->getSplittedStockQtys();
        if (count($stockQtys)) {
            $productId = $this->getProductId();
            foreach ($stockQtys as $stockId => $qty) {
                $stockIds = array($stockId => $stockId);
                $stockItems = array();
                foreach ($this->getStockItems() as $_stockId => $stockItem) {
                    if ($_stockId == $stockId) {
                        $stockItems[$stockId] = $stockItem;
                        break;
                    }
                }

                $itemStockData = new Varien_Object();
                $itemStockData->setProductId($productId);
                $itemStockData->setProduct($this->getProduct());
                $itemStockData->setBuyRequest($this->getBuyRequest());
                $itemStockData->setStockItems($stockItems);
                $itemStockData->setStockIds($stockIds);
                $itemStockData->setStockId($stockId);
                $itemStockData->setIsInStock((count($stockIds) ? true : false));
                $itemStockData->setQty($qty);
                if ($this->isParentItem()) {
                    $children = array();
                    foreach ($this->getChildren() as $childItem) {
                        $childItemStockData = $childItem->getStockData($stockIds);
                        $children[$childItem->getProductId()] = $childItemStockData;
                    }
                } else {
                    $children = null;
                }

                $itemStockData->setChildren($children);
                $itemStockData->setParent((count($children) ? true : false));
                $stockData[] = $itemStockData;
            }
        }

        return $stockData;
    }
    /**
     * Get stock data
     * 
     * @param array $stockIds
     * @param bool $forceNoBackorders
     * 
     * @return Varien_Object
     */
    public function getStockData($stockIds = null, $forceNoBackorders = false)
    {
        $helper             = $this->getWarehouseHelper();
        $config             = $helper->getConfig();
        $productHelper      = $helper->getProductHelper();
        $product            = $this->getProduct();
        
        $stockData          = new Varien_Object();
        $stockData->setProductId($this->getProductId());
        $stockData->setProduct($product);
        $stockData->setBuyRequest($this->getBuyRequest());
        if (!is_null($stockIds) && count($stockIds)) {
            $_stockIds = $this->getStockIds();
            $_stockItems = $this->getStockItems();
            $__stockIds = array();
            $__stockItems = array();
            foreach ($_stockIds as $_stockId) {
                if (in_array($_stockId, $stockIds)) {
                    $__stockIds[$_stockId] = $_stockId;
                    $__stockItems[$_stockId] = $_stockItems[$_stockId];
                }
            }

            $_stockIds = $__stockIds;
            $_stockItems = $__stockItems;
        } else {
            $_stockIds = $this->getInStockStockIds();
            $_stockItems = $this->getInStockStockItems();
            if ($forceNoBackorders) {
                $__stockIds = array();
                $__stockItems = array();
                foreach ($_stockIds as $_stockId) {
                    if (isset($_stockItems[$_stockId]) && (!$_stockItems[$_stockId]->getItemBackorders())) {
                        $__stockIds[$_stockId] = $_stockId;
                        $__stockItems[$_stockId] = $_stockItems[$_stockId];
                    }
                }

                $_stockIds = $__stockIds;
                $_stockItems = $__stockItems;
            }
        }

        if ($this->isParentItem()) {
            $children = array();
            foreach ($this->getChildren() as $childItem) {
                $childItemStockData = $childItem->getStockData($stockIds, $forceNoBackorders);
                $childStockIds = $childItemStockData['stock_ids'];
                foreach ($_stockIds as $_stockId) {
                    if (!isset($childStockIds[$_stockId]) || !$childStockIds[$_stockId]) {
                        if (isset($_stockIds[$_stockId])) {
                            unset($_stockIds[$_stockId]);
                        }

                        if (isset($_stockItems[$_stockId])) {
                            unset($_stockItems[$_stockId]);
                        }
                    }
                }

                $children[$childItem->getProductId()] = $childItemStockData;
            }
        } else {
            $children = [];
        }

        $stockData->setStockItems($_stockItems);
        $stockData->setStockIds($_stockIds);
        
        if ($config->isAllowAdjustment()) {
            $sessionStockId     = $productHelper->getSessionStockId($product);
            if ($sessionStockId && in_array($sessionStockId, $_stockIds)) {
                $stockData->setSessionStockId($sessionStockId);
            }
        }
        
        $stockData->setIsInStock((count($_stockIds) ? true : false));
        $stockData->setQty($this->getQty());
        $stockData->setChildren($children);
        
        $stockData->setParent((count($children) ? true : false));
        return $stockData;
    }
    /**
     * Check if item is splitted
     * 
     * @return boolean
     */
    public function isSplitted()
    {
        return ($this->getIsClone() || $this->getIsCloned()) ? true : false;
    }
}
