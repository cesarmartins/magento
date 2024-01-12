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
$installer                                      = $this;

$connection                                     = $installer->getConnection();

$helper                                         = Mage::helper('warehouse');
$databaseHelper                                 = $helper->getCoreHelper()->getDatabaseHelper();

$shippingTablerateTableName                     = 'warehouse/tablerate';
$shippingTablerateTable                         = $installer->getTable($shippingTablerateTableName);
$shippingTablerateMethodTable                   = $installer->getTable('warehouse/tablerate_method');

$installer->startSetup();

/**
 * Shipping Tablerate Method
 */
$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$shippingTablerateMethodTable}` (
  `method_id` smallint(5) unsigned not null auto_increment, 
  `code` varchar(32) not null default '', 
  `name` varchar(128) default NULL, 
  PRIMARY KEY  (`method_id`), 
  KEY `IDX_SHIPPING_TABLERATE_METHOD_CODE` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);

/**
 * Shipping Tablerate
 */
$connection->addColumn($shippingTablerateTable, 'method_id', 'smallint(5) unsigned null default null');
$connection->addConstraint(
    'FK_SHIPPING_TABLERATE_METHOD_ID', 
    $shippingTablerateTable, 
    'method_id', 
    $shippingTablerateMethodTable, 
    'method_id'
);

$databaseHelper->replaceUniqueKey(
    $installer, $shippingTablerateTableName, 'dest_country', array(
        'website_id', 
        'dest_country_id', 
        'dest_region_id', 
        'dest_zip', 
        'condition_name', 
        'condition_value', 
        'warehouse_id', 
        'method_id', 
    )
);

/**
 * Fixtures
 */

$installer->run(
    "INSERT INTO `{$shippingTablerateMethodTable}` (`method_id`, `code`, `name`) VALUES (
    '1', {$connection->quote('default')}, {$connection->quote('Default')}
);"
);
    
$installer->run("UPDATE `{$shippingTablerateTable}` SET `method_id` = '1';");

$installer->endSetup();
