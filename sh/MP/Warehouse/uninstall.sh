#!/bin/bash

CWD="$(pwd)/../../.."

CONFIG_FILE="$CWD/app/etc/local.xml"
INDEXER_FILE="$CWD/shell/indexer.php"

PHP_BIN=`which php`

echo "Do you want to completely uninstall the extension?(y/n)"
read UNINSTALL

function getParam()
{
  RETVAL=$(grep -Eoh "<$1>(<!\[CDATA\[)?(.*)(\]\]>)?<\/$1>" $CONFIG_FILE | sed "s#<$1><!\[CDATA\[##g;s#\]\]><\/$1>##g")

  echo -e "$RETVAL"
}

function getSql()
{
	PREFIX=$(getParam "table_prefix")

	SQL_TEXT="
	DROP TABLE IF EXISTS ${PREFIX}warehouse_store;
	DROP TABLE IF EXISTS ${PREFIX}warehouse_shipping_carrier;
	DROP TABLE IF EXISTS ${PREFIX}warehouse_area;
	DROP TABLE IF EXISTS ${PREFIX}warehouse_customer_group;
	DROP TABLE IF EXISTS ${PREFIX}warehouse_currency;
	DROP TABLE IF EXISTS ${PREFIX}warehouse_flat_creditmemo_grid_warehouse;
	DROP TABLE IF EXISTS ${PREFIX}warehouse_flat_invoice_grid_warehouse;
	DROP TABLE IF EXISTS ${PREFIX}warehouse_flat_shipment_grid_warehouse;
	DROP TABLE IF EXISTS ${PREFIX}warehouse_flat_order_grid_warehouse;

	DROP TABLE IF EXISTS ${PREFIX}catalog_product_shelf;
	DROP TABLE IF EXISTS ${PREFIX}catalog_product_stock_price;
	DROP TABLE IF EXISTS ${PREFIX}catalog_product_stock_priority;
	DROP TABLE IF EXISTS ${PREFIX}catalog_product_stock_shipping_carrier;
	DROP TABLE IF EXISTS ${PREFIX}catalog_product_batch_price;
	DROP TABLE IF EXISTS ${PREFIX}catalog_product_index_batch_price;
	DROP TABLE IF EXISTS ${PREFIX}catalog_product_batch_special_price;
	DROP TABLE IF EXISTS ${PREFIX}catalog_product_index_batch_special_price;
	DROP TABLE IF EXISTS ${PREFIX}catalog_product_stock_tax_class;

	ALTER TABLE ${PREFIX}sales_flat_quote DROP items_qtys;

	DELETE FROM ${PREFIX}shipping_tablerate WHERE (warehouse_id IS NOT NULL) OR (method_id IS NOT NULL);
	ALTER TABLE ${PREFIX}shipping_tablerate DROP warehouse_id;
	ALTER TABLE ${PREFIX}shipping_tablerate DROP FOREIGN KEY FK_SHIPPING_TABLERATE_METHOD_ID;
	ALTER TABLE ${PREFIX}shipping_tablerate DROP method_id;
	DROP TABLE IF EXISTS ${PREFIX}shipping_tablerate_method;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_eav;
	ALTER TABLE ${PREFIX}catalog_product_index_eav DROP FOREIGN KEY FK_CATALOG_PRODUCT_INDEX_EAV_STOCK_ID;
	ALTER TABLE ${PREFIX}catalog_product_index_eav DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_eav_idx;
	ALTER TABLE ${PREFIX}catalog_product_index_eav_idx DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_eav_tmp;
	ALTER TABLE ${PREFIX}catalog_product_index_eav_tmp DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_eav_decimal;
	ALTER TABLE ${PREFIX}catalog_product_index_eav_decimal DROP FOREIGN KEY FK_CATALOG_PRODUCT_INDEX_EAV_DECIMAL_STOCK_ID;
	ALTER TABLE ${PREFIX}catalog_product_index_eav_decimal DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_eav_decimal_idx;
	ALTER TABLE ${PREFIX}catalog_product_index_eav_decimal_idx DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_eav_decimal_tmp;
	ALTER TABLE ${PREFIX}catalog_product_index_eav_decimal_tmp DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price;
	ALTER TABLE ${PREFIX}catalog_product_index_price DROP FOREIGN KEY FK_CATALOG_PRODUCT_INDEX_PRICE_STOCK;
	ALTER TABLE ${PREFIX}catalog_product_index_price DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_idx;
	ALTER TABLE ${PREFIX}catalog_product_index_price_idx DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_tmp;
	ALTER TABLE ${PREFIX}catalog_product_index_price_tmp DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_final_idx;
	ALTER TABLE ${PREFIX}catalog_product_index_price_final_idx DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_final_tmp;
	ALTER TABLE ${PREFIX}catalog_product_index_price_final_tmp DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_bundle_idx;
	ALTER TABLE ${PREFIX}catalog_product_index_price_bundle_idx DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_bundle_tmp;
	ALTER TABLE ${PREFIX}catalog_product_index_price_bundle_tmp DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_bundle_opt_idx;
	ALTER TABLE ${PREFIX}catalog_product_index_price_bundle_opt_idx DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_bundle_opt_tmp;
	ALTER TABLE ${PREFIX}catalog_product_index_price_bundle_opt_tmp DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_bundle_sel_idx;
	ALTER TABLE ${PREFIX}catalog_product_index_price_bundle_sel_idx DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_bundle_sel_tmp;
	ALTER TABLE ${PREFIX}catalog_product_index_price_bundle_sel_tmp DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_cfg_opt_agr_idx;
	ALTER TABLE ${PREFIX}catalog_product_index_price_cfg_opt_agr_idx DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_cfg_opt_agr_tmp;
	ALTER TABLE ${PREFIX}catalog_product_index_price_cfg_opt_agr_tmp DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_cfg_opt_idx;
	ALTER TABLE ${PREFIX}catalog_product_index_price_cfg_opt_idx DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_cfg_opt_tmp;
	ALTER TABLE ${PREFIX}catalog_product_index_price_cfg_opt_tmp DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_opt_agr_idx;
	ALTER TABLE ${PREFIX}catalog_product_index_price_opt_agr_idx DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_opt_agr_tmp;
	ALTER TABLE ${PREFIX}catalog_product_index_price_opt_agr_tmp DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_opt_idx;
	ALTER TABLE ${PREFIX}catalog_product_index_price_opt_idx DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_opt_tmp;
	ALTER TABLE ${PREFIX}catalog_product_index_price_opt_tmp DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_downlod_idx;
	ALTER TABLE ${PREFIX}catalog_product_index_price_downlod_idx DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_price_downlod_tmp;
	ALTER TABLE ${PREFIX}catalog_product_index_price_downlod_tmp DROP stock_id;

	ALTER TABLE ${PREFIX}sales_flat_quote_item DROP FOREIGN KEY FK_SALES_QUOTE_ITEM_STOCK;
	ALTER TABLE ${PREFIX}sales_flat_quote_item DROP stock_id;

	ALTER TABLE ${PREFIX}sales_flat_order_item DROP FOREIGN KEY FK_SALES_ORDER_ITEM_STOCK;
	ALTER TABLE ${PREFIX}sales_flat_order_item DROP stock_id;

	ALTER TABLE ${PREFIX}sales_flat_invoice_item DROP FOREIGN KEY FK_SALES_INVOICE_ITEM_STOCK;
	ALTER TABLE ${PREFIX}sales_flat_invoice_item DROP stock_id;

	ALTER TABLE ${PREFIX}sales_flat_shipment_item DROP FOREIGN KEY FK_SALES_SHIPMENT_ITEM_STOCK;
	ALTER TABLE ${PREFIX}sales_flat_shipment_item DROP stock_id;

	ALTER TABLE ${PREFIX}sales_flat_creditmemo_item DROP FOREIGN KEY FK_SALES_CREDITMEMO_ITEM_STOCK;
	ALTER TABLE ${PREFIX}sales_flat_creditmemo_item DROP stock_id;

	ALTER TABLE ${PREFIX}sales_flat_quote_address DROP FOREIGN KEY FK_SALES_QUOTE_ADDRESS_STOCK;
	ALTER TABLE ${PREFIX}sales_flat_quote_address DROP stock_id;

	ALTER TABLE ${PREFIX}sales_flat_order_address DROP FOREIGN KEY FK_SALES_ORDER_ADDRESS_STOCK;
	ALTER TABLE ${PREFIX}sales_flat_order_address DROP stock_id;

	DELETE FROM ${PREFIX}catalog_product_entity_tier_price WHERE stock_id IS NOT NULL;

	ALTER TABLE ${PREFIX}catalog_product_entity_tier_price DROP FOREIGN KEY FK_CATALOG_PRODUCT_ENTITY_TIER_PRICE_STOCK;
	ALTER TABLE ${PREFIX}catalog_product_entity_tier_price DROP stock_id;

	TRUNCATE TABLE ${PREFIX}catalog_product_index_tier_price;

	ALTER TABLE ${PREFIX}catalog_product_index_tier_price DROP FOREIGN KEY FK_CATALOG_PRODUCT_INDEX_TIER_PRICE_STOCK;
	ALTER TABLE ${PREFIX}catalog_product_index_tier_price DROP stock_id;

	DELETE FROM ${PREFIX}eav_attribute WHERE (attribute_code = 'stock_id') AND (entity_type_id = (
		SELECT entity_type_id FROM ${PREFIX}eav_entity_type WHERE entity_type_code = 'customer_address'
	));

	DELETE FROM ${PREFIX}eav_attribute WHERE (attribute_code = 'warehouse_id') AND (entity_type_id = (
		SELECT entity_type_id FROM ${PREFIX}eav_entity_type WHERE entity_type_code = 'customer_address'
	));

	DELETE FROM ${PREFIX}customer_eav_attribute WHERE attribute_id NOT IN (SELECT attribute_id FROM ${PREFIX}eav_attribute);

	DROP TABLE IF EXISTS ${PREFIX}warehouse;

	DELETE FROM ${PREFIX}cataloginventory_stock WHERE stock_id <> 1;

	UPDATE ${PREFIX}cataloginventory_stock SET stock_name = 'Default' WHERE stock_id = 1;

	DELETE FROM ${PREFIX}core_resource WHERE code = 'warehouse_setup';
	DELETE FROM ${PREFIX}core_resource WHERE code = 'geocoder_setup';
	DELETE FROM ${PREFIX}core_resource WHERE code = 'customerlocator_setup';
	DELETE FROM ${PREFIX}core_resource WHERE code = 'geoip_setup';
	DELETE FROM ${PREFIX}core_resource WHERE code = 'shippingtablerate_setup';
	DELETE FROM ${PREFIX}core_resource WHERE code = 'innocore_setup';
	"
	echo -e "$SQL_TEXT"
}


function executeSql()
{
	USER=$(getParam "username")
	PASS=$(getParam "password")
	DBNAME=$(getParam "dbname")
	SQL_TEXT=$(getSql)

	#splits sql text with semicolon ;
	SQLS=$(echo $SQL_TEXT | sed ':a;N;$!ba;s/\n//g' | sed s/\;/\;\\n/g)

	#convert sql text into array
	IFS=$'\n' read -d '' -r -a SQL_ARRAY <<< "$SQLS"

	#loop through sql array and execute
	COUNTER=0
	while [  $COUNTER -lt ${#SQL_ARRAY[@]} ]; do
	 sql=${SQL_ARRAY[$COUNTER]}
	 mysql -u $USER -p$PASS $DBNAME -e "$sql"
	 let COUNTER=COUNTER+1
	done
}

if [ "$UNINSTALL" == "y" ]; then

    rm -fr $CWD/app/code/local/Innoexts/Core/
    rm -fr $CWD/skin/adminhtml/default/default/template/innoexts/core/
    rm -f $CWD/app/etc/modules/Innoexts_Core.xml
    rm -f $CWD/app/locale/en_US/Innoexts_Core.csv
    rm -fr $CWD/js/innoexts/core/
    rm -fr $CWD/shell/Innoexts/Core/

    rm -fr $CWD/app/code/local/Innoexts/GeoCoder/
    rm -f $CWD/app/etc/modules/Innoexts_GeoCoder.xml
    rm -f $CWD/app/locale/en_US/Innoexts_GeoCoder.csv

    rm -fr $CWD/app/code/local/Innoexts/GeoIp/
    rm -f $CWD/app/etc/modules/Innoexts_GeoIp.xml

    rm -fr $CWD/app/code/local/Innoexts/CustomerLocator/
    rm -f $CWD/app/design/frontend/base/default/layout/customerlocator.xml
    rm -fr $CWD/app/design/frontend/base/default/template/customerlocator/
    rm -f $CWD/app/design/frontend/default/iphone/layout/customerlocator.xml
    rm -f $CWD/app/design/frontend/default/modern/layout/customerlocator.xml
    rm -f $CWD/app/design/frontend/rwd/default/layout/customerlocator.xml
    rm -f $CWD/app/etc/modules/Innoexts_CustomerLocator.xml
    rm -f $CWD/app/locale/en_US/Innoexts_CustomerLocator.csv
    rm -fr $CWD/skin/frontend/base/default/css/customerlocator/
    rm -fr $CWD/skin/frontend/default/blank/css/customerlocator/
    rm -fr $CWD/skin/frontend/default/blue/css/customerlocator/
    rm -fr $CWD/skin/frontend/default/blue/images/customerlocator/
    rm -fr $CWD/skin/frontend/default/default/css/customerlocator/
    rm -fr $CWD/skin/frontend/default/default/images/customerlocator/
    rm -fr $CWD/skin/frontend/default/iphone/css/customerlocator/
    rm -fr $CWD/skin/frontend/default/modern/css/customerlocator/
    rm -fr $CWD/skin/frontend/rwd/default/css/customerlocator/
    rm -fr $CWD/sh/Innoexts/CustomerLocator/
    rm -f $CWD/var/connect/Innoexts_CustomerLocator.xml

    rm -fr $CWD/app/code/local/Innoexts/ShippingTablerate/
    rm -f $CWD/app/design/adminhtml/default/default/layout/shippingtablerate.xml
    rm -fr $CWD/app/design/adminhtml/default/default/template/shippingtablerate/
    rm -f $CWD/app/etc/modules/Innoexts_ShippingTablerate.xml
    rm -f $CWD/app/locale/en_US/Innoexts_ShippingTablerate.csv
    rm -fr $CWD/skin/adminhtml/default/default/shippingtablerate/
    rm -fr $CWD/skin/adminhtml/default/default/images/shippingtablerate/

    rm -fr $CWD/app/code/local/Innoexts/Warehouse/
    rm -f $CWD/app/design/adminhtml/default/default/layout/warehouse.xml
    rm -fr $CWD/app/design/adminhtml/default/default/template/warehouse/
    rm -f $CWD/app/design/frontend/base/default/layout/warehouse.xml
    rm -fr $CWD/app/design/frontend/base/default/template/warehouse/
    rm -f $CWD/app/design/frontend/default/iphone/layout/warehouse.xml
    rm -fr $CWD/app/design/frontend/default/iphone/template/warehouse/
    rm -f $CWD/app/design/frontend/default/modern/layout/warehouse.xml
    rm -fr $CWD/app/design/frontend/default/modern/template/warehouse/
    rm -f $CWD/app/design/frontend/rwd/default/layout/warehouse.xml
    rm -fr $CWD/app/design/frontend/rwd/default/template/warehouse/
    rm -f $CWD/app/etc/modules/MP_Warehouse.xml
    rm -f $CWD/app/locale/en_US/MP_Warehouse.csv
    rm -fr $CWD/app/locale/en_US/template/email/sales/warehouse/
    rm -fr $CWD/js/innoexts/warehouse/
    rm -fr $CWD/shell/Innoexts/Warehouse/
    rm -fr $CWD/skin/adminhtml/default/default/warehouse/
    rm -fr $CWD/skin/adminhtml/default/default/images/warehouse/
    rm -fr $CWD/skin/frontend/base/default/js/warehouse/
    rm -fr $CWD/skin/frontend/base/default/css/warehouse/
    rm -fr $CWD/skin/frontend/default/blank/css/warehouse/
    rm -fr $CWD/skin/frontend/default/blue/css/warehouse/
    rm -fr $CWD/skin/frontend/default/blue/images/warehouse/
    rm -fr $CWD/skin/frontend/default/default/css/warehouse/
    rm -fr $CWD/skin/frontend/default/default/images/warehouse/
    rm -fr $CWD/skin/frontend/default/iphone/css/warehouse/
    rm -fr $CWD/skin/frontend/default/modern/css/warehouse/
    rm -fr $CWD/skin/frontend/rwd/default/css/warehouse/
    rm -fr $CWD/sql/Innoexts/Warehouse/
    rm -fr $CWD/var/import/Innoexts/Warehouse/
    rm -f $CWD/var/connect/MP_Warehouse.xml

	rm -fr $CWD/var/cache

	$(executeSql)

	$PHP_BIN $INDEXER_FILE --reindexall
fi
