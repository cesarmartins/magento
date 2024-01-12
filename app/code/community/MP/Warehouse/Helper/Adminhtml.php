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
 * Admin html helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Adminhtml 
    extends Mage_Core_Helper_Abstract
{
    /**
     * Get warehouse helper
     * 
     * @return MP_Warehouse_Helper_Data
     */
    public function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Add column relation to collection
     * 
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * 
     * @return self
     */
    protected function addColumnRelationToCollection($collection, $column)
    {
        if (!$column->getRelation()) {
            return $this;
        }

        $relation       = $column->getRelation();
        $fieldAlias     = $column->getId();
        $fieldName      = $relation['field_name'];
        $fkFieldName    = $relation['fk_field_name'];
        $refFieldName   = $relation['ref_field_name'];
        $tableAlias     = $relation['table_alias'];
        $table          = $collection->getTable($relation['table_name']);
        $collection->addFilterToMap($fieldAlias, $tableAlias.'.'.$fieldName);
        $collection->getSelect()->joinLeft(
            array($tableAlias => $table), 
            '(main_table.'.$fkFieldName.' = '.$tableAlias.'.'.$refFieldName.')', 
            array($fieldAlias => $tableAlias.'.'.$fieldName)
        );
        return $this;
    }
    /**
     * Add column relation to collection
     * 
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * 
     * @return self
     */
    protected function addColumnRelationDataToCollection($collection, $column)
    {
        if (!$collection || !$column || !$column->getRelation()) {
            return $this;
        }

        $relation       = $column->getRelation();
        $fkFieldName    = $relation['fk_field_name'];
        $refFieldName   = $relation['ref_field_name'];
        $fieldName      = $relation['field_name'];
        $tableName      = $relation['table_name'];
        $table          = $collection->getTable($tableName);
        $modelValues = array();
        foreach ($collection as $model) {
            $modelValues[$model->getData($fkFieldName)] = array();
        }

        if (count($modelValues)) {
            $adapter    = $collection->getConnection();
            $select     = $adapter->select()
                ->from($table)
                ->where($adapter->quoteInto($fkFieldName.' IN (?)', array_keys($modelValues)));
            $items = $adapter->fetchAll($select);
            foreach ($items as $item) {
                $modelId    = $item[$refFieldName];
                $value      = $item[$fieldName];
                $modelValues[$modelId][] = $value;
            }
        }

        foreach ($collection as $model) {
            $modelId = $model->getData($fkFieldName);
            if (isset($modelValues[$modelId])) {
                $model->setData($column->getId(), $modelValues[$modelId]);
            }
        }

        return $this;
    }
    /**
     * Get column filter to collection
     * 
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * 
     * @return self
     */
    public function addColumnFilterToCollection($collection, $column)
    {
        $this->addColumnRelationToCollection($collection, $column);
        $field          = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
        $condition      = $column->getFilter()->getCondition();
        if ($field && isset($condition)) {
            $collection->addFieldToFilter($field, $condition);
        }

        return $this;
    }
    
    
    
    /**
     * Orders Grid
     */
    /**
     * Prepare order grid collection
     * 
     * @param Mage_Adminhtml_Block_Sales_Order_Grid $grid
     * 
     * @return self
     */
    public function prepareOrderGridCollection($grid)
    {
        $this->addColumnRelationDataToCollection(
            $grid->getCollection(), 
            $grid->getColumn('stock_ids')
        );
        return $this;
    }
    /**
     * Add stock order grid column
     * 
     * @param Mage_Adminhtml_Block_Sales_Order_Grid $grid
     * 
     * @return self
     */
    public function addStockOrderGridColumn($grid)
    {
        $helper     = $this->getWarehouseHelper();
        $options    = $helper->getStocksHash();
        $grid->addColumnAfter(
            'stock_ids', array(
            'header'    => $helper->__('Warehouses'), 
            'sortable'  => false, 
            'index'     => 'stock_ids', 
            'type'      => 'options', 
            'options'   => $options, 
            'filter_condition_callback' => array($this, 'addColumnFilterToCollection'), 
            'relation'  => array(
                'table_alias'       => 'stock_ids_table', 
                'table_name'        => 'warehouse/order_grid_warehouse', 
                'fk_field_name'     => 'entity_id', 
                'ref_field_name'    => 'entity_id', 
                'field_name'        => 'stock_id', 
            ), 
            ), 'status'
        );
        return $this;
    }
    /**
     * Invoices Grid
     */
    /**
     * Prepare invoice grid collection
     * 
     * @param Mage_Adminhtml_Block_Sales_Invoice_Grid $grid
     * 
     * @return self
     */
    public function prepareInvoiceGridCollection($grid)
    {
        $this->addColumnRelationDataToCollection(
            $grid->getCollection(), 
            $grid->getColumn('stock_ids')
        );
        return $this;
    }
    /**
     * Add stock invoice grid column
     * 
     * @param Mage_Adminhtml_Block_Sales_Invoice_Grid $grid
     * 
     * @return self
     */
    public function addStockInvoiceGridColumn($grid)
    {
        $helper     = $this->getWarehouseHelper();
        $options    = $helper->getStocksHash();
        $grid->addColumnAfter(
            'stock_ids', array(
            'header'    => $helper->__('Warehouses'), 
            'sortable'  => false, 
            'index'     => 'stock_ids', 
            'type'      => 'options', 
            'options'   => $options, 
            'filter_condition_callback' => array($this, 'addColumnFilterToCollection'), 
            'relation'  => array(
                'table_alias'       => 'stock_ids_table', 
                'table_name'        => 'warehouse/invoice_grid_warehouse', 
                'fk_field_name'     => 'entity_id', 
                'ref_field_name'    => 'entity_id', 
                'field_name'        => 'stock_id', 
            ), 
            ), 'grand_total'
        );
        return $this;
    }
    /**
     * Shipments Grid
     */
    /**
     * Prepare shipment grid collection
     * 
     * @param Mage_Adminhtml_Block_Sales_Shipment_Grid $grid
     * 
     * @return self
     */
    public function prepareShipmentGridCollection($grid)
    {
        $this->addColumnRelationDataToCollection(
            $grid->getCollection(), 
            $grid->getColumn('stock_ids')
        );
        return $this;
    }
    /**
     * Add stock shipment grid column
     * 
     * @param Mage_Adminhtml_Block_Sales_Shipment_Grid $grid
     * 
     * @return self
     */
    public function addStockShipmentGridColumn($grid)
    {
        $helper     = $this->getWarehouseHelper();
        $options    = $helper->getStocksHash();
        $grid->addColumnAfter(
            'stock_ids', array(
            'header'    => $helper->__('Warehouses'), 
            'sortable'  => false, 
            'index'     => 'stock_ids', 
            'type'      => 'options', 
            'options'   => $options, 
            'filter_condition_callback' => array($this, 'addColumnFilterToCollection'), 
            'relation'  => array(
                'table_alias'       => 'stock_ids_table', 
                'table_name'        => 'warehouse/shipment_grid_warehouse', 
                'fk_field_name'     => 'entity_id', 
                'ref_field_name'    => 'entity_id', 
                'field_name'        => 'stock_id', 
            ), 
            ), 'total_qty'
        );
        return $this;
    }
    /**
     * Creditmemos Grid
     */
    /**
     * Prepare creditmemo grid collection
     * 
     * @param Mage_Adminhtml_Block_Sales_Creditmemo_Grid $grid
     * 
     * @return self
     */
    public function prepareCreditmemoGridCollection($grid)
    {
        $this->addColumnRelationDataToCollection(
            $grid->getCollection(), 
            $grid->getColumn('stock_ids')
        );
        return $this;
    }
    /**
     * Add stock creditmemo grid column
     * 
     * @param Mage_Adminhtml_Block_Sales_Creditmemo_Grid $grid
     * 
     * @return self
     */
    public function addStockCreditmemoGridColumn($grid)
    {
        $helper     = $this->getWarehouseHelper();
        $options    = $helper->getStocksHash();
        $grid->addColumnAfter(
            'stock_ids', array(
            'header'    => $helper->__('Warehouses'), 
            'sortable'  => false, 
            'index'     => 'stock_ids', 
            'type'      => 'options', 
            'options'   => $options, 
            'filter_condition_callback' => array($this, 'addColumnFilterToCollection'), 
            'relation'  => array(
                'table_alias'       => 'stock_ids_table', 
                'table_name'        => 'warehouse/creditmemo_grid_warehouse', 
                'fk_field_name'     => 'entity_id', 
                'ref_field_name'    => 'entity_id', 
                'field_name'        => 'stock_id', 
            ), 
            ), 'grand_total'
        );
        return $this;
    }
    /**
     * Products Grid
     */
    /**
     * Add column qty data to collection
     * 
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * 
     * @return self
     */
    protected function addColumnQtyDataToCollection($collection, $column)
    {
        $helper     = $this->getWarehouseHelper();
        $stockIds   = $helper->getStockIds();
        $qtys       = array();
        foreach ($collection as $product) {
            $productId = (int) $product->getId();
            $qtys[$productId] = array();
        }

        if (!empty($qtys)) {
            $adapter    = $collection->getConnection();
            $table      = $collection->getTable('cataloginventory/stock_item');
            $select     = $adapter->select()->from($table)
                ->where($adapter->quoteInto('product_id IN (?)', array_keys($qtys)));
            $data       = $adapter->fetchAll($select);
            foreach ($data as $row) {
                $productId  = (int) $row['product_id'];
                $stockId    = (int) $row['stock_id'];
                $qty        = (float) $row['qty'];
                $qtys[$productId][$stockId] = $qty;
            }

            foreach ($qtys as $productId => $productQtys) {
                foreach ($stockIds as $stockId) {
                    if (!isset($productQtys[$stockId])) {
                        $qtys[$productId][$stockId] = 0;
                    }
                }
            }
        }

        foreach ($collection as $product) {
            $productId = (int) $product->getId();
            if (isset($qtys[$productId])) {
                $product->setData($column->getId(), $qtys[$productId]);
            }
        }

        return $this;
    }
    /**
     * Add column batch price data to collection
     * 
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * 
     * @return self
     */
    protected function addColumnBatchPriceDataToCollection($collection, $column)
    {
        $helper         = $this->getWarehouseHelper();
        $priceHelper    = $helper->getProductPriceHelper();
        $stockIds       = $helper->getStockIds();
        foreach ($collection as $product) {
            $batchPrices = array();
            foreach ($stockIds as $stockId) {
                $batchPrice = $priceHelper->getWebsiteBatchPriceByStockId($product, $stockId);
                if (is_null($batchPrice)) {
                    $batchPrice = $product->getPrice();
                }

                $batchPrices[$stockId] = $batchPrice;
            }

            $product->setBatchPrices($batchPrices);
        }

        return $this;
    }
    /**
     * Get column qty filter to collection
     * 
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * 
     * @return self
     */
    public function addColumnQtyFilterToCollection($collection, $column)
    {
        $helper             = $this->getWarehouseHelper();
        $config             = $helper->getConfig();
        if (!$config->isCatalogBackendGridQtyVisible()) {
            return $this;
        }

        $adapter            = $collection->getConnection();
        $condition          = $column->getFilter()->getCondition();
        $select             = $collection->getSelect();
        
        $qtyTableAlias      = 'cisi';
        $qtyTable           = $collection->getTable('cataloginventory/stock_item');
        $qty                = $collection->getConnection()
            ->select()
            ->from(array($qtyTableAlias => $qtyTable), array())
            ->columns(array('qty' => 'SUM('.$qtyTableAlias.'.qty)'))
            ->where('e.entity_id = '.$qtyTableAlias.'.product_id')
            ->assemble();
        $conditionPieces = array();
        if (isset($condition['from'])) {
            array_push($conditionPieces, '('.$qty.') >= '.$adapter->quote($condition['from']));
        }

        if (isset($condition['to'])) {
            array_push($conditionPieces, '('.$qty.') <= '.$adapter->quote($condition['to']));
        }

        $select->where(implode(' AND ', $conditionPieces));
        return $this;
    }
    /**
     * Add qty product grid column
     * 
     * @param Mage_Adminhtml_Block_Catalog_Product_Grid $grid
     * 
     * @return self
     */
    public function addQtyProductGridColumn($grid)
    {
        $helper     = $this->getWarehouseHelper();
        $config     = $helper->getConfig();
        if (!$config->isCatalogBackendGridQtyVisible()) {
            return $this;
        }

        $grid->addColumn(
            'qtys', array(
            'header'        => $helper->__('Qty'), 
            'sortable'      => false, 
            'index'         => 'qtys', 
            'width'         => '140px', 
            'align'         => 'left', 
            'renderer'      => 'warehouse/adminhtml_catalog_product_grid_column_renderer_qtys', 
            'filter_condition_callback' => array($this, 'addColumnQtyFilterToCollection'), 
            'filter'        => 'adminhtml/widget_grid_column_filter_range', 
            )
        );
        return $this;
    }
    /**
     * Add batch price product grid column
     * 
     * @param Mage_Adminhtml_Block_Catalog_Product_Grid $grid
     * 
     * @return self
     */
    public function addBatchPriceProductGridColumn($grid)
    {
        $helper         = $this->getWarehouseHelper();
        $config         = $helper->getConfig();
        if (!$config->isCatalogBackendGridBatchPricesVisible()) {
            return $this;
        }

        $storeId        = (int) $grid->getRequest()->getParam('store', 0);
        $store          = Mage::app()->getStore($storeId);
        $baseCurrency   = $store->getBaseCurrency();
        $grid->addColumn(
            'batch_prices', array(
            'header'        => $helper->__('Batch Price'), 
            'currency_code' => $baseCurrency->getCode(), 
            'index'         => 'batch_prices', 
            'width'         => '140px', 
            'align'         => 'left', 
            'renderer'      => 'warehouse/adminhtml_catalog_product_grid_column_renderer_batchprices', 
            'filter'        => false, 
            'sortable'      => false, 
            )
        );
        return $this;
    }
    /**
     * Prepare product grid
     * 
     * @param Mage_Adminhtml_Block_Catalog_Product_Grid $grid
     * 
     * @return self
     */
    public function prepareProductGrid($grid)
    {
        $helper     = $this->getWarehouseHelper();
        $config     = $helper->getConfig();
        if ($helper->getVersionHelper()->isGe1600()) {
            $grid->removeColumn('qty');
        }

        if ($config->isCatalogBackendGridQtyVisible()) {
            $qtyColumnId = 'qtys';
            $this->addColumnQtyDataToCollection($grid->getCollection(), $grid->getColumn($qtyColumnId));
            $grid->addColumnsOrder($qtyColumnId, 'price');
        }

        if ($config->isCatalogBackendGridBatchPricesVisible()) {
            $batchPricesColumnId = 'batch_prices';
            $this->addColumnBatchPriceDataToCollection($grid->getCollection(), $grid->getColumn($batchPricesColumnId));
            $grid->addColumnsOrder($batchPricesColumnId, 'price');
        }

        $grid->sortColumnsByOrder();
        return $this;
    }
    /**
     * Product Lowstock
     */
    /**
     * Before load product lowstock collection
     * 
     * @param Mage_Reports_Model_Mysql4_Product_Lowstock_Collection $collection
     * 
     * @return self
     */
    public function beforeLoadProductLowstockCollection($collection)
    {
        $helper             = $this->getWarehouseHelper();
        $inventoryHelper    = $helper->getCatalogInventoryHelper();
        $select             = $collection->getSelect();
        $stockIds           = $helper->getStockIds();
        foreach ($stockIds as $stockId) {
            $qtyFieldName           = 'qty_'.$stockId;
            $collection->joinField(
                $qtyFieldName, 'cataloginventory/stock_item', 'qty', 
                'product_id=entity_id', "{{table}}.stock_id = '{$stockId}'", 'left'
            );
        }

        $queryPieces = array();
        foreach ($stockIds as $stockId) {
            $stockItemTableAlias    = 'at_qty_'.$stockId;
            array_push(
                $queryPieces, 
                sprintf(
                    '(IF(%s, %d, %s)=1)', 
                    $stockItemTableAlias.'.use_config_manage_stock', 
                    $inventoryHelper->getManageStock(), 
                    $stockItemTableAlias.'.manage_stock'
                )
            );
        }

        if (count($queryPieces)) {
            $select->where(implode(' OR ', $queryPieces));
        }

        $queryPieces = array();
        foreach ($stockIds as $stockId) {
            $stockItemTableAlias    = 'at_qty_'.$stockId;
            array_push(
                $queryPieces, 
                sprintf(
                    '(%s < IF(%s, %d, %s))', 
                    $stockItemTableAlias.'.qty', 
                    $stockItemTableAlias.'.use_config_notify_stock_qty', 
                    $inventoryHelper->getNotifyStockQty(), 
                    $stockItemTableAlias.'.notify_stock_qty'
                )
            );
        }

        if (count($queryPieces)) {
            $select->where(implode(' OR ', $queryPieces));
        }

        $collection->setOrder('sku', 'asc');
        return $this;
    }
    /**
     * Add qty product lowstock grid column filters to collection
     * 
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * 
     * @return self
     */
    public function addQtyProductLowstockGridColumnFiltersToCollection($collection, $column)
    {
        $field          = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
        $condition      = $column->getFilter()->getCondition();
        if ($field && isset($condition)) {
            $adapter    = $collection->getConnection();
            $sql        = $adapter->prepareSqlCondition('at_'.$field.'.qty', $condition);
            $collection->getSelect()->where($sql);
        }

        return $this;
    }
    /**
     * Add product lowstock grid columns
     * 
     * @param Mage_Adminhtml_Block_Report_Product_Lowstock_Grid $grid
     * 
     * @return self
     */
    public function addQtyProductLowstockGridColumns($grid)
    {
        $helper         = $this->getWarehouseHelper();
        $stockIds       = $helper->getStockIds();
        foreach ($stockIds as $stockId) {
            $fieldName = 'qty_'.$stockId;
            $grid->addColumn(
                $fieldName, array(
                'header'    => sprintf(
                    $helper->__('%s Qty'), 
                    $helper->getWarehouseTitleByStockId($stockId)
                ), 
                'align'     => 'right', 
                'sortable'  => false, 
                'filter'    => 'adminhtml/widget_grid_column_filter_range', 
                'filter_condition_callback' => array($this, 'addQtyProductLowstockGridColumnFiltersToCollection'), 
                'stock_id'  => $stockId, 
                'index'     => $fieldName, 
                'type'      => 'number', 
                )
            );
        }

        return $this;
    }
    /**
     * Prepare product lowstock grid
     * 
     * @param Mage_Adminhtml_Block_Report_Product_Lowstock_Grid $grid
     * 
     * @return self
     */
    public function prepareProductLowstockGrid($grid)
    {
        $helper         = $this->getWarehouseHelper();
        $stockIds       = $helper->getStockIds();
        if ($helper->getVersionHelper()->isGe1600()) {
            $grid->removeColumn('qty');
        }

        $prevColumn     = 'sku';
        foreach ($stockIds as $stockId) {
            $fieldName      = 'qty_'.$stockId;
            $grid->addColumnsOrder($fieldName, $prevColumn);
            $prevColumn     = $fieldName;
        }

        $grid->sortColumnsByOrder();
        return $this;
    }
}
