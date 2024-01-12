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
class MP_Warehouse_Helper_Core_Adminhtml
    extends MP_Warehouse_Helper_Core_Abstract
{
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
     * Get eav field table alias
     *
     * @param string $fieldName
     *
     * @return string
     */
    protected function getEavFieldTableAlias($fieldName)
    {
        return 'at_'.$fieldName;
    }
    /**
     * Get eav field expression
     *
     * @param string $fieldName
     * @param mixed $storeId
     *
     * @return Zend_Db_Expr
     */
    protected function getEavFieldExpr($fieldName, $storeId)
    {
        $priceHelper            = $this->getCoreHelper()
            ->getProductHelper()
            ->getPriceHelper();
        $fieldTableAlias    = $this->getEavFieldTableAlias($fieldName);
        $fieldExpr          = $fieldTableAlias.'.value';
        if ($storeId && !$priceHelper->isGlobalScope()) {
            $defaultFieldTableAlias     = $fieldTableAlias.'_default';
            $defaultFieldExpr           = str_replace(
                $fieldTableAlias,
                $defaultFieldTableAlias,
                $fieldExpr
            );
            $fieldExpr                  = "IF (".
                "{$fieldTableAlias}.value_id > 0, ".
                "{$fieldExpr}, ".
                "{$defaultFieldExpr}".
                ")";
        }

        return new Zend_Db_Expr($fieldExpr);
    }
}
