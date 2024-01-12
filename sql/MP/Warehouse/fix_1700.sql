ALTER TABLE `catalog_product_index_price` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price`;

ALTER TABLE `catalog_product_index_price_idx` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price`;

ALTER TABLE `catalog_product_index_price_tmp` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price`;

ALTER TABLE `catalog_product_index_price_final_idx` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `base_group_price`;

ALTER TABLE `catalog_product_index_price_final_tmp` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `base_group_price`;



ALTER TABLE `catalog_product_index_price_bundle_idx` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price_percent`;

ALTER TABLE `catalog_product_index_price_bundle_tmp` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price_percent`;

ALTER TABLE `catalog_product_index_price_bundle_opt_idx` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `alt_group_price`;

ALTER TABLE `catalog_product_index_price_bundle_opt_tmp` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `alt_group_price`;

ALTER TABLE `catalog_product_index_price_bundle_sel_idx` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price`;

ALTER TABLE `catalog_product_index_price_bundle_sel_tmp` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price`;

ALTER TABLE `catalog_product_index_price_cfg_opt_agr_idx` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price`;

ALTER TABLE `catalog_product_index_price_cfg_opt_agr_tmp` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price`;

ALTER TABLE `catalog_product_index_price_cfg_opt_idx` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price`;

ALTER TABLE `catalog_product_index_price_cfg_opt_tmp` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price`;

ALTER TABLE `catalog_product_index_price_opt_agr_idx` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price`;

ALTER TABLE `catalog_product_index_price_opt_agr_tmp` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price`;

ALTER TABLE `catalog_product_index_price_opt_idx` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price`;

ALTER TABLE `catalog_product_index_price_opt_tmp` CHANGE 
    COLUMN `stock_id` `stock_id` smallint(6) unsigned null default null AFTER `group_price`;

