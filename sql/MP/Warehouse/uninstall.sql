DROP TABLE IF EXISTS `warehouse_store`;
DROP TABLE IF EXISTS `warehouse_shipping_carrier`;
DROP TABLE IF EXISTS `warehouse_area`;
DROP TABLE IF EXISTS `warehouse_customer_group`;
DROP TABLE IF EXISTS `warehouse_currency`;
DROP TABLE IF EXISTS `warehouse_flat_creditmemo_grid_warehouse`;
DROP TABLE IF EXISTS `warehouse_flat_invoice_grid_warehouse`;
DROP TABLE IF EXISTS `warehouse_flat_shipment_grid_warehouse`;
DROP TABLE IF EXISTS `warehouse_flat_order_grid_warehouse`;

DROP TABLE IF EXISTS `catalog_product_shelf`;
DROP TABLE IF EXISTS `catalog_product_stock_price`;
DROP TABLE IF EXISTS `catalog_product_stock_priority`;
DROP TABLE IF EXISTS `catalog_product_stock_shipping_carrier`;
DROP TABLE IF EXISTS `catalog_product_batch_price`;
DROP TABLE IF EXISTS `catalog_product_index_batch_price`;
DROP TABLE IF EXISTS `catalog_product_batch_special_price`;
DROP TABLE IF EXISTS `catalog_product_index_batch_special_price`;
DROP TABLE IF EXISTS `catalog_product_stock_tax_class`;

ALTER TABLE `sales_flat_quote` DROP `items_qtys`;

DELETE FROM `shipping_tablerate` WHERE (`warehouse_id` IS NOT NULL) OR (`method_id` IS NOT NULL);
ALTER TABLE `shipping_tablerate` DROP `warehouse_id`;
ALTER TABLE `shipping_tablerate` DROP FOREIGN KEY FK_SHIPPING_TABLERATE_METHOD_ID;
ALTER TABLE `shipping_tablerate` DROP `method_id`;
DROP TABLE IF EXISTS `shipping_tablerate_method`;



TRUNCATE TABLE `catalog_product_index_eav`;
ALTER TABLE `catalog_product_index_eav` DROP FOREIGN KEY FK_CATALOG_PRODUCT_INDEX_EAV_STOCK_ID;
ALTER TABLE `catalog_product_index_eav` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_eav_idx`;
ALTER TABLE `catalog_product_index_eav_idx` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_eav_tmp`;
ALTER TABLE `catalog_product_index_eav_tmp` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_eav_decimal`;
ALTER TABLE `catalog_product_index_eav_decimal` DROP FOREIGN KEY FK_CATALOG_PRODUCT_INDEX_EAV_DECIMAL_STOCK_ID;
ALTER TABLE `catalog_product_index_eav_decimal` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_eav_decimal_idx`;
ALTER TABLE `catalog_product_index_eav_decimal_idx` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_eav_decimal_tmp`;
ALTER TABLE `catalog_product_index_eav_decimal_tmp` DROP `stock_id`;



TRUNCATE TABLE `catalog_product_index_price`;
ALTER TABLE `catalog_product_index_price` DROP FOREIGN KEY FK_CATALOG_PRODUCT_INDEX_PRICE_STOCK;
ALTER TABLE `catalog_product_index_price` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_idx`;
ALTER TABLE `catalog_product_index_price_idx` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_tmp`;
ALTER TABLE `catalog_product_index_price_tmp` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_final_idx`;
ALTER TABLE `catalog_product_index_price_final_idx` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_final_tmp`;
ALTER TABLE `catalog_product_index_price_final_tmp` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_bundle_idx`;
ALTER TABLE `catalog_product_index_price_bundle_idx` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_bundle_tmp`;
ALTER TABLE `catalog_product_index_price_bundle_tmp` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_bundle_opt_idx`;
ALTER TABLE `catalog_product_index_price_bundle_opt_idx` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_bundle_opt_tmp`;
ALTER TABLE `catalog_product_index_price_bundle_opt_tmp` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_bundle_sel_idx`;
ALTER TABLE `catalog_product_index_price_bundle_sel_idx` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_bundle_sel_tmp`;
ALTER TABLE `catalog_product_index_price_bundle_sel_tmp` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_cfg_opt_agr_idx`;
ALTER TABLE `catalog_product_index_price_cfg_opt_agr_idx` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_cfg_opt_agr_tmp`;
ALTER TABLE `catalog_product_index_price_cfg_opt_agr_tmp` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_cfg_opt_idx`;
ALTER TABLE `catalog_product_index_price_cfg_opt_idx` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_cfg_opt_tmp`;
ALTER TABLE `catalog_product_index_price_cfg_opt_tmp` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_opt_agr_idx`;
ALTER TABLE `catalog_product_index_price_opt_agr_idx` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_opt_agr_tmp`;
ALTER TABLE `catalog_product_index_price_opt_agr_tmp` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_opt_idx`;
ALTER TABLE `catalog_product_index_price_opt_idx` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_opt_tmp`;
ALTER TABLE `catalog_product_index_price_opt_tmp` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_downlod_idx`;
ALTER TABLE `catalog_product_index_price_downlod_idx` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_price_downlod_tmp`;
ALTER TABLE `catalog_product_index_price_downlod_tmp` DROP `stock_id`;



ALTER TABLE `sales_flat_quote_item` DROP FOREIGN KEY FK_SALES_QUOTE_ITEM_STOCK;
ALTER TABLE `sales_flat_quote_item` DROP `stock_id`;

ALTER TABLE `sales_flat_order_item` DROP FOREIGN KEY FK_SALES_ORDER_ITEM_STOCK;
ALTER TABLE `sales_flat_order_item` DROP `stock_id`;

ALTER TABLE `sales_flat_invoice_item` DROP FOREIGN KEY FK_SALES_INVOICE_ITEM_STOCK;
ALTER TABLE `sales_flat_invoice_item` DROP `stock_id`;

ALTER TABLE `sales_flat_shipment_item` DROP FOREIGN KEY FK_SALES_SHIPMENT_ITEM_STOCK;
ALTER TABLE `sales_flat_shipment_item` DROP `stock_id`;

ALTER TABLE `sales_flat_creditmemo_item` DROP FOREIGN KEY FK_SALES_CREDITMEMO_ITEM_STOCK;
ALTER TABLE `sales_flat_creditmemo_item` DROP `stock_id`;

ALTER TABLE `sales_flat_quote_address` DROP FOREIGN KEY FK_SALES_QUOTE_ADDRESS_STOCK;
ALTER TABLE `sales_flat_quote_address` DROP `stock_id`;

ALTER TABLE `sales_flat_order_address` DROP FOREIGN KEY FK_SALES_ORDER_ADDRESS_STOCK;
ALTER TABLE `sales_flat_order_address` DROP `stock_id`;

DELETE FROM `catalog_product_entity_tier_price` WHERE `stock_id` IS NOT NULL;

ALTER TABLE `catalog_product_entity_tier_price` DROP FOREIGN KEY FK_CATALOG_PRODUCT_ENTITY_TIER_PRICE_STOCK;
ALTER TABLE `catalog_product_entity_tier_price` DROP `stock_id`;

TRUNCATE TABLE `catalog_product_index_tier_price`;

ALTER TABLE `catalog_product_index_tier_price` DROP FOREIGN KEY FK_CATALOG_PRODUCT_INDEX_TIER_PRICE_STOCK;
ALTER TABLE `catalog_product_index_tier_price` DROP `stock_id`;

DELETE FROM `eav_attribute` WHERE (`attribute_code` = 'stock_id') AND (`entity_type_id` = (
    SELECT `entity_type_id` FROM `eav_entity_type` WHERE `entity_type_code` = 'customer_address'
));

DELETE FROM `eav_attribute` WHERE (`attribute_code` = 'warehouse_id') AND (`entity_type_id` = (
    SELECT `entity_type_id` FROM `eav_entity_type` WHERE `entity_type_code` = 'customer_address'
));

DELETE FROM `customer_eav_attribute` WHERE `attribute_id` NOT IN (SELECT `attribute_id` FROM `eav_attribute`);

DROP TABLE IF EXISTS `warehouse`;

DELETE FROM `cataloginventory_stock` WHERE `stock_id` <> 1;

UPDATE `cataloginventory_stock` SET `stock_name` = 'Default' WHERE `stock_id` = 1;

DELETE FROM `core_resource` WHERE `code` = 'warehouse_setup';
