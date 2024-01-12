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
 * Catalog inventory helper
 * 
 * @category    MP
 * @package     MP_Warehouse
 * @author      Mage Plugins Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Cataloginventory 
    extends Mage_Core_Helper_Abstract
{
    /**
     * Stocks
     * 
     * @var Mage_Cataloginventory_Model_Stock[]
     */
    protected $_stocks;
    /**
     * Stock item cache
     * 
     * @var Mage_Cataloginventory_Model_Stock_Item[][]
     */
    protected $_stockItemCache;
    /**
     * Stock items cache
     * 
     * @var Mage_Cataloginventory_Model_Stock_Item[]
     */
    protected $_stockItemsCache;
    
    /**
     * Get default stock id
     * 
     * @return integer
     */
    public function getDefaultStockId()
    {
        return Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
    }
    /**
     * Get admin stock id
     * 
     * @return integer
     */
    public function getAdminStockId()
    {
        return MP_Warehouse_Model_Cataloginventory_Stock::ADMIN_STOCK_ID;
    }
    /**
     * Get stock
     *
     * @param integer $stockId
     * 
     * @return Mage_Cataloginventory_Model_Stock
     */
    public function getStock($stockId = null)
    {
        $stock                  = Mage::getModel('cataloginventory/stock');
        if ($stockId) {
            $stock->setStockId($stockId);
        }

        return $stock;
    }
    /**
     * Get stock singleton
     *
     * @param integer $stockId
     * 
     * @return Mage_Cataloginventory_Model_Stock
     */
    public function getStockSingleton($stockId = null)
    {
        $stock                  = Mage::getSingleton('cataloginventory/stock');
        if ($stockId) {
            $stock->setStockId($stockId);
        }

        return $stock;
    }
    /**
     * Get stock resource
     * 
     * @param integer $stockId
     * 
     * @return Mage_Cataloginventory_Model_Mysql4_Stock
     */
    public function getStockResource($stockId = null)
    {
        $stock                  = Mage::getResourceSingleton('cataloginventory/stock');
        if ($stockId) {
            $stock->setStockId($stockId);
        }

        return $stock;
    }
    /**
     * Get stock collection
     * 
     * @return Mage_CatalogInventory_Model_Mysql4_Stock_Collection
     */
    public function getStockCollection()
    {
        return $this
            ->getStockSingleton()
            ->getCollection()
            ->addIdFilter(0, true);
    }
    /**
     * Get stocks
     * 
     * @return Mage_Cataloginventory_Model_Stock[]
     */
    public function getStocks()
    {
        if (is_null($this->_stocks)) {
            $stocks                 = array();
            foreach ($this->getStockCollection() as $stock) {
                $stocks[$stock->getId()] = $stock;
            }

            $this->_stocks          = $stocks;
        }

        return $this->_stocks;
    }
    /**
     * Get stock ids
     * 
     * @return array
     */
    public function getStockIds()
    {
        return array_keys($this->getStocks());
    }
    /**
     * Check if stock id exists
     * 
     * @param integer $stockId
     * 
     * @return boolean
     */
    public function isStockIdExists($stockId)
    {
        $stockIds               = $this->getStockIds();
        return in_array($stockId, $stockIds);
    }
    /**
     * Get stock item
     * 
     * @param integer $stockId
     * 
     * @return Mage_Cataloginventory_Model_Stock_Item
     */
    public function getStockItem($stockId = null)
    {
        $stockItem              = Mage::getModel('cataloginventory/stock_item');
        if ($stockId) {
            $stockItem->setStockId($stockId);
        }

        return $stockItem;
    }
    /**
     * Get stock item singleton
     *
     * @param integer $stockId
     * 
     * @return Mage_Cataloginventory_Model_Stock_Item
     */
    public function getStockItemSingleton($stockId = null)
    {
        $stockItem              = Mage::getSingleton('cataloginventory/stock_item');
        if ($stockId) {
            $stockItem->setStockId($stockId);
        }

        return $stockItem;
    }
    /**
     * Get stock item collection
     * 
     * @param integer|null $productId
     * @param boolean $inStockOnly
     * 
     * @return Mage_CatalogInventory_Model_Mysql4_Stock_Item_Collection
     */
    public function getStockItemCollection($productId = null, $inStockOnly = true)
    {
        $collection             = $this
            ->getStockItemSingleton()
            ->getCollection();
        if (!is_null($productId)) {
            $collection->addProductsFilter(array($productId));
        }

        if ($inStockOnly) {
            $collection->addInStockFilter($this->getManageStock());
        }

        return $collection;
    }
    /**
     * Get stock item cached
     * 
     * @param integer $productId
     * @param integer $stockId
     * 
     * @return Mage_Cataloginventory_Model_Stock_Item
     */
    public function getStockItemCached($productId, $stockId = null)
    {
        $stockId                = ($stockId) ? $stockId : 0;
        if (!isset($this->_stockItemCache[$productId]) || 
            !isset($this->_stockItemCache[$productId][$stockId])
        ) {
            $this->_stockItemCache[$productId][$stockId] = $this->getStockItem($stockId);
        }

        return $this->_stockItemCache[$productId][$stockId];
    }
    /**
     * Unset stock item cached 
     * 
     * @param integer $productId
     * 
     * @return $this
     */
    public function unsetStockItemCached($productId)
    {
        if (isset($this->_stockItemCache[$productId])) {
            unset($this->_stockItemCache[$productId]);
        }

        return $this;
    }
    /**
     * Get stock items cached
     * 
     * @param integer $productId 
     * 
     * @return Mage_Cataloginventory_Model_Stock_Item[]
     */
    public function getStockItemsCached($productId)
    {
        if (!isset($this->_stockItemsCache[$productId])) {
            $this->_stockItemsCache[$productId] = array();
            foreach ($this->getStockItemCollection($productId, true) as $stockItem) {
                $stockId = (int) $stockItem->getStockId();
                $this->_stockItemsCache[$productId][$stockId] = $stockItem;
            }
        }

        return $this->_stockItemsCache[$productId];
    }
    /**
     * Unset stock items cached 
     * 
     * @param integer $productId
     * 
     * @return $this
     */
    public function unsetStockItemsCached($productId)
    {
        if (isset($this->_stockItemsCache[$productId])) {
            unset($this->_stockItemsCache[$productId]);
        }

        return $this;
    }
    /**
     * Get stock status
     *
     * @param integer $stockId
     * 
     * @return Mage_Cataloginventory_Model_Stock_Status
     */
    public function getStockStatus($stockId = null)
    {
        $stockStatus            = Mage::getModel('cataloginventory/stock_status');
        if ($stockId) {
            $stockStatus->setStockId($stockId);
        }

        return $stockStatus;
    }
    /**
     * Get stock status singleton
     *
     * @param integer $stockId
     * 
     * @return Mage_Cataloginventory_Model_Stock_Status
     */
    public function getStockStatusSingleton($stockId = null)
    {
        $stockStatus            = Mage::getSingleton('cataloginventory/stock_status');
        if ($stockId) {
            $stockStatus->setStockId($stockId);
        }

        return $stockStatus;
    }
    /**
     * Get manage stock config option value
     * 
     * @return integer
     */
    public function getManageStock()
    {
        return (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
    }
    /**
     * Get notify stock qty config option value
     * 
     * @return integer
     */
    public function getNotifyStockQty()
    {
        return (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_NOTIFY_STOCK_QTY);
    }
    /**
     * Get stock item options (used in config)
     *
     * @return array
     */
    public function getConfigItemOptions()
    {
        return Mage::helper('cataloginventory')
            ->getConfigItemOptions();
    }
    /**
     * Get attribute codes
     * 
     * @return array
     */
    public function getAttributeCodes()
    {
        return array(
            'qty', 
            'min_qty', 
            'use_config_min_qty', 
            'is_qty_decimal', 
            'backorders', 
            'use_config_backorders', 
            'min_sale_qty', 
            'use_config_min_sale_qty', 
            'max_sale_qty', 
            'use_config_max_sale_qty', 
            'is_in_stock', 
            'notify_stock_qty', 
            'use_config_notify_stock_qty', 
            'manage_stock', 
            'use_config_manage_stock', 
            'qty_increments', 
            'use_config_qty_increments', 
            'enable_qty_increments', 
            'use_config_enable_qty_inc', 
            'is_decimal_divided', 
        );
    }
}
