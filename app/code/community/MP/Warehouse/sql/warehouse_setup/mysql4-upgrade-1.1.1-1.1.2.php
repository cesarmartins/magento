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

$installer                                = $this;
$connection                               = $installer->getConnection();

$warehouseAreaTable = $installer->getTable('warehouse/warehouse_area');

$installer->startSetup();

$connection->changeColumn($warehouseAreaTable, 'zip', 'zip', 'varchar(21) null default null');
$connection->addColumn($warehouseAreaTable, 'is_zip_range', 'tinyint(1) unsigned not null default 0 after `zip`');
$connection->addColumn($warehouseAreaTable, 'from_zip', 'int(10) unsigned null default null after `is_zip_range`');
$connection->addKey($warehouseAreaTable, 'IDX_WAREHOUSE_AREA_FROM_ZIP', array('from_zip'), 'index');
$connection->addColumn($warehouseAreaTable, 'to_zip', 'int(10) unsigned null default null after `from_zip`');
$connection->addKey($warehouseAreaTable, 'IDX_WAREHOUSE_AREA_TO_ZIP', array('to_zip'), 'index');

$installer->endSetup();
