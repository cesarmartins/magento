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

$installer->startSetup();

$helper = Mage::helper('warehouse');
$defaultStockId = $helper->getCatalogInventoryHelper()->getDefaultStockId();

/* Orders */
$orderTable = $installer->getTable('sales/order');
$orderItemTable = $installer->getTable('sales/order_item');
$orderGridWarehouseTable = $installer->getTable('warehouse/order_grid_warehouse');

$installer->run("UPDATE `{$orderItemTable}` SET `stock_id` = {$defaultStockId} WHERE `stock_id` IS NULL");

$orders = array();
$ordersStmt = $connection->query("SELECT * FROM {$orderTable}");
$orderIds = array();

while ($order = $ordersStmt->fetch()) {
    if (isset($order['entity_id'])) {
        array_push($orderIds, $order['entity_id']);
    }
}

if (count($orderIds)) {
    foreach ($orderIds as $orderId) {
        $row = $connection
            ->query("SELECT * FROM {$orderGridWarehouseTable} WHERE `entity_id` = {$connection->quote($orderId)}")
            ->fetch();
        if (empty($row)) {
            $connection->query(
                "INSERT INTO {$orderGridWarehouseTable} (`entity_id`, `stock_id`) VALUES (".
                $connection->quote($orderId).', '.$connection->quote($defaultStockId).
                ")"
            );
        }
    }
}

/* Invoices */
$invoiceTable = $installer->getTable('sales/invoice');
$invoiceItemTable = $installer->getTable('sales/invoice_item');
$invoiceGridWarehouseTable = $installer->getTable('warehouse/invoice_grid_warehouse');

$installer->run("UPDATE `{$invoiceItemTable}` SET `stock_id` = {$defaultStockId} WHERE `stock_id` IS NULL");

$invoices = array();
$invoicesStmt = $connection->query("SELECT * FROM {$invoiceTable}");
$invoiceIds = array();

while ($invoice = $invoicesStmt->fetch()) {
    if (isset($invoice['entity_id'])) {
        array_push($invoiceIds, $invoice['entity_id']);
    }
}

if (count($invoiceIds)) {
    foreach ($invoiceIds as $invoiceId) {
        $row = $connection
            ->query("SELECT * FROM {$invoiceGridWarehouseTable} WHERE `entity_id` = {$connection->quote($invoiceId)}")
            ->fetch();
        if (empty($row)) {
            $connection->query(
                "INSERT INTO {$invoiceGridWarehouseTable} (`entity_id`, `stock_id`) VALUES (".
                $connection->quote($invoiceId).', '.$connection->quote($defaultStockId).
                ")"
            );
        }
    }
}

/* Shipments */
$shipmentTable = $installer->getTable('sales/shipment');
$shipmentItemTable = $installer->getTable('sales/shipment_item');
$shipmentGridWarehouseTable = $installer->getTable('warehouse/shipment_grid_warehouse');

$installer->run("UPDATE `{$shipmentItemTable}` SET `stock_id` = {$defaultStockId} WHERE `stock_id` IS NULL");

$shipments = array();
$shipmentsStmt = $connection->query("SELECT * FROM {$shipmentTable}");
$shipmentIds = array();

while ($shipment = $shipmentsStmt->fetch()) {
    if (isset($shipment['entity_id'])) {
        array_push($shipmentIds, $shipment['entity_id']);
    }
}

if (count($shipmentIds)) {
    foreach ($shipmentIds as $shipmentId) {
        $row = $connection
            ->query("SELECT * FROM {$shipmentGridWarehouseTable} WHERE `entity_id` = {$connection->quote($shipmentId)}")
            ->fetch();
        if (empty($row)) {
            $connection->query(
                "INSERT INTO {$shipmentGridWarehouseTable} (`entity_id`, `stock_id`) VALUES (".
                $connection->quote($shipmentId).', '.$connection->quote($defaultStockId).
                ")"
            );
        }
    }
}

/* Credit Memos */
$creditmemoTable = $installer->getTable('sales/creditmemo');
$creditmemoItemTable = $installer->getTable('sales/creditmemo_item');
$creditmemoGridWarehouseTable = $installer->getTable('warehouse/creditmemo_grid_warehouse');

$installer->run("UPDATE `{$creditmemoItemTable}` SET `stock_id` = {$defaultStockId} WHERE `stock_id` IS NULL");

$creditmemos = array();
$creditmemosStmt = $connection->query("SELECT * FROM {$creditmemoTable}");
$creditmemoIds = array();

while ($creditmemo = $creditmemosStmt->fetch()) {
    if (isset($creditmemo['entity_id'])) {
        array_push($creditmemoIds, $creditmemo['entity_id']);
    }
}

if (count($creditmemoIds)) {
    foreach ($creditmemoIds as $creditmemoId) {
        $row = $connection
            ->query("SELECT * FROM {$creditmemoGridWarehouseTable} WHERE `entity_id` = {$connection->quote($creditmemoId)}")
            ->fetch();
        if (empty($row)) {
            $connection->query(
                "INSERT INTO {$creditmemoGridWarehouseTable} (`entity_id`, `stock_id`) VALUES (".
                $connection->quote($creditmemoId).', '.$connection->quote($defaultStockId).
                ")"
            );
        }
    }
}

$installer->endSetup();
