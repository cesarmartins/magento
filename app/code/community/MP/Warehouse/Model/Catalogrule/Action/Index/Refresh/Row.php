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
 * Catalog rule
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Catalogrule_Action_Index_Refresh_Row 
    extends Mage_CatalogRule_Model_Action_Index_Refresh_Row
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
     * Get table
     * 
     * @param string $entityName
     * @return string
     */
    protected function getTable($entityName)
    {
        return $this->_resource->getTable($entityName);
    }

    /**
     * Create temporary table
     *
     * @return void
     */
    protected function _createTemporaryTable()
    {
        $this->_connection->dropTemporaryTable($this->_getTemporaryTable());

        $table = $this->_connection->newTable($this->_getTemporaryTable())
            ->addColumn(
                'grouped_id',
                Varien_Db_Ddl_Table::TYPE_VARCHAR,
                80,
                array(),
                'Grouped ID'
            )
            ->addColumn(
                'product_id',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                null,
                array(
                    'unsigned' => true
                ),
                'Product ID'
            )
            ->addColumn(
                'stock_id',
                Varien_Db_Ddl_Table::TYPE_SMALLINT, 
                6, 
                array(
                    'unsigned' => true, 
                ),
                'Stock ID'
            )
            ->addColumn(
                'customer_group_id',
                Varien_Db_Ddl_Table::TYPE_SMALLINT,
                5,
                array(
                    'unsigned' => true
                ),
                'Customer Group ID'
            )
            ->addColumn(
                'from_date',
                Varien_Db_Ddl_Table::TYPE_DATE,
                null,
                array(),
                'From Date'
            )
            ->addColumn(
                'to_date',
                Varien_Db_Ddl_Table::TYPE_DATE,
                null,
                array(),
                'To Date'
            )
            ->addColumn(
                'action_amount',
                Varien_Db_Ddl_Table::TYPE_DECIMAL,
                '12,4',
                array(),
                'Action Amount'
            )
            ->addColumn(
                'action_operator',
                Varien_Db_Ddl_Table::TYPE_VARCHAR,
                10,
                array(),
                'Action Operator'
            )
            ->addColumn(
                'action_stop',
                Varien_Db_Ddl_Table::TYPE_SMALLINT,
                6,
                array(),
                'Action Stop'
            )
            ->addColumn(
                'sort_order',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                10,
                array(
                    'unsigned' => true
                ),
                'Sort Order'
            )
            ->addColumn(
                'price',
                Varien_Db_Ddl_Table::TYPE_DECIMAL,
                '12,4',
                array(),
                'Product Price'
            )
            ->addColumn(
                'rule_product_id',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                null,
                array(
                    'unsigned' => true
                ),
                'Rule Product ID'
            )
            ->addColumn(
                'from_time',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                null,
                array(
                    'unsigned' => true,
                    'nullable' => true,
                    'default'  => 0,
                ),
                'From Time'
            )
            ->addColumn(
                'to_time',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                null,
                array(
                    'unsigned' => true,
                    'nullable' => true,
                    'default'  => 0,
                ),
                'To Time'
            )
            ->addIndex(
                $this->_connection->getIndexName($this->_getTemporaryTable(), 'stock_id'),
                array('stock_id')
            )
            ->addIndex(
                $this->_connection->getIndexName($this->_getTemporaryTable(), 'grouped_id'),
                array('grouped_id')
            )
            ->setComment('CatalogRule Price Temporary Table');

        $this->_connection->createTemporaryTable($table);
    }

    /**
     * Prepare temporary data
     * 
     * @param Mage_Core_Model_Website $website
     * 
     * @return Varien_Db_Select
     */
    protected function _prepareTemporarySelect(Mage_Core_Model_Website $website)
    {
        $helper             = $this->getWarehouseHelper();
        $productPriceHelper = $helper->getProductPriceHelper();

        /** @var Mage_Catalog_Helper_Product_Flat $productFlatHelper */
        $productFlatHelper = $this->_factory->getHelper('catalog/product_flat');

        /** @var Mage_Eav_Model_Config $eavConfig */
        $eavConfig = $this->_factory->getSingleton('eav/config');

        $priceAttribute = $eavConfig->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'price');
        $adapter        = $this->_connection;
        $websiteId      = $website->getId();
        $storeId        = $website->getDefaultStore()->getId();

        $select = $adapter->select()->from(
            array('rp' => $this->getTable('catalogrule/rule_product')),
            array()
        )
        ->joinInner(
            array('r' => $this->getTable('catalogrule/rule')),
            'r.rule_id = rp.rule_id',
            array()
        )
        ->where('rp.website_id = ?', $websiteId)
        ->order(
            array(
                'rp.product_id',
                'rp.stock_id',
                'rp.customer_group_id',
                'rp.sort_order',
                'rp.rule_product_id'
            )
        )
        ->joinLeft(
            array('pgd' => $this->getTable('catalog/product_attribute_group_price')),
            implode(
                ' AND ',
                array(
                    'pgd.entity_id = rp.product_id',
                    'pgd.customer_group_id = rp.customer_group_id',
                    'pgd.website_id = 0',
                    'pgd.stock_id = rp.stock_id'
                )
            ),
            array()
        );

        if (!$productPriceHelper->isGlobalScope()) {
            $select->joinLeft(
                array('pg' => $this->getTable('catalog/product_attribute_group_price')),
                implode(
                    ' AND ',
                    array(
                        'pg.entity_id = rp.product_id',
                        'pg.customer_group_id = rp.customer_group_id',
                        'pg.website_id = rp.website_id',
                        'pg.stock_id = rp.stock_id'
                    )
                ),
                array()
            );
        }

        $customerGroupPriceExpr = new Zend_Db_Expr('pgd.value');

        if (!$productPriceHelper->isGlobalScope()) {
            $customerGroupPriceExpr = $adapter->getIfNullSql('pg.value', $customerGroupPriceExpr);
        }

        if ($productFlatHelper->isEnabled() && $productFlatHelper->isBuilt($storeId)) {
            $select->joinInner(
                array('p' => $this->getTable('catalog/product_flat') . '_' . $storeId),
                'p.entity_id = rp.product_id',
                array()
            );

            $priceExpr = new Zend_Db_Expr('p.price');
        } else {
            $select->joinInner(
                array('pd' => $this->getTable(array('catalog/product', $priceAttribute->getBackendType()))),
                implode(
                    ' AND ',
                    array(
                        'pd.entity_id = rp.product_id',
                        'pd.store_id = 0',
                        'pd.attribute_id = ' . $priceAttribute->getId()
                    )
                ),
                array()
            );

            if (!$productPriceHelper->isGlobalScope()) {
                $select->joinLeft(
                    array('p' => $this->getTable(array('catalog/product', $priceAttribute->getBackendType()))),
                    implode(
                        ' AND ',
                        array(
                            'p.entity_id = rp.product_id',
                            'p.store_id = ' . $storeId,
                            'p.attribute_id = ' . $priceAttribute->getId()
                        )
                    ),
                    array()
                );
            }

            $priceExpr = new Zend_Db_Expr('pd.value');

            if (!$productPriceHelper->isGlobalScope()) {
                $priceExpr = $adapter->getIfNullSql('p.value', $priceExpr);
            }
        }

        $priceExpr = $adapter->getIfNullSql($customerGroupPriceExpr, $priceExpr);

        $select->columns(
            array(
                'grouped_id'        => $adapter->getConcatSql(
                    array(
                        'rp.product_id',
                        'rp.stock_id',
                        'rp.customer_group_id',
                    ),
                    '-'
                ),
                'product_id'        => 'rp.product_id',
                'stock_id'          => 'rp.stock_id',
                'customer_group_id' => 'rp.customer_group_id',
                'from_date'         => 'r.from_date',
                'to_date'           => 'r.to_date',
                'action_amount'     => 'rp.action_amount',
                'action_operator'   => 'rp.action_operator',
                'action_stop'       => 'rp.action_stop',
                'sort_order'        => 'rp.sort_order',
                'price'             => $priceExpr,
                'rule_product_id'   => 'rp.rule_product_id',
                'from_time'         => 'rp.from_time',
                'to_time'           => 'rp.to_time'
            )
        );

        $select->where('rp.product_id IN (?)', $this->_productId);

        return $select;
    }

    /**
     * Prepare price column
     *
     * @return Zend_Db_Expr
     */
    protected function _calculatePrice()
    {
        $adapter            = $this->_connection;
        $toPercent          = $adapter->quote('to_percent');
        $byPercent          = $adapter->quote('by_percent');
        $toFixed            = $adapter->quote('to_fixed');
        $byFixed            = $adapter->quote('by_fixed');
        $nA                 = $adapter->quote('N/A');
        $groupIdExpr        = $adapter->getIfNullSql(new Zend_Db_Expr('@group_id'), $nA);
        $actionStopExpr     = $adapter->getIfNullSql(new Zend_Db_Expr('@action_stop'), new Zend_Db_Expr(0));
        return $adapter->getCaseSql(
            '',
            array(
                $groupIdExpr.' != cppt.grouped_id' => '@price := '.$adapter->getCaseSql(
                    $adapter->quoteIdentifier('cppt.action_operator'),
                    array(
                            $toPercent => new Zend_Db_Expr('cppt.price * cppt.action_amount / 100'),
                            $byPercent => new Zend_Db_Expr('cppt.price * (1 - cppt.action_amount / 100)'),
                            $toFixed   => $adapter->getCheckSql(
                                new Zend_Db_Expr('cppt.action_amount < cppt.price'),
                                new Zend_Db_Expr('cppt.action_amount'),
                                new Zend_Db_Expr('cppt.price')
                            ),
                            $byFixed   => $adapter->getCheckSql(
                                new Zend_Db_Expr('0 > cppt.price - cppt.action_amount'),
                                new Zend_Db_Expr('0'),
                                new Zend_Db_Expr('cppt.price - cppt.action_amount')
                            ),
                        )
                ),
                $groupIdExpr.' = cppt.grouped_id AND '.$actionStopExpr.' = 0' => '@price := '.$adapter->getCaseSql(
                    $adapter->quoteIdentifier('cppt.action_operator'),
                    array(
                            $toPercent => new Zend_Db_Expr('@price * cppt.action_amount / 100'),
                            $byPercent => new Zend_Db_Expr('@price * (1 - cppt.action_amount / 100)'),
                            $toFixed   => $adapter->getCheckSql(
                                new Zend_Db_Expr('cppt.action_amount < @price'),
                                new Zend_Db_Expr('cppt.action_amount'),
                                new Zend_Db_Expr('@price')
                            ),
                            $byFixed   => $adapter->getCheckSql(
                                new Zend_Db_Expr('0 > @price - cppt.action_amount'),
                                new Zend_Db_Expr('0'),
                                new Zend_Db_Expr('@price - cppt.action_amount')
                            ),
                        )
                )
            ),
            '@price := @price'
        );
    }

    /**
     * Prepare index select
     *
     * @param Mage_Core_Model_Website $website
     * @param $time
     * 
     * @return Varien_Db_Select
     */
    protected function _prepareIndexSelect(Mage_Core_Model_Website $website, $time)
    {
        $websiteId = $website->getId();
        $adapter   = $this->_connection;
        $nA        = $adapter->quote('N/A');

        $adapter->query('SET @price := NULL');
        $adapter->query('SET @group_id := NULL');
        $adapter->query('SET @action_stop := NULL');

        $groupIdExpr    = $adapter->getIfNullSql(new Zend_Db_Expr('@group_id'), $nA);
        $actionStopExpr = $adapter->getIfNullSql(new Zend_Db_Expr('@action_stop'), new Zend_Db_Expr(0));

        $indexSelect = $adapter->select()
            ->from(array('cppt' => $this->_getTemporaryTable()), array())
            ->order(
                array(
                    'cppt.grouped_id',
                    'cppt.sort_order',
                    'cppt.rule_product_id'
                )
            )
            ->columns(
                array(
                    'customer_group_id' => 'cppt.customer_group_id',
                    'product_id'        => 'cppt.product_id',
                    'stock_id'          => 'cppt.stock_id',
                    'rule_price'        => $this->_calculatePrice(),
                    'latest_start_date' => 'cppt.from_date',
                    'earliest_end_date' => 'cppt.to_date',
                    new Zend_Db_Expr(
                        $adapter->getCaseSql(
                            '',
                            array(
                                $groupIdExpr . ' != cppt.grouped_id' =>
                                    new Zend_Db_Expr('@action_stop := cppt.action_stop'),
                                $groupIdExpr . ' = cppt.grouped_id' =>
                                    '@action_stop := ' . $actionStopExpr . ' + cppt.action_stop'
                            )
                        )
                    ),
                    new Zend_Db_Expr('@group_id := cppt.grouped_id'),
                    'from_time'         => 'cppt.from_time',
                    'to_time'           => 'cppt.to_time'
                )
            );

        $select = $adapter->select()
            ->from($indexSelect, array())
            ->joinInner(
                array(
                    'dates' => $adapter->select()->union(
                        array(
                            new Zend_Db_Expr(
                                'SELECT ' . $adapter->getDateAddSql(
                                    $adapter->fromUnixtime($time), -1, Varien_Db_Adapter_Interface::INTERVAL_DAY
                                ) . ' AS rule_date'
                            ), 
                            new Zend_Db_Expr('SELECT ' . $adapter->fromUnixtime($time) . ' AS rule_date'),
                            new Zend_Db_Expr(
                                'SELECT ' . $adapter->getDateAddSql(
                                    $adapter->fromUnixtime($time), 1, Varien_Db_Adapter_Interface::INTERVAL_DAY
                                ) . ' AS rule_date'
                            ), 
                        )
                    )
                ),
                '1=1',
                array()
            )
            ->columns(
                array(
                    'rule_product_price_id' => new Zend_Db_Expr('NULL'),
                    'rule_date'             => 'dates.rule_date',
                    'customer_group_id'     => 'customer_group_id',
                    'product_id'            => 'product_id',
                    'stock_id'              => 'stock_id',
                    'rule_price'            => 'MIN(rule_price)',
                    'website_id'            => new Zend_Db_Expr($websiteId),
                    'latest_start_date'     => 'latest_start_date',
                    'earliest_end_date'     => 'earliest_end_date'
                )
            )
            ->where(new Zend_Db_Expr($adapter->getUnixTimestamp('dates.rule_date') . ' >= from_time'))
            ->where(
                $adapter->getCheckSql(
                    new Zend_Db_Expr('to_time = 0'),
                    new Zend_Db_Expr(1),
                    new Zend_Db_Expr($adapter->getUnixTimestamp('dates.rule_date') . ' <= to_time')
                )
            )
            ->group(
                array(
                    'customer_group_id',
                    'product_id',
                    'stock_id',
                    'dates.rule_date'
                )
            );

        return $select;
    }
}
