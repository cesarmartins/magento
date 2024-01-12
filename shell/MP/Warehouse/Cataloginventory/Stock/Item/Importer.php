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

require_once rtrim(dirname(__FILE__), '/').'/../../../../Core/Importer.php';

/**
 * Stock item importer script
 * 
 * @category   MP
 * @package    MP_Shell
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Shell_Warehouse_Cataloginventory_Stock_Item_Importer
    extends MP_Shell_Core_Importer
{
    /**
     * Product
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;
    /**
     * Retrieve warehouse helper
     *
     * @return MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Get product
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function getProduct()
    {
        if (is_null($this->_product)) {
            $this->_product = Mage::getModel('catalog/product');
        }

        return $this->_product;
    }
    /**
     * Get resource
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product
     */
    protected function getResource()
    {
        return $this->getProduct()->getResource();
    }
    /**
     * Get adapter
     * 
     * @return Varien_Db_Adapter_Interface
     */
    protected function getWriteAdapter()
    {
        return $this->getResource()->getWriteConnection();
    }
    /**
     * Get select
     * 
     * @return Varien_Db_Select
     */
    protected function getSelect()
    {
        return $this->getWriteAdapter()->select();
    }
    /**
     * Get stock item table name
     * 
     * @return string
     */
    protected function getStockItemTableName()
    {
        return 'cataloginventory/stock_item';
    }
    /**
     * Get stock item table
     * 
     * @return string
     */
    protected function getStockItemTable()
    {
        return $this->getResource()->getTable($this->getStockItemTableName());
    }
    /**
     * Get stock item conditions
     * 
     * @param array $stockItem
     * @return string
     */
    protected function getStockItemConditions($stockItem)
    {
        $adapter = $this->getWriteAdapter();
        return implode(
            ' AND ', array(
            "(product_id    = {$adapter->quote($stockItem['product_id'])})", 
            "(stock_id      = {$adapter->quote($stockItem['stock_id'])})", 
            )
        );
    }
    /**
     * Check if stock item exists
     * 
     * @param array $stockItem
     * @return bool
     */
    protected function isStockItemExists($stockItem)
    {
        $isExists = false;
        $adapter = $this->getWriteAdapter();
        $select = $adapter->select()
            ->from($this->getStockItemTable(), array('COUNT(*)'))
            ->where($this->getStockItemConditions($stockItem));
        $query = $adapter->query($select);
        $count = (int) $query->fetchColumn();
        if ($count) {
            $isExists = true;
        }

        return $isExists;
    }
    /**
     * Add stock item
     * 
     * @param array $stockItem
     * @return MP_Shell_Warehouse_Cataloginventory_Stock_Item_Importer
     */
    protected function addStockItem($stockItem)
    {
        $adapter = $this->getWriteAdapter();
        $adapter->insert($this->getStockItemTable(), $stockItem);
        return $this;
    }
    /**
     * Update stock item
     * 
     * @param array $stockItem
     * @return MP_Shell_Warehouse_Cataloginventory_Stock_Item_Importer
     */
    protected function updateStockItem($stockItem)
    {
        $adapter = $this->getWriteAdapter();
        $adapter->update($this->getStockItemTable(), $stockItem, $this->getStockItemConditions($stockItem));
        return $this;
    }
    /**
     * Append stock item
     * 
     * @param array $stockItem
     * @return MP_Shell_Warehouse_Cataloginventory_Stock_Item_Importer
     */
    protected function appendStockItem($stockItem)
    {
        if ($this->isStockItemExists($stockItem)) {
            $this->updateStockItem($stockItem);
        } else {
            $this->addStockItem($stockItem);
        }

        return $this;
    }
    /**
     * Reindex
     * 
     * @return MP_Shell_Warehouse_Cataloginventory_Stock_Item_Importer
     */
    protected function reindex()
    {
        $this->printMessage('Reindexing.');
        $stockProcess = Mage::getSingleton('index/indexer')->getProcessByCode('cataloginventory_stock');
        if ($stockProcess) {
            $stockProcess->reindexAll();
        }

        return $this;
    }
    /**
     * Get stock item attributes
     * 
     * @return array
     */
    protected function getStockItemAttributes()
    {
        return array(
            'qty', 
            'use_config_min_qty', 
            'min_qty', 
            'is_qty_decimal', 
            'use_config_backorders', 
            'backorders', 
            'use_config_min_sale_qty', 
            'min_sale_qty', 
            'use_config_max_sale_qty', 
            'max_sale_qty', 
            'is_in_stock', 
            'use_config_notify_stock_qty', 
            'notify_stock_qty', 
            'use_config_manage_stock', 
            'manage_stock', 
            'use_config_qty_increments', 
            'qty_increments', 
            'use_config_enable_qty_inc', 
            'enable_qty_increments', 
            'qty', 
            'is_in_stock', 
            'use_config_manage_stock', 
            'manage_stock', 
            'use_config_backorders', 
            'backorders', 
        );
    }
    /**
     * Import row
     * 
     * @param array $row
     * @return bool
     */
    protected function importRow($row)
    {
        $helper = $this->getWarehouseHelper();
        $isImported = false;
        $sku = null;
        if (isset($row['sku']) && $row['sku']) {
            $sku = $row['sku'];
        }

        if ($sku) {
            $product = $this->getProduct();
            $productId = $product->getIdBySku($sku);
            if ($productId) {
                $stockIds = $helper->getStockIds();
                if (count($stockIds)) {
                    $stockItemAttributes = $this->getStockItemAttributes();
                    foreach ($stockIds as $stockId) {
                        $code = $helper->getWarehouseCodeByStockId($stockId);
                        if ($code) {
                            $stockItem = array();
                            foreach ($stockItemAttributes as $attribute) {
                                $key = $attribute.'_'.$code;
                                if (isset($row[$key])) {
                                    $stockItem[$attribute] = $row[$key];
                                }
                            }

                            if (count($stockItem)) {
                                $stockItem = array_merge(
                                    array(
                                        'product_id'    => $productId, 
                                        'stock_id'      => $stockId, 
                                    ), $stockItem
                                );
                                $this->appendStockItem($stockItem);
                            }
                        }
                    }
                }
            } else {
                $this->printMessage("Can't find product by sku: {$sku}");
            }
        }

        return $isImported;
    }    
}

$shell = new MP_Shell_Warehouse_Cataloginventory_Stock_Item_Importer();
$shell->run();

/**
php shell/MP/Warehouse/Cataloginventory/Stock/Item/Importer.php \
    --file-path /var/import/ \
    --file-filename localfilename.csv

php shell/MP/Warehouse/Cataloginventory/Stock/Item/Importer.php \
    --ftp \
    --ftp-host ftp.yourhost.com \
    --ftp-user username \
    --ftp-password password \
    --ftp-filename remotefilename.csv \
    --file-path /var/import/ \
    --file-filename localfilename.csv
 */
/*
php shell/MP/Warehouse/Cataloginventory/Stock/Item/Importer.php \
    --file-path /var/import/MP/Warehouse/ \
    --file-filename product-stocks.csv
*/
