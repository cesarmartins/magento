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

$installer                          = $this;
$connection                         = $installer->getConnection();

$helper                             = Mage::helper('warehouse');
$stockTable                         = $installer->getTable('cataloginventory/stock');
$productTable                       = $installer->getTable('catalog/product');
$productStockPriorityTable          = $installer->getTable('catalog/product_stock_priority');
$productStockShippingCarrierTable   = $installer->getTable('catalog/product_stock_shipping_carrier');

$installer->startSetup();

$installer->run(
    "
-- DROP TABLE IF EXISTS `{$productStockPriorityTable}`;

CREATE TABLE `{$productStockPriorityTable}` (
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

$installer->run(
    "
-- DROP TABLE IF EXISTS `{$productStockShippingCarrierTable}`;

CREATE TABLE `{$productStockShippingCarrierTable}` (
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


$installer->endSetup();
