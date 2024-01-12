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

/**
 * Catalog search advanced resource
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Catalogsearch_Advanced 
    extends Mage_CatalogSearch_Model_Mysql4_Advanced
{
    /**
     * Get warehouse helper
     *
     * @return MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Add filter by indexable attribute
     *
     * @param Mage_CatalogSearch_Model_Resource_Advanced_Collection $collection
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param string|array $value
     * 
     * @return bool
     */
    public function addIndexableAttributeModifiedFilter($collection, $attribute, $value)
    {
        if ($attribute->getIndexType() == 'decimal') {
            $table          = $this->getTable('catalog/product_index_eav_decimal');
        } else {
            $table          = $this->getTable('catalog/product_index_eav');
        }

        $tableAlias     = 'a_' . $attribute->getAttributeId();
        $storeId        = Mage::app()->getStore()->getId();
        $select         = $collection->getSelect();
        if (is_array($value)) {
            if (isset($value['from']) && isset($value['to'])) {
                if (empty($value['from']) && empty($value['to'])) {
                    return false;
                }
            }
        }

        $select->distinct(true);
        $conditions         = array(
            "e.entity_id={$tableAlias}.entity_id", 
            "{$tableAlias}.attribute_id={$attribute->getAttributeId()}", 
            "{$tableAlias}.store_id={$storeId}", 
            "{$tableAlias}.stock_id={$this->getWarehouseHelper()
                ->getProductHelper()
                ->getCollectionStockId($collection)}", 
        );
        $select->join(array($tableAlias => $table), implode(' AND ', $conditions), array());
        if (is_array($value) && (isset($value['from']) || isset($value['to']))) {
            if (isset($value['from']) && !empty($value['from'])) {
                $select->where("{$tableAlias}.value >= ?", $value['from']);
            }

            if (isset($value['to']) && !empty($value['to'])) {
                $select->where("{$tableAlias}.value <= ?", $value['to']);
            }

            return true;
        }

        $select->where("{$tableAlias}.value IN(?)", $value);
        return true;
    }
}
