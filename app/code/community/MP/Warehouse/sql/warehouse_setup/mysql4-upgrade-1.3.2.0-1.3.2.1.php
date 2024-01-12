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

$helper                           = Mage::helper('warehouse');
$databaseHelper                   = $helper->getDatabaseHelper();
$defaultStockId                   = $helper->getDefaultStockId();
$stockTable                       = $installer->getTable('cataloginventory/stock');
$catalogRuleProductTableName      = 'catalogrule/rule_product';
$catalogRuleProductTable          = $installer->getTable($catalogRuleProductTableName);
$catalogRuleProductPriceTableName = 'catalogrule/rule_product_price';
$catalogRuleProductPriceTable     = $installer->getTable($catalogRuleProductPriceTableName);

/**
 * Catalog Rule Product
 */
$connection->addColumn(
    $catalogRuleProductTable, 'stock_id', 'smallint(6) unsigned not null default 1 after `product_id`'
);

$connection->addKey(
    $catalogRuleProductTable, 'IDX_CATALOGRULE_PRODUCT_STOCK_ID', array('stock_id'), 'index'
);

$connection->addConstraint(
    'FK_CATALOGRULE_PRODUCT_STOCK_ID', $catalogRuleProductTable, 'stock_id', $stockTable, 'stock_id'
);

$databaseHelper->replaceUniqueKey(
    $installer, $catalogRuleProductTableName, 'sort_order', array(
        'rule_id', 'from_time', 'to_time', 'website_id', 'customer_group_id', 'product_id', 'stock_id', 'sort_order'
    )
);

/**
 * Catalog Rule Product Price
 */
$connection->addColumn(
    $catalogRuleProductPriceTable, 'stock_id', 'smallint(6) unsigned not null default 1 after `product_id`'
);

$connection->addKey(
    $catalogRuleProductPriceTable, 'IDX_CATALOGRULE_PRODUCT_PRICE_STOCK_ID', array('stock_id'), 'index'
);

$connection->addConstraint(
    'FK_CATALOGRULE_PRODUCT_PRICE_STOCK_ID', $catalogRuleProductPriceTable, 'stock_id', $stockTable, 'stock_id'
);

$databaseHelper->replaceUniqueKey(
    $installer, $catalogRuleProductPriceTableName, 'rule_date', array(
        'rule_date', 'website_id', 'customer_group_id', 'product_id', 'stock_id'
    )
);

$installer->endSetup();
