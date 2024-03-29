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

$installer                                  = $this;

$connection                                 = $installer->getConnection();

$productTable                               = $installer->getTable('catalog/product');
$stockTable                                 = $installer->getTable('cataloginventory/stock');
$websiteTable                               = $installer->getTable('core/website');
$productEntityDecimalTable                  = $installer->getTable('catalog_product_entity_decimal');
$productIndexBatchPriceTable                = $installer->getTable('catalog/product_index_batch_price');
$productIndexBatchSpecialPriceTable         = $installer->getTable('catalog/product_index_batch_special_price');
$productIndexTierPriceTableName             = 'catalog/product_index_tier_price';
$productIndexTierPriceTable                 = $installer->getTable($productIndexTierPriceTableName);
$productBatchPriceTable                     = $installer->getTable('catalog/product_batch_price');
$productBatchSpecialPriceTable              = $installer->getTable('catalog/product_batch_special_price');
$productStockPriceTable                     = $installer->getTable('catalog/product_stock_price');
$productTierPriceTableName                  = 'catalog/product_attribute_tier_price';
$productTierPriceTable                      = $installer->getTable($productTierPriceTableName);
$eavAttributeTable                          = $installer->getTable('eav/attribute');
$eavEntityTypeTable                         = $installer->getTable('eav/entity_type');

$installer->startSetup();

/* Product Stock Price */

$connection->changeColumn($productStockPriceTable, 'price', 'price', 'decimal(12,4) null default null');

$priceQuery = "SELECT `value` FROM `{$productEntityDecimalTable}` WHERE 
(`entity_id` = `{$productStockPriceTable}`.`product_id`) AND (attribute_id = (
    SELECT `attribute_id` FROM `{$eavAttributeTable}` WHERE (`attribute_code` = 'price') AND (
        `entity_type_id` = (
            SELECT `entity_type_id` FROM `{$eavEntityTypeTable}` WHERE `entity_type_code` = 'catalog_product'
        )
    )
)) AND (`store_id` = 0)";

$installer->run(
    "
UPDATE `{$productStockPriceTable}` SET `price` = (
    IF (`price_type` = 'fixed', ({$priceQuery}) - `price`, ({$priceQuery}) - `price` * (({$priceQuery}) / 100))
)
"
);
$connection->dropColumn($productStockPriceTable, 'price_type');

$connection->addColumn($productStockPriceTable, 'website_id', 'smallint(5) unsigned not null default 0 after `stock_id`');
$connection->addKey($productStockPriceTable, 'IDX_CATALOG_PRODUCT_STOCK_PRICE_WEBSITE_ID', array('website_id'), 'index');
$connection->addConstraint('FK_CATALOG_PRODUCT_STOCK_PRICE_WEBSITE_ID', $productStockPriceTable, 'website_id', $websiteTable, 'website_id');
$connection->addKey($productStockPriceTable, 'PRIMARY', array('product_id', 'stock_id', 'website_id'), 'primary');

/* Product Batch Price */

$installer->run(
    "
CREATE TABLE `{$productBatchPriceTable}` (
  `product_id` int(10) unsigned not null, 
  `stock_id` smallint(6) unsigned not null, 
  `website_id` smallint(5) unsigned not null default 0, 
  `price` decimal(12,4) null default null, 
  PRIMARY KEY  (`product_id`, `stock_id`, `website_id`), 
  KEY `FK_CATALOG_PRODUCT_BATCH_PRICE_PRODUCT` (`product_id`), 
  KEY `FK_CATALOG_PRODUCT_BATCH_PRICE_STOCK` (`stock_id`), 
  KEY `FK_CATALOG_PRODUCT_BATCH_PRICE_WEBSITE` (`website_id`), 
  CONSTRAINT `FK_CATALOG_PRODUCT_BATCH_PRICE_PRODUCT` FOREIGN KEY (`product_id`) 
    REFERENCES {$productTable} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOG_PRODUCT_BATCH_PRICE_STOCK` FOREIGN KEY (`stock_id`) 
    REFERENCES {$stockTable} (`stock_id`) ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOG_PRODUCT_BATCH_PRICE_WEBSITE` FOREIGN KEY (`website_id`) 
    REFERENCES {$websiteTable} (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);

$installer->run(
    "
INSERT INTO `{$productBatchPriceTable}` (`product_id`, `stock_id`, `website_id`, `price`) 
  SELECT `product_id`, `stock_id`, `website_id`, `price` FROM `{$productStockPriceTable}`
"
);

$installer->run("DROP TABLE `{$productStockPriceTable}`");

$installer->run(
    "
CREATE TABLE `{$productIndexBatchPriceTable}` (
  `entity_id` int(10) unsigned not null, 
  `stock_id` smallint(6) unsigned not null default 0, 
  `website_id` smallint(5) unsigned not null default 0, 
  `price` decimal(12,4) null default null, 
  `min_price` decimal(12,4) null default null, 
  `max_price` decimal(12,4) null default null, 
  PRIMARY KEY  (`entity_id`, `stock_id`, `website_id`), 
  KEY `FK_CATALOG_PRODUCT_INDEX_BATCH_PRICE_ENTITY` (`entity_id`), 
  KEY `FK_CATALOG_PRODUCT_INDEX_BATCH_PRICE_STOCK` (`stock_id`), 
  KEY `FK_CATALOG_PRODUCT_INDEX_BATCH_PRICE_WEBSITE` (`website_id`), 
  CONSTRAINT `FK_CATALOG_PRODUCT_INDEX_BATCH_PRICE_ENTITY` FOREIGN KEY (`entity_id`) 
    REFERENCES {$productTable} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOG_PRODUCT_INDEX_BATCH_PRICE_STOCK` FOREIGN KEY (`stock_id`) 
    REFERENCES {$stockTable} (`stock_id`) ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOG_PRODUCT_INDEX_BATCH_PRICE_WEBSITE` FOREIGN KEY (`website_id`) 
    REFERENCES {$websiteTable} (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);

/* Product Batch Special Price */

$installer->run(
    "
CREATE TABLE `{$productBatchSpecialPriceTable}` (
  `product_id` int(10) unsigned not null, 
  `stock_id` smallint(6) unsigned not null, 
  `website_id` smallint(5) unsigned not null default 0, 
  `price` decimal(12,4) null default null, 
  PRIMARY KEY  (`product_id`, `stock_id`, `website_id`), 
  KEY `FK_CATALOG_PRODUCT_BATCH_SPECIAL_PRICE_PRODUCT` (`product_id`), 
  KEY `FK_CATALOG_PRODUCT_BATCH_SPECIAL_PRICE_STOCK` (`stock_id`), 
  KEY `FK_CATALOG_PRODUCT_BATCH_SPECIAL_PRICE_WEBSITE` (`website_id`), 
  CONSTRAINT `FK_CATALOG_PRODUCT_BATCH_SPECIAL_PRICE_PRODUCT` FOREIGN KEY (`product_id`) 
    REFERENCES {$productTable} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOG_PRODUCT_BATCH_SPECIAL_PRICE_STOCK` FOREIGN KEY (`stock_id`) 
    REFERENCES {$stockTable} (`stock_id`) ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOG_PRODUCT_BATCH_SPECIAL_PRICE_WEBSITE` FOREIGN KEY (`website_id`) 
    REFERENCES {$websiteTable} (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);

$installer->run(
    "
CREATE TABLE `{$productIndexBatchSpecialPriceTable}` (
  `entity_id` int(10) unsigned not null, 
  `stock_id` smallint(6) unsigned not null, 
  `website_id` smallint(5) unsigned not null default 0, 
  `price` decimal(12,4) null default null, 
  `min_price` decimal(12,4) null default null, 
  `max_price` decimal(12,4) null default null, 
  PRIMARY KEY  (`entity_id`, `stock_id`, `website_id`), 
  KEY `FK_CATALOG_PRODUCT_INDEX_BATCH_SPECIAL_PRICE_ENTITY` (`entity_id`), 
  KEY `FK_CATALOG_PRODUCT_INDEX_BATCH_SPECIAL_PRICE_STOCK` (`stock_id`), 
  KEY `FK_CATALOG_PRODUCT_INDEX_BATCH_SPECIAL_PRICE_WEBSITE` (`website_id`), 
  CONSTRAINT `FK_CATALOG_PRODUCT_INDEX_BATCH_SPECIAL_PRICE_ENTITY` FOREIGN KEY (`entity_id`) 
    REFERENCES {$productTable} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOG_PRODUCT_INDEX_BATCH_SPECIAL_PRICE_STOCK` FOREIGN KEY (`stock_id`) 
    REFERENCES {$stockTable} (`stock_id`) ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOG_PRODUCT_INDEX_BATCH_SPECIAL_PRICE_WEBSITE` FOREIGN KEY (`website_id`) 
    REFERENCES {$websiteTable} (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);

/* Product Tier Price */

$connection->addColumn($productTierPriceTable, 'stock_id', 'smallint(6) unsigned null default null after `website_id`');
$connection->addKey($productTierPriceTable, 'IDX_CATALOG_PRODUCT_ENTITY_TIER_PRICE_STOCK', array('stock_id'), 'index');
$connection->addConstraint('FK_CATALOG_PRODUCT_ENTITY_TIER_PRICE_STOCK', $productTierPriceTable, 'stock_id', $stockTable, 'stock_id');

if (Mage::helper('warehouse')->getVersionHelper()->isGe1600()) {
    $productTierPriceIndexes = $connection->getIndexList($productTierPriceTable);
    foreach ($productTierPriceIndexes as $index) {
        if ($index['INDEX_TYPE'] == Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE) {
            $connection->dropIndex($productTierPriceTable, $index['KEY_NAME']);
        }
    }

    $connection->addIndex(
        $productTierPriceTable, 
        $installer->getIdxName(
            $productTierPriceTableName, 
            array('entity_id', 'all_groups', 'customer_group_id', 'qty', 'website_id', 'stock_id'), 
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('entity_id', 'all_groups', 'customer_group_id', 'qty', 'website_id', 'stock_id'), 
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );
} else {
    $connection->addKey(
        $productTierPriceTable, 'UNQ_CATALOG_PRODUCT_TIER_PRICE', array(
        'entity_id', 'all_groups', 'customer_group_id', 'qty', 'website_id', 'stock_id', 
        ), 'unique'
    );
}

/* Product Index Tier Price */

$connection->addColumn($productIndexTierPriceTable, 'stock_id', 'smallint(6) unsigned not null default 0 after `website_id`');
$connection->addKey($productIndexTierPriceTable, 'IDX_CATALOG_PRODUCT_INDEX_TIER_PRICE_STOCK', array('stock_id'), 'index');
$connection->addConstraint('FK_CATALOG_PRODUCT_INDEX_TIER_PRICE_STOCK', $productIndexTierPriceTable, 'stock_id', $stockTable, 'stock_id');

$connection->addKey($productIndexTierPriceTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'stock_id'), 'primary');



$installer->endSetup();
