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
 * Product collection
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Catalog_Product_Collection 
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
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
     * Add group price data to loaded items
     *
     * @return MP_Warehouse_Model_Mysql4_Catalog_Product_Collection
     */
    public function addGroupPriceData()
    {
        $helper = $this->getWarehouseHelper();
        if ($this->getFlag('group_price_added')) {
            return $this;
        }

        $groupPrices = array();
        $productIds = array();
        foreach ($this->getItems() as $item) {
            $productIds[] = $item->getId();
            $groupPrices[$item->getId()] = array();
        }

        if (!count($productIds)) {
            return $this;
        }

        $websiteId          = null;
        $storeId            = $this->getStoreId();
        if ($helper->getProductPriceHelper()->isGlobalScope()) {
            $websiteId          = 0;
        } else if ($storeId) {
            $websiteId          = $helper->getWebsiteIdByStoreId($storeId);
        }

        $adapter            = $this->getConnection();
        $columns   = array(
            'price_id'      => 'value_id',
            'website_id'    => 'website_id',
            'stock_id'      => 'stock_id',
            'all_groups'    => 'all_groups',
            'cust_group'    => 'customer_group_id',
            'price'         => 'value',
            'product_id'    => 'entity_id',
        );
        $select  = $adapter->select()
            ->from($this->getTable('catalog/product_attribute_group_price'), $columns)
            ->where('entity_id IN(?)', $productIds)
            ->order(array('entity_id'));
        if ($websiteId == '0') {
            $select->where('website_id = ?', $websiteId);
        } else {
            $select->where('website_id IN(?)', array('0', $websiteId));
        }

        foreach ($adapter->fetchAll($select) as $row) {
            $groupPrices[$row['product_id']][] = array(
                'website_id'     => $row['website_id'],
                'stock_id'       => $row['stock_id'],
                'cust_group'     => $row['all_groups'] ? Mage_Customer_Model_Group::CUST_GROUP_ALL : $row['cust_group'],
                'price'          => $row['price'],
                'website_price'  => $row['price'],
            );
        }

        foreach ($this->getItems() as $item) {
            $data = $groupPrices[$item->getId()];
            $item->setGroupPrices($data);
            $helper->getProductPriceHelper()->setGroupPrice($item);
        }

        $this->setFlag('group_price_added', true);
        return $this;
    }
    /**
     * Add tier price data to loaded items
     *
     * @return MP_Warehouse_Model_Mysql4_Catalog_Product_Collection
     */
    public function addTierPriceData()
    {
        $helper = $this->getWarehouseHelper();
        if ($this->getFlag('tier_price_added')) {
            return $this;
        }

        $tierPrices = array();
        $productIds = array();
        foreach ($this->getItems() as $item) {
            $productIds[] = $item->getId();
            $tierPrices[$item->getId()] = array();
        }

        if (!count($productIds)) {
            return $this;
        }

        $websiteId          = null;
        $storeId            = $this->getStoreId();
        if ($helper->getProductPriceHelper()->isGlobalScope()) {
            $websiteId          = 0;
        } else if ($storeId) {
            $websiteId          = $helper->getWebsiteIdByStoreId($storeId);
        }

        $adapter            = $this->getConnection();
        $columns   = array(
            'price_id'      => 'value_id', 
            'website_id'    => 'website_id', 
            'stock_id'      => 'stock_id', 
            'all_groups'    => 'all_groups', 
            'cust_group'    => 'customer_group_id', 
            'price_qty'     => 'qty', 
            'price'         => 'value', 
            'product_id'    => 'entity_id', 
        );
        $select  = $adapter->select()
            ->from($this->getTable('catalog/product_attribute_tier_price'), $columns)
            ->where('entity_id IN(?)', $productIds)
            ->order(array('entity_id','qty'));
        if ($websiteId == '0') {
            $select->where('website_id = ?', $websiteId);
        } else {
            $select->where('website_id IN(?)', array('0', $websiteId));
        }

        foreach ($adapter->fetchAll($select) as $row) {
            $tierPrices[$row['product_id']][] = array(
                'website_id'     => $row['website_id'], 
                'stock_id'       => $row['stock_id'], 
                'cust_group'     => $row['all_groups'] ? Mage_Customer_Model_Group::CUST_GROUP_ALL : $row['cust_group'], 
                'price_qty'      => $row['price_qty'], 
                'price'          => $row['price'], 
                'website_price'  => $row['price'], 
            );
        }

        foreach ($this->getItems() as $item) {
            $data = $tierPrices[$item->getId()];
            $item->setTierPrices($data);
            $helper->getProductPriceHelper()->setTierPrice($item);
        }

        $this->setFlag('tier_price_added', true);
        return $this;
    }

    #Adds an additional price column called 'indexed_price' as the price_index.price value is
    #overidden elsewhere in the codebase by the normal(i.e. direct on conf product) product price.
    protected function _productLimitationJoinPrice()
    {
        $filters = $this->_productLimitationFilters;
        if (empty($filters['use_price_index'])) {
            return $this;
        }

        $connection = $this->getConnection();

        $joinCond = $joinCond = join(' AND ', array(
            'price_index.entity_id = e.entity_id',
            $connection->quoteInto('price_index.website_id = ?', $filters['website_id']),
            $connection->quoteInto('price_index.customer_group_id = ?', $filters['customer_group_id'])
        ));

        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (!isset($fromPart['price_index'])) {
            $minimalExpr = new Zend_Db_Expr(
                'IF(`price_index`.`tier_price`, LEAST(`price_index`.`min_price`, `price_index`.`tier_price`), `price_index`.`min_price`)'
            );
            $indexedExpr = new Zend_Db_Expr('price_index.price');
            $this->getSelect()->join(
                array('price_index' => $this->getTable('catalog/product_index_price')),
                $joinCond,
                array('indexed_price'=>$indexedExpr,'price', 'final_price', 'minimal_price'=>$minimalExpr , 'min_price', 'max_price', 'tier_price'));

        } else {
            $fromPart['price_index']['joinCondition'] = $joinCond;
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        }

        return $this;
    }



    
    /**
     * @see https://github.com/merchantprotocol/M1-warehouse/issues/20
     * @return MP_Warehouse_Model_Mysql4_Catalog_Product_Collection
     */
    public function load()
    {
        $this->distinct(true);
        return parent::load();
    }
}
