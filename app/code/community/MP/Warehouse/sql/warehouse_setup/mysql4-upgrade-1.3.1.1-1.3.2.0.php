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

/** @var MP_Warehouse_Model_Resource_Eav_Mysql4_Setup $installer */
$installer = $this;

/** @var Varien_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$installer->startSetup();

$helper                         = Mage::helper('warehouse');
$databaseHelper                 = $helper->getDatabaseHelper();
$defaultStockId                 = $helper->getDefaultStockId();
$stockTable                     = $installer->getTable('cataloginventory/stock');
$productGroupPriceTableName      = 'catalog/product_attribute_group_price';
$productGroupPriceTable          = $installer->getTable($productGroupPriceTableName);
$productIndexGroupPriceTableName = 'catalog/product_index_group_price';
$productIndexGroupPriceTable     = $installer->getTable($productIndexGroupPriceTableName);

/**
 * Product group price
 */
$connection->addColumn(
    $productGroupPriceTable, 'stock_id', 'smallint(6) unsigned null default null after `website_id`'
);

$connection->addKey(
    $productGroupPriceTable, 'IDX_CATALOG_PRODUCT_ENTITY_GROUP_PRICE_STOCK', array('stock_id'), 'index'
);

$connection->addConstraint(
    'FK_CATALOG_PRODUCT_ENTITY_GROUP_PRICE_STOCK', $productGroupPriceTable, 'stock_id', $stockTable, 'stock_id'
);

$databaseHelper->replaceUniqueKey(
    $installer, $productGroupPriceTableName, 'UNQ_CATALOG_PRODUCT_GROUP_PRICE', array(
        'entity_id', 'all_groups', 'customer_group_id', 'website_id', 'stock_id'
    )
);

/**
 * Product index group price
 */
$connection->addColumn(
    $productIndexGroupPriceTable, 'stock_id', 'smallint(6) unsigned not null default 0 after `website_id`'
);

$connection->addKey(
    $productIndexGroupPriceTable, 'IDX_CATALOG_PRODUCT_INDEX_GROUP_PRICE_STOCK', array('stock_id'), 'index'
);

$connection->addConstraint(
    'FK_CATALOG_PRODUCT_INDEX_GROUP_PRICE_STOCK', $productIndexGroupPriceTable, 'stock_id', $stockTable, 'stock_id'
);

$connection->addKey(
    $productIndexGroupPriceTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'stock_id'), 'primary'
);

$installer->endSetup();
