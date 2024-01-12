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
 * Configurable products price indexer resource
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Catalog_Product_Indexer_Price_Grouped 
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Grouped
{
    /**
     * Get warehouse helper
     *
     * @return  MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Get price indexer helper
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price_Indexer
     */
    protected function getProductPriceIndexerHelper()
    {
        return $this->getWarehouseHelper()->getProductPriceIndexerHelper();
    }
    /**
     * Get version helper
     * 
     * @return MP_Warehouse_Helper_Core_Version
     */
    protected function getVersionHelper()
    {
        return $this->getProductPriceIndexerHelper()->getVersionHelper();
    }
    /**
     * Calculate minimal and maximal prices for Grouped products
     * Use calculated price for relation products
     *
     * @param int|array $entityIds  the parent entity ids limitation
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Grouped
     */
    protected function _prepareGroupedProductPriceData($entityIds = null)
    {
        $write = $this->_getWriteAdapter();
        $table = $this->getIdxTable();
        $select = $write->select()
            ->from(array('e' => $this->getTable('catalog/product')), 'entity_id');
        
        if ($this->getVersionHelper()->isGe1900()) {
            $select->join(
                array('l' => $this->getTable('catalog/product_link')),
                'e.entity_id = l.product_id AND l.link_type_id='.Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED, array()
            );
        } else {
            $select->joinLeft(
                array('l' => $this->getTable('catalog/product_link')),
                'e.entity_id = l.product_id AND l.link_type_id='.Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED, array()
            );
        }
        
        $select->join(array('cg' => $this->getTable('customer/customer_group')), '', array('customer_group_id'));
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        if ($this->getVersionHelper()->isGe1600()) {
            $minCheckSql = $write->getCheckSql('le.required_options = 0', 'i.min_price', 0);
            $maxCheckSql = $write->getCheckSql('le.required_options = 0', 'i.max_price', 0);
            $taxClassId  = $this->_getReadAdapter()->getCheckSql('MIN(i.tax_class_id) IS NULL', '0', 'MIN(i.tax_class_id)');
            $minPrice    = new Zend_Db_Expr('MIN(' . $minCheckSql . ')');
            $maxPrice    = new Zend_Db_Expr('MAX(' . $maxCheckSql . ')');
        } else {
            $taxClassId  = new Zend_Db_Expr('IFNULL(i.tax_class_id, 0)');
            $minPrice    = new Zend_Db_Expr('MIN(IF(le.required_options = 0, i.min_price, 0))');
            $maxPrice    = new Zend_Db_Expr('MAX(IF(le.required_options = 0, i.max_price, 0))');
        }

        $stockId = 'IF (i.stock_id IS NOT NULL, i.stock_id, 1)';
        
        $select->columns('website_id', 'cw');
        
        if ($this->getVersionHelper()->isGe1900()) {
            $select->join(array('le' => $this->getTable('catalog/product')), 'le.entity_id = l.linked_product_id', array());
        } else {
            $select->joinLeft(array('le' => $this->getTable('catalog/product')), 'le.entity_id = l.linked_product_id', array());
        }
        
        if ($this->getVersionHelper()->isGe1700()) {
            $columns = array(
                'tax_class_id'  => $taxClassId,
                'price'         => new Zend_Db_Expr('NULL'),
                'final_price'   => new Zend_Db_Expr('NULL'),
                'min_price'     => $minPrice,
                'max_price'     => $maxPrice,
                'tier_price'    => new Zend_Db_Expr('NULL'), 
                'group_price'   => new Zend_Db_Expr('NULL'),
                'stock_id'      => $stockId
            );
        } else {
            $columns = array(
                'tax_class_id'  => $taxClassId,
                'price'         => new Zend_Db_Expr('NULL'),
                'final_price'   => new Zend_Db_Expr('NULL'),
                'min_price'     => $minPrice,
                'max_price'     => $maxPrice,
                'tier_price'    => new Zend_Db_Expr('NULL'), 
                'stock_id'      => $stockId
            );
        }
        
        if ($this->getVersionHelper()->isGe1900()) {
            $select->join(
                array('i' => $table), '(i.entity_id = l.linked_product_id) AND (i.website_id = cw.website_id) AND '.
                '(i.customer_group_id = cg.customer_group_id)', $columns
            );
        } else {
            $select->joinLeft(
                array('i' => $table), '(i.entity_id = l.linked_product_id) AND (i.website_id = cw.website_id) AND '.
                '(i.customer_group_id = cg.customer_group_id)', $columns
            );
        }
        
        $select->group(array('e.entity_id', 'cg.customer_group_id', 'cw.website_id', $stockId))
                ->where('e.type_id=?', $this->getTypeId());
        
        if ($this->getVersionHelper()->isGe1900()) {
            $statusCond = $write->quoteInto(' = ?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $statusCond);
        }
        
        if (!is_null($entityIds)) {
            $select->where('l.product_id IN(?)', $entityIds);
        }
        
        $eventData = array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('e.entity_id'),
            'website_field' => new Zend_Db_Expr('cw.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id'), 
        );
        if (!$this->getWarehouseHelper()->getConfig()->isMultipleMode()) {
            $eventData['stock_field'] = new Zend_Db_Expr('i.stock_id');
        }

        Mage::dispatchEvent('catalog_product_prepare_index_select', $eventData);
        
        $query = $select->insertFromSelect($table);
        $write->query($query);
        return $this;
    }
}
