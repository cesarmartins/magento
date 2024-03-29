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

$installer = $this;

$connection = $installer->getConnection();

$installer->startSetup();

$helper                                         = Mage::helper('warehouse');
$defaultStockId                                 = $helper->getCatalogInventoryHelper()->getDefaultStockId();

$storeTable                                     = $installer->getTable('core/store');
$stockTable                                     = $installer->getTable('cataloginventory/stock');

$productTable                                   = $installer->getTable('catalog/product');
$productShelfTable                              = $installer->getTable('catalog/product_shelf');
$productStockPriceTable                         = $installer->getTable('catalog/product_stock_price');
$productStockPriorityTable                      = $installer->getTable('catalog/product_stock_priority');
$productStockShippingCarrierTable               = $installer->getTable('catalog/product_stock_shipping_carrier');

$quoteTable                                     = $installer->getTable('sales/quote');
$orderTable                                     = $installer->getTable('sales/order');
$invoiceTable                                   = $installer->getTable('sales/invoice');
$shipmentTable                                  = $installer->getTable('sales/shipment');
$creditmemoTable                                = $installer->getTable('sales/creditmemo');
$quoteAddressTable                              = $installer->getTable('sales/quote_address');
$orderAddressTable                              = $installer->getTable('sales/order_address');
$quoteItemTable                                 = $installer->getTable('sales/quote_item');
$orderItemTable                                 = $installer->getTable('sales/order_item');
$invoiceItemTable                               = $installer->getTable('sales/invoice_item');
$shipmentItemTable                              = $installer->getTable('sales/shipment_item');
$creditmemoItemTable                            = $installer->getTable('sales/creditmemo_item');

$productIndexPriceTable                         = $installer->getTable('catalog/product_index_price');
$productIndexPriceIdxTable                      = $installer->getTable('catalog/product_price_indexer_idx');
$productIndexPriceTmpTable                      = $installer->getTable('catalog/product_price_indexer_tmp');
$productIndexPriceFinalIdxTable                 = $installer->getTable('catalog/product_price_indexer_final_idx');
$productIndexPriceFinalTmpTable                 = $installer->getTable('catalog/product_price_indexer_final_tmp');
$productIndexPriceBundleIdxTable                = $installer->getTable('bundle/price_indexer_idx');
$productIndexPriceBundleTmpTable                = $installer->getTable('bundle/price_indexer_tmp');
$productIndexPriceBundleSelectionIdxTable       = $installer->getTable('bundle/selection_indexer_idx');
$productIndexPriceBundleSelectionTmpTable       = $installer->getTable('bundle/selection_indexer_tmp');
$productIndexPriceBundleOptionIdxTable          = $installer->getTable('bundle/option_indexer_idx');
$productIndexPriceBundleOptionTmpTable          = $installer->getTable('bundle/option_indexer_tmp');
$productIndexPriceOptionAggregateIdxTable       = $installer->getTable('catalog/product_price_indexer_option_aggregate_idx');
$productIndexPriceOptionAggregateTmpTable       = $installer->getTable('catalog/product_price_indexer_option_aggregate_tmp');
$productIndexPriceOptionIdxTable                = $installer->getTable('catalog/product_price_indexer_option_idx');
$productIndexPriceOptionTmpTable                = $installer->getTable('catalog/product_price_indexer_option_tmp');
$productIndexPriceDownloadableIdxTable          = $installer->getTable('downloadable/product_price_indexer_idx');
$productIndexPriceDownloadableTmpTable          = $installer->getTable('downloadable/product_price_indexer_tmp');
$productIndexPriceCfgOptionAggregateIdxTable    = $installer->getTable('catalog/product_price_indexer_cfg_option_aggregate_idx');
$productIndexPriceCfgOptionAggregateTmpTable    = $installer->getTable('catalog/product_price_indexer_cfg_option_aggregate_tmp');
$productIndexPriceCfgOptionIdxTable             = $installer->getTable('catalog/product_price_indexer_cfg_option_idx');
$productIndexPriceCfgOptionTmpTable             = $installer->getTable('catalog/product_price_indexer_cfg_option_tmp');

$shippingTablerateTable                         = $installer->getTable('shipping/tablerate');

$orderGridTable                                 = $installer->getTable('sales/order_grid');
$invoiceGridTable                               = $installer->getTable('sales/invoice_grid');
$shipmentGridTable                              = $installer->getTable('sales/shipment_grid');
$creditmemoGridTable                            = $installer->getTable('sales/creditmemo_grid');

$warehouseTable                                 = $installer->getTable('warehouse/warehouse');
$warehouseAreaTable                             = $installer->getTable('warehouse/warehouse_area');
$warehouseStoreTable                            = $installer->getTable('warehouse/warehouse_store');
$warehouseShippingCarrierTable                  = $installer->getTable('warehouse/warehouse_shipping_carrier');
$orderGridWarehouseTable                        = $installer->getTable('warehouse/order_grid_warehouse');
$invoiceGridWarehouseTable                      = $installer->getTable('warehouse/invoice_grid_warehouse');
$shipmentGridWarehouseTable                     = $installer->getTable('warehouse/shipment_grid_warehouse');
$creditmemoGridWarehouseTable                   = $installer->getTable('warehouse/creditmemo_grid_warehouse');


/**
 * Warehouse
 */
$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$warehouseTable}` (
  `warehouse_id` smallint(6) unsigned not null auto_increment, 
  `code` varchar(32) not null default '', 
  `title` varchar(128) default NULL, 
  `description` text null default null, 
  `stock_id` smallint(6) unsigned not null, 
  `priority` smallint(6) unsigned not null default 0, 
  `notify` tinyint(1) unsigned not null default 0, 
  `contact_name` varchar(64) not null default '', 
  `contact_email` varchar(64) not null default '', 
  `origin_country_id` varchar(2) not null, 
  `origin_region_id` mediumint(8) null, 
  `origin_region` varchar(100) null, 
  `origin_postcode` varchar(50) not null, 
  `origin_city` varchar(100) not null, 
  `origin_latitude` decimal(18,12) null default null, 
  `origin_longitude` decimal(18,12) null default null, 
  `created_at` datetime not null default '0000-00-00 00:00:00', 
  `updated_at` datetime not null default '0000-00-00 00:00:00', 
  PRIMARY KEY  (`warehouse_id`), 
  KEY `FK_WAREHOUSE_STOCK` (`stock_id`), 
  KEY `IDX_CODE` (`code`), 
  KEY `IDX_TITLE` (`title`), 
  KEY `IDX_ORIGIN_LATITUDE` (`origin_latitude`), 
  KEY `IDX_ORIGIN_LONGITUDE` (`origin_longitude`), 
  KEY `IDX_CREATED_AT` (`created_at`), 
  KEY `IDX_UPDATED_AT` (`updated_at`), 
  CONSTRAINT `FK_WAREHOUSE_STOCK` FOREIGN KEY (`stock_id`) REFERENCES {$stockTable} (`stock_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);
$installer->run(
    "INSERT INTO `{$warehouseTable}` (`code`, `title`, `stock_id`, `priority`) 
    VALUES ({$connection->quote('default')}, {$connection->quote('Default')}, 1, 1);"
);
/**
 * Warehouse Area 
 */
$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$warehouseAreaTable}` (
  `warehouse_area_id` int(10) unsigned not null auto_increment, 
  `warehouse_id` smallint(6) unsigned not null, 
  `country_id` varchar(4) not null default '0', 
  `region_id` int(10) not null default '0', 
  `zip` varchar(21) null default null, 
  `is_zip_range` tinyint(1) unsigned not null default 0, 
  `from_zip` int(10) unsigned null default null, 
  `to_zip` int(10) unsigned null default null, 
  PRIMARY KEY  (`warehouse_area_id`), 
  KEY `FK_WAREHOUSE_AREA_WAREHOUSE` (`warehouse_id`), 
  KEY `IDX_COUNTRY` (`country_id`), 
  KEY `IDX_REGION` (`region_id`), 
  KEY `IDX_ZIP` (`zip`), 
  KEY `IDX_WAREHOUSE_AREA_FROM_ZIP` (`from_zip`), 
  KEY `IDX_WAREHOUSE_AREA_TO_ZIP` (`to_zip`), 
  CONSTRAINT `FK_WAREHOUSE_AREA_WAREHOUSE` FOREIGN KEY (`warehouse_id`) REFERENCES {$warehouseTable} (`warehouse_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);
/**
 * Warehouse Store
 */
$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$warehouseStoreTable}` (
  `warehouse_id` smallint(6) unsigned not null, 
  `store_id` smallint(5) unsigned not null, 
  PRIMARY KEY  (`warehouse_id`, `store_id`), 
  KEY `FK_WAREHOUSE_STORE_WAREHOUSE` (`warehouse_id`), 
  KEY `FK_WAREHOUSE_STORE_STORE` (`store_id`), 
  CONSTRAINT `FK_WAREHOUSE_STORE_WAREHOUSE` FOREIGN KEY (`warehouse_id`) REFERENCES {$warehouseTable} (`warehouse_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_WAREHOUSE_STORE_STORE` FOREIGN KEY (`store_id`) REFERENCES {$storeTable} (`store_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);
/**
 * Warehouse Shipping Carrier
 */
$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$warehouseShippingCarrierTable}` (
  `warehouse_id` smallint(6) unsigned not null, 
  `shipping_carrier` varchar(255) not null, 
  PRIMARY KEY  (`warehouse_id`, `shipping_carrier`), 
  KEY `FK_WAREHOUSE_SHIPPING_CARRIER_WAREHOUSE` (`warehouse_id`), 
  KEY `IDX_SHIPPING_CARRIER` (`shipping_carrier`), 
  CONSTRAINT `FK_WAREHOUSE_SHIPPING_CARRIER_WAREHOUSE` FOREIGN KEY (`warehouse_id`) REFERENCES {$warehouseTable} (`warehouse_id`) 
      ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);
/**
 * Product Shelf
 */
$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$productShelfTable}` (
  `product_id` int(10) unsigned not null, 
  `stock_id` smallint(6) unsigned not null, 
  `name` varchar(128) not null default '', 
  PRIMARY KEY  (`product_id`, `stock_id`, `name`), 
  KEY `FK_CATALOG_PRODUCT_SHELF_PRODUCT` (`product_id`), 
  KEY `FK_CATALOG_PRODUCT_SHELF_STOCK` (`stock_id`), 
  KEY `IDX_NAME` (`name`), 
  CONSTRAINT `FK_CATALOG_PRODUCT_SHELF_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES {$productTable} (`entity_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOG_PRODUCT_SHELF_STOCK` FOREIGN KEY (`stock_id`) REFERENCES {$stockTable} (`stock_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);
/**
 * Product Stock Price
 */
$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$productStockPriceTable}` (
  `product_id` int(10) unsigned not null, 
  `stock_id` smallint(6) unsigned not null, 
  `price` decimal(12,4) not null default '0.00', 
  `price_type` enum('fixed', 'percent') not null default 'fixed', 
  PRIMARY KEY  (`product_id`, `stock_id`), 
  KEY `FK_CATALOG_PRODUCT_STOCK_PRICE_PRODUCT` (`product_id`), 
  KEY `FK_CATALOG_PRODUCT_STOCK_PRICE_STOCK` (`stock_id`), 
  CONSTRAINT `FK_CATALOG_PRODUCT_STOCK_PRICE_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES {$productTable} (`entity_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOG_PRODUCT_STOCK_PRICE_STOCK` FOREIGN KEY (`stock_id`) REFERENCES {$stockTable} (`stock_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);
/**
 * Product Stock Priority 
 */
$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$productStockPriorityTable}` (
  `product_id` int(10) unsigned not null, 
  `stock_id` smallint(6) unsigned not null, 
  `priority` smallint(6) unsigned not null default 0, 
  PRIMARY KEY  (`product_id`, `stock_id`), 
  KEY `FK_CATALOG_PRODUCT_STOCK_PRIORITY_PRODUCT` (`product_id`), 
  KEY `FK_CATALOG_PRODUCT_STOCK_PRIORITY_STOCK` (`stock_id`), 
  CONSTRAINT `FK_CATALOG_PRODUCT_STOCK_PRIORITY_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES {$productTable} (`entity_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOG_PRODUCT_STOCK_PRIORITY_STOCK` FOREIGN KEY (`stock_id`) REFERENCES {$stockTable} (`stock_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);
/**
 * Product Stock Shipping Carrier
 */
$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$productStockShippingCarrierTable}` (
  `product_id` int(10) unsigned not null, 
  `stock_id` smallint(6) unsigned not null, 
  `shipping_carrier` varchar(255) not null, 
  PRIMARY KEY  (`product_id`, `stock_id`, `shipping_carrier`), 
  KEY `FK_CATALOG_PRODUCT_STOCK_SHIPPING_CARRIER_PRODUCT` (`product_id`), 
  KEY `FK_CATALOG_PRODUCT_STOCK_SHIPPING_CARRIER_STOCK` (`stock_id`), 
  KEY `IDX_SHIPPING_CARRIER` (`shipping_carrier`), 
  CONSTRAINT `FK_CATALOG_PRODUCT_STOCK_SHIPPING_CARRIER_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES {$productTable} (`entity_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOG_PRODUCT_STOCK_SHIPPING_CARRIER_STOCK` FOREIGN KEY (`stock_id`) REFERENCES {$stockTable} (`stock_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);


/**
 * Quote 
 */
$connection->addColumn($quoteTable, 'items_qtys', 'text null default null');
/**
 * Quote Address 
 */
$connection->addColumn($quoteAddressTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addConstraint(
    'FK_SALES_QUOTE_ADDRESS_STOCK', $quoteAddressTable, 'stock_id', $stockTable, 'stock_id', 'set null'
);
/**
 * Order Address 
 */
$connection->addColumn($orderAddressTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addConstraint(
    'FK_SALES_ORDER_ADDRESS_STOCK', $orderAddressTable, 'stock_id', $stockTable, 'stock_id', 'set null'
);
/**
 * Quote Item 
 */
$connection->addColumn($quoteItemTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addConstraint(
    'FK_SALES_QUOTE_ITEM_STOCK', $quoteItemTable, 'stock_id', $stockTable, 'stock_id', 'set null'
);
/**
 * Order Item 
 */
$connection->addColumn($orderItemTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addConstraint(
    'FK_SALES_ORDER_ITEM_STOCK', $orderItemTable, 'stock_id', $stockTable, 'stock_id', 'set null'
);
/**
 * Invoice Item 
 */
$connection->addColumn($invoiceItemTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addConstraint(
    'FK_SALES_INVOICE_ITEM_STOCK', $invoiceItemTable, 'stock_id', $stockTable, 'stock_id', 'set null'
);
/**
 * Shipment Item 
 */
$connection->addColumn($shipmentItemTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addConstraint(
    'FK_SALES_SHIPMENT_ITEM_STOCK', $shipmentItemTable, 'stock_id', $stockTable, 'stock_id', 'set null'
);
/**
 * Credit Memo Item 
 */
$connection->addColumn($creditmemoItemTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addConstraint(
    'FK_SALES_CREDITMEMO_ITEM_STOCK', $creditmemoItemTable, 'stock_id', $stockTable, 'stock_id', 'set null'
);

/**
 * Shipping Tablerate
 */
$connection->addColumn($shippingTablerateTable, 'warehouse_id', "smallint(6) unsigned NOT NULL default '0'");
$connection->addKey(
    $shippingTablerateTable, 'dest_country', array(
    'website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'condition_name', 'condition_value', 'warehouse_id', 
    ), 'unique'
);
if (Mage::helper('warehouse/core_version')->isGe1600()) {
    $shippingTablerateIndexes = $connection->getIndexList($shippingTablerateTable);
    foreach ($shippingTablerateIndexes as $index) {
        if ($index['INDEX_TYPE'] == Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE) {
            $connection->dropIndex($shippingTablerateTable, $index['KEY_NAME']);
        }
    }

    $connection->addIndex(
        $installer->getTable('shipping/tablerate'),
        $installer->getIdxName(
            'shipping/tablerate', 
            array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'condition_name', 'condition_value', 'warehouse_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ), 
        array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'condition_name', 'condition_value', 'warehouse_id'), 
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );
}

/**
 * Product Index Price 
 */
$connection->addColumn($productIndexPriceTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey($productIndexPriceTable, 'IDX_STOCK', array('stock_id'), 'index');
$connection->addKey($productIndexPriceTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'stock_id'), 'primary');
$connection->addConstraint(
    'FK_CATALOG_PRODUCT_INDEX_PRICE_STOCK', $productIndexPriceTable, 'stock_id', $stockTable, 'stock_id'
);
/**
 * Product Index Price Index
 */
$connection->addColumn($productIndexPriceIdxTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey($productIndexPriceIdxTable, 'IDX_STOCK', array('stock_id'), 'index');
$connection->addKey($productIndexPriceIdxTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'stock_id'), 'primary');
/**
 * Product Index Price Temp
 */
$connection->addColumn($productIndexPriceTmpTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey($productIndexPriceTmpTable, 'IDX_STOCK', array('stock_id'), 'index');
$connection->addKey($productIndexPriceTmpTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'stock_id'), 'primary');
/**
 * Product Index Price Final Index
 */
$connection->addColumn($productIndexPriceFinalIdxTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey($productIndexPriceFinalIdxTable, 'IDX_STOCK', array('stock_id'), 'index');
$connection->addKey($productIndexPriceFinalIdxTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'stock_id'), 'primary');
/**
 * Product Index Price Final Temp
 */
$connection->addColumn($productIndexPriceFinalTmpTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey($productIndexPriceFinalTmpTable, 'IDX_STOCK', array('stock_id'), 'index');
$connection->addKey($productIndexPriceFinalTmpTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'stock_id'), 'primary');
/**
 * Product Index Price Bundle Index
 */
$connection->addColumn($productIndexPriceBundleIdxTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey($productIndexPriceBundleIdxTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'stock_id'), 'primary');
/**
 * Product Index Price Bundle Temp
 */
$connection->addColumn($productIndexPriceBundleTmpTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey($productIndexPriceBundleTmpTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'stock_id'), 'primary');
/**
 * Product Index Price Bundle Selection Index
 */
$connection->addColumn($productIndexPriceBundleSelectionIdxTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey(
    $productIndexPriceBundleSelectionIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'selection_id', 'stock_id'
    ), 'primary'
);
/**
 * Product Index Price Bundle Selection Temp
 */
$connection->addColumn($productIndexPriceBundleSelectionTmpTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey(
    $productIndexPriceBundleSelectionTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'selection_id', 'stock_id'
    ), 'primary'
);
/**
 * Product Index Price Bundle Option Index
 */
$connection->addColumn($productIndexPriceBundleOptionIdxTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey(
    $productIndexPriceBundleOptionIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'stock_id'
    ), 'primary'
);
/**
 * Product Index Price Bundle Option Temp
 */
$connection->addColumn($productIndexPriceBundleOptionTmpTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey(
    $productIndexPriceBundleOptionTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'stock_id'
    ), 'primary'
);
/**
 * Product Index Price Option Aggregate Index
 */
$connection->addColumn($productIndexPriceOptionAggregateIdxTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey(
    $productIndexPriceOptionAggregateIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'stock_id'
    ), 'primary'
);
/**
 * Product Index Price Option Aggregate Temp
 */
$connection->addColumn($productIndexPriceOptionAggregateTmpTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey(
    $productIndexPriceOptionAggregateTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'stock_id'
    ), 'primary'
);
/**
 * Product Index Price Option Index
 */
$connection->addColumn($productIndexPriceOptionIdxTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey(
    $productIndexPriceOptionIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'stock_id'
    ), 'primary'
);
/**
 * Product Index Price Option Temp
 */
$connection->addColumn($productIndexPriceOptionTmpTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey(
    $productIndexPriceOptionTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'stock_id'
    ), 'primary'
);
/**
 * Product Index Price Downloadable Index
 */
$connection->addColumn($productIndexPriceDownloadableIdxTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey(
    $productIndexPriceDownloadableIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'stock_id'
    ), 'primary'
);
/**
 * Product Index Price Downloadable Temp
 */
$connection->addColumn($productIndexPriceDownloadableTmpTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey(
    $productIndexPriceDownloadableTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'stock_id'
    ), 'primary'
);
/**
 * Product Index Price Configurable Option Aggregate Index
 */
$connection->addColumn($productIndexPriceCfgOptionAggregateIdxTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey(
    $productIndexPriceCfgOptionAggregateIdxTable, 'PRIMARY', array(
    'parent_id', 'child_id', 'customer_group_id', 'website_id', 'stock_id'
    ), 'primary'
);
/**
 * Product Index Price Configurable Option Aggregate Temp
 */
$connection->addColumn($productIndexPriceCfgOptionAggregateTmpTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey(
    $productIndexPriceCfgOptionAggregateTmpTable, 'PRIMARY', array(
    'parent_id', 'child_id', 'customer_group_id', 'website_id', 'stock_id'
    ), 'primary'
);
/**
 * Product Index Price Configurable Option Index
 */
$connection->addColumn($productIndexPriceCfgOptionIdxTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey(
    $productIndexPriceCfgOptionIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'stock_id'
    ), 'primary'
);
/**
 * Product Index Price Configurable Option Temp
 */
$connection->addColumn($productIndexPriceCfgOptionTmpTable, 'stock_id', 'smallint(6) unsigned null default null');
$connection->addKey(
    $productIndexPriceCfgOptionTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'stock_id'
    ), 'primary'
);



/**
 * Order Grid Warehouse 
 */
$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$orderGridWarehouseTable}` (
  `entity_id` int(10) unsigned not null, 
  `stock_id` smallint(6) unsigned not null, 
  PRIMARY KEY  (`entity_id`, `stock_id`), 
  KEY `IDX_ENTITY_ID` (`entity_id`), 
  KEY `FK_WAREHOUSE_ORDER_GRID_WAREHOUSE_STOCK` (`stock_id`), 
  CONSTRAINT `FK_WAREHOUSE_ORDER_GRID_WAREHOUSE_ENTITY_ID` FOREIGN KEY (`entity_id`) REFERENCES {$orderGridTable} (`entity_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_WAREHOUSE_ORDER_GRID_WAREHOUSE_STOCK` FOREIGN KEY (`stock_id`) REFERENCES {$stockTable} (`stock_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);
/**
 * Shipment Grid Warehouse 
 */
$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$shipmentGridWarehouseTable}` (
  `entity_id` int(10) unsigned not null, 
  `stock_id` smallint(6) unsigned not null, 
  PRIMARY KEY  (`entity_id`, `stock_id`), 
  KEY `IDX_ENTITY_ID` (`entity_id`), 
  KEY `FK_WAREHOUSE_SHIPMENT_GRID_WAREHOUSE_STOCK` (`stock_id`), 
  CONSTRAINT `FK_WAREHOUSE_SHIPMENT_GRID_ENTITY_ID` FOREIGN KEY (`entity_id`) REFERENCES {$shipmentGridTable} (`entity_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_WAREHOUSE_SHIPMENT_GRID_WAREHOUSE_STOCK` FOREIGN KEY (`stock_id`) REFERENCES {$stockTable} (`stock_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);
/**
 * Credit Memo Grid Warehouse
 */
$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$creditmemoGridWarehouseTable}` (
  `entity_id` int(10) unsigned not null, 
  `stock_id` smallint(6) unsigned not null, 
  PRIMARY KEY  (`entity_id`, `stock_id`), 
  KEY `IDX_ENTITY_ID` (`entity_id`), 
  KEY `FK_WAREHOUSE_CREDITMEMO_GRID_WAREHOUSE_STOCK` (`stock_id`), 
  CONSTRAINT `FK_WAREHOUSE_CREDITMEMO_GRID_ENTITY_ID` FOREIGN KEY (`entity_id`) REFERENCES {$creditmemoGridTable} (`entity_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_WAREHOUSE_CREDITMEMO_GRID_WAREHOUSE_STOCK` FOREIGN KEY (`stock_id`) REFERENCES {$stockTable} (`stock_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);
/**
 * Invoice Grid Warehouse
 */
$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$invoiceGridWarehouseTable}` (
  `entity_id` int(10) unsigned not null, 
  `stock_id` smallint(6) unsigned not null, 
  PRIMARY KEY  (`entity_id`, `stock_id`), 
  KEY `IDX_ENTITY_ID` (`entity_id`), 
  KEY `FK_WAREHOUSE_INVOICE_GRID_WAREHOUSE_STOCK` (`stock_id`), 
  CONSTRAINT `FK_WAREHOUSE_INVOICE_GRID_ENTITY_ID` FOREIGN KEY (`entity_id`) REFERENCES {$invoiceGridTable} (`entity_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_WAREHOUSE_INVOICE_GRID_WAREHOUSE_STOCK` FOREIGN KEY (`stock_id`) REFERENCES {$stockTable} (`stock_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);
/**
 * Order
 */
$installer->run("UPDATE `{$orderItemTable}` SET `stock_id` = {$defaultStockId} WHERE `stock_id` IS NULL");
$installer->run(
    "INSERT INTO {$orderGridWarehouseTable} (`entity_id`, `stock_id`) 
  SELECT entity_id, {$connection->quote($defaultStockId)} FROM {$orderTable}"
);
/**
 * Invoice 
 */
$installer->run("UPDATE `{$invoiceItemTable}` SET `stock_id` = {$defaultStockId} WHERE `stock_id` IS NULL");
$installer->run(
    "INSERT INTO {$invoiceGridWarehouseTable} (`entity_id`, `stock_id`) 
  SELECT entity_id, {$connection->quote($defaultStockId)} FROM {$invoiceTable}"
);
/**
 * Shipment
 */
$installer->run("UPDATE `{$shipmentItemTable}` SET `stock_id` = {$defaultStockId} WHERE `stock_id` IS NULL");
$installer->run(
    "INSERT INTO {$shipmentGridWarehouseTable} (`entity_id`, `stock_id`) 
  SELECT entity_id, {$connection->quote($defaultStockId)} FROM {$shipmentTable}"
);
/**
 * Credit Memo
 */
$installer->run("UPDATE `{$creditmemoItemTable}` SET `stock_id` = {$defaultStockId} WHERE `stock_id` IS NULL");
$installer->run(
    "INSERT INTO {$creditmemoGridWarehouseTable} (`entity_id`, `stock_id`) 
  SELECT entity_id, {$connection->quote($defaultStockId)} FROM {$creditmemoTable}"
);

/**
 * Test 
 */
$installer->run("UPDATE `{$warehouseTable}` SET `description` = 'Default warehouse custom description'"); 

$installer->endSetup();
