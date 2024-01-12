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
 * Product price indexer helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Catalog_Product_Price_Indexer
    extends MP_Warehouse_Helper_Core_Abstract
{
    /**
     * Get version helper
     * 
     * @return MP_Warehouse_Helper_Core_Version
     */
    public function getVersionHelper()
    {
        return $this->getCoreHelper()->getVersionHelper();
    }
    /**
     * Get price helper
     * 
     * @return MP_Warehouse_Helper_Core_Catalog_Product_Price
     */
    public function getPriceHelper()
    {
        return Mage::helper('warehouse/core_catalog_product_price');
    }
    /**
     * Get product helper
     * 
     * @return MP_Warehouse_Helper_Core_Catalog_Product
     */
    public function getProductHelper()
    {
        return $this->getPriceHelper()->getProductHelper();
    }
    /**
     * Get table
     * 
     * @param string $entityName
     * 
     * @return string 
     */
    public function getTable($entityName)
    {
        return $this->getCoreHelper()->getTable($entityName);
    }
    /**
     * Get table name for the entity separated value
     *
     * @param string $entityName
     * @param string $valueType
     * 
     * @return string
     */
    public function getValueTable($entityName, $valueType)
    {
        return $this->getTable($entityName) . '_' . $valueType;
    }
    /**
     * Add attribute to select
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param Varien_Db_Select $select
     * @param string $attrCode
     * @param string|Zend_Db_Expr $entity
     * @param string|Zend_Db_Expr $store
     * @param Zend_Db_Expr $condition
     * @param bool $required
     * 
     * @return Zend_Db_Expr
     */
    public function addAttributeToSelect($adapter, $select, $attrCode, $entity, $store, $condition = null, $required = false)
    {
        $attribute      = $this->getProductHelper()->getAttribute($attrCode);
        $attributeId    = $attribute->getAttributeId();
        $attributeTable = $attribute->getBackend()->getTable();
        $joinType       = !is_null($condition) || $required ? 'join' : 'joinLeft';
        if ($attribute->isScopeGlobal()) {
            $alias = 'ta_' . $attrCode;
            $select->$joinType(
                array($alias => $attributeTable), 
                implode(
                    ' AND ', array(
                    "{$alias}.entity_id = {$entity}", 
                    "{$alias}.attribute_id = {$attributeId}", 
                    "{$alias}.store_id = 0", 
                    )
                ), 
                array()
            );
            $expression = new Zend_Db_Expr("{$alias}.value");
        } else {
            $dAlias = 'tad_' . $attrCode;
            $sAlias = 'tas_' . $attrCode;
            $select->$joinType(
                array($dAlias => $attributeTable), 
                implode(
                    ' AND ', array(
                    "{$dAlias}.entity_id = {$entity}", 
                    "{$dAlias}.attribute_id = {$attributeId}", 
                    "{$dAlias}.store_id = 0", 
                    )
                ), 
                array()
            );
            $select->joinLeft(
                array($sAlias => $attributeTable), 
                implode(
                    ' AND ', array(
                    "{$sAlias}.entity_id = {$entity}", 
                    "{$sAlias}.attribute_id = {$attributeId}", 
                    "{$sAlias}.store_id = {$store}", 
                    )
                ), 
                array()
            );
            if ($this->getVersionHelper()->isGe1600()) {
                $expression = $adapter->getCheckSql(
                    $adapter->getIfNullSql("{$sAlias}.value_id", -1) . ' > 0', 
                    "{$sAlias}.value", 
                    "{$dAlias}.value"
                );
            } else {
                $expression = new Zend_Db_Expr("IF({$sAlias}.value_id > 0, {$sAlias}.value, {$dAlias}.value)");
            }    
        }

        if (!is_null($condition)) {
            $select->where("{$expression}{$condition}");
        }

        return $expression;
    }
    /**
     * Get product compound price index table
     *
     * @return string
     */
    public function getCompoundPriceIndexTable()
    {
        return $this->getTable('catalog/product_index_compound_price');
    }
    /**
     * Get product compound special price index table
     *
     * @return string
     */
    public function getCompoundSpecialPriceIndexTable()
    {
        return $this->getTable('catalog/product_index_compound_special_price');
    }
    /**
     * Get compound price join additional conditions
     * 
     * @param string $tableAlias
     * 
     * @return array
     */
    protected function getCompoundPriceJoinAdditionalConditions($tableAlias)
    {
        return array();
    }
    /**
     * Get compound price join conditions
     * 
     * @param string $tableAlias
     * 
     * @return array
     */
    protected function getCompoundPriceJoinConditions($tableAlias)
    {
        return array_merge(
            array("({$tableAlias}.entity_id = e.entity_id)"), 
            $this->getCompoundPriceJoinAdditionalConditions($tableAlias)
        );
    }
    
    /**
     * Add compound price join to select
     * 
     * @param Zend_Db_Select $select
     * @param string $tableAlias
     * @param string $table
     * 
     * @return self 
     */
    public function addCompoundPriceJoin($select, $tableAlias, $table)
    {
        $select->joinLeft(
            array($tableAlias => $table), 
            implode(' AND ', $this->getCompoundPriceJoinConditions($tableAlias)), 
            array()
        );
        return $this;
    }
    /**
     * Get tier price join additional conditions
     * 
     * @param string $tableAlias
     * 
     * @return array
     */
    protected function getTierPriceJoinAdditionalConditions($tableAlias)
    {
        return array();
    }
    /**
     * Get tier price join conditions
     * 
     * @param string $tableAlias
     * 
     * @return array
     */
    protected function getTierPriceJoinConditions($tableAlias)
    {
        return array_merge(
            array(
                "({$tableAlias}.entity_id = e.entity_id)", 
                "({$tableAlias}.website_id = cw.website_id)", 
                "({$tableAlias}.customer_group_id = cg.customer_group_id)", 
            ), 
            $this->getTierPriceJoinAdditionalConditions($tableAlias)
        );
    }
    /**
     * Add tier price join
     * 
     * @param Zend_Db_Select $select
     * @param string $tableAlias
     * @param string $table
     * 
     * @return self
     */
    public function addTierPriceJoin($select, $tableAlias, $table)
    {
        $select->joinLeft(
            array($tableAlias => $table), 
            implode(' AND ', $this->getTierPriceJoinConditions($tableAlias)), 
            array()
        );
        return $this;
    }
    /**
     * Get group price join additional conditions
     * 
     * @param string $tableAlias
     * 
     * @return array
     */
    protected function getGroupPriceJoinAdditionalConditions($tableAlias)
    {
        return array();
    }
    /**
     * Get group price join conditions
     * 
     * @param string $tableAlias
     * 
     * @return array
     */
    protected function getGroupPriceJoinConditions($tableAlias)
    {
        return array_merge(
            array(
                "({$tableAlias}.entity_id = e.entity_id)", 
                "({$tableAlias}.website_id = cw.website_id)", 
                "({$tableAlias}.customer_group_id = cg.customer_group_id)", 
            ), 
            $this->getGroupPriceJoinAdditionalConditions($tableAlias)
        );
    }
    /**
     * Add group price join
     * 
     * @param Zend_Db_Select $select
     * @param string $tableAlias
     * @param string $table
     * 
     * @return self
     */
    public function addGroupPriceJoin($select, $tableAlias, $table)
    {
        if ($this->getVersionHelper()->isGe1700()) {
            $select->joinLeft(
                array($tableAlias => $table), 
                implode(' AND ', $this->getGroupPriceJoinConditions($tableAlias)), 
                array()
            );
        }

        return $this;
    }
    /**
     * Add store join
     * 
     * @param Zend_Db_Select $select
     * 
     * @return self
     */
    protected function addStoreJoin($select)
    {
        $select->join(
            array('csg' => $this->getTable('core/store_group')),
            'csg.website_id = cw.website_id AND cw.default_group_id = csg.group_id',
            array()
        )->join(
            array('cs' => $this->getTable('core/store')),
            'csg.default_store_id = cs.store_id AND cs.store_id != 0',
            array()
        );
        return $this;
    }
    /**
     * Add price joins
     *
     * @param Varien_Db_Adapter_Interface $adapter
     * @param Zend_Db_Select $select
     *
     * @return self
     */
    public function addPriceJoins($adapter, $select)
    {
        $this->addTierPriceJoin($select, 'tp', $this->getTable('catalog/product_index_tier_price'));
        if ($this->getVersionHelper()->isGe1700()) {
            $this->addGroupPriceJoin($select, 'gp', $this->getTable('catalog/product_index_group_price'));
        }

        return $this;
    }
    /**
     * Get final price select
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * 
     * @return Zend_Db_Select
     */
    public function getFinalPriceSelect($adapter)
    {
        $select = $adapter->select()
            ->from(
                array('e' => $this->getTable('catalog/product')), 
                array('entity_id')
            )->join(
                array('cg' => $this->getTable('customer/customer_group')), 
                '', 
                array('customer_group_id')
            )->join(
                array('cw' => $this->getTable('core/website')), 
                '', 
                array('website_id')
            )->join(
                array('cwd' => $this->getTable('catalog/product_index_website')), 
                'cw.website_id = cwd.website_id', 
                array()
            );
        $this->addStoreJoin($select);
        $select->join(
            array('pw' => $this->getTable('catalog/product_website')),
            '(pw.product_id = e.entity_id) AND (pw.website_id = cw.website_id)', 
            array()
        );
        return $select;
    }
    /**
     * Get final price expression
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param Zend_Db_Expr $price
     * @param Zend_Db_Expr $specialPrice
     * @param Zend_Db_Expr $specialFrom
     * @param Zend_Db_Expr $specialTo
     * 
     * @return Zend_Db_Expr 
     */
    public function getFinalPriceExpr($adapter, $price, $specialPrice, $specialFrom, $specialTo)
    {
        if ($this->getVersionHelper()->isGe1600()) {
            $currentDate    = $adapter->getDatePartSql('cwd.website_date');
            if ($this->getVersionHelper()->isGe1700()) {
                $groupPrice     = $adapter->getCheckSql('gp.price IS NULL', "{$price}", 'gp.price');
            }

            $specialFromDate    = $adapter->getDatePartSql($specialFrom);
            $specialToDate      = $adapter->getDatePartSql($specialTo);
            $specialFromUse     = $adapter->getCheckSql("{$specialFromDate} <= {$currentDate}", '1', '0');
            $specialToUse       = $adapter->getCheckSql("{$specialToDate} >= {$currentDate}", '1', '0');
            $specialFromHas     = $adapter->getCheckSql("{$specialFrom} IS NULL", '1', "{$specialFromUse}");
            $specialToHas       = $adapter->getCheckSql("{$specialTo} IS NULL", '1', "{$specialToUse}");
            $finalPrice         = $adapter->getCheckSql(
                "{$specialFromHas} > 0 AND {$specialToHas} > 0"
                . " AND {$specialPrice} < {$price}", $specialPrice, $price
            );
            if ($this->getVersionHelper()->isGe1700()) {
                $finalPrice         = $adapter->getCheckSql("{$groupPrice} < {$finalPrice}", $groupPrice, $finalPrice);
            }
        } else {
            $currentDate    = new Zend_Db_Expr('cwd.date');
            $finalPrice     = new Zend_Db_Expr(
                "IF(IF({$specialFrom} IS NULL, 1, "
                . "IF(DATE({$specialFrom}) <= {$currentDate}, 1, 0)) > 0 AND IF({$specialTo} IS NULL, 1, "
                . "IF(DATE({$specialTo}) >= {$currentDate}, 1, 0)) > 0 AND {$specialPrice} < {$price}, "
                . "{$specialPrice}, {$price})"
            );
        }

        return $finalPrice;
    }
    /**
     * Get final price select additional columns
     * 
     * @return array
     */
    protected function getFinalPriceSelectAdditionalColumns()
    {
        return array();
    }
    /**
     * Get final price select columns
     * 
     * @param Zend_Db_Expr $price
     * @param Zend_Db_Expr $finalPrice
     * 
     * @return array
     */
    protected function getFinalPriceSelectColumns($price, $finalPrice)
    {
        $columns = array(
            'orig_price'    => $price, 
            'price'         => $finalPrice, 
            'min_price'     => $finalPrice, 
            'max_price'     => $finalPrice, 
            'tier_price'    => new Zend_Db_Expr('tp.min_price'), 
            'base_tier'     => new Zend_Db_Expr('tp.min_price'), 
        );
        if ($this->getVersionHelper()->isGe1700()) {
            $columns = array_merge(
                $columns, array(
                'group_price'      => new Zend_Db_Expr('gp.price'), 
                'base_group_price' => new Zend_Db_Expr('gp.price'), 
                )
            );
        }

        return array_merge($columns, $this->getFinalPriceSelectAdditionalColumns());
    }
    /**
     * Get prepare product index select event additional data
     * 
     * @return array
     */
    protected function getPrepareProductIndexSelectEventAdditionalData()
    {
        return array();
    }
    /**
     * Get prepare product index select event data
     * 
     * @param Varien_Db_Select $select
     * 
     * @return array
     */
    protected function getPrepareProductIndexSelectEventData($select)
    {
        return array_merge(
            array(
                'select'        => $select, 
                'entity_field'  => new Zend_Db_Expr('e.entity_id'), 
                'website_field' => new Zend_Db_Expr('cw.website_id'), 
                'store_field'   => new Zend_Db_Expr('cs.store_id'), 
            ), 
            $this->getPrepareProductIndexSelectEventAdditionalData()
        );
    }
    /**
     * Get prepare product index table event additional data
     * 
     * @return array
     */
    protected function getPrepareProductIndexTableEventAdditionalData()
    {
        return array();
    }
    /**
     * Get prepare product index table event data
     * 
     * @param Varien_Db_Select $select
     * @param string $table
     * 
     * @return array
     */
    protected function getPrepareProductIndexTableEventData($select, $table)
    {
        $data = array(
            'index_table'       => array('i' => $table), 
            'select'            => $select, 
            'entity_id'         => 'i.entity_id', 
            'customer_group_id' => 'i.customer_group_id', 
            'website_id'        => 'i.website_id', 
            'update_fields'     => array('price', 'min_price', 'max_price'), 
        );
        if ($this->getVersionHelper()->isGe1600()) {
            $data['website_date'] = 'wd.website_date';
        } else {
            $data['website_date'] = 'wd.date';
        }

        return array_merge($data, $this->getPrepareProductIndexTableEventAdditionalData());
    }
    /**
     * Prepare price
     *
     * @param Varien_Db_Select $select
     * @param float $price
     *
     * @return Zend_Db_Expr
     */
    protected function preparePrice($select, $price)
    {
        $this->addCompoundPriceJoin($select, 'cp', $this->getCompoundPriceIndexTable());
        return new Zend_Db_Expr("IF (cp.price IS NOT NULL, cp.price, {$price})");
    }
    /**
     * Prepare special price
     *
     * @param Varien_Db_Select $select
     * @param float $price
     *
     * @return Zend_Db_Expr
     */
    protected function prepareSpecialPrice($select, $price)
    {
        $this->addCompoundPriceJoin($select, 'csp', $this->getCompoundSpecialPriceIndexTable());
        return new Zend_Db_Expr("IF (csp.price IS NOT NULL, csp.price, {$price})");
    }
    /**
     * Prepare final price data
     *
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * @param string $typeId
     * @param int|array $entityIds
     *
     * @return self
     */
    public function prepareFinalPriceData($adapter, $table, $typeId, $entityIds = null)
    {
        $adapter->delete($table);
        $select         = $this->getFinalPriceSelect($adapter);
        $select->where('e.type_id=?', $typeId);
        $statusCond     = $adapter->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->addAttributeToSelect($adapter, $select, 'status', 'e.entity_id', 'cs.store_id', $statusCond, true);
        if ($this->getVersionHelper()->isGe1600()) {
            if (Mage::helper('core')->isModuleEnabled('Mage_Tax')) {
                $taxClassId = $this->addAttributeToSelect($adapter, $select, 'tax_class_id', 'e.entity_id', 'cs.store_id');
            } else {
                $taxClassId = new Zend_Db_Expr('0');
            }
        } else {
            $taxClassId = $this->addAttributeToSelect($adapter, $select, 'tax_class_id', 'e.entity_id', 'cs.store_id');
        }

        $select->columns(array('tax_class_id' => $taxClassId));

        $this->addPriceJoins($adapter, $select);

        $price          = $this->addAttributeToSelect($adapter, $select, 'price', 'e.entity_id', 'cs.store_id');
        $price          = $this->preparePrice($select, $price);

        $specialFrom    = $this->addAttributeToSelect($adapter, $select, 'special_from_date', 'e.entity_id', 'cs.store_id');
        $specialTo      = $this->addAttributeToSelect($adapter, $select, 'special_to_date', 'e.entity_id', 'cs.store_id');

        $specialPrice   = $this->addAttributeToSelect($adapter, $select, 'special_price', 'e.entity_id', 'cs.store_id');
        $specialPrice   = $this->prepareSpecialPrice($select, $specialPrice);

        $finalPrice     = $this->getFinalPriceExpr($adapter, $price, $specialPrice, $specialFrom, $specialTo);

        $select->columns($this->getFinalPriceSelectColumns($price, $finalPrice));
        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        Mage::dispatchEvent(
            'prepare_catalog_product_index_select',
            $this->getPrepareProductIndexSelectEventData($select)
        );

        $query = $select->insertFromSelect($table);
        $adapter->query($query);

        $select = $adapter->select()
            ->join(
                array('wd' => $this->getTable('catalog/product_index_website')),
                'i.website_id = wd.website_id',
                array()
            );
        Mage::dispatchEvent(
            'prepare_catalog_product_price_index_table',
            $this->getPrepareProductIndexTableEventData($select, $table)
        );
        return $this;
    }
    /**
     * Add option select store join
     * 
     * @param Zend_Db_Select $select
     * 
     * @return self
     */
    protected function addOptionSelectStoreJoin($select)
    {
        $select->join(
            array('csg' => $this->getTable('core/store_group')), 
            'csg.group_id = cw.default_group_id', 
            array()
        )->join(
            array('cs' => $this->getTable('core/store')), 
            'cs.store_id = csg.default_store_id', 
            array()
        );
        return $this;
    }
    /**
     * Get option type price select additional columns
     * 
     * @return array
     */
    protected function getOptionTypePriceSelectAdditionalColumns()
    {
        return array();
    }
    /**
     * Get option type price select columns
     * 
     * @param Zend_Db_Expr $minPrice
     * @param Zend_Db_Expr $maxPrice
     * @param Zend_Db_Expr $tierPrice
     * @param Zend_Db_Expr $groupPrice
     * 
     * @return array
     */
    protected function getOptionTypePriceSelectColumns($minPrice, $maxPrice, $tierPrice, $groupPrice = null)
    {
        $columns = array(
            'min_price'   => $minPrice, 
            'max_price'   => $maxPrice, 
            'tier_price'  => $tierPrice, 
        );
        if ($this->getVersionHelper()->isGe1700()) {
            $columns['group_price'] = $groupPrice;
        }

        return array_merge($columns, $this->getOptionTypePriceSelectAdditionalColumns());
    }
    /**
     * Get option type price select group additional columns
     * 
     * @return array
     */
    protected function getOptionTypePriceSelectGroupAdditionalColumns()
    {
        return array();
    }
    /**
     * Get option type price select group columns
     * 
     * @return array
     */
    protected function getOptionTypePriceSelectGroupColumns()
    {
        return array_merge(
            array('i.entity_id', 'i.customer_group_id', 'i.website_id', 'o.option_id'), 
            $this->getOptionTypePriceSelectGroupAdditionalColumns()
        );
    }
    /**
     * Get option type price select
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * 
     * @return Zend_Db_Select
     */
    public function getOptionTypePriceSelect($adapter, $table)
    {
        $select = $adapter->select()
            ->from(array('i' => $table), array('entity_id', 'customer_group_id', 'website_id'))
            ->join(array('cw' => $this->getTable('core/website')), 'cw.website_id = i.website_id', array());
        $this->addOptionSelectStoreJoin($select);
        $select->join(
            array('o' => $this->getTable('catalog/product_option')), 
            'o.product_id = i.entity_id', 
            array('option_id')
        )->join(
            array('ot' => $this->getTable('catalog/product_option_type_value')), 
            'ot.option_id = o.option_id', 
            array()
        )->join(
            array('otpd' => $this->getTable('catalog/product_option_type_price')), 
            'otpd.option_type_id = ot.option_type_id AND otpd.store_id = 0', 
            array()
        )->joinLeft(
            array('otps' => $this->getTable('catalog/product_option_type_price')), 
            'otps.option_type_id = otpd.option_type_id AND otpd.store_id = cs.store_id', 
            array()
        )->group($this->getOptionTypePriceSelectGroupColumns());
        $groupPrice = null;
        if ($this->getVersionHelper()->isGe1600()) {
            $optPriceType   = $adapter->getCheckSql('otps.option_type_price_id > 0', 'otps.price_type', 'otpd.price_type');
            $optPriceValue  = $adapter->getCheckSql('otps.option_type_price_id > 0', 'otps.price', 'otpd.price');
            $minPriceRound  = new Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 8)");
            $minPriceExpr   = $adapter->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $minPriceRound);
            $minPriceMin    = new Zend_Db_Expr("MIN({$minPriceExpr})");
            $minPrice       = $adapter->getCheckSql("MIN(o.is_require) = 1", $minPriceMin, '0');
            $tierPriceRound = new Zend_Db_Expr("ROUND(i.base_tier * ({$optPriceValue} / 100), 8)");
            $tierPriceExpr  = $adapter->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $tierPriceRound);
            $tierPriceMin   = new Zend_Db_Expr("MIN($tierPriceExpr)");
            $tierPriceValue = $adapter->getCheckSql("MIN(o.is_require) > 0", $tierPriceMin, 0);
            $tierPrice      = $adapter->getCheckSql("MIN(i.base_tier) IS NOT NULL", $tierPriceValue, "NULL");
            
            if ($this->getVersionHelper()->isGe1700()) {
                $groupPriceRound = new Zend_Db_Expr("ROUND(i.base_group_price * ({$optPriceValue} / 100), 8)");
                $groupPriceExpr  = $adapter->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $groupPriceRound);
                $groupPriceMin   = new Zend_Db_Expr("MIN($groupPriceExpr)");
                $groupPriceValue = $adapter->getCheckSql("MIN(o.is_require) > 0", $groupPriceMin, 0);
                $groupPrice      = $adapter->getCheckSql("MIN(i.base_group_price) IS NOT NULL", $groupPriceValue, "NULL");
            }
            
            $maxPriceRound  = new Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 8)");
            $maxPriceExpr   = $adapter->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $maxPriceRound);
            $maxPrice       = $adapter->getCheckSql(
                "(MIN(o.type)='radio' OR MIN(o.type)='drop_down')",
                "MAX($maxPriceExpr)", "SUM($maxPriceExpr)"
            );
        } else {
            $minPrice = new Zend_Db_Expr(
                "IF(o.is_require, MIN(IF(IF(otps.option_type_price_id>0, otps.price_type, "
                . "otpd.price_type)='fixed', IF(otps.option_type_price_id>0, otps.price, otpd.price), "
                . "ROUND(i.price * (IF(otps.option_type_price_id>0, otps.price, otpd.price) / 100), 8))), 0)"
            );
            $tierPrice = new Zend_Db_Expr(
                "IF(i.base_tier IS NOT NULL, IF(o.is_require, "
                . "MIN(IF(IF(otps.option_type_price_id>0, otps.price_type, otpd.price_type)='fixed', "
                . "IF(otps.option_type_price_id>0, otps.price, otpd.price), "
                . "ROUND(i.base_tier * (IF(otps.option_type_price_id>0, otps.price, otpd.price) / 100), 8))), 0), NULL)"
            );
            $maxPrice = new Zend_Db_Expr(
                "IF((o.type='radio' OR o.type='drop_down'), "
                . "MAX(IF(IF(otps.option_type_price_id>0, otps.price_type, otpd.price_type)='fixed', "
                . "IF(otps.option_type_price_id>0, otps.price, otpd.price), "
                . "ROUND(i.price * (IF(otps.option_type_price_id>0, otps.price, otpd.price) / 100), 8))), "
                . "SUM(IF(IF(otps.option_type_price_id>0, otps.price_type, otpd.price_type)='fixed', "
                . "IF(otps.option_type_price_id>0, otps.price, otpd.price), "
                . "ROUND(i.price * (IF(otps.option_type_price_id>0, otps.price, otpd.price) / 100), 8))))"
            );
        }

        $select->columns($this->getOptionTypePriceSelectColumns($minPrice, $maxPrice, $tierPrice, $groupPrice));
        return $select;
    }
    
    /**
     * Get option price select additional columns
     * 
     * @return array
     */
    protected function getOptionPriceSelectAdditionalColumns()
    {
        return array();
    }
    /**
     * Get option price select columns
     * 
     * @param Zend_Db_Expr $minPrice
     * @param Zend_Db_Expr $maxPrice
     * @param Zend_Db_Expr $tierPrice
     * @param Zend_Db_Expr $groupPrice
     * 
     * @return array
     */
    protected function getOptionPriceSelectColumns($minPrice, $maxPrice, $tierPrice, $groupPrice = null)
    {
        $columns = array(
            'min_price'   => $minPrice, 
            'max_price'   => $maxPrice, 
            'tier_price'  => $tierPrice, 
        );
        if ($this->getVersionHelper()->isGe1700()) {
            $columns['group_price'] = $groupPrice;
        }

        return array_merge($columns, $this->getOptionPriceSelectAdditionalColumns());
    }
    /**
     * Get option price select
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * 
     * @return Zend_Db_Select
     */
    public function getOptionPriceSelect($adapter, $table)
    {
        $select = $adapter->select()
            ->from(array('i' => $table), array('entity_id', 'customer_group_id', 'website_id'))
            ->join(array('cw' => $this->getTable('core/website')), 'cw.website_id = i.website_id', array());
        $this->addOptionSelectStoreJoin($select);
        $select->join(
            array('o' => $this->getTable('catalog/product_option')), 
            'o.product_id = i.entity_id', 
            array('option_id')
        )->join(
            array('opd' => $this->getTable('catalog/product_option_price')), 
            'opd.option_id = o.option_id AND opd.store_id = 0', 
            array()
        )->joinLeft(
            array('ops' => $this->getTable('catalog/product_option_price')), 
            'ops.option_id = opd.option_id AND ops.store_id = cs.store_id', 
            array()
        );
        $groupPrice = null;
        if ($this->getVersionHelper()->isGe1600()) {
            $optPriceType   = $adapter->getCheckSql('ops.option_price_id > 0', 'ops.price_type', 'opd.price_type');
            $optPriceValue  = $adapter->getCheckSql('ops.option_price_id > 0', 'ops.price', 'opd.price');
            $minPriceRound  = new Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 8)");
            $priceExpr      = $adapter->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $minPriceRound);
            $minPrice       = $adapter->getCheckSql("{$priceExpr} > 0 AND o.is_require > 1", $priceExpr, 0);
            $maxPrice       = $priceExpr;
            $tierPriceRound = new Zend_Db_Expr("ROUND(i.base_tier * ({$optPriceValue} / 100), 8)");
            $tierPriceExpr  = $adapter->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $tierPriceRound);
            $tierPriceValue = $adapter->getCheckSql("{$tierPriceExpr} > 0 AND o.is_require > 0", $tierPriceExpr, 0);
            $tierPrice      = $adapter->getCheckSql("i.base_tier IS NOT NULL", $tierPriceValue, "NULL");
            
            if ($this->getVersionHelper()->isGe1700()) {
                $groupPriceRound = new Zend_Db_Expr("ROUND(i.base_group_price * ({$optPriceValue} / 100), 8)");
                $groupPriceExpr  = $adapter->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $groupPriceRound);
                $groupPriceValue = $adapter->getCheckSql("{$groupPriceExpr} > 0 AND o.is_require > 0", $groupPriceExpr, 0);
                $groupPrice      = $adapter->getCheckSql("i.base_group_price IS NOT NULL", $groupPriceValue, "NULL");
            }
        } else {
            $minPrice = new Zend_Db_Expr(
                "IF((@price:=IF(IF(ops.option_price_id>0, ops.price_type, opd.price_type)='fixed',"
                . " IF(ops.option_price_id>0, ops.price, opd.price), ROUND(i.price * (IF(ops.option_price_id>0, "
                . "ops.price, opd.price) / 100), 8))) AND o.is_require, @price,0)"
            );
            $maxPrice = new Zend_Db_Expr("@price");
            $tierPrice = new Zend_Db_Expr(
                "IF(i.base_tier IS NOT NULL, IF((@tier_price:=IF(IF(ops.option_price_id>0, "
                . "ops.price_type, opd.price_type)='fixed', IF(ops.option_price_id>0, ops.price, opd.price), "
                . "ROUND(i.base_tier * (IF(ops.option_price_id>0, ops.price, opd.price) / 100), 8))) AND o.is_require, "
                . "@tier_price, 0), NULL)"
            );
        }

        $select->columns($this->getOptionPriceSelectColumns($minPrice, $maxPrice, $tierPrice, $groupPrice));
        return $select;
    }
    /**
     * Get aggregated option price select group additional columns
     * 
     * @return array
     */
    protected function getAggregatedOptionPriceSelectGroupAdditionalColumns()
    {
        return array();
    }
    /**
     * Get aggregated option price select group columns
     * 
     * @return array
     */
    protected function getAggregatedOptionPriceSelectGroupColumns()
    {
        return array_merge(
            array('entity_id', 'customer_group_id', 'website_id'), 
            $this->getAggregatedOptionPriceSelectGroupAdditionalColumns()
        );
    }
    /**
     * Get aggregated option price select additional columns
     * 
     * @return array
     */
    protected function getAggregatedOptionPriceSelectAdditionalColumns()
    {
        return array();
    }
    /**
     * Get aggregated option price select columns
     * 
     * @return array
     */
    protected function getAggregatedOptionPriceSelectColumns()
    {
        $columns = array(
            'entity_id', 'customer_group_id', 'website_id', 
            'min_price' => 'SUM(min_price)', 'max_price' => 'SUM(max_price)', 
            'tier_price' => 'SUM(tier_price)', 
        );
        if ($this->getVersionHelper()->isGe1700()) {
            $columns['group_price'] = 'SUM(group_price)';
        }

        return array_merge($columns, $this->getAggregatedOptionPriceSelectAdditionalColumns());
    }
    /**
     * Get aggregated option price select
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * 
     * @return Zend_Db_Select
     */
    public function getAggregatedOptionPriceSelect($adapter, $table)
    {
        $select = $adapter->select()
            ->from(array($table), $this->getAggregatedOptionPriceSelectColumns())
            ->group($this->getAggregatedOptionPriceSelectGroupColumns());
        return $select;
    }
    /**
     * Get option final price select join additional conditions
     * 
     * @return array
     */
    protected function getOptionFinalPriceSelectJoinAdditionalConditions()
    {
        return array();
    }
    /**
     * Get option final price select join conditions
     * 
     * @return array
     */
    protected function getOptionFinalPriceSelectJoinConditions()
    {
        return array_merge(
            array(
                '(i.entity_id = io.entity_id)', 
                '(i.customer_group_id = io.customer_group_id)', 
                '(i.website_id = io.website_id)', 
            ), 
            $this->getOptionFinalPriceSelectJoinAdditionalConditions()
        );
    }
    /**
     * Get option final price select
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * 
     * @return Zend_Db_Select
     */
    public function getOptionFinalPriceSelect($adapter, $table)
    {
        $select = $adapter->select()
            ->join(
                array('io' => $table), 
                implode(' AND ', $this->getOptionFinalPriceSelectJoinConditions()), 
                array()
            );
        if ($this->getVersionHelper()->isGe1600()) {
            $tierPrice = $adapter->getCheckSql('i.tier_price IS NOT NULL', 'i.tier_price + io.tier_price', 'NULL');
        } else {
            $tierPrice = new Zend_Db_Expr('IF(i.tier_price IS NOT NULL, i.tier_price + io.tier_price, NULL)');
        }

        $select->columns(
            array(
            'min_price'     => new Zend_Db_Expr('i.min_price + io.min_price'),
            'max_price'     => new Zend_Db_Expr('i.max_price + io.max_price'),
            'tier_price'    => $tierPrice, 
            )
        );
        if ($this->getVersionHelper()->isGe1700()) {
            $select->columns(
                array(
                'group_price'   => $adapter->getCheckSql(
                    'i.group_price IS NOT NULL',
                    'i.group_price + io.group_price', 'NULL'
                ), 
                )
            );
        }

        return $select;
    }
    /**
     * Apply custom option
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * @param string $aggregateTable
     * @param string $priceTable
     * @param bool $useIdxTable
     * 
     * @return self
     */
    public function applyCustomOption($adapter, $table, $aggregateTable, $priceTable, $useIdxTable)
    {
        $adapter->delete($aggregateTable);
        $adapter->delete($priceTable);
        
        $select             = $this->getOptionTypePriceSelect($adapter, $table);
        $query              = $select->insertFromSelect($aggregateTable);
        $adapter->query($query);
        
        $select             = $this->getOptionPriceSelect($adapter, $table);
        $query              = $select->insertFromSelect($aggregateTable);
        $adapter->query($query);
        
        $select             = $this->getAggregatedOptionPriceSelect($adapter, $aggregateTable);
        $query              = $select->insertFromSelect($priceTable);
        $adapter->query($query);
        
        
        $select             = $this->getOptionFinalPriceSelect($adapter, $priceTable);
        $query = $select->crossUpdateFromSelect(array('i' => $table));
        $adapter->query($query);
        
        if ($this->getVersionHelper()->isGe1620()) {
            $adapter->delete($aggregateTable);
            $adapter->delete($priceTable);
        } else {
            if ($useIdxTable) {
                $adapter->truncate($aggregateTable);
                $adapter->truncate($priceTable);
            } else {
                $adapter->delete($aggregateTable);
                $adapter->delete($priceTable);
            }
        }
        
        return $this;
    }
    /**
     * Get price select additional columns
     * 
     * @return array
     */
    protected function getPriceSelectAdditionalColumns()
    {
        return array();
    }
    /**
     * Get price select columns
     * 
     * @return array
     */
    public function getPriceSelectColumns()
    {
        $columns = array(
            'entity_id'         => 'entity_id', 
            'customer_group_id' => 'customer_group_id', 
            'website_id'        => 'website_id', 
            'tax_class_id'      => 'tax_class_id', 
            'price'             => 'orig_price', 
            'final_price'       => 'price', 
            'min_price'         => 'min_price', 
            'max_price'         => 'max_price', 
            'tier_price'        => 'tier_price', 
        );
        if ($this->getVersionHelper()->isGe1700()) {
            $columns['group_price'] = 'group_price';
        }

        return array_merge($columns, $this->getPriceSelectAdditionalColumns());
    }
    /**
     * Mode price data to index table
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * @param string $indexTable
     * @param bool $useIdxTable
     * 
     * @return self
     */
    public function movePriceDataToIndexTable($adapter, $table, $indexTable, $useIdxTable)
    {
        $columns            = $this->getPriceSelectColumns();
        $select             = $adapter->select()->from($table, $columns);
        $query              = $select->insertFromSelect($indexTable);
        $adapter->query($query);
        
        if ($this->getVersionHelper()->isGe1620()) {
            $adapter->delete($table);
        } else {
            if ($useIdxTable) {
                $adapter->truncate($table);
            } else {
                $adapter->delete($table);
            }
        }
        
        return $this;
    }
    /**
     * Get configurable option price select additional columns
     * 
     * @return array
     */
    protected function getConfigurableOptionPriceSelectAdditionalColumns()
    {
        if ($this->getVersionHelper()->isGe1600()) {
            return array();
        } else {
            return array();
        }
    }
    /**
     * Get configurable option price select columns
     * 
     * @param Zend_Db_Expr $price
     * @param Zend_Db_Expr $tierPrice
     * @param Zend_Db_Expr $groupPrice
     * 
     * @return array
     */
    protected function getConfigurableOptionPriceSelectColumns($price, $tierPrice, $groupPrice = null)
    {
        if ($this->getVersionHelper()->isGe1600()) {
            $columns = array(
                'price'      => $price,
                'tier_price' => $tierPrice, 
            );
            if ($this->getVersionHelper()->isGe1700()) {
                $columns['group_price'] = $groupPrice;
            }
        } else {
            $columns = array($price, $tierPrice);
        }

        return array_merge($columns, $this->getConfigurableOptionPriceSelectAdditionalColumns());
    }
    /**
     * Get configurable option price select group additional columns
     * 
     * @return array
     */
    protected function getConfigurableOptionPriceSelectGroupAdditionalColumns()
    {
        return array();
    }
    /**
     * Get configurable option price select group columns
     * 
     * @return array
     */
    protected function getConfigurableOptionPriceSelectGroupColumns()
    {
        return array_merge(
            array('l.parent_id', 'i.customer_group_id', 'i.website_id', 'l.product_id'), 
            $this->getConfigurableOptionPriceSelectGroupAdditionalColumns()
        );
    }
    /**
     * Get configurable option price select
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * 
     * @return Zend_Db_Select
     */
    public function getConfigurableOptionPriceSelect($adapter, $table)
    {
        $select = $adapter->select()
            ->from(array('i' => $table), null)
            ->join(
                array('l' => $this->getTable('catalog/product_super_link')), 
                'l.parent_id = i.entity_id', 
                array('parent_id', 'product_id')
            )->columns(array('customer_group_id', 'website_id'), 'i')
            ->join(
                array('a' => $this->getTable('catalog/product_super_attribute')), 
                'l.parent_id = a.product_id', 
                array()
            )->join(
                array('cp' => $this->getValueTable('catalog/product', 'int')), 
                implode(
                    ' AND ', array(
                    'l.product_id = cp.entity_id', 
                    'cp.attribute_id = a.attribute_id', 
                    'cp.store_id = 0', 
                    )
                ), 
                array()
            )->joinLeft(
                array('apd' => $this->getTable('catalog/product_super_attribute_pricing')), 
                implode(
                    ' AND ', array(
                    'a.product_super_attribute_id = apd.product_super_attribute_id', 
                    'apd.website_id = 0', 
                    'cp.value = apd.value_index', 
                    )
                ), 
                array()
            )->joinLeft(
                array('apw' => $this->getTable('catalog/product_super_attribute_pricing')), 
                implode(
                    ' AND ', array(
                    'a.product_super_attribute_id = apw.product_super_attribute_id', 
                    'apw.website_id = i.website_id', 
                    'cp.value = apw.value_index', 
                    )
                ), 
                array()
            )->join(
                array('le' => $this->getTable('catalog/product')),
                'le.entity_id = l.product_id', 
                array()
            )->where('le.required_options=0')
            ->group($this->getConfigurableOptionPriceSelectGroupColumns());
        $priceColumn        = null;
        $tierPriceColumn    = null;
        $groupPriceColumn   = null;
        if ($this->getVersionHelper()->isGe1600()) {
            $priceExpression = $adapter->getCheckSql('apw.value_id IS NOT NULL', 'apw.pricing_value', 'apd.pricing_value');
            $percentExpr = $adapter->getCheckSql('apw.value_id IS NOT NULL', 'apw.is_percent', 'apd.is_percent');
            $roundExpr = "ROUND(i.price * ({$priceExpression} / 100), 8)";
            $roundPriceExpr = $adapter->getCheckSql("{$percentExpr} = 1", $roundExpr, $priceExpression);
            $priceColumn = $adapter->getCheckSql("{$priceExpression} IS NULL", '0', $roundPriceExpr);
            $priceColumn = new Zend_Db_Expr("SUM({$priceColumn})");
    
            $tierPrice = $priceExpression;
            $tierRoundPriceExp = $adapter->getCheckSql("{$percentExpr} = 1", $roundExpr, $tierPrice);
            $tierPriceExp = $adapter->getCheckSql("{$tierPrice} IS NULL", '0', $tierRoundPriceExp);
            $tierPriceColumn = $adapter->getCheckSql("MIN(i.tier_price) IS NOT NULL", "SUM({$tierPriceExp})", 'NULL');
            
            if ($this->getVersionHelper()->isGe1700()) {
                $groupPrice = $priceExpression;
                $groupRoundPriceExp = $adapter->getCheckSql("{$percentExpr} = 1", $roundExpr, $groupPrice);
                $groupPriceExp = $adapter->getCheckSql("{$groupPrice} IS NULL", '0', $groupRoundPriceExp);
                $groupPriceColumn = $adapter->getCheckSql("MIN(i.group_price) IS NOT NULL", "SUM({$groupPriceExp})", 'NULL');
            }
        } else {
            $priceColumn = new Zend_Db_Expr(
                "SUM(IF((@price:=IF(apw.value_id, apw.pricing_value, apd.pricing_value))"
                . " IS NULL, 0, IF(IF(apw.value_id, apw.is_percent, apd.is_percent) = 1, "
                . "ROUND(i.price * (@price / 100), 8), @price)))"
            );
            $tierPriceColumn = new Zend_Db_Expr(
                "IF(i.tier_price IS NOT NULL, SUM(IF((@tier_price:="
                . "IF(apw.value_id, apw.pricing_value, apd.pricing_value)) IS NULL, 0, IF("
                . "IF(apw.value_id, apw.is_percent, apd.is_percent) = 1, "
                . "ROUND(i.price * (@tier_price / 100), 8), @tier_price))), NULL)"
            );
        }

        $select->columns(
            $this->getConfigurableOptionPriceSelectColumns($priceColumn, $tierPriceColumn, $groupPriceColumn)
        );
        return $select;
    }
    /**
     * Get aggregated configurable option price select join additional columns
     * 
     * @return array
     */
    protected function getAggregatedConfigurableOptionPriceSelectJoinAdditionalColumns()
    {
        return array();
    }
    /**
     * Get aggregated configurable option price select join columns
     * 
     * @return array
     */
    protected function getAggregatedConfigurableOptionPriceSelectJoinColumns()
    {
        $columns = array(
            'parent_id', 'customer_group_id', 'website_id', 
            'MIN(price)', 'MAX(price)', 'MIN(tier_price)', 
        );
        if ($this->getVersionHelper()->isGe1700()) {
            array_push($columns, 'MIN(group_price)');
        }

        return array_merge(
            $columns, $this->getAggregatedConfigurableOptionPriceSelectJoinAdditionalColumns()
        );
    }
    /**
     * Get aggregated configurable option price select join group additional columns
     * 
     * @return array
     */
    protected function getAggregatedConfigurableOptionPriceSelectJoinGroupAdditionalColumns()
    {
        return array();
    }
    /**
     * Get aggregated configurable option price select join group columns
     * 
     * @return array
     */
    protected function getAggregatedConfigurableOptionPriceSelectJoinGroupColumns()
    {
        return array_merge(
            array('parent_id', 'customer_group_id', 'website_id'), 
            $this->getAggregatedConfigurableOptionPriceSelectJoinGroupAdditionalColumns()
        );
    }
    /**
     * Get aggregated configurable option price select
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * 
     * @return Zend_Db_Select 
     */
    public function getAggregatedConfigurableOptionPriceSelect($adapter, $table)
    {
        $select = $adapter->select()
            ->from(array($table), $this->getAggregatedConfigurableOptionPriceSelectJoinColumns())
            ->group($this->getAggregatedConfigurableOptionPriceSelectJoinGroupColumns());
        return $select;
    }
    /**
     * Get configurable option final price select join additional conditions
     * 
     * @return array
     */
    protected function getConfigurableOptionFinalPriceSelectJoinAdditionalConditions()
    {
        return array();
    }
    /**
     * Get configurable option final price select join conditions
     * 
     * @return array
     */
    protected function getConfigurableOptionFinalPriceSelectJoinConditions()
    {
        return array_merge(
            array(
                '(i.entity_id = io.entity_id)', 
                '(i.customer_group_id = io.customer_group_id)', 
                '(i.website_id = io.website_id)', 
            ), 
            $this->getConfigurableOptionFinalPriceSelectJoinAdditionalConditions()
        );
    }
    /**
     * Get configurable option final price select
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * 
     * @return Zend_Db_Select
     */
    public function getConfigurableOptionFinalPriceSelect($adapter, $table)
    {
        $select = $adapter->select()
            ->join(
                array('io' => $table), 
                implode(' AND ', $this->getConfigurableOptionFinalPriceSelectJoinConditions()), 
                array()
            );
        if ($this->getVersionHelper()->isGe1600()) {
            $tierPrice = $adapter->getCheckSql('i.tier_price IS NOT NULL', 'i.tier_price + io.tier_price', 'NULL');
        } else {
            $tierPrice = new Zend_Db_Expr('IF(i.tier_price IS NOT NULL, i.tier_price + io.tier_price, NULL)');
        }

        $select->columns(
            array(
            'min_price'  => new Zend_Db_Expr('i.min_price + io.min_price'),
            'max_price'  => new Zend_Db_Expr('i.max_price + io.max_price'),
            'tier_price' => $tierPrice, 
            )
        );
        
        if ($this->getVersionHelper()->isGe1700()) {
            $select->columns(
                array(
                'group_price' => $adapter->getCheckSql(
                    'i.group_price IS NOT NULL',
                    'i.group_price + io.group_price', 'NULL'
                ),
                )
            );
        }

        return $select;
    }
    /**
     * Apply configurable option
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * @param string $aggregateTable
     * @param string $priceTable
     * @param bool $useIdxTable
     * 
     * @return self
     */
    public function applyConfigurableOption($adapter, $table, $aggregateTable, $priceTable, $useIdxTable)
    {
        $adapter->delete($aggregateTable);
        $adapter->delete($priceTable);
        
        $select             = $this->getConfigurableOptionPriceSelect($adapter, $table);
        $query              = $select->insertFromSelect($aggregateTable);
        $adapter->query($query);
        
        $select             = $this->getAggregatedConfigurableOptionPriceSelect($adapter, $aggregateTable);
        $query              = $select->insertFromSelect($priceTable);
        $adapter->query($query);
        
        $select = $this->getConfigurableOptionFinalPriceSelect($adapter, $priceTable);
        $query = $select->crossUpdateFromSelect(array('i' => $table));
        $adapter->query($query);
        
        if ($this->getVersionHelper()->isGe1620()) {
            $adapter->delete($aggregateTable);
            $adapter->delete($priceTable);
        } else {
            if ($useIdxTable) {
                $adapter->truncate($aggregateTable);
                $adapter->truncate($priceTable);
            } else {
                $adapter->delete($aggregateTable);
                $adapter->delete($priceTable);
            }
        }
        
        return $this;
    }
    /**
     * Get grouped product price select additional columns
     * 
     * @return array
     */
    protected function getGroupedProductPriceSelectAdditionalColumns()
    {
        return array();
    }
    /**
     * Get grouped product price select columns
     * 
     * @param Zend_Db_Expr $taxClassId
     * @param Zend_Db_Expr $minPrice
     * @param Zend_Db_Expr $maxPrice
     * 
     * @return array
     */
    protected function getGroupedProductPriceSelectColumns($taxClassId, $minPrice, $maxPrice)
    {
        $columns = array(
            'tax_class_id'  => $taxClassId, 
            'price'         => new Zend_Db_Expr('NULL'), 
            'final_price'   => new Zend_Db_Expr('NULL'), 
            'min_price'     => $minPrice, 
            'max_price'     => $maxPrice, 
            'tier_price'    => new Zend_Db_Expr('NULL'), 
        );
        if ($this->getVersionHelper()->isGe1700()) {
            $columns = array_merge(
                $columns, array(
                'group_price'   => new Zend_Db_Expr('NULL'), 
                )
            );
        }

        return array_merge($columns, $this->getGroupedProductPriceSelectAdditionalColumns());
    }
    
    /**
     * Get grouped product price select group additional columns
     * 
     * @return array
     */
    protected function getGroupedProductPriceSelectGroupAdditionalColumns()
    {
        return array();
    }
    /**
     * Get grouped product price select group columns
     * 
     * @return array
     */
    protected function getGroupedProductPriceSelectGroupColumns()
    {
        return array_merge(
            array('e.entity_id', 'cg.customer_group_id', 'cw.website_id'), 
            $this->getGroupedProductPriceSelectGroupAdditionalColumns()
        );
    }
    /**
     * Prepare grouped product price data
     *
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $indexTable
     * @param string $typeId
     * @param int|array $entityIds
     *
     * @return self
     */
    public function prepareGroupedProductPriceData($adapter, $indexTable, $typeId, $entityIds = null)
    {
        $select = $adapter->select()
            ->from(array('e' => $this->getTable('catalog/product')), 'entity_id')
            ->join(
                array('l' => $this->getTable('catalog/product_link')),
                implode(
                    ' AND ', array(
                    'e.entity_id = l.product_id',
                    'l.link_type_id='.Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED,
                    )
                ),
                array()
            )->join(
                array('cg' => $this->getTable('customer/customer_group')),
                '',
                array('customer_group_id')
            )->join(
                array('cw' => $this->getTable('core/website')),
                '',
                array('website_id')
            );
        $this->addStoreJoin($select);
        $select->join(
            array('pw' => $this->getTable('catalog/product_website')),
            'pw.product_id = e.entity_id AND pw.website_id = cw.website_id',
            array()
        );
        if ($this->getVersionHelper()->isGe1600()) {
            $minCheckSql = $adapter->getCheckSql('le.required_options = 0', 'i.min_price', 0);
            $maxCheckSql = $adapter->getCheckSql('le.required_options = 0', 'i.max_price', 0);
            $taxClassId  = $adapter->getCheckSql('MIN(i.tax_class_id) IS NULL', '0', 'MIN(i.tax_class_id)');
            $minPrice    = new Zend_Db_Expr('MIN(' . $minCheckSql . ')');
            $maxPrice    = new Zend_Db_Expr('MAX(' . $maxCheckSql . ')');
        } else {
            $taxClassId  = new Zend_Db_Expr('IFNULL(i.tax_class_id, 0)');
            $minPrice    = new Zend_Db_Expr('MIN(IF(le.required_options = 0, i.min_price, 0))');
            $maxPrice    = new Zend_Db_Expr('MAX(IF(le.required_options = 0, i.max_price, 0))');
        }

        $select->join(
            array('le' => $this->getTable('catalog/product')),
            'le.entity_id = l.linked_product_id',
            array()
        );
        $select->join(
            array('i' => $indexTable),
            implode(
                ' AND ', array(
                '(i.entity_id = l.linked_product_id)',
                '(i.website_id = cw.website_id)',
                '(i.customer_group_id = cg.customer_group_id)',
                )
            ),
            $this->getGroupedProductPriceSelectColumns($taxClassId, $minPrice, $maxPrice)
        );
        $select->group($this->getGroupedProductPriceSelectGroupColumns())
            ->where('e.type_id=?', $typeId);

        if (!is_null($entityIds)) {
            $select->where('l.product_id IN(?)', $entityIds);
        }

        Mage::dispatchEvent(
            'catalog_product_prepare_index_select',
            $this->getPrepareProductIndexSelectEventData($select)
        );

        $query = $select->insertFromSelect($indexTable);
        $adapter->query($query);
        return $this;
    }
    /**
     * Get downloadable link price select additional columns
     * 
     * @return array
     */
    protected function getDownloadableLinkPriceSelectAdditionalColumns()
    {
        return array();
    }
    /**
     * Get downloadable link price select columns
     * 
     * @param Zend_Db_Expr $minPrice
     * @param Zend_Db_Expr $maxPrice
     * 
     * @return array
     */
    protected function getDownloadableLinkPriceSelectColumns($minPrice, $maxPrice)
    {
        return array_merge(
            array(
                'min_price' => $minPrice,
                'max_price' => $maxPrice, 
            ), 
            $this->getDownloadableLinkPriceSelectAdditionalColumns()
        );
    }
    /**
     * Get downloadable link price select group additional columns
     * 
     * @return array
     */
    protected function getDownloadableLinkPriceSelectGroupAdditionalColumns()
    {
        return array();
    }
    /**
     * Get downloadable link price select group columns
     * 
     * @return array
     */
    protected function getDownloadableLinkPriceSelectGroupColumns()
    {
        return array_merge(
            array('i.entity_id', 'i.customer_group_id', 'i.website_id'), 
            $this->getDownloadableLinkPriceSelectGroupAdditionalColumns()
        );
    }
    /**
     * Get downloadable link price select
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * 
     * @return Zend_Db_Select
     */
    public function getDownloadableLinkPriceSelect($adapter, $table)
    {
        $dlType = $this->getProductHelper()
            ->getAttribute('links_purchased_separately');
        if ($this->getVersionHelper()->isGe1600()) {
            $ifPrice = $adapter->getIfNullSql('dlpw.price_id', 'dlpd.price');
            $minPrice = new Zend_Db_Expr('MIN('.$ifPrice.')');
            $maxPrice = new Zend_Db_Expr('SUM('.$ifPrice.')');
        } else {
            $minPrice = new Zend_Db_Expr('MIN(IF(dlpw.price_id, dlpw.price, dlpd.price))');
            $maxPrice = new Zend_Db_Expr('SUM(IF(dlpw.price_id, dlpw.price, dlpd.price))');
        }

        $select = $adapter->select()
            ->from(
                array('i' => $table), 
                array('entity_id', 'customer_group_id', 'website_id')
            )->join(
                array('dl' => $dlType->getBackend()->getTable()), 
                implode(
                    ' AND ', array(
                    "dl.entity_id = i.entity_id", 
                    "dl.attribute_id = {$dlType->getAttributeId()}", 
                    "dl.store_id = 0", 
                    )
                ), 
                array()
            )->join(
                array('dll' => $this->getTable('downloadable/link')), 
                'dll.product_id = i.entity_id', 
                array()
            )->join(
                array('dlpd' => $this->getTable('downloadable/link_price')), 
                implode(
                    ' AND ', array(
                    'dll.link_id = dlpd.link_id', 
                    'dlpd.website_id = 0', 
                    )
                ), 
                array()
            )->joinLeft(
                array('dlpw' => $this->getTable('downloadable/link_price')), 
                implode(
                    ' AND ', array(
                    'dlpd.link_id = dlpw.link_id', 
                    'dlpw.website_id = i.website_id', 
                    )
                ), 
                array()
            )->where('dl.value = ?', 1)
            ->group($this->getDownloadableLinkPriceSelectGroupColumns())
            ->columns($this->getDownloadableLinkPriceSelectColumns($minPrice, $maxPrice));
        return $select;
    }
    /**
     * Get downloadable link final price select
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * 
     * @return Zend_Db_Select
     */
    public function getDownloadableLinkFinalPriceSelect($adapter, $table)
    {
        if ($this->getVersionHelper()->isGe1600()) {
            $ifTierPrice = $adapter->getCheckSql('i.tier_price IS NOT NULL', '(i.tier_price + id.min_price)', 'NULL');
            
            if ($this->getVersionHelper()->isGe1700()) {
                $ifGroupPrice = $adapter->getCheckSql('i.group_price IS NOT NULL', '(i.group_price + id.min_price)', 'NULL');
            }
            
            $tierPrice = new Zend_Db_Expr($ifTierPrice);
        } else {
            $tierPrice = new Zend_Db_Expr('IF(i.tier_price IS NOT NULL, i.tier_price + id.min_price, NULL)');
        }

        $select = $adapter->select()
            ->join(
                array('id' => $table), 
                implode(
                    ' AND ', array(
                    'i.entity_id = id.entity_id', 
                    'i.customer_group_id = id.customer_group_id', 
                    'i.website_id = id.website_id', 
                    )
                ), 
                array()
            )->columns(
                array(
                'min_price'     => new Zend_Db_Expr('i.min_price + id.min_price'),
                'max_price'     => new Zend_Db_Expr('i.max_price + id.max_price'),
                'tier_price'    => $tierPrice, 
                )
            );
        
        if ($this->getVersionHelper()->isGe1700()) {
            $select->columns(
                array(
                'group_price'   => new Zend_Db_Expr($ifGroupPrice), 
                )
            );
        }

        return $select;
    }
    /**
     * Apply downloadable link
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * @param string $priceTable
     * @param bool $useIdxTable
     * 
     * @return self
     */
    public function applyDownloadableLink($adapter, $table, $priceTable, $useIdxTable)
    {
        $adapter->delete($priceTable);
        
        $select             = $this->getDownloadableLinkPriceSelect($adapter, $table);
        $query = $select->insertFromSelect($priceTable);
        $adapter->query($query);
        
        $select             = $this->getDownloadableLinkFinalPriceSelect($adapter, $priceTable);
        $query = $select->crossUpdateFromSelect(array('i' => $table));
        $adapter->query($query);
        
        if ($this->getVersionHelper()->isGe1620()) {
            $adapter->delete($priceTable);
        } else {
            if ($useIdxTable) {
                $adapter->truncate($priceTable);
            } else {
                $adapter->delete($priceTable);
            }
        }
        
        return $this;
    }
    /**
     * Get bundle price select additional columns
     * 
     * @return array
     */
    protected function getBundlePriceSelectAdditionalColumns()
    {
        return array();
    }
    /**
     * Get bundle price select columns
     * 
     * @param int $priceType
     * @param Zend_Db_Expr $finalPrice
     * @param Zend_Db_Expr $origPrice
     * @param Zend_Db_Expr $specialPrice
     * @param Zend_Db_Expr $tierPrice
     * @param Zend_Db_Expr $tierPercent
     * @param Zend_Db_Expr $groupPrice
     * 
     * @return array
     */
    protected function getBundlePriceSelectColumns(
        $priceType,
        $finalPrice,
        $origPrice,
        $specialPrice,
        $tierPrice,
        $tierPercent,
        $groupPrice = null
    ) {
        $columns = array(
            'price_type'    => new Zend_Db_Expr($priceType), 
            'special_price' => $specialPrice, 
            'tier_percent'  => $tierPercent, 
            'orig_price'    => $origPrice, 
            'price'         => $finalPrice, 
            'min_price'     => $finalPrice, 
            'max_price'     => $finalPrice, 
            'tier_price'    => $tierPrice, 
            'base_tier'     => $tierPrice, 
        );
        if ($this->getVersionHelper()->isGe1700()) {
            $columns = array_merge(
                $columns, array(
                'group_price'         => $groupPrice, 
                'base_group_price'    => $groupPrice, 
                'group_price_percent' => new Zend_Db_Expr('gp.price'), 
                )
            );
        }

        return array_merge($columns, $this->getBundlePriceSelectAdditionalColumns());
    }
    /**
     * Get bundle origional price expression
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param Zend_Db_Expr $price
     * 
     * @return Zend_Db_Expr
     */
    public function getBundleOrigPriceExpr($adapter, $price)
    {
        if ($this->getVersionHelper()->isGe1600()) {
            $origPrice = $adapter->getCheckSql($price . ' IS NULL', '0', $price);
        } else {
            $origPrice = new Zend_Db_Expr("IF({$price} IS NULL, 0, {$price})");
        }

        return $origPrice;
    }
    /**
     * Get bundle special price expression
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param Zend_Db_Expr $price
     * 
     * @return Zend_Db_Expr
     */
    public function getBundleSpecialPriceExpr($adapter, $specialPrice, $specialFrom, $specialTo)
    {
        if ($this->getVersionHelper()->isGe1600()) {
            $currentDate     = new Zend_Db_Expr('cwd.website_date');
        } else {
            $currentDate     = new Zend_Db_Expr('cwd.date');
        }

        if ($this->getVersionHelper()->isGe1600()) {
            $specialExpr    = $adapter->getCheckSql(
                $adapter->getCheckSql(
                    $specialFrom.' IS NULL', '1', $adapter->getCheckSql($specialFrom . ' <= ' . $currentDate, '1', '0')
                )." > 0 AND ".
                $adapter->getCheckSql(
                    $specialTo . ' IS NULL', '1', 
                    $adapter->getCheckSql($specialTo . ' >= ' . $currentDate, '1', '0')
                ). " > 0 AND {$specialPrice} > 0 ", $specialPrice, '0'
            );
        } else {
            $specialExpr    = new Zend_Db_Expr(
                "IF(IF({$specialFrom} IS NULL, 1, "
                . "IF({$specialFrom} <= {$currentDate}, 1, 0)) > 0 AND IF({$specialTo} IS NULL, 1, "
                . "IF({$specialTo} >= {$currentDate}, 1, 0)) > 0 AND {$specialPrice} > 0, $specialPrice, 0)"
            );
        }

        return $specialExpr;
    }
    /**
     * Get bundle tier percent expression
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * 
     * @return Zend_Db_Expr 
     */
    public function getBundleTierPercentExpr($adapter)
    {
        return new Zend_Db_Expr("tp.min_price");
    }
    /**
     * Get bundle tier price expression
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param int $priceType
     * @param Zend_Db_Expr $price
     * 
     * @return Zend_Db_Expr
     */
    public function getBundleTierPriceExpr($adapter, $priceType, $price)
    {
        $tierExpr       = $this->getBundleTierPercentExpr($adapter);
        if ($this->getVersionHelper()->isGe1600()) {
            if ($priceType == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                $tierPrice  = $adapter->getCheckSql(
                    $tierExpr . ' IS NOT NULL',
                    'ROUND(' . $price .' - ' . '(' . $price . ' * (' . $tierExpr . ' / 100)), 8)',
                    'NULL'
                );
            } else {
                $tierPrice  = $adapter->getCheckSql($tierExpr . ' IS NOT NULL', '0', 'NULL');
            }
        } else {
            if ($priceType == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                $tierPrice  = new Zend_Db_Expr("IF({$tierExpr} IS NOT NULL, ROUND({$price} - ({$price} * ({$tierExpr} / 100)), 8), NULL)");
            } else {
                $tierPrice  = new Zend_Db_Expr("IF({$tierExpr} IS NOT NULL, 0, NULL)");
            }
        }

        return $tierPrice;
    }
    /**
     * Get bundle group percent expression
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * 
     * @return Zend_Db_Expr 
     */
    public function getBundleGroupPercentExpr($adapter)
    {
        if ($this->getVersionHelper()->isGe1700()) {
            return $adapter->getCheckSql(
                'gp.price IS NOT NULL AND gp.price > 0 AND gp.price < 100', 'gp.price', '0'
            );
        } else {
            return null;
        }
    }
    /**
     * Get bundle group price expression
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param int $priceType
     * @param Zend_Db_Expr $price
     * 
     * @return Zend_Db_Expr
     */
    public function getBundleGroupPriceExpr($adapter, $priceType, $price)
    {
        if ($this->getVersionHelper()->isGe1700()) {
            $groupPriceExpr = $this->getBundleGroupPercentExpr($adapter);
            if ($priceType == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                $groupPrice = $adapter->getCheckSql(
                    $groupPriceExpr . ' > 0',
                    'ROUND(' . $price . ' - ' . '(' . $price . ' * (' . $groupPriceExpr . ' / 100)), 8)',
                    'NULL'
                );
            } else {
                $groupPrice = $adapter->getCheckSql($groupPriceExpr . ' > 0', $groupPriceExpr, 'NULL');
            }

            return $groupPrice;
        } else {
            return null;
        }
    }
    /**
     * Get bundle final price expression
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param int $priceType
     * @param Zend_Db_Expr $price
     * 
     * @return Zend_Db_Expr
     */
    public function getBundleFinalPriceExpr($adapter, $priceType, $price, $specialExpr, $groupPrice = null)
    {
        if ($this->getVersionHelper()->isGe1600()) {
            if ($priceType == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                $finalPrice = $adapter->getCheckSql(
                    $specialExpr . ' > 0',
                    'ROUND(' . $price . ' * (' . $specialExpr . '  / 100), 8)',
                    $price
                );
                if ($this->getVersionHelper()->isGe1700()) {
                    $finalPrice = $adapter->getCheckSql(
                        "{$groupPrice} IS NOT NULL AND {$groupPrice} < {$finalPrice}",
                        $groupPrice,
                        $finalPrice
                    );
                }
            } else {
                $finalPrice  = new Zend_Db_Expr("0");
            }
        } else {
            if ($priceType == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                $finalPrice  = new Zend_Db_Expr("IF({$specialExpr} > 0, ROUND($price * ({$specialExpr} / 100), 8), {$price})");
            } else {
                $finalPrice  = new Zend_Db_Expr("0");
            }
        }

        return $finalPrice;
    }
    /**
     * Prepare bundle price by type
     *
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * @param string $typeId
     * @param int $priceType
     * @param int|array $entityIds
     *
     * @return self
     */
    public function prepareBundlePriceByType($adapter, $table, $typeId, $priceType, $entityIds = null)
    {
        $select             = $this->getFinalPriceSelect($adapter);
        $select->where('e.type_id=?', $typeId);
        $statusCond         = $adapter->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->addAttributeToSelect($adapter, $select, 'status', 'e.entity_id', 'cs.store_id', $statusCond, true);

        if ($this->getVersionHelper()->isGe1600()) {
            if (Mage::helper('core')->isModuleEnabled('Mage_Tax')) {
                $taxClassId = $this->addAttributeToSelect($adapter, $select, 'tax_class_id', 'e.entity_id', 'cs.store_id');
            } else {
                $taxClassId = new Zend_Db_Expr('0');
            }

            if ($priceType == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC) {
                $select->columns(array('tax_class_id' => new Zend_Db_Expr('0')));
            } else {
                $select->columns(array('tax_class_id' => $adapter->getCheckSql($taxClassId . ' IS NOT NULL', $taxClassId, 0)));
            }
        } else {
            if ($priceType == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC) {
                $select->columns(array('tax_class_id' => new Zend_Db_Expr('0')));
            } else {
                $taxClassId = $this->addAttributeToSelect($adapter, $select, 'tax_class_id', 'e.entity_id', 'cs.store_id');
                $select->columns(array('tax_class_id' => new Zend_Db_Expr("IF($taxClassId IS NOT NULL, $taxClassId, 0)")));
            }
        }

        $priceTypeCond      = $adapter->quoteInto('=?', $priceType);
        $this->addAttributeToSelect($adapter, $select, 'price_type', 'e.entity_id', 'cs.store_id', $priceTypeCond);

        $this->addPriceJoins($adapter, $select);

        $price              = $this->addAttributeToSelect($adapter, $select, 'price', 'e.entity_id', 'cs.store_id');
        $price              = $this->preparePrice($select, $price);

        $origPrice          = $this->getBundleOrigPriceExpr($adapter, $price);
        $specialFrom        = $this->addAttributeToSelect($adapter, $select, 'special_from_date', 'e.entity_id', 'cs.store_id');
        $specialTo          = $this->addAttributeToSelect($adapter, $select, 'special_to_date', 'e.entity_id', 'cs.store_id');
        $specialPrice       = $this->addAttributeToSelect($adapter, $select, 'special_price', 'e.entity_id', 'cs.store_id');
        $specialPrice       = $this->getBundleSpecialPriceExpr($adapter, $specialPrice, $specialFrom, $specialTo);
        $tierPercent        = $this->getBundleTierPercentExpr($adapter);
        $tierPrice          = $this->getBundleTierPriceExpr($adapter, $priceType, $price);
        $groupPrice         = $this->getBundleGroupPriceExpr($adapter, $priceType, $price);
        $finalPrice         = $this->getBundleFinalPriceExpr($adapter, $priceType, $price, $specialPrice, $groupPrice);

        $select->columns(
            $this->getBundlePriceSelectColumns(
                $priceType, $finalPrice, $origPrice, $specialPrice, $tierPrice, $tierPercent, $groupPrice
            )
        );
        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        Mage::dispatchEvent(
            'catalog_product_prepare_index_select',
            $this->getPrepareProductIndexSelectEventData($select)
        );
        $query              = $select->insertFromSelect($table);
        $adapter->query($query);
        return $this;
    }
    /**
     * Get bundle selection price select additional columns
     * 
     * @return array
     */
    protected function getBundleSelectionPriceSelectAdditionalColumns()
    {
        return array();
    }
    /**
     * Get bundle selection price select columns
     * 
     * @param Zend_Db_Expr $price
     * @param Zend_Db_Expr $groupType
     * @param Zend_Db_Expr $tierPrice
     * @param Zend_Db_Expr $groupPrice
     * 
     * @return array
     */
    protected function getBundleSelectionPriceSelectColumns($price, $groupType, $tierPrice, $groupPrice = null)
    {
        $columns = array(
            'group_type'    => $groupType, 
            'is_required'   => 'bo.required', 
            'price'         => $price, 
            'tier_price'    => $tierPrice, 
        );
        if ($this->getVersionHelper()->isGe1700()) {
            $columns = array_merge(
                $columns, array(
                'group_price'   => $groupPrice, 
                )
            );
        }

        return array_merge($columns, $this->getBundleSelectionPriceSelectAdditionalColumns());
    }
    /**
     * Get bundle selection price select index join additional conditions
     * 
     * @return array
     */
    protected function getBundleSelectionPriceSelectIndexJoinAdditionalConditions()
    {
        return array();
    }
    /**
     * Get bundle selection price select index join conditions
     * 
     * @return array
     */
    protected function getBundleSelectionPriceSelectIndexJoinConditions()
    {
        return array_merge(
            array(
                '(bs.product_id = idx.entity_id)', 
                '(i.customer_group_id = idx.customer_group_id)', 
                '(i.website_id = idx.website_id)', 
            ), 
            $this->getBundleSelectionPriceSelectIndexJoinAdditionalConditions()
        );
    }
    /**
     * Calculate bundle selection price
     *
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $table
     * @param string $indexTable
     * @param string $selectionTable
     * @param string $priceType
     * 
     * @return self
     */
    public function calculateBundleSelectionPrice($adapter, $table, $indexTable, $selectionTable, $priceType)
    {
        $groupExpr = null;
        if ($this->getVersionHelper()->isGe1600()) {
            if ($priceType == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                $selectionPriceValue = $adapter->getCheckSql(
                    'bsp.selection_price_value IS NULL', 'bs.selection_price_value', 'bsp.selection_price_value'
                );
                $selectionPriceType = $adapter->getCheckSql(
                    'bsp.selection_price_type IS NULL', 'bs.selection_price_type', 'bsp.selection_price_type'
                );
                $priceExpr = new Zend_Db_Expr(
                    $adapter->getCheckSql(
                        $selectionPriceType . ' = 1',
                        'ROUND(i.price * (' . $selectionPriceValue . ' / 100), 8)',
                        $adapter->getCheckSql(
                            'i.special_price > 0 AND i.special_price < 100',
                            'ROUND(' . $selectionPriceValue . ' * (i.special_price / 100), 8)',
                            $selectionPriceValue
                        )
                    ) . '* bs.selection_qty'
                );
                $tierExpr = $adapter->getCheckSql(
                    'i.base_tier IS NOT NULL',
                    $adapter->getCheckSql(
                        $selectionPriceType .' = 1',
                        'ROUND(i.base_tier - (i.base_tier * (' . $selectionPriceValue . ' / 100)), 8)',
                        $adapter->getCheckSql(
                            'i.tier_percent > 0',
                            'ROUND(' . $selectionPriceValue
                            . ' - (' . $selectionPriceValue . ' * (i.tier_percent / 100)), 8)',
                            $selectionPriceValue
                        )
                    ) . ' * bs.selection_qty',
                    'NULL'
                );
                
                if ($this->getVersionHelper()->isGe1700()) {
                    $groupExpr = $adapter->getCheckSql(
                        'i.base_group_price IS NOT NULL',
                        $adapter->getCheckSql(
                            $selectionPriceType .' = 1',
                            $priceExpr,
                            $adapter->getCheckSql(
                                'i.group_price_percent > 0',
                                'ROUND(' . $selectionPriceValue
                                . ' - (' . $selectionPriceValue . ' * (i.group_price_percent / 100)), 8)',
                                $selectionPriceValue
                            )
                        ) . ' * bs.selection_qty',
                        'NULL'
                    );
                    $priceExpr = new Zend_Db_Expr(
                        $adapter->getCheckSql("{$groupExpr} < {$priceExpr}", $groupExpr, $priceExpr)
                    );
                }
            } else {
                $priceExpr = new Zend_Db_Expr(
                    $adapter->getCheckSql(
                        'i.special_price > 0 AND i.special_price < 100',
                        'ROUND(idx.min_price * (i.special_price / 100), 8)',
                        'idx.min_price'
                    ) . ' * bs.selection_qty'
                );
                $tierExpr = $adapter->getCheckSql(
                    'i.base_tier IS NOT NULL',
                    'ROUND(idx.min_price * (i.base_tier / 100), 8)* bs.selection_qty',
                    'NULL'
                );
                
                if ($this->getVersionHelper()->isGe1700()) {
                    $groupExpr = $adapter->getCheckSql(
                        'i.base_group_price IS NOT NULL',
                        'ROUND(idx.min_price * (i.base_group_price / 100), 8)* bs.selection_qty',
                        'NULL'
                    );
                    $groupPriceExpr = new Zend_Db_Expr(
                        $adapter->getCheckSql(
                            'i.base_group_price IS NOT NULL AND i.base_group_price > 0 AND i.base_group_price < 100',
                            'ROUND(idx.min_price - idx.min_price * (i.base_group_price / 100), 8)',
                            'idx.min_price'
                        ) . ' * bs.selection_qty'
                    );
                    $priceExpr = new Zend_Db_Expr(
                        $adapter->getCheckSql("{$groupPriceExpr} < {$priceExpr}", $groupPriceExpr, $priceExpr)
                    );
                }
            }

            $groupType = $adapter->getCheckSql("bo.type = 'select' OR bo.type = 'radio'", '0', '1');
        } else {
            if ($priceType == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                $priceExpr = new Zend_Db_Expr(
                    "IF(IF(bsp.selection_price_type IS NULL, bs.selection_price_type, "
                    . "bsp.selection_price_type) = 1, "
                    . "ROUND(i.price * (IF(bsp.selection_price_value IS NULL, bs.selection_price_value, "
                    . "bsp.selection_price_value) / 100), 8), IF(i.special_price > 0, "
                    . "ROUND(IF(bsp.selection_price_value IS NULL, bs.selection_price_value, bsp.selection_price_value) "
                    . "* (i.special_price / 100), 8), IF(bsp.selection_price_value IS NULL, bs.selection_price_value, "
                    . "bsp.selection_price_value))) * bs.selection_qty"
                );
                $tierExpr = new Zend_Db_Expr(
                    "IF(i.base_tier IS NOT NULL, IF(IF(bsp.selection_price_type IS NULL, "
                    . "bs.selection_price_type, bsp.selection_price_type) = 1, "
                    . "ROUND(i.base_tier - (i.base_tier * (IF(bsp.selection_price_value IS NULL, bs.selection_price_value, "
                    . "bsp.selection_price_value) / 100)), 8), IF(i.tier_percent > 0, "
                    . "ROUND(IF(bsp.selection_price_value IS NULL, bs.selection_price_value, bsp.selection_price_value) "
                    . "- (IF(bsp.selection_price_value IS NULL, bs.selection_price_value, bsp.selection_price_value) "
                    . "* (i.tier_percent / 100)), 8), IF(bsp.selection_price_value IS NULL, bs.selection_price_value, "
                    . "bsp.selection_price_value))) * bs.selection_qty, NULL)"
                );
            } else {
                $priceExpr = new Zend_Db_Expr(
                    "IF(i.special_price > 0, ROUND(idx.min_price * (i.special_price / 100), 8), "
                    . "idx.min_price) * bs.selection_qty"
                );
                $tierExpr = new Zend_Db_Expr(
                    "IF(i.base_tier IS NOT NULL, ROUND(idx.min_price * (i.base_tier / 100), 8) "
                    . "* bs.selection_qty, NULL)"
                );
            }

            $groupType = new Zend_Db_Expr("IF(bo.type = 'select' OR bo.type = 'radio', 0, 1)");
        }

        $select = $adapter->select()
            ->from(array('i' => $table), array('entity_id', 'customer_group_id', 'website_id'))
            ->join(array('bo' => $this->getTable('bundle/option')), 'bo.parent_id = i.entity_id', array('option_id'))
            ->join(array('bs' => $this->getTable('bundle/selection')), 'bs.option_id = bo.option_id', array('selection_id'))
            ->joinLeft(
                array('bsp' => $this->getTable('bundle/selection_price')), 
                implode(
                    ' AND ', array(
                    'bs.selection_id = bsp.selection_id', 
                    'bsp.website_id = i.website_id', 
                    )
                ), 
                array('')
            )->join(
                array('idx' => $indexTable), 
                implode(' AND ', $this->getBundleSelectionPriceSelectIndexJoinConditions()), 
                array()
            )->join(
                array('e' => $this->getTable('catalog/product')), 
                implode(
                    ' AND ', array(
                    'bs.product_id = e.entity_id', 
                    'e.required_options=0', 
                    )
                ), 
                array()
            )->where('i.price_type=?', $priceType);
        
        $select->columns($this->getBundleSelectionPriceSelectColumns($priceExpr, $groupType, $tierExpr, $groupExpr));
        
        $query          = $select->insertFromSelect($selectionTable);
        $adapter->query($query);
        return $this;
    }
    /**
     * Get bundle option price select additional columns
     * 
     * @return array
     */
    protected function getBundleOptionPriceSelectAdditionalColumns()
    {
        return array();
    }
    /**
     * Get bundle option price select columns
     * 
     * @param Zend_Db_Expr $minPrice
     * @param Zend_Db_Expr $altPrice
     * @param Zend_Db_Expr $maxPrice
     * @param Zend_Db_Expr $tierPrice
     * @param Zend_Db_Expr $altTierPrice
     * @param Zend_Db_Expr $groupPrice
     * @param Zend_Db_Expr $altGroupPrice
     * 
     * @return array
     */
    protected function getBundleOptionPriceSelectColumns(
        $minPrice,
        $altPrice,
        $maxPrice,
        $tierPrice,
        $altTierPrice,
        $groupPrice = null,
        $altGroupPrice = null
    ) {
        $columns = array(
            'min_price'         => $minPrice, 
            'alt_price'         => $altPrice, 
            'max_price'         => $maxPrice, 
            'tier_price'        => $tierPrice, 
            'alt_tier_price'    => $altTierPrice, 
        );
        if ($this->getVersionHelper()->isGe1700()) {
            $columns = array_merge(
                $columns, array(
                'group_price'       => $groupPrice, 
                'alt_group_price'   => $altGroupPrice, 
                )
            );
        }

        return array_merge($columns, $this->getBundleOptionPriceSelectAdditionalColumns());
    }
    /**
     * Get bundle option price select group additional columns
     * 
     * @return array
     */
    protected function getBundleOptionPriceSelectGroupAdditionalColumns()
    {
        return array();
    }
    /**
     * Get bundle option price select group columns
     * 
     * @return array
     */
    protected function getBundleOptionPriceSelectGroupColumns()
    {
        $columns = array('entity_id', 'customer_group_id', 'website_id', 'option_id');
        $columns = array_merge($columns, $this->getBundleOptionPriceSelectGroupAdditionalColumns());
        if ($this->getVersionHelper()->isGe1600()) {
            $columns = array_merge($columns, array('is_required', 'group_type'));
        }

        return $columns;
    }
    /**
     * Get bundle final price select price join additional conditions
     * 
     * @return array
     */
    protected function getBundleFinalPriceSelectPriceJoinAdditionalConditions()
    {
        return array();
    }
    /**
     * Get bundle final price select price join conditions
     * 
     * @return array
     */
    protected function getBundleFinalPriceSelectPriceJoinConditions()
    {
        $conditions = array(
            '(i.entity_id = io.entity_id)', 
            '(i.customer_group_id = io.customer_group_id)', 
            '(i.website_id = io.website_id)', 
        );
        return array_merge($conditions, $this->getBundleFinalPriceSelectPriceJoinAdditionalConditions());
    }
    /**
     * Get bundle final price select additional columns
     * 
     * @return array
     */
    protected function getBundleFinalPriceSelectAdditionalColumns()
    {
        return array();
    }
    /**
     * Get bundle final price select columns
     * 
     * @param Zend_Db_Expr $minPrice
     * @param Zend_Db_Expr $maxPrice
     * @param Zend_Db_Expr $tierPrice
     * @param Zend_Db_Expr $groupPrice
     * 
     * @return array
     */
    protected function getBundleFinalPriceSelectColumns($minPrice, $maxPrice, $tierPrice, $groupPrice = null)
    {
        $columns = array(
            'tax_class_id'      => 'i.tax_class_id', 
            'orig_price'        => 'i.orig_price', 
            'price'             => 'i.price', 
            'min_price'         => $minPrice, 
            'max_price'         => $maxPrice, 
            'tier_price'        => $tierPrice, 
            'base_tier'         => 'MIN(i.base_tier)', 
        );
        if ($this->getVersionHelper()->isGe1700()) {
            $columns = array_merge(
                $columns, array(
                'group_price'       => $groupPrice, 
                'base_group_price'  => 'MIN(i.base_group_price)', 
                )
            );
        }

        return array_merge($columns, $this->getBundleFinalPriceSelectAdditionalColumns());
    }
    /**
     * Get bundle final price select group additional columns
     * 
     * @return array
     */
    protected function getBundleFinalPriceSelectGroupAdditionalColumns()
    {
        return array();
    }
    /**
     * Get bundle final price select group columns
     * 
     * @return array
     */
    protected function getBundleFinalPriceSelectGroupColumns()
    {
        $columns = array('io.entity_id', 'io.customer_group_id', 'io.website_id');
        if ($this->getVersionHelper()->isGe1600()) {
            $columns = array_merge($columns, $this->getBundleFinalPriceSelectGroupAdditionalColumns());
            $columns = array_merge($columns, array('i.tax_class_id', 'i.orig_price', 'i.price'));
        }

        return $columns;
    }
    /**
     * Calculate bundle option price
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $finalPriceTable
     * @param string $priceTable
     * @param string $indexTable
     * @param string $selectionTable
     * @param string $optionTable
     * 
     * @return self
     */
    public function calculateBundleOptionPrice(
        $adapter,
        $finalPriceTable,
        $priceTable,
        $indexTable,
        $selectionTable,
        $optionTable
    ) {
        $adapter->delete($selectionTable);
        $this->calculateBundleSelectionPrice(
            $adapter, $priceTable, $indexTable, $selectionTable, 
            Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED
        );
        $this->calculateBundleSelectionPrice(
            $adapter, $priceTable, $indexTable, $selectionTable, 
            Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC
        );
        $adapter->delete($optionTable);
        
        if ($this->getVersionHelper()->isGe1600()) {
            $minPrice       = $adapter->getCheckSql('i.is_required = 1', 'MIN(i.price)', '0');
            $altPrice       = $adapter->getCheckSql('i.is_required = 0', 'MIN(i.price)', '0');
            $maxPrice       = $adapter->getCheckSql('i.group_type = 1', 'SUM(i.price)', 'MAX(i.price)');
            $tierPrice      = $adapter->getCheckSql('i.is_required = 1', 'MIN(i.tier_price)', '0');
            $altTierPrice   = $adapter->getCheckSql('i.is_required = 0', 'MIN(i.tier_price)', '0');
            if ($this->getVersionHelper()->isGe1700()) {
                $groupPrice     = $adapter->getCheckSql('i.is_required = 1', 'MIN(i.group_price)', '0');
                $altGroupPrice  = $adapter->getCheckSql('i.is_required = 0', 'MIN(i.group_price)', '0');
            } else {
                $groupPrice     = null;
                $altGroupPrice  = null;
            }
        } else {
            $minPrice       = new Zend_Db_Expr("IF(i.is_required = 1, MIN(i.price), 0)");
            $altPrice       = new Zend_Db_Expr("IF(i.is_required = 0, MIN(i.price), 0)");
            $maxPrice       = new Zend_Db_Expr("IF(i.group_type = 1, SUM(i.price), MAX(i.price))");
            $tierPrice      = new Zend_Db_Expr("IF(i.is_required = 1, MIN(i.tier_price), 0)");
            $altTierPrice   = new Zend_Db_Expr("IF(i.is_required = 0, MIN(i.tier_price), 0)");
            $groupPrice     = null;
            $altGroupPrice  = null;
        }

        $select = $adapter->select()
                ->from(
                    array('i' => $selectionTable), 
                    array('entity_id', 'customer_group_id', 'website_id', 'option_id')
                )->group($this->getBundleOptionPriceSelectGroupColumns())
                ->columns(
                    $this->getBundleOptionPriceSelectColumns(
                        $minPrice, $altPrice, $maxPrice, $tierPrice, $altTierPrice, $groupPrice, $altGroupPrice
                    )
                );
        
        $query = $select->insertFromSelect($optionTable);
        $adapter->query($query);
        
        $adapter->delete($finalPriceTable);
        
        if ($this->getVersionHelper()->isGe1600()) {
            $minPrice  = new Zend_Db_Expr(
                $adapter->getCheckSql('SUM(io.min_price) = 0', 'MIN(io.alt_price)', 'SUM(io.min_price)').' + i.price'
            );
            $maxPrice  = new Zend_Db_Expr("SUM(io.max_price) + i.price");
            $tierPrice = $adapter->getCheckSql(
                'MIN(i.tier_percent) IS NOT NULL', 
                $adapter->getCheckSql(
                    'SUM(io.tier_price) = 0', 'SUM(io.alt_tier_price)', 'SUM(io.tier_price)'
                ).' + MIN(i.tier_price)', 
                'NULL'
            );
            if ($this->getVersionHelper()->isGe1700()) {
                $groupPrice = $adapter->getCheckSql(
                    'MIN(i.group_price_percent) IS NOT NULL', 
                    $adapter->getCheckSql(
                        'SUM(io.group_price) = 0', 
                        'SUM(io.alt_group_price)', 
                        'SUM(io.group_price)'
                    ).' + MIN(i.group_price)', 
                    'NULL'
                );
            } else {
                $groupPrice = null;
            }
        } else {
            $minPrice   = new Zend_Db_Expr("IF(SUM(io.min_price) = 0, MIN(io.alt_price), SUM(io.min_price)) + i.price");
            $maxPrice   = new Zend_Db_Expr("SUM(io.max_price) + i.price");
            $tierPrice  = new Zend_Db_Expr(
                "IF(i.tier_percent IS NOT NULL, IF(SUM(io.tier_price) = 0, ".
                    "SUM(io.alt_tier_price), SUM(io.tier_price)) + i.tier_price, NULL)"
            );
            $groupPrice = null;
        }

        $select = $adapter->select()
                ->from(
                    array('io' => $optionTable), 
                    array('entity_id', 'customer_group_id', 'website_id')
                )->join(
                    array('i' => $priceTable), 
                    implode(' AND ', $this->getBundleFinalPriceSelectPriceJoinConditions()), 
                    array()
                )->group(
                    $this->getBundleFinalPriceSelectGroupColumns()
                )->columns(
                    $this->getBundleFinalPriceSelectColumns($minPrice, $maxPrice, $tierPrice, $groupPrice)
                );
        $query = $select->insertFromSelect($finalPriceTable);
        $adapter->query($query);
        return $this;
    }
    /**
     * Get bundle tier price select additional conditions
     * 
     * @return array
     */
    protected function getBundleTierPriceSelectAdditionalConditions()
    {
        return array();
    }
    /**
     * Get bundle tier price select conditions
     * 
     * @return array
     */
    protected function getBundleTierPriceSelectConditions()
    {
        return array_merge(
            array(
            '(cw.website_id != 0)', 
            ), $this->getBundleTierPriceSelectAdditionalConditions()
        );
    }
    /**
     * Get bundle tier price select additional columns
     * 
     * @return array
     */
    protected function getBundleTierPriceSelectAdditionalColumns()
    {
        return array();
    }
    /**
     * Get bundle tier price select columns
     * 
     * @return array
     */
    protected function getBundleTierPriceSelectColumns()
    {
        $columns    = array(
            'entity_id'             => new Zend_Db_Expr('tp.entity_id'), 
            'customer_group_id'     => new Zend_Db_Expr('cg.customer_group_id'), 
            'website_id'            => new Zend_Db_Expr('cw.website_id'), 
        );
        $columns    = array_merge($columns, $this->getBundleTierPriceSelectAdditionalColumns());
        $columns    = array_merge(
            $columns, array(
            'min_price'             => new Zend_Db_Expr("MIN(tp.value)"), 
            )
        );
        return $columns;
    }
    /**
     * Get bundle tier price select group additional columns
     * 
     * @return array
     */
    protected function getBundleTierPriceSelectGroupAdditionalColumns()
    {
        return array();
    }
    /**
     * Get bundle tier price select group columns
     * 
     * @return array
     */
    protected function getBundleTierPriceSelectGroupColumns()
    {
        return array_merge(
            array(
            'tp.entity_id', 
            'cg.customer_group_id', 
            'cw.website_id', 
            ), $this->getBundleTierPriceSelectGroupAdditionalColumns()
        );
    }
    /**
     * Add bundle tier price select additional joins
     * 
     * @param Zend_Db_Select $select
     * 
     * @return self
     */
    protected function addBundleTierPriceSelectAdditionalJoins($select)
    {
        return $this;
    }
    /**
     * Prepare bundle tier price index
     * 
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $tierPriceTable
     * @param int $typeId
     * @param int|array $entityIds
     * 
     * @return self
     */
    public function prepareBundleTierPriceIndex($adapter, $tierPriceTable, $typeId, $entityIds = null)
    {
        $select = $adapter->select()
            ->from(array('i' => $tierPriceTable), null)
            ->join(array('e' => $this->getTable('catalog/product')), 'i.entity_id=e.entity_id', array())
            ->where('e.type_id = ?', $typeId);
        $query = $select->deleteFromSelect('i');
        $adapter->query($query);
        
        $select = $adapter->select()
            ->from(array('tp' => $this->getValueTable('catalog/product', 'tier_price')), array())
            ->join(array('e' => $this->getTable('catalog/product')), 'tp.entity_id=e.entity_id', array())
            ->join(
                array('cg' => $this->getTable('customer/customer_group')), 
                'tp.all_groups = 1 OR (tp.all_groups = 0 AND tp.customer_group_id = cg.customer_group_id)', 
                array()
            )->join(
                array('cw' => $this->getTable('core/website')), 
                'tp.website_id = 0 OR tp.website_id = cw.website_id', 
                array()
            );
        $this->addBundleTierPriceSelectAdditionalJoins($select);
        $select->where(implode(' AND ', $this->getBundleTierPriceSelectConditions()))
            ->where('e.type_id=?', $typeId)
            ->columns($this->getBundleTierPriceSelectColumns())
            ->group($this->getBundleTierPriceSelectGroupColumns());
        if (!empty($entityIds)) {
            $select->where('tp.entity_id IN(?)', $entityIds);
        }

        $query = $select->insertFromSelect($tierPriceTable);
        $adapter->query($query);
        
        return $this;
    }
    /**
     * Get bundle group price select additional conditions
     * 
     * @return array
     */
    protected function getBundleGroupPriceSelectAdditionalConditions()
    {
        return array();
    }
    /**
     * Get bundle group price select conditions
     * 
     * @return array
     */
    protected function getBundleGroupPriceSelectConditions()
    {
        return array_merge(
            array(
            '(cw.website_id != 0)', 
            ), $this->getBundleGroupPriceSelectAdditionalConditions()
        );
    }
    /**
     * Get bundle group price select additional columns
     * 
     * @return array
     */
    protected function getBundleGroupPriceSelectAdditionalColumns()
    {
        return array();
    }
    /**
     * Get bundle group price select columns
     * 
     * @return array
     */
    protected function getBundleGroupPriceSelectColumns()
    {
        $columns    = array(
            'entity_id'             => new Zend_Db_Expr('gp.entity_id'), 
            'customer_group_id'     => new Zend_Db_Expr('cg.customer_group_id'), 
            'website_id'            => new Zend_Db_Expr('cw.website_id'), 
        );
        $columns    = array_merge($columns, $this->getBundleGroupPriceSelectAdditionalColumns());
        $columns    = array_merge(
            $columns, array(
            'min_price'             => new Zend_Db_Expr("MIN(gp.value)"), 
            )
        );
        return $columns;
    }
    /**
     * Get bundle group price select group additional columns
     * 
     * @return array
     */
    protected function getBundleGroupPriceSelectGroupAdditionalColumns()
    {
        return array();
    }
    /**
     * Get bundle group price select group columns
     * 
     * @return array
     */
    protected function getBundleGroupPriceSelectGroupColumns()
    {
        return array_merge(
            array(
            'gp.entity_id', 
            'cg.customer_group_id', 
            'cw.website_id', 
            ), $this->getBundleGroupPriceSelectGroupAdditionalColumns()
        );
    }
    /**
     * Add bundle group price select additional joins
     * 
     * @param Zend_Db_Select $select
     * 
     * @return self
     */
    protected function addBundleGroupPriceSelectAdditionalJoins($select)
    {
        return $this;
    }
    /**
     * Prepare bundle group price index
     *
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $tierPriceTable
     * @param int $typeId
     * @param int|array $entityIds
     * 
     * @return self
     */
    public function prepareBundleGroupPriceIndex($adapter, $groupPriceTable, $typeId, $entityIds = null)
    {
        $select = $adapter->select()
            ->from(array('i' => $groupPriceTable), null)
            ->join(array('e' => $this->getTable('catalog/product')), 'i.entity_id=e.entity_id', array())
            ->where('e.type_id = ?', $typeId);
        $query = $select->deleteFromSelect('i');
        $adapter->query($query);
        
        $select = $adapter->select()
            ->from(array('gp' => $this->getValueTable('catalog/product', 'group_price')), array())
            ->join(array('e' => $this->getTable('catalog/product')), 'gp.entity_id=e.entity_id', array())
            ->join(
                array('cg' => $this->getTable('customer/customer_group')), 
                'gp.all_groups = 1 OR (gp.all_groups = 0 AND gp.customer_group_id = cg.customer_group_id)', 
                array()
            )->join(
                array('cw' => $this->getTable('core/website')), 
                'gp.website_id = 0 OR gp.website_id = cw.website_id', 
                array()
            );
        $this->addBundleGroupPriceSelectAdditionalJoins($select);
        $select->where(implode(' AND ', $this->getBundleGroupPriceSelectConditions()))
            ->where('e.type_id=?', $typeId)
            ->columns($this->getBundleGroupPriceSelectColumns())
            ->group($this->getBundleGroupPriceSelectGroupColumns());
        if (!empty($entityIds)) {
            $select->where('gp.entity_id IN(?)', $entityIds);
        }

        $query = $select->insertFromSelect($groupPriceTable);
        $adapter->query($query);
        
        return $this;
    }
    /**
     * Prepare bundle price
     *
     * @param int|array $entityIds
     * @param bool $useIdxTable
     * 
     * @return self
     */
    public function prepareBundlePrice(
        $adapter,
        $finalPriceTable,
        $priceTable,
        $indexTable, 
        $customOptionAggregateTable,
        $customOptionPriceTable, 
        $selectionTable,
        $optionTable, 
        $tierPriceTable,
        $groupPriceTable,
        $typeId,
        $useIdxTable,
        $entityIds = null
    ) {
        $this->prepareBundleTierPriceIndex($adapter, $tierPriceTable, $typeId, $entityIds);
        if ($this->getVersionHelper()->isGe1700()) {
            $this->prepareBundleGroupPriceIndex($adapter, $groupPriceTable, $typeId, $entityIds);
        }

        $adapter->delete($priceTable);

        $this->prepareBundlePriceByType(
            $adapter, $priceTable, $typeId, Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED, $entityIds
        );
        $this->prepareBundlePriceByType(
            $adapter, $priceTable, $typeId, Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC, $entityIds
        );
        
        $select     = $adapter->select()
            ->join(
                array('wd' => $this->getTable('catalog/product_index_website')), 
                'i.website_id = wd.website_id', 
                array()
            );
        
        Mage::dispatchEvent(
            'prepare_catalog_product_price_index_table', 
            $this->getPrepareProductIndexTableEventData($select, $priceTable)
        );
        $this->calculateBundleOptionPrice(
            $adapter, $finalPriceTable, $priceTable, $indexTable, $selectionTable, $optionTable
        );
        $this->applyCustomOption(
            $adapter, $finalPriceTable, $customOptionAggregateTable, $customOptionPriceTable, $useIdxTable
        );
        $this->movePriceDataToIndexTable(
            $adapter, $finalPriceTable, $indexTable, $useIdxTable
        );
        return $this;
    }
    /**
     * Update zone discount index
     * 
     * @param Zend_Db_Select $select
     * @param string $table
     * @param int $entityId
     * @param array $fields
     * 
     * @return self
     */
    public function updateZonePriceIndex($select, $table, $entityId, $fields)
    {
        $resource           = Mage::getSingleton('core/resource');
        $adapter            = $resource->getConnection('core_write');
        $zonePriceTable     = $resource->getTableName('catalog/product_zone_price');
        if (empty($fields)) {
            return $this;
        }

        $tableAlias         = null;
        if (is_array($table)) {
            foreach ($table as $key => $value) {
                if (is_string($key)) {
                    $tableAlias = $key;
                } else {
                    $tableAlias = $value;
                }

                break;
            }
        } else {
            $tableAlias = $table;
        }

        foreach ($fields as $field) {
            if ($field != 'min_price') {
                continue;
            }

            $price              = $adapter->quoteIdentifier(array($tableAlias, $field));
            $priceAlias         = $field.'_pzp';
            $priceCountAlias    = $priceAlias.'_count';
            $function           = 'MIN';
            $countSelect        = $adapter->select()
                ->from(
                    array($priceCountAlias => $zonePriceTable), 
                    'COUNT(*)'
                )->where($priceCountAlias.".product_id = {$entityId}");
            $priceSelect        = $adapter->select()
                ->from(
                    array($priceAlias => $zonePriceTable), 
                    array()
                )->where($priceAlias.".product_id = {$entityId}")
                ->columns(
                    new Zend_Db_Expr(
                        $function."(IF(".
                        $priceAlias.".price_type = 'fixed', ".
                        "IF(".
                        $priceAlias.".price < {$price}, ".
                        "{$price} - ".$priceAlias.".price, ".
                        "{$price}".
                        "), ".
                        "IF(".
                        $priceAlias.".price < 100, ".
                        "ROUND({$price} - (".$priceAlias.".price * ({$price} / 100)), 8), ".
                        "{$price}".
                        ")".
                        "))"
                    )
                );
            $priceExpr          = new Zend_Db_Expr(
                "IF(".
                "(".$countSelect->assemble().") > 0, ".
                "(".$priceSelect->assemble()."), ".
                "{$price}".
                ")"
            );
            $select->columns(array($field => $priceExpr));
        }

        $query = $select->crossUpdateFromSelect($table);
        $adapter->query($query);
        return $this;
    }
}
