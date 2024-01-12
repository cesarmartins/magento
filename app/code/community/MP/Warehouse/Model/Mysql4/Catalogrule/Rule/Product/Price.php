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
 * Catalog rule resource
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Catalogrule_Rule_Product_Price
    extends Mage_CatalogRule_Model_Mysql4_Rule_Product_Price
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
     * Get version helper
     *
     * @return MP_Warehouse_Helper_Core_Version
     */
    public function getVersionHelper()
    {
        return $this->getWarehouseHelper()->getVersionHelper();
    }

    /**
     * Apply price rule price to price index table
     *
     * @param Varien_Db_Select $select
     * @param array|string $indexTable
     * @param string $entityId
     * @param string $customerGroupId
     * @param int $stockId
     * @param string $websiteId
     * @param array $updateFields
     * @param string $websiteDate
     * @return $this
     */
    public function applyPriceRuleToIndexTable2(
        Varien_Db_Select $select,
        $indexTable,
        $entityId,
        $customerGroupId,
        $stockId,
        $websiteId,
        $updateFields,
        $websiteDate
    ) {
        if (empty($updateFields)) {
            return $this;
        }

        $indexAlias = $indexTable;

        if (is_array($indexTable)) {
            foreach ($indexTable as $k => $v) {
                $indexAlias = (is_string($k)) ? $k : $v;

                break;
            }
        }

        if ($this->getVersionHelper()->isGe1600()) {
            $where = implode(
                ' AND ',
                array(
                    "(rp.product_id = {$entityId})",
                    "(rp.website_id = {$websiteId})",
                    "(rp.customer_group_id = {$customerGroupId})",
                    "(rp.stock_id = {$stockId})"
                )
            );

            $select->join(array('rp' => $this->getMainTable()), "rp.rule_date = {$websiteDate}", array())->where($where);
        } else {
            $select->join(
                array('rp' => $this->getMainTable()),
                "rp.product_id = {$entityId} AND rp.website_id = {$websiteId}".
                " AND rp.customer_group_id = {$customerGroupId}".
                " AND rp.stock_id = {$stockId}".
                " AND rp.rule_date = {$websiteDate}",
                array()
            );
        }

        foreach ($updateFields as $priceField) {
            $priceCond = $this->_getWriteAdapter()->quoteIdentifier(array($indexAlias, $priceField));

            if ($this->getVersionHelper()->isGe1600()) {
                $priceExpr = $this->_getWriteAdapter()->getCheckSql(
                    "rp.rule_price < {$priceCond}", 'rp.rule_price', $priceCond
                );
            } else {
                $priceExpr = new Zend_Db_Expr("IF(rp.rule_price < {$priceCond}, rp.rule_price, {$priceCond})");
            }

            $select->columns(array($priceField => $priceExpr));
        }

        $query = $select->crossUpdateFromSelect($indexTable);
        $this->_getWriteAdapter()->query($query);

        return $this;
    }
}
