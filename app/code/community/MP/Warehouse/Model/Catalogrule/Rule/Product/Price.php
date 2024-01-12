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
 * Rule product price
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Catalogrule_Rule_Product_Price extends Mage_CatalogRule_Model_Rule_Product_Price
{
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
        /** @var MP_Warehouse_Model_Mysql4_Catalogrule_Rule_Product_Price $resource */
        $resource = $this->_getResource();
        $resource->applyPriceRuleToIndexTable2(
            clone $select,
            $indexTable,
            $entityId,
            $customerGroupId,
            $stockId,
            $websiteId,
            $updateFields,
            $websiteDate
        );

        return $this;
    }
}
