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

$customerGroupTable                         = $installer->getTable('customer/customer_group');
$warehouseTable                             = $installer->getTable('warehouse/warehouse');
$warehouseCustomerGroupTable                = $installer->getTable('warehouse/warehouse_customer_group');
$warehouseCurrencyTable                     = $installer->getTable('warehouse/warehouse_currency');

$installer->startSetup();

/**
 * Warehouse Customer Group
 */
$installer->run(
    "
CREATE TABLE `{$warehouseCustomerGroupTable}` (
  `warehouse_id` smallint(6) unsigned not null, 
  `customer_group_id` smallint(5) unsigned not null, 
  PRIMARY KEY  (`warehouse_id`, `customer_group_id`), 
  KEY `FK_WAREHOUSE_CUSTOMER_GROUP_WAREHOUSE_ID` (`warehouse_id`), 
  KEY `FK_WAREHOUSE_CUSTOMER_GROUP_CUSTOMER_GROUP_ID` (`customer_group_id`), 
  CONSTRAINT `FK_WAREHOUSE_CUSTOMER_GROUP_WAREHOUSE` FOREIGN KEY (`warehouse_id`) 
    REFERENCES {$warehouseTable} (`warehouse_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_WAREHOUSE_CUSTOMER_GROUP_CUSTOMER_GROUP_ID` FOREIGN KEY (`customer_group_id`) 
    REFERENCES {$customerGroupTable} (`customer_group_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);

/**
 * Warehouse Currency
 */
$installer->run(
    "
CREATE TABLE `{$warehouseCurrencyTable}` (
  `warehouse_id` smallint(6) unsigned not null, 
  `currency` varchar(3) not null, 
  PRIMARY KEY  (`warehouse_id`, `currency`), 
  KEY `FK_WAREHOUSE_CURRENCY_WAREHOUSE_ID` (`warehouse_id`), 
  KEY `IDX_WAREHOUSE_CURRENCY_CURRENCY` (`currency`), 
  CONSTRAINT `FK_WAREHOUSE_CURRENCY_WAREHOUSE_ID` FOREIGN KEY (`warehouse_id`) 
    REFERENCES {$warehouseTable} (`warehouse_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);

$installer->endSetup();
