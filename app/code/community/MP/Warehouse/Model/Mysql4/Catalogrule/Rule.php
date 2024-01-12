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
class MP_Warehouse_Model_Mysql4_Catalogrule_Rule extends Mage_CatalogRule_Model_Mysql4_Rule
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
     * Get config
     *
     * @return MP_Warehouse_Model_Config
     */
    protected function getConfig()
    {
        return $this->getWarehouseHelper()->getConfig();
    }

    /**
     * Get version helper
     *
     * @return MP_Warehouse_Helper_Core_Version
     */
    protected function getVersionHelper()
    {
        return $this->getWarehouseHelper()->getVersionHelper();
    }

    /**
     * Get product price helper
     *
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function getProductPriceHelper()
    {
        return $this->getWarehouseHelper()->getProductPriceHelper();
    }
    /**
     * Get write adapter
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getWriteConnection()
    {
        return $this->_getWriteAdapter();
    }

    /**
     * Inserts rule data into catalogrule/rule_product table
     *
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param array $websiteIds
     * @param array $stockIds
     * @param array $productIds
     * @return void
     */
    public function insertRuleData2(
        Mage_CatalogRule_Model_Rule $rule,
        array $websiteIds,
        array $stockIds,
        array $productIds = array()
    ) {
        /** @var $write Varien_Db_Adapter_Interface */
        $write = $this->_getWriteAdapter();

        $customerGroupIds = $rule->getCustomerGroupIds();

        $fromTime = (int) strtotime($rule->getFromDate());
        $toTime   = (int) strtotime($rule->getToDate());
        $toTime   = $toTime ? ($toTime + self::SECONDS_IN_DAY - 1) : 0;

        /** @var Mage_Core_Model_Date $coreDate */
        $coreDate  = $this->_factory->getModel('core/date');
        $timestamp = $coreDate->gmtTimestamp('Today');

        if ($fromTime > $timestamp
            || ($toTime && $toTime < $timestamp)
        ) {
            return;
        }

        $sortOrder         = (int) $rule->getSortOrder();
        $actionOperator    = $rule->getSimpleAction();
        $actionAmount      = (float) $rule->getDiscountAmount();
        $subActionOperator = $rule->getSubIsEnable() ? $rule->getSubSimpleAction() : '';
        $subActionAmount   = (float) $rule->getSubDiscountAmount();
        $actionStop        = (int) $rule->getStopRulesProcessing();

        /** @var $helper Mage_Catalog_Helper_Product_Flat */
        $helper = $this->_factory->getHelper('catalog/product_flat');

        if ($helper->isEnabled() && $helper->isBuiltAllStores()) {
            /** @var $store Mage_Core_Model_Store */
            foreach ($this->_app->getStores(false) as $store) {
                if (!in_array($store->getWebsiteId(), $websiteIds)) {
                    continue;
                }

                foreach ($stockIds as $stockId) {
                    /** @var $selectByStore Varien_Db_Select */
                    $selectByStore = $rule->getProductFlatSelect($store->getId())
                        ->joinLeft(
                            array('cg' => $this->getTable('customer/customer_group')),
                            $write->quoteInto('cg.customer_group_id IN (?)', $customerGroupIds),
                            array('cg.customer_group_id')
                        )
                        ->reset(Varien_Db_Select::COLUMNS)
                        ->columns(
                            array(
                                new Zend_Db_Expr($store->getWebsiteId()),
                                'cg.customer_group_id',
                                'p.entity_id',
                                new Zend_Db_Expr($stockId),
                                new Zend_Db_Expr($rule->getId()),
                                new Zend_Db_Expr($fromTime),
                                new Zend_Db_Expr($toTime),
                                new Zend_Db_Expr("'" . $actionOperator . "'"),
                                new Zend_Db_Expr($actionAmount),
                                new Zend_Db_Expr($actionStop),
                                new Zend_Db_Expr($sortOrder),
                                new Zend_Db_Expr("'" . $subActionOperator . "'"),
                                new Zend_Db_Expr($subActionAmount)
                            )
                        );

                    if (count($productIds) > 0) {
                        $selectByStore->where('p.entity_id IN (?)', array_keys($productIds));
                    }

                    $selects = $write->selectsByRange('entity_id', $selectByStore, self::RANGE_PRODUCT_STEP);

                    foreach ($selects as $select) {
                        $write->query(
                            $write->insertFromSelect(
                                $select,
                                $this->getTable('catalogrule/rule_product'),
                                array(
                                    'website_id',
                                    'customer_group_id',
                                    'product_id',
                                    'stock_id',
                                    'rule_id',
                                    'from_time',
                                    'to_time',
                                    'action_operator',
                                    'action_amount',
                                    'action_stop',
                                    'sort_order',
                                    'sub_simple_action',
                                    'sub_discount_amount',
                                ),
                                Varien_Db_Adapter_Interface::INSERT_IGNORE
                            )
                        );
                    }
                }
            }
        } else {
            if (count($productIds) == 0) {
                Varien_Profiler::start('__MATCH_PRODUCTS__');
                $productIds = $rule->getMatchingProductIds();
                Varien_Profiler::stop('__MATCH_PRODUCTS__');
            }

            $rows = array();

            foreach ($productIds as $productId => $validationByWebsite) {
                foreach ($websiteIds as $websiteId) {
                    foreach ($customerGroupIds as $customerGroupId) {
                        if (empty($validationByWebsite[$websiteId])) {
                            continue;
                        }

                        foreach ($stockIds as $stockId) {
                            $rows[] = array(
                                'rule_id'             => $rule->getId(),
                                'from_time'           => $fromTime,
                                'to_time'             => $toTime,
                                'website_id'          => $websiteId,
                                'customer_group_id'   => $customerGroupId,
                                'product_id'          => $productId,
                                'stock_id'            => $stockId,
                                'action_operator'     => $actionOperator,
                                'action_amount'       => $actionAmount,
                                'action_stop'         => $actionStop,
                                'sort_order'          => $sortOrder,
                                'sub_simple_action'   => $subActionOperator,
                                'sub_discount_amount' => $subActionAmount,
                            );

                            if (count($rows) == 1000) {
                                $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
                                $rows = array();
                            }
                        }
                    }
                }
            }

            if (!empty($rows)) {
                $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
            }
        }
    }

    /**
     * Update products which are matched for rule
     *
     * @param Mage_CatalogRule_Model_Rule $rule
     * @return $this
     * @throws Exception
     */
    public function updateRuleProductData(Mage_CatalogRule_Model_Rule $rule)
    {
        $helper     = $this->getWarehouseHelper();
        $ruleId     = $rule->getId();
        $websiteIds = $rule->getWebsiteIds();

        if (!is_array($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }

        if (empty($websiteIds)) {
            return $this;
        }

        $stockIds = $helper->getStockIds();

        if (!is_array($stockIds)) {
            $stockIds = explode(',', $stockIds);
        }

        if (empty($stockIds)) {
            return $this;
        }

        $write = $this->_getWriteAdapter();
        $write->beginTransaction();

        if (!$this->getVersionHelper()->isGe1800()) {
            if ($this->getVersionHelper()->isGe1600() && $rule->getProductsFilter()) {
                $write->delete(
                    $this->getTable('catalogrule/rule_product'),
                    array('rule_id = ?' => $ruleId, 'product_id IN (?)' => $rule->getProductsFilter())
                );
            } else {
                $write->delete(
                    $this->getTable('catalogrule/rule_product'),
                    $write->quoteInto('rule_id = ?', $ruleId)
                );
            }

            if (!$rule->getIsActive()) {
                $write->commit();

                return $this;
            }

            $productIds        = $rule->getMatchingProductIds();
            $customerGroupIds  = $rule->getCustomerGroupIds();
            $fromTime          = strtotime($rule->getFromDate());
            $toTime            = strtotime($rule->getToDate());
            $toTime            = $toTime ? ($toTime + self::SECONDS_IN_DAY - 1) : 0;
            $sortOrder         = (int) $rule->getSortOrder();
            $actionOperator    = $rule->getSimpleAction();
            $actionAmount      = (float) $rule->getDiscountAmount();
            $subActionOperator = $rule->getSubIsEnable() ? $rule->getSubSimpleAction() : '';
            $subActionAmount   = (float) $rule->getSubDiscountAmount();

            $actionStop = $rule->getStopRulesProcessing();
            $rows       = array();

            if (!$this->getVersionHelper()->isGe1600()) {
                $queryStart = 'INSERT INTO ' . $this->getTable('catalogrule/rule_product') . ' (
                    rule_id, from_time, to_time, website_id, stock_id, customer_group_id, product_id, action_operator,
                    action_amount, action_stop, sort_order ) values ';

                $queryEnd = ' ON DUPLICATE KEY UPDATE action_operator=VALUES(action_operator),
                    action_amount=VALUES(action_amount), action_stop=VALUES(action_stop)';
            }

            try {
                foreach ($productIds as $productId) {
                    foreach ($websiteIds as $websiteId) {
                        foreach ($customerGroupIds as $customerGroupId) {
                            foreach ($stockIds as $stockId) {
                                if ($this->getVersionHelper()->isGe1600()) {
                                    $row = array(
                                        'rule_id'           => $ruleId,
                                        'from_time'         => $fromTime,
                                        'to_time'           => $toTime,
                                        'website_id'        => $websiteId,
                                        'stock_id'          => $stockId,
                                        'customer_group_id' => $customerGroupId,
                                        'product_id'        => $productId,
                                        'action_operator'   => $actionOperator,
                                        'action_amount'     => $actionAmount,
                                        'action_stop'       => $actionStop,
                                        'sort_order'        => $sortOrder,
                                    );

                                    if ($this->getVersionHelper()->isGe1700()) {
                                        $row['sub_simple_action']   = $subActionOperator;
                                        $row['sub_discount_amount'] = $subActionAmount;
                                    }

                                    $rows[] = $row;

                                    if (count($rows) == 1000) {
                                        $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
                                        $rows = array();
                                    }
                                } else {
                                    $rows[] = "('" . implode(
                                        "','",
                                        array(
                                            $ruleId,
                                            $fromTime,
                                            $toTime,
                                            $websiteId,
                                            $stockId,
                                            $customerGroupId,
                                            $productId,
                                            $actionOperator,
                                            $actionAmount,
                                            $actionStop,
                                            $sortOrder
                                        )
                                    ) . "')";

                                    if (sizeof($rows) == 1000) {
                                        $sql = $queryStart . join(',', $rows) . $queryEnd;
                                        $write->query($sql);

                                        $rows = array();
                                    }
                                }
                            }
                        }
                    }
                }

                if ($this->getVersionHelper()->isGe1600()) {
                    if (!empty($rows)) {
                        $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
                    }
                } else {
                    if (!empty($rows)) {
                        $sql = $queryStart . join(',', $rows) . $queryEnd;
                        $write->query($sql);
                    }
                }

                $write->commit();
            } catch (Exception $e) {
                $write->rollback();

                throw $e;
            }
        } else {
            if ($rule->getProductsFilter()) {
                $this->cleanProductData($ruleId, $rule->getProductsFilter());
            } else {
                $this->cleanProductData($ruleId);
            }

            if (!$rule->getIsActive()) {
                $write->commit();

                return $this;
            }

            try {
                $this->insertRuleData2($rule, $websiteIds, $stockIds);
                $write->commit();
            } catch (Exception $e) {
                $write->rollback();

                throw $e;
            }
        }

        return $this;
    }

    /**
     * Get price join condition
     *
     * @return string
     */
    protected function getPriceJoinCondition()
    {
        $helper             = $this->getWarehouseHelper();
        $productPriceHelper = $helper->getProductPriceHelper();

        return implode(
            ' AND ',
            array(
                '(%1$s.entity_id = rp.product_id)',
                '(%1$s.attribute_id = ' . $productPriceHelper->getPriceAttributeId() . ')',
                '(%1$s.store_id = %2$s)'
            )
        );
    }

    /**
     * Get DB resource statement for processing query result
     *
     * @param int $fromDate
     * @param int $toDate
     * @param int|null $productId
     * @param int $websiteId
     * @return Zend_Db_Statement_Interface
     */
    protected function _getRuleProductsStmt2($fromDate, $toDate, $productId = null, $websiteId = null)
    {
        $helper             = $this->getWarehouseHelper();
        $productPriceHelper = $helper->getProductPriceHelper();
        $read               = $this->_getReadAdapter();
        $order              = array(
            'rp.website_id',
            'rp.customer_group_id',
            'rp.stock_id',
            'rp.product_id',
            'rp.sort_order',
            'rp.rule_id'
        );

        $select = $read->select()->from(array('rp' => $this->getTable('catalogrule/rule_product')))
            ->where(
                $read->quoteInto('rp.from_time = 0 or rp.from_time <= ?', $toDate)
                . ' OR ' .
                $read->quoteInto('rp.to_time = 0 or rp.to_time >= ?', $fromDate)
            )->order($order);

        if (!is_null($productId)) {
            $select->where('rp.product_id = ?', $productId);
        }

        $select->joinInner(
            array('product_website' => $this->getTable('catalog/product_website')),
            'product_website.product_id = rp.product_id ' .
            'AND rp.website_id = product_website.website_id ' .
            'AND product_website.website_id = ' . $websiteId,
            array()
        );

        if ($productPriceHelper->isWebsiteScope()) {
            $select->join(
                array('cw' => $helper->getCoreHelper()->getTable('core/website')),
                '(cw.website_id = rp.website_id)',
                array()
            );

            $select->join(
                array('csg' => $helper->getCoreHelper()->getTable('core/store_group')),
                '(csg.group_id = cw.default_group_id)',
                array()
            );
        }

        $select->join(
            array('pp_default' => $productPriceHelper->getPriceAttributeTable()),
            sprintf($this->getPriceJoinCondition(), 'pp_default', Mage_Core_Model_App::ADMIN_STORE_ID),
            array()
        );

        $defaultPrice = new Zend_Db_Expr('pp_default.value');
        $defaultPrice = new Zend_Db_Expr("IF(cpgcp.price IS NOT NULL, cpgcp.price, {$defaultPrice})");

        $select->columns(array('default_price' => $defaultPrice));

        return $read->query($select);
    }

    /**
     * Generate catalog price rules prices for specified date range
     * If from date is not defined - will be used previous day by UTC
     * If to date is not defined - will be used next day by UTC
     *
     * @param int|string|null $fromDate
     * @param int|string|null $toDate
     * @param int $productId
     * @return Mage_CatalogRule_Model_Resource_Rule
     * @throws Exception
     */
    public function applyAllRulesForDateRange($fromDate = null, $toDate = null, $productId = null)
    {
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();

        Mage::dispatchEvent('catalogrule_before_apply', array('resource' => $this));

        $clearOldData = false;

        if ($fromDate === null) {
            $fromDate     = mktime(0, 0, 0, date('m'), date('d') - 1);
            $clearOldData = true;
        }

        if (is_string($fromDate)) {
            $fromDate = strtotime($fromDate);
        }

        if ($toDate === null) {
            $toDate = mktime(0, 0, 0, date('m'), date('d') + 1);
        }

        if (is_string($toDate)) {
            $toDate = strtotime($toDate);
        }

        $product = null;

        if ($productId instanceof Mage_Catalog_Model_Product) {
            $product   = $productId;
            $productId = $productId->getId();
        }

        $this->removeCatalogPricesForDateRange($fromDate, $toDate, $productId);

        if ($clearOldData) {
            $this->deleteOldData($fromDate, $productId);
        }

        $dayPrices = array();

        try {
            foreach (Mage::app()->getWebsites(false) as $website) {
                $websiteId    = $website->getId();
                $productsStmt = $this->_getRuleProductsStmt2($fromDate, $toDate, $productId, $websiteId);
                $dayPrices    = array();
                $stopFlags    = array();
                $prevKey      = null;

                while ($ruleData = $productsStmt->fetch()) {
                    $ruleProductId = $ruleData['product_id'];
                    $productKey    = implode(
                        '_',
                        array(
                            $ruleProductId,
                            $ruleData['website_id'],
                            $ruleData['customer_group_id'],
                            $ruleData['stock_id']
                        )
                    );

                    if ($prevKey && ($prevKey != $productKey)) {
                        $stopFlags = array();
                    }

                    for ($time = $fromDate; $time <= $toDate; $time += self::SECONDS_IN_DAY) {
                        if (($ruleData['from_time'] == 0 || $time >= $ruleData['from_time'])
                            && ($ruleData['to_time'] == 0 || $time <=$ruleData['to_time'])
                        ) {
                            $priceKey = $time . '_' . $productKey;

                            if (isset($stopFlags[$priceKey])) {
                                continue;
                            }

                            if (!isset($dayPrices[$priceKey])) {
                                $dayPrices[$priceKey] = array(
                                    'rule_date'         => $time,
                                    'website_id'        => $ruleData['website_id'],
                                    'customer_group_id' => $ruleData['customer_group_id'],
                                    'stock_id'          => $ruleData['stock_id'],
                                    'product_id'        => $ruleProductId,
                                    'rule_price'        => $this->_calcRuleProductPrice($ruleData),
                                    'latest_start_date' => $ruleData['from_time'],
                                    'earliest_end_date' => $ruleData['to_time'],
                                );
                            } else {
                                $dayPrices[$priceKey]['rule_price'] = $this->_calcRuleProductPrice(
                                    $ruleData,
                                    $dayPrices[$priceKey]
                                );

                                $dayPrices[$priceKey]['latest_start_date'] = max(
                                    $dayPrices[$priceKey]['latest_start_date'],
                                    $ruleData['from_time']
                                );

                                $dayPrices[$priceKey]['earliest_end_date'] = min(
                                    $dayPrices[$priceKey]['earliest_end_date'],
                                    $ruleData['to_time']
                                );
                            }

                            if ($ruleData['action_stop']) {
                                $stopFlags[$priceKey] = true;
                            }
                        }
                    }

                    $prevKey = $productKey;

                    if (count($dayPrices) > 1000) {
                        $this->_saveRuleProductPrices($dayPrices);
                        $dayPrices = array();
                    }
                }

                $this->_saveRuleProductPrices($dayPrices);
            }

            $this->_saveRuleProductPrices($dayPrices);
            $write->delete($this->getTable('catalogrule/rule_group_website'), array());

            $timestamp  = Mage::getModel('core/date')->gmtTimestamp();
            $attributes = array('rule_id', 'customer_group_id', 'website_id');

            $select = $write->select()->distinct(true)
                ->from($this->getTable('catalogrule/rule_product'), $attributes)
                ->where("{$timestamp} >= from_time AND (({$timestamp} <= to_time AND to_time > 0) OR to_time = 0)");
            $query = $select->insertFromSelect($this->getTable('catalogrule/rule_group_website'));

            $write->query($query);
            $write->commit();
        } catch (Exception $e) {
            $write->rollback();

            throw $e;
        }

        $productCondition = Mage::getModel('catalog/product_condition')
            ->setTable($this->getTable('catalogrule/affected_product'))
            ->setPkFieldName('product_id');

        Mage::dispatchEvent(
            'catalogrule_after_apply',
            array(
                'product'           => $product,
                'product_condition' => $productCondition
            )
        );

        $write->delete($this->getTable('catalogrule/affected_product'));

        return $this;
    }

    /**
     * Save rule prices for products to DB
     *
     * @param array $arrData
     * @return Mage_CatalogRule_Model_Resource_Rule
     */
    protected function _saveRuleProductPrices($arrData)
    {
        if ($this->getVersionHelper()->isGe1600()) {
            return parent::_saveRuleProductPrices($arrData);
        }

        if (empty($arrData)) {
            return $this;
        }

        $header = 'REPLACE INTO ' . $this->getTable('catalogrule/rule_product_price') . ' (
                rule_date,
                website_id,
                customer_group_id,
                stock_id,
                product_id,
                rule_price,
                latest_start_date,
                earliest_end_date
            ) VALUES ';

        $rows       = array();
        $productIds = array();

        foreach ($arrData as $data) {
            $productIds[$data['product_id']] = true;

            $data['rule_date']         = $this->formatDate($data['rule_date'], false);
            $data['latest_start_date'] = $this->formatDate($data['latest_start_date'], false);
            $data['earliest_end_date'] = $this->formatDate($data['earliest_end_date'], false);

            $rows[] = '(' . $this->_getWriteAdapter()->quote($data) . ')';
        }

        $query       = $header.join(',', $rows);
        $insertQuery = 'REPLACE INTO '
            . $this->getTable('catalogrule/affected_product')
            . ' (product_id) VALUES '
            . '(' . join('),(', array_keys($productIds)) . ')';

        $this->_getWriteAdapter()->query($insertQuery);
        $this->_getWriteAdapter()->query($query);

        return $this;
    }

    /**
     * Get catalog rules product price for specific date, website, store and customer group
     *
     * @param int|string $date
     * @param int $wId
     * @param int $gId
     * @param int $stockId
     * @param int $pId
     * @return float|bool
     */
    public function getRulePrice2($date, $wId, $gId, $stockId, $pId)
    {
        $data = $this->getRulePrices2($date, $wId, $gId, $stockId, array($pId));

        if (isset($data[$pId])) {
            return $data[$pId];
        }

        return false;
    }

    /**
     * Retrieve product prices by catalog rule for specific date, website, store and customer group
     * Collect data with  product Id => price pairs
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string $stockId
     * @param array $productIds
     * @return array
     */
    public function getRulePrices2($date, $websiteId, $customerGroupId, $stockId, $productIds)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable('catalogrule/rule_product_price'), array('product_id', 'rule_price'))
            ->where('rule_date = ?', $this->formatDate($date, false))
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('stock_id = ?', $stockId)
            ->where('product_id IN(?)', $productIds);

        return $adapter->fetchPairs($select);
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param int $stockId
     * @param int $productId
     * @return array
     */
    public function getRulesFromProduct2($date, $websiteId, $customerGroupId, $stockId, $productId)
    {
        $adapter = $this->_getReadAdapter();

        if ($this->getVersionHelper()->isGe1700()) {
            if (is_string($date)) {
               $date = strtotime($date);
            }

            $select = $adapter->select()
                ->from($this->getTable('catalogrule/rule_product'))
                ->where('website_id = ?', $websiteId)
                ->where('customer_group_id = ?', $customerGroupId)
                ->where('stock_id = ?', $stockId)
                ->where('product_id = ?', $productId)
                ->where('from_time = 0 or from_time < ?', $date)
                ->where('to_time = 0 or to_time > ?', $date);
        } else {
            $dateQuoted        = $adapter->quote($this->formatDate($date, false));
            $joinCondsQuoted[] = 'main_table.rule_id = rp.rule_id';
            $joinCondsQuoted[] = $adapter->quoteInto('rp.website_id = ?', $websiteId);
            $joinCondsQuoted[] = $adapter->quoteInto('rp.customer_group_id = ?', $customerGroupId);
            $joinCondsQuoted[] = $adapter->quoteInto('rp.product_id = ?', $productId);

            if ($this->getVersionHelper()->isGe1600()) {
                $fromDate = $adapter->getIfNullSql('main_table.from_date', $dateQuoted);
                $toDate   = $adapter->getIfNullSql('main_table.to_date', $dateQuoted);
                $select   = $adapter->select()
                    ->from(array('main_table' => $this->getTable('catalogrule/rule')))
                    ->joinInner(
                        array('rp' => $this->getTable('catalogrule/rule_product')),
                        implode(' AND ', $joinCondsQuoted),
                        array()
                    )
                    ->where(new Zend_Db_Expr("{$dateQuoted} BETWEEN {$fromDate} AND {$toDate}"))
                    ->where('main_table.is_active = ?', 1)
                    ->order('main_table.sort_order');
            } else {
                $select = $adapter->select()
                    ->distinct()
                    ->from(array('main_table' => $this->getTable('catalogrule/rule')), 'main_table.*')
                    ->joinInner(
                        array('rp' => $this->getTable('catalogrule/rule_product')),
                        implode(' AND ', $joinCondsQuoted),
                        array()
                    )
                    ->where(
                        new Zend_Db_Expr(
                            "{$dateQuoted} BETWEEN IFNULL(main_table.from_date, " .
                            "{$dateQuoted}) AND IFNULL(main_table.to_date, " .
                            "{$dateQuoted})"
                        )
                    )
                    ->where('main_table.is_active = ?', 1)
                    ->order('main_table.sort_order');
            }
        }

        return $adapter->fetchAll($select);
    }

    /**
     * Retrieve product price data for all customer groups
     *
     * @param int|string $date
     * @param int $wId
     * @param int $pId
     * @return array
     */
    public function getRulesForProduct2($date, $wId, $pId)
    {
        $read   = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getTable('catalogrule/rule_product_price'), '*')
            ->where('rule_date=?', $this->formatDate($date, false))
            ->where('website_id=?', $wId)
            ->where('product_id=?', $pId);

        return $read->fetchAll($select);
    }

    /**
     * Apply catalog rule to product
     *
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param Mage_Catalog_Model_Product $product
     * @param array $websiteIds
     * @return Mage_CatalogRule_Model_Resource_Rule
     * @throws Exception
     */
    public function applyToProduct2($rule, $product, $websiteIds)
    {
        if (!$rule->getIsActive()) {
            return $this;
        }

        $helper    = $this->getWarehouseHelper();
        $ruleId    = $rule->getId();
        $productId = $product->getId();
        $write     = $this->_getWriteAdapter();

        $write->beginTransaction();

        if (!$this->getVersionHelper()->isGe1800()) {
            $write->delete(
                $this->getTable('catalogrule/rule_product'),
                array(
                    $write->quoteInto('rule_id = ?', $ruleId),
                    $write->quoteInto('product_id = ?', $productId)
                )
            );

            if (!$rule->getConditions()->validate($product)) {
                
                #It's an odd bug - and maybe there's a good reason, but it's making my rules disappear when I save products, which is pretty annoying

                // $write->delete(
                //     $this->getTable('catalogrule/rule_product_price'),
                //     array($write->quoteInto('product_id = ?', $productId))
                // );

                $write->commit();

                return $this;
            }

            $customerGroupIds  = $rule->getCustomerGroupIds();
            $stocksIds         = $helper->getStockIds();
            $fromTime          = strtotime($rule->getFromDate());
            $toTime            = strtotime($rule->getToDate());
            $toTime            = $toTime ? $toTime + self::SECONDS_IN_DAY - 1 : 0;
            $sortOrder         = (int)$rule->getSortOrder();
            $actionStop        = $rule->getStopRulesProcessing();
            $actionOperator    = $rule->getSimpleAction();
            $actionAmount      = (float) $rule->getDiscountAmount();
            $subActionOperator = $rule->getSubIsEnable() ? $rule->getSubSimpleAction() : '';
            $subActionAmount   = (float) $rule->getSubDiscountAmount();

            $rows = array();

            if (!$this->getVersionHelper()->isGe1600()) {
                $header = 'REPLACE INTO ' . $this->getTable('catalogrule/rule_product') . ' (
                    rule_id,
                    from_time,
                    to_time,
                    website_id,
                    customer_group_id,
                    stock_id,
                    product_id,
                    action_operator,
                    action_amount,
                    action_stop,
                    sort_order
                ) VALUES ';
            }

            try {
                foreach ($websiteIds as $websiteId) {
                    foreach ($customerGroupIds as $customerGroupId) {
                        foreach ($stocksIds as $stockId) {
                            if ($this->getVersionHelper()->isGe1600()) {
                                $row = array(
                                    'rule_id'               => $ruleId,
                                    'from_time'             => $fromTime,
                                    'to_time'               => $toTime,
                                    'website_id'            => $websiteId,
                                    'customer_group_id'     => $customerGroupId,
                                    'stock_id'              => $stockId,
                                    'product_id'            => $productId,
                                    'action_operator'       => $actionOperator,
                                    'action_amount'         => $actionAmount,
                                    'action_stop'           => $actionStop,
                                    'sort_order'            => $sortOrder,
                                );

                                if ($this->getVersionHelper()->isGe1700()) {
                                    $row['sub_simple_action']   = $subActionOperator;
                                    $row['sub_discount_amount'] = $subActionAmount;
                                }

                                $rows[] = $row;

                                if (count($rows) == 1000) {
                                    $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);

                                    $rows = array();
                                }
                            } else {
                                $rows[] = "(
                                    '$ruleId',
                                    '$fromTime',
                                    '$toTime',
                                    '$websiteId',
                                    '$customerGroupId',
                                    '$stockId',
                                    '$productId',
                                    '$actionOperator',
                                    '$actionAmount',
                                    '$actionStop',
                                    '$sortOrder'
                                )";

                                if (sizeof($rows) == 100) {
                                    $sql = $header . join(',', $rows);
                                    $write->query($sql);

                                    $rows = array();
                                }
                            }
                        }
                    }
                }

                if ($this->getVersionHelper()->isGe1600()) {
                    if (!empty($rows)) {
                        $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
                    }
                } else {
                    if (!empty($rows)) {
                        $sql = $header . join(',', $rows);
                        $write->query($sql);
                    }
                }
            } catch (Exception $e) {
                $write->rollback();

                throw $e;
            }

            $this->applyAllRulesForDateRange(null, null, $product);
        } else {
            $this->cleanProductData($ruleId, array($productId));

            if (!$this->validateProduct($rule, $product, $websiteIds)) {
                $write->delete(
                    $this->getTable('catalogrule/rule_product_price'),
                    array($write->quoteInto('product_id = ?', $productId))
                );

                $write->commit();

                return $this;
            }

            try {
                $stocksIds = $helper->getStockIds();

                $this->insertRuleData2(
                    $rule,
                    $websiteIds,
                    $stocksIds,
                    array(
                        $productId => array_combine(
                            array_values($websiteIds),
                            array_values($websiteIds)
                        )
                    )
                );
            } catch (Exception $e) {
                $write->rollback();

                throw $e;
            }
        }

        $write->commit();

        return $this;
    }
}
