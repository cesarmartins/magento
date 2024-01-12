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
 * Stock item api V2
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Cataloginventory_Stock_Item_Api_V2 
    extends MP_Warehouse_Model_Cataloginventory_Stock_Item_Api
{
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
        $helper                 = $this->getWarehouseHelper();
        $product                = Mage::getModel('catalog/product');
        $newId                  = $product->getIdBySku($productId);
        if ($newId) {
            $productId              = $newId;
        }

        $storeId                = $this->_getStoreId();
        $product->setStoreId($storeId)
            ->load($productId);
        if (!$product->getId()) {
            $this->_fault('not_exists');
        }


        if($product->getMarca()){
            $doca = Mage::getModel('warehouse/warehouse')->getCollection()
                            ->addFieldToSelect('warehouse_id')
                            ->addFieldToFilter('marca',$product->getMarca())
                            ->getFIrstItem();
            $stockId = $doca->getId();
        }


        $stockItems             = $product->getStockItems();
        $stockItem              = (isset($stockItems[$stockId])) ? $stockItems[$stockId] : null;
        if (!$stockItem) {
            $stockItem              = array();
        }

        $stockItem['stock_id']  = $stockId;
        if (isset($data->qty)) {
            $stockItem['qty']       = $data->qty;
        }

        if (isset($data->is_in_stock)) {
            $stockItem['is_in_stock'] = $data->is_in_stock;
        }

        if (isset($data->manage_stock)) {
            $stockItem['manage_stock'] = $data->manage_stock;
        }

        if (isset($data->use_config_manage_stock)) {
            $stockItem['use_config_manage_stock'] = $data->use_config_manage_stock;
        }
        
        if ($helper->getVersionHelper()->isGe1700()) {
            if (isset($data->use_config_backorders)) {
                $stockItem['use_config_backorders'] = $data->use_config_backorders;
            }

            if (isset($data->backorders)) {
                $stockItem['backorders'] = $data->backorders;
            }
        }

        $stocksData             = array();
        $stocksData[$stockId]   = $stockItem;
        $product->setStocksData($stocksData);
        try {
            $product->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_updated', $e->getMessage());
        }

        return true;
    }
}
