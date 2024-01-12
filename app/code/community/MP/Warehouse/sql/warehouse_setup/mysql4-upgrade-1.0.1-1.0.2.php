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

$installer                     = $this;
$connection                    = $installer->getConnection();
$warehouseTable                = $installer->getTable('warehouse');
$catalogProductTable           = $installer->getTable('catalog/product');
$catalogProductShelfTable      = $installer->getTable('catalog/product_shelf');

$installer->startSetup();

$installer->run(
    "
DROP TABLE IF EXISTS `{$catalogProductShelfTable}`;

CREATE TABLE `{$catalogProductShelfTable}` (
  `product_id` int(10) unsigned not null, 
  `warehouse_id` smallint(6) unsigned not null, 
  `name` varchar(128) not null default '', 
  PRIMARY KEY  (`product_id`, `warehouse_id`, `name`), 
  KEY `FK_CATALOG_PRODUCT_SHELF_PRODUCT` (`product_id`), 
  KEY `FK_CATALOG_PRODUCT_SHELF_WAREHOUSE` (`warehouse_id`), 
  KEY `IDX_NAME` (`name`), 
  CONSTRAINT `FK_CATALOG_PRODUCT_SHELF_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES {$catalogProductTable} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOG_PRODUCT_SHELF_WAREHOUSE` FOREIGN KEY (`warehouse_id`) REFERENCES {$warehouseTable} (`warehouse_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);

$installer->endSetup();
