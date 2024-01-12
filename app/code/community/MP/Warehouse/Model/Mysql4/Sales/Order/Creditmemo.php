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
 * Creditmemo resource
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Sales_Order_Creditmemo 
    extends Mage_Sales_Model_Mysql4_Order_Creditmemo
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
     * Update records in grid table
     *
     * @param array|int $ids
     * 
     * @return MP_Warehouse_Model_Mysql4_Sales_Order_Creditmemo
     */
    public function updateGridRecords($ids)
    {
        parent::updateGridRecords($ids);
        if ($this->_grid) {
            if (!is_array($ids)) { $ids = array($ids); 
            }

            if ($this->_eventPrefix && $this->_eventObject) {
                $proxy = new Varien_Object();
                $proxy->setIds($ids)->setData($this->_eventObject, $this);
                Mage::dispatchEvent($this->_eventPrefix . '_update_grid_records', array('proxy' => $proxy));
                $ids = $proxy->getIds();
            }

            if (empty($ids)) {
                return $this;
            }

            $creditmemoItemTable = $this->getTable('sales/creditmemo_item');
            $creditmemoWarehouseTable = $this->getTable('warehouse/creditmemo_grid_warehouse');
            $write = $this->_getWriteAdapter();
            $write->delete($creditmemoWarehouseTable, 'entity_id IN '.$write->quoteInto('(?)', $ids));
            $select = $write->select()
                ->from(array('creditmemo_item_table' => $creditmemoItemTable), array('parent_id', 'stock_id'))
                ->where('creditmemo_item_table.parent_id IN(?)', $ids)
                ->distinct(true);
            $write->query($select->insertFromSelect($creditmemoWarehouseTable, array('entity_id', 'stock_id', ), false));
        }

        return $this;
    }
}
