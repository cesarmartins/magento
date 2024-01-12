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
 * Stock item api
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Cataloginventory_Stock_Item_Api 
    extends Mage_CatalogInventory_Model_Stock_Item_Api
{
    /**
     * Get helper
     * 
     * @return  MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Get stock items
     * 
     * @param array $productIds
     * @param int $stockId
     * 
     * @return array
     */
    protected function _items($productIds, $stockId)
    {
        $helper                 = $this->getWarehouseHelper();
        if (!is_array($productIds)) {
            $productIds             = array($productIds);
        }

        $product                = Mage::getModel('catalog/product');
        foreach ($productIds as &$productId) {
            if ($newId = $product->getIdBySku($productId)) {
                $productId              = $newId;
            }
        }

        $collection             = Mage::getModel('catalog/product')
            ->getCollection()
            ->setFlag('ignore_stock_items', true)
            ->addFieldToFilter('entity_id', array('in' => $productIds))
            ->load();
        $helper->getCatalogInventoryHelper()
            ->getStock($stockId)
            ->addItemsToProducts($collection);
        $items                  = array();
        foreach ($collection as $product) {
            $stockItem              = $product->getStockItem();
            $item                   = array(
                'product_id'            => $product->getId(), 
                'sku'                   => $product->getSku(), 
            );
            if ($stockItem) {
                $item['qty']            = $stockItem->getQty();
                $item['is_in_stock']    = $stockItem->getIsInStock();
                $item['stock_id']       = $stockItem->getStockId();
            } else {
                $item['qty']            = 0;
                $item['is_in_stock']    = 0;
                $item['stock_id']       = $stockId;
            }

            $items[]                = $item;
        }

        return $items;
    }
    /**
     * Get stock items
     * 
     * @param array $productIds
     * 
     * @return array
     */
    public function items($productIds)
    {
        return $this->_items($productIds, $this->getWarehouseHelper()->getDefaultStockId());
    }
    /**
     * Get stock items by stock identifier
     * 
     * @param array $productIds
     * @param int $stockId
     * 
     * @return array
     */
    public function itemsByStock($productIds, $stockId)
    {
        return $this->_items($productIds, $stockId);
    }
    /**
     * Update stock
     * 
     * @param int $productId
     * @param int $stockId
     * @param mixed $data
     * 
     * @return bool
     */
    protected function _update($productId, $data, $stockId)
    {
        $helper = $this->getWarehouseHelper();

        $product = Mage::getModel('catalog/product');
        $newId = $product->getIdBySku($productId);
        if ($newId) {
            $productId = $newId;
        }

        $storeId = $this->_getStoreId();
        $product->setStoreId($storeId)
            ->load($productId);
        if (!$product->getId()) {
        }

        $stockItems = $product->getStockItems();
        $stockItem  = (isset($stockItems[$stockId])) ? $stockItems[$stockId] : null;

        if (!$stockItem) {
            $stockItem = array();
        }

        foreach ($helper->getCatalogInventoryHelper()->getAttributeCodes() as $attributeCode) {
            if (array_key_exists($attributeCode, $data)) {
                $stockItem[$attributeCode] = $data[$attributeCode];
            }
        }

        $stockItem['stock_id']  = $stockId;
        $stocksData             = array();
        $stocksData[$stockId]   = $stockItem;

        $product->setStocksData($stocksData);
        try {
            $product->save();
        } catch (Mage_Core_Exception $e) {

            return $e->getMessage();
            //$this->_fault('not_updated', $e->getMessage());
        }

        return true;
    }
    /**
     * Update stock
     * 
     * @param int $productId
     * @param int $stockId
     * @param mixed $data
     * 
     * @return bool
     */
    public function update($productId, $data)
    {
        $return = $this->_update($productId, $data, $this->getWarehouseHelper()->getDefaultStockId());

        if($return === true) return true;

        return $this->_fault('not_updated',  $return);
    }
    /**
     * Update stock item by stock
     * 
     * @param int $productId
     * @param int $stockId
     * @param mixed $data
     * 
     * @return bool
     */
    public function updateByStock($productId, $data, $stockId)
    {
        $return = $this->_update($productId, $data, $stockId);

        if($return === true) return true;

        return $this->_fault('not_updated',  $return);

    }

    public function multiUpdate($productIds, $productData)
    {
        $total = count($productIds);
        if ($total != count($productData)) {
            $this->_fault('multi_update_not_match');
        }
        $productData = (array)$productData;
        $returnMsg = [];
        $hasError = false; 
        foreach ($productIds as $index => $productId) {
            $reMsg = $this->update($productId, $productData[$index]);
            $returnError[] =  "Product {$productId}:  {$reMsg}";
            $hasError = (!($reMsg === true) || $hasError); 
        }
        if(!$hasError)return true;

        $return = implode(" | ", $returnError);
        return $this->_fault('not_updated',  $return);
    }

    public function cesarTeste(){
        echo 'chegou aqui';
    }
}