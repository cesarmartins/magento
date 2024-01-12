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
 * Product price helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Catalog_Product_Price 
    extends Mage_Core_Helper_Abstract
{
    /**
     * Scope
     */
    const SCOPE_GLOBAL      = 0;
    const SCOPE_WEBSITE     = 1;
    /**
     * Tier Price attribute
     * 
     * @var Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected $_tierPriceAttribute = null;
    /**
     * Group Price attribute
     *
     * @var Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected $_groupPriceAttribute = null;
    /**
     * Get warehouse helper
     * 
     * @return MP_Warehouse_Helper_Data
     */
    public function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Get product helper
     * 
     * @return MP_Warehouse_Helper_Catalog_Product
     */
    public function getProductHelper()
    {
        return Mage::helper('warehouse/catalog_product');
    }
    /**
     * Get indexer helper
     * 
     * @return MP_Warehouse_Helper_Catalog_Product
     */
    public function getIndexerHelper()
    {
        return Mage::helper('warehouse/catalog_product_price_indexer');
    }
    /**
     * Get version helper
     * 
     * @return MP_Warehouse_Helper_Core_Version
     */
    public function getVersionHelper()
    {
        return $this->getWarehouseHelper()->getVersionHelper();
    }
    /**
     * Check if group price is fixed
     * 
     * @param string $productTypeId
     * 
     * @return bool
     */
    public function isGroupPriceFixed($productTypeId)
    {
        $price = Mage::getSingleton('catalog/product_type')->priceFactory($productTypeId);
        if ($this->getVersionHelper()->isGe1700()) {
            return $price->isGroupPriceFixed();
        } else {
            return $price->isTierPriceFixed();
        }
    }
    /**
     * Get scope
     * 
     * @return int
     */
    public function getScope()
    {
        return Mage::helper('catalog')->getPriceScope();
    }
    /**
     * Check if global scope is active
     * 
     * @return bool 
     */
    public function isGlobalScope()
    {
        return ($this->getScope() == self::SCOPE_GLOBAL)  ? true : false;
    }
    /**
     * Check if website scope is active
     * 
     * @return bool
     */
    public function isWebsiteScope()
    {
        return ($this->getScope() == self::SCOPE_WEBSITE)  ? true : false;
    }
    /**
     * Check if data is inactive
     * 
     * @param array $data
     * @param mixed $websiteId
     * 
     * @return bool
     */
    public function isInactiveData($data, $websiteId)
    {
        if ($this->isGlobalScope() && ($data['website_id'] > 0)) {
            return true;
        }

        return false;
    }
    /**
     * Check if data is ancestor
     * 
     * @param array $data
     * @param mixed $websiteId
     * 
     * @return bool
     */
    public function isAncestorData($data, $websiteId)
    {
        if (!$this->isGlobalScope() && ($websiteId != 0)) {
            if ($this->isWebsiteScope() && ((int) $data['website_id'] == 0)) {
                return true;
            }
        }

        return false;
    }
    /**
     * Get group price attribute
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getGroupPriceAttribute()
    {
        if (is_null($this->_groupPriceAttribute)) {
            $attribute = $this->getProductHelper()->getGroupPriceAttribute();
            if ($attribute) {
                $this->_groupPriceAttribute = $attribute;
            }
        }

        return $this->_groupPriceAttribute;
    }
    /**
     * Get tier price attribute
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getTierPriceAttribute()
    {
        if (is_null($this->_tierPriceAttribute)) {
            $attribute = $this->getProductHelper()->getTierPriceAttribute();
            if ($attribute) {
                $this->_tierPriceAttribute = $attribute;
            }
        }

        return $this->_tierPriceAttribute;
    }
    /**
     * Prepare batch prices for save
     * 
     * @param array $data
     * 
     * @return array
     */
    protected function _prepareBatchPricesForSave($data)
    {
        $_data = array();
        if (!is_array($data)) {
            return $_data;
        }

        foreach ($data as $key => $datum) {
            if (is_array($datum) && isset($datum['stock_id'])) {
                $_data[$key] = $datum;
            }
        }

        return $_data;
    }
    /**
     * Save batch prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $tableName
     * @param string $attributeCode
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    protected function _saveBatchPrices($product, $tableName, $attributeCode)
    {
        if (!$product || !($product instanceof Mage_Catalog_Model_Product)) {
            return $this;
        }

        $productHelper  = $this->getProductHelper();
        $productId      = $product->getId();
        $resource       = $product->getResource();
        $websiteId      = $productHelper->getWebsiteId($product);
        $table          = $resource->getTable($tableName);
        $adapter        = $resource->getWriteConnection();
        $_data          = $this->_prepareBatchPricesForSave($product->getData($attributeCode));
        if (count($_data)) {
            $data       = array();
            $oldData    = array();
            foreach ($_data as $item) {
                if (isset($item['stock_id']) && isset($item['price'])) {
                    $stockId    = (int) $item['stock_id'];
                    $price      = (
                        $item['price'] && ($item['price'] > 0)
                    ) ? round((float) $item['price'], 2) : 0;
                    $data[$stockId] = array(
                        'product_id'    => $productId, 
                        'stock_id'      => $stockId, 
                        'website_id'    => $websiteId, 
                        'price'         => $price, 
                    );
                }
            }

            $select = $adapter->select()->from($table)
                ->where(
                    implode(
                        ' AND ', array(
                        "(product_id = {$adapter->quote($productId)})", 
                        "(website_id = {$adapter->quote($websiteId)})"
                        )
                    )
                );
            $query = $adapter->query($select);
            while ($item = $query->fetch()) {
                $stockId           = (int) $item['stock_id'];
                $oldData[$stockId] = $item;
            }

            foreach ($oldData as $item) {
                $stockId = (int) $item['stock_id'];
                if (!isset($data[$stockId])) {
                    $adapter->delete(
                        $table, array(
                        $adapter->quoteInto('product_id = ?', $productId), 
                        $adapter->quoteInto('stock_id = ?', $stockId), 
                        $adapter->quoteInto('website_id = ?', $websiteId)
                        )
                    );
                }
            }

            foreach ($data as $item) {
                $stockId = (int) $item['stock_id'];
                if (!isset($oldData[$stockId])) {
                    $adapter->insert($table, $item);
                } else {
                    $adapter->update(
                        $table, $item, array(
                        $adapter->quoteInto('product_id = ?', $productId), 
                        $adapter->quoteInto('stock_id = ?', $stockId), 
                        $adapter->quoteInto('website_id = ?', $websiteId), 
                        )
                    );
                }
            }

            $product->setForceReindexRequired(1);
        }

        return $this;
    }
    /**
     * Save batch prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function saveBatchPrices($product)
    {
        return $this->_saveBatchPrices(
            $product, 
            'catalog/product_batch_price', 
            'batch_prices'
        );
    }
    /**
     * Save batch special prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function saveBatchSpecialPrices($product)
    {
        return $this->_saveBatchPrices(
            $product, 
            'catalog/product_batch_special_price', 
            'batch_special_prices'
        );
    }
    /**
     * Load batch prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $tableName
     * @param string $attributeCode
     * @param string $priceSetter
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    protected function _loadBatchPrices($product, $tableName, $attributeCode, $priceSetter)
    {
        if (!$product || 
            !($product instanceof Mage_Catalog_Model_Product) || 
            ($product->hasData($attributeCode))
        ) {
            return $this;
        }

        $productId  = $product->getId();
        $resource   = $product->getResource();
        $table      = $resource->getTable($tableName);
        $adapter    = $resource->getWriteConnection();
        $select = $adapter->select()->from($table)->where('product_id = ?', $productId);
        $query      = $adapter->query($select);
        $data       = array();
        while ($item = $query->fetch()) {
            $stockId    = (int) $item['stock_id'];
            $price      = $item['price'];
            $websiteId  = (int) $item['website_id'];
            $data[$websiteId][$stockId] = $price;
        }

        $product->setData($attributeCode, $data);
        $this->$priceSetter($product);
        return $this;
    }
    /**
     * Load batch prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $tableName
     * @param string $attributeCode
     * @param string $priceSetter
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function loadBatchPrices($product)
    {
        return $this->_loadBatchPrices(
            $product, 
            'catalog/product_batch_price', 
            'batch_prices', 
            'setPrice'
        );
    }
    /**
     * Load batch special prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $tableName
     * @param string $attributeCode
     * @param string $priceSetter
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function loadBatchSpecialPrices($product)
    {
        return $this->_loadBatchPrices(
            $product, 
            'catalog/product_batch_special_price', 
            'batch_special_prices', 
            'setSpecialPrice'
        );
    }
    /**
     * Load collection batch prices
     * 
     * @param Varien_Data_Collection_Db $collection
     * @param string $tableName
     * @param string $attributeCode
     * @param string $priceSetter
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    protected function _loadCollectionBatchPrices($collection, $tableName, $attributeCode, $priceSetter)
    {
        if (!$collection) {
            return $this;
        }

        $productIds = array();
        foreach ($collection as $product) {
            if (!$product->hasData($attributeCode)) {
                array_push($productIds, $product->getId());
            }
        }

        if (count($productIds)) {
            $table          = $collection->getTable($tableName);
            $adapter        = $collection->getConnection();
            $select         = $adapter->select()->from($table)
                ->where($adapter->quoteInto('product_id IN (?)', $productIds));
            $query          = $adapter->query($select);
            $productData    = array();
            while ($item = $query->fetch()) {
                $stockId        = (int) $item['stock_id'];
                $price          = $item['price'];
                $websiteId      = (int) $item['website_id'];
                $productId      = $item['product_id'];
                $productData[$productId][$websiteId][$stockId] = $price;
            }

            foreach ($collection as $product) {
                $productId  = $product->getId();
                $data       = (isset($productData[$productId])) ? $productData[$productId] : array();
                $product->setData($attributeCode, $data);
                $this->$priceSetter($product);
            }
        }

        return $this;
    }
    /**
     * Load collection batch prices
     * 
     * @param Varien_Data_Collection_Db $collection
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function loadCollectionBatchPrices($collection)
    {
        return $this->_loadCollectionBatchPrices(
            $collection, 
            'catalog/product_batch_price', 
            'batch_prices', 
            'setPrice'
        );
    }
    /**
     * Load collection batch special prices
     * 
     * @param Varien_Data_Collection_Db $collection
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function loadCollectionBatchSpecialPrices($collection)
    {
        return $this->_loadCollectionBatchPrices(
            $collection, 
            'catalog/product_batch_special_price', 
            'batch_special_prices', 
            'setSpecialPrice'
        );
    }
    /**
     * Remove batch prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $attributeCode
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    protected function _removeBatchPrices($product, $attributeCode)
    {
        if (!$product || !($product instanceof Mage_Catalog_Model_Product)) {
            return $this;
        }

        $product->unsetData($attributeCode);
        $product->unsetData('website_'.$attributeCode);
        return $this;
    }
    /**
     * Remove batch prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function removeBatchPrices($product)
    {
        return $this->_removeBatchPrices($product, 'batch_prices');
    }
    /**
     * Remove batch special prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function removeBatchSpecialPrices($product)
    {
        return $this->_removeBatchPrices($product, 'batch_special_prices');
    }
    /**
     * Load group price
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function loadGroupPrice($product)
    {
        if ($product->hasData('group_price')) {
            return $this;
        }

        $attribute = $this->getGroupPriceAttribute();
        if (!$attribute) {
            return $this;
        }

        $backend = $attribute->getBackend();
        if (!$backend) {
            return $this;
        }

        $backend->afterLoad($product);
        return $this;
    }
    /**
     * Load tier price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function loadTierPrice($product)
    {
        if ($product->hasData('tier_price')) {
            return $this;
        }

        $attribute = $this->getTierPriceAttribute();
        if (!$attribute) {
            return $this;
        }

        $backend = $attribute->getBackend();
        if (!$backend) {
            return $this;
        }

        $backend->afterLoad($product);
        return $this;
    }
    /**
     * Load collection group prices
     *
     * @param Varien_Data_Collection_Db $collection
     *
     * @return self
     */
    public function loadCollectionGroupPrices($collection)
    {
        if (!$collection ||
            !(in_array(
                get_class($collection), array(
                'Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection',
                'Mage_Catalog_Model_Resource_Product_Collection',
                )
            ))
        ) {
            return $this;
        }

        $collection->addGroupPriceData();
        return $this;
    }
    /**
     * Load collection tier prices
     * 
     * @param Varien_Data_Collection_Db $collection
     * 
     * @return self
     */
    public function loadCollectionTierPrices($collection)
    {
        if (!$collection || 
            !(in_array(
                get_class($collection), array(
                'Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection', 
                'Mage_Catalog_Model_Resource_Product_Collection', 
                )
            ))
        ) {
            return $this;
        }

        $collection->addTierPriceData();
        return $this;
    }
    /**
     * Set group price
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function setGroupPrice($product)
    {
        $attribute = $this->getGroupPriceAttribute();
        if (!$attribute) {
            return $this;
        }

        $backend = $attribute->getBackend();
        if (!$backend) {
            return $this;
        }

        $helper         = $this->getWarehouseHelper();
        $productHelper  = $this->getProductHelper();
        $isEditMode     = $product->getData('_edit_mode');
        $storeId        = $helper->getCurrentStore()->getId();
        $websiteId      = null;
        if ($this->isGlobalScope()) {
            $websiteId      = 0;
        } else if ($storeId) {
            $websiteId      = $helper->getWebsiteIdByStoreId($storeId);
        }

        if ($isEditMode) {
            $stockId        = null;
        } else {
            $stockId        = $productHelper->getCurrentStockId($product);
        }

        $typeId         = $product->getTypeId();
        $groupPrices    = $product->getGroupPrices();

        if (!empty($groupPrices) && !$isEditMode) {
            $groupPrices     = $backend->preparePriceData2($groupPrices, $typeId, $websiteId, $stockId);
        }

        $product->setFinalPrice(null);
        $product->setData('group_price', $groupPrices);
        return $this;
    }
    /**
     * Set tier price
     * 
     * @param type $product
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function setTierPrice($product)
    {
        $attribute = $this->getTierPriceAttribute();
        if (!$attribute) {
            return $this;
        }

        $backend = $attribute->getBackend();
        if (!$backend) {
            return $this;
        }

        $helper         = $this->getWarehouseHelper();
        $productHelper  = $this->getProductHelper();
        $isEditMode     = $product->getData('_edit_mode');
        $storeId        = $helper->getCurrentStore()->getId();
        $websiteId      = null;
        if ($this->isGlobalScope()) {
            $websiteId      = 0;
        } else if ($storeId) {
            $websiteId      = $helper->getWebsiteIdByStoreId($storeId);
        }

        if ($isEditMode) {
            $stockId        = null;
        } else {
            $stockId        = $productHelper->getCurrentStockId($product);
        }

        $typeId         = $product->getTypeId();
        $tierPrices     = $product->getTierPrices();
        if (!empty($tierPrices) && !$isEditMode) {
            $tierPrices     = $backend->preparePriceData2($tierPrices, $typeId, $websiteId, $stockId);
        }

        $product->setFinalPrice(null);
        $product->setData('tier_price', $tierPrices);
        return $this;
    }
    /**
     * Get website batch prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param mixed $websiteId
     * 
     * @return array
     */
    public function getWebsiteBatchPrices($product, $websiteId = null)
    {
        $helper = $this->getWarehouseHelper();
        $websiteBatchPrices = array();
        if (is_null($websiteId)) {
            $websiteId = $this->getProductHelper()->getWebsiteId($product);
        }

        $stockIds = $helper->getStockIds();
        if (count($stockIds)) {
            $batchPrices = $product->getBatchPrices();
            if (count($batchPrices)) {
                foreach ($stockIds as $stockId) {
                    if ($helper->getProductPriceHelper()->isWebsiteScope()) {
                        if ($websiteId && 
                            isset($batchPrices[$websiteId]) && 
                            isset($batchPrices[$websiteId][$stockId])
                        ) {
                            $websiteBatchPrices[$stockId] = $batchPrices[$websiteId][$stockId];
                        }
                    }

                    if (!isset($websiteBatchPrices[$stockId]) && 
                        isset($batchPrices[0]) && 
                        isset($batchPrices[0][$stockId])
                    ) {
                        $websiteBatchPrices[$stockId] = $batchPrices[0][$stockId];
                    }
                }
            }
        }

        return $websiteBatchPrices;
    }
    /**
     * Set website batch prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param mixed $websiteId
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function setWebsiteBatchPrices($product, $websiteId = null)
    {
        if (!$product->hasWebsiteBatchPrices()) {
            $product->setWebsiteBatchPrices($this->getWebsiteBatchPrices($product, $websiteId));
        }

        return $this;
    }
    /**
     * Get website batch price by stock identifier
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param mixed $stockId
     * 
     * @return mixed 
     */
    public function getWebsiteBatchPriceByStockId($product, $stockId)
    {
        $batchPrice = null;
        $batchPrices = $product->getWebsiteBatchPrices();
        if (isset($batchPrices[$stockId])) {
            $batchPrice = (float) $batchPrices[$stockId];
        }

        return $batchPrice;
    }
    /**
     * Get max website batch price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return mixed 
     */
    protected function getMaxWebsiteBatchPrice($product)
    {
        $price = null;
        $batchPrices = $product->getWebsiteBatchPrices();
        if (!is_null($batchPrices) && count($batchPrices)) {
            foreach ($batchPrices as $batchPrice) {
                if (is_null($price)) {
                    $price = $batchPrice;
                } else if ($batchPrice > $price) {
                    $price = $batchPrice;
                }
            }
        }

        return $price;
    }
    /**
     * Set product price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function setPrice($product)
    {
        $this->setWebsiteBatchPrices($product);
        if (!$product->getData('_edit_mode')) {
            $helper = $this->getWarehouseHelper();
            if (!$product->getInitialPrice() && $product->getPrice()) {
                $product->setInitialPrice($product->getPrice());
            }

            $batchPrice = null;
            $stockId = $this->getProductHelper()->getCurrentStockId($product);
            if ($stockId) {
                $batchPrices = $product->getWebsiteBatchPrices();
                if (isset($batchPrices[$stockId])) {
                    $batchPrice = (float) $batchPrices[$stockId];
                }
            } else if ($helper->isMultipleMode()) {
                $batchPrice = $this->getMaxWebsiteBatchPrice($product);
            }

            if (is_null($batchPrice) && $product->getInitialPrice()) {
                $batchPrice = $product->getInitialPrice();
            }

            if (!is_null($batchPrice)) {
                $product->setFinalPrice(null);
                $product->setPrice($batchPrice);
            }
        }

        return $this;
    }
    /**
     * Get stock price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param int $stockId
     * 
     * @return float
     */
    public function getStockPrice($product, $stockId)
    {
        $price = $this->getWebsiteBatchPriceByStockId($product, $stockId);
        if (is_null($price)) {
            $price = $product->getInitialPrice();
        }

        if (is_null($price)) {
            $price = $product->getPrice();
        }

        return $price;
    }
    /**
     * Get default price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param mixed $websiteId
     * @param mixed $stockId
     * 
     * @return mixed 
     */
    public function getDefaultPrice($product, $websiteId, $stockId)
    {
        $price = $product->getPrice();
        $helper = $this->getWarehouseHelper();
        if ($helper->getProductPriceHelper()->isWebsiteScope() && $websiteId) {
            $batchPrices = $product->getBatchPrices();
            if (isset($batchPrices[0]) && isset($batchPrices[0][$stockId])) {
                $price = (float) $batchPrices[0][$stockId];
            }
        }

        return $price;
    }
    /**
     * Get website batch special prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param mixed $websiteId
     * 
     * @return array
     */
    public function getWebsiteBatchSpecialPrices($product, $websiteId = null)
    {
        $helper = $this->getWarehouseHelper();
        $websiteBatchSpecialPrices = array();
        if (is_null($websiteId)) {
            $websiteId = $this->getProductHelper()->getWebsiteId($product);
        }

        $stockIds = $helper->getStockIds();
        if (count($stockIds)) {
            $batchSpecialPrices = $product->getBatchSpecialPrices();
            if (count($batchSpecialPrices)) {
                foreach ($stockIds as $stockId) {
                    if ($helper->getProductPriceHelper()->isWebsiteScope()) {
                        if ($websiteId && 
                            isset($batchSpecialPrices[$websiteId]) && 
                            isset($batchSpecialPrices[$websiteId][$stockId])
                        ) {
                            $websiteBatchSpecialPrices[$stockId] = $batchSpecialPrices[$websiteId][$stockId];
                        }
                    }

                    if (!isset($websiteBatchSpecialPrices[$stockId]) && 
                        isset($batchSpecialPrices[0]) && 
                        isset($batchSpecialPrices[0][$stockId])
                    ) {
                        $websiteBatchSpecialPrices[$stockId] = $batchSpecialPrices[0][$stockId];
                    }
                }
            }
        }

        return $websiteBatchSpecialPrices;
    }
    /**
     * Set website batch special prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param mixed $websiteId
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function setWebsiteBatchSpecialPrices($product, $websiteId = null)
    {
        if (!$product->hasWebsiteBatchSpecialPrices()) {
            $product->setWebsiteBatchSpecialPrices($this->getWebsiteBatchSpecialPrices($product, $websiteId));
        }

        return $this;
    }
    /**
     * Get max website batch special price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return mixed 
     */
    public function getMaxWebsiteBatchSpecialPrice($product)
    {
        $price = null;
        $batchSpecialPrices = $product->getWebsiteBatchSpecialPrices();
        if (!is_null($batchSpecialPrices) && count($batchSpecialPrices)) {
            foreach ($batchSpecialPrices as $batchSpecialPrice) {
                if (is_null($price)) {
                    $price = $batchSpecialPrice;
                } else if ($batchSpecialPrice > $price) {
                    $price = $batchSpecialPrice;
                }
            }
        }

        return $price;
    }
    /**
     * Set special price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function setSpecialPrice($product)
    {
        $this->setWebsiteBatchSpecialPrices($product);
        if (!$product->getData('_edit_mode')) {
            if (is_null($product->getInitialSpecialPrice())) {
                $specialPrice = ($product->getSpecialPrice()) ? $product->getSpecialPrice() : false;
                $product->setInitialSpecialPrice($specialPrice);
            }

            $batchSpecialPrice = null;
            $stockId = $this->getProductHelper()->getCurrentStockId($product);
            if ($stockId) {
                $batchSpecialPrices = $product->getWebsiteBatchSpecialPrices();
                if (isset($batchSpecialPrices[$stockId])) {
                    $batchSpecialPrice = (float) $batchSpecialPrices[$stockId];
                }
            }

            if (is_null($batchSpecialPrice) && $product->getInitialSpecialPrice()) {
                $batchSpecialPrice = $product->getInitialSpecialPrice();
            }

            if (!is_null($batchSpecialPrice)) {
                $product->setFinalPrice(null);
                $product->setSpecialPrice($batchSpecialPrice);
            } else {
                $product->setFinalPrice(null);
                $product->setSpecialPrice(null);
            }
        }

        return $this;
    }
    /**
     * Get default special price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param mixed $websiteId
     * @param mixed $stockId
     * 
     * @return mixed 
     */
    public function getDefaultSpecialPrice($product, $websiteId, $stockId)
    {
        $price = $product->getSpecialPrice();
        $helper = $this->getWarehouseHelper();
        if ($helper->getProductPriceHelper()->isWebsiteScope() && $websiteId) {
            $batchPrices = $product->getBatchSpecialPrices();
            if (isset($batchPrices[0]) && isset($batchPrices[0][$stockId])) {
                $price = (float) $batchPrices[0][$stockId];
            }
        }

        return $price;
    }
    /**
     * Load price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return self
     */
    public function loadPrices($product)
    {
        $this->loadBatchPrices($product);
        $this->loadBatchSpecialPrices($product);
        $this->loadTierPrice($product);
        $this->loadGroupPrice($product);
        return $this;
    }
    /**
     * Load collection prices
     * 
     * @param Varien_Data_Collection_Db $collection
     * 
     * @return self
     */
    public function loadCollectionPrices($collection)
    {
        $this->loadCollectionBatchPrices($collection)
            ->loadCollectionBatchSpecialPrices($collection)
            ->loadCollectionTierPrices($collection)
            ->loadCollectionGroupPrices($collection);
        return $this;
    }
    /**
     * Set prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return self
     */
    public function setPrices($product)
    {
        $this->setPrice($product);
        $this->setSpecialPrice($product);
        $this->setTierPrice($product);
        $this->setGroupPrice($product);
        return $this;
    }
    /**
     * Apply Prices
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return self
     */
    public function applyPrices($product)
    {
        $this->getProductHelper()->setTaxClassId($product);
        $this->loadPrices($product);
        $this->setPrices($product);
        return $this;
    }
    /**
     * Get escaped price
     * 
     * @param float $price
     * 
     * @return float
     */
    public function getEscapedPrice($price)
    {
        if (!is_numeric($price)) {
            return null;
        }

        return number_format($price, 2, null, '');
    }
    /**
     * Round price
     * 
     * @param float $price
     * 
     * @return float
     */
    public function round($price)
    {
        return round($price, 4);
    }
}

