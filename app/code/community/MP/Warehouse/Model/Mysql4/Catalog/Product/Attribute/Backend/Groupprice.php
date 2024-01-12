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
 * Product group price backend attribute resource
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Catalog_Product_Attribute_Backend_Groupprice
    extends Mage_Catalog_Model_Resource_Product_Attribute_Backend_Groupprice
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
     * Load group prices for product
     *
     * @param int $productId
     * @param int $websiteId
     * @param int $stockId
     * @return array
     */
    public function loadPriceData2($productId, $websiteId = null, $stockId = null)
    {
        $adapter = $this->_getReadAdapter();
        $columns = array(
            'price_id'   => $this->getIdFieldName(),
            'website_id' => 'website_id',
            'all_groups' => 'all_groups',
            'stock_id'   => 'stock_id',
            'cust_group' => 'customer_group_id',
            'price'      => 'value'
        );

        $select = $adapter->select()
            ->from($this->getMainTable(), $columns)
            ->where('entity_id=?', $productId);

        if (!is_null($websiteId)) {
            if ($websiteId == '0') {
                $select->where('website_id = ?', $websiteId);
            } else {
                $select->where('website_id IN(?)', array(0, $websiteId));
            }
        }

        if (!is_null($stockId)) {
            if ($stockId == '') {
                $select->where('(stock_id IS NULL) OR (stock_id = ?)', $stockId);
            } else {
                $select->where("(stock_id = ?) OR (stock_id IS NULL) OR (stock_id = '')", $stockId);
            }
        }

        return $adapter->fetchAll($select);
    }

    /**
     * Delete group prices for product
     *
     * @param int $productId
     * @param int $websiteId
     * @param int $priceId
     * @return int
     */
    public function deletePriceData2($productId, $websiteId = null, $priceId = null)
    {
        $adapter = $this->_getWriteAdapter();

        $conds = array(
            $adapter->quoteInto('entity_id = ?', $productId)
        );

        if (!is_null($websiteId)) {
            $conds[] = $adapter->quoteInto('website_id = ?', $websiteId);
        }

        if (!is_null($priceId)) {
            $conds[] = $adapter->quoteInto($this->getIdFieldName() . ' = ?', $priceId);
        }

        $where = implode(' AND ', $conds);

        return $adapter->delete($this->getMainTable(), $where);
    }
}
