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
 * Warehouse collection
 * 
 * @category    MP
 * @package     MP_Warehouse
 * @author      Mage Plugins Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Warehouse_Collection 
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('warehouse/warehouse');
    }
    /**
     * Add id filter
     * 
     * @param mixed $warehouseId
     * @param boolean $exclude
     * 
     * @return $this
     */
    public function addIdFilter($warehouseId, $exclude = false)
    {
        if (is_array($warehouseId)) {
            if (!empty($warehouseId)) {
                if ($exclude) {
                    $condition              = array('nin' => $warehouseId);
                } else {
                    $condition              = array('in' => $warehouseId);
                }
            } else {
                $condition              = '';
            }
        } else {
            if ($exclude) {
                $condition              = array('neq' => $warehouseId);
            } else {
                $condition              = $warehouseId;
            }
        }

        $this->addFieldToFilter('warehouse_id', $condition);
        return $this;
    }
    /**
     * Get options array
     * 
     * @param string $valueField
     * 
     * @return array
     */
    public function toOptionArray($valueField = 'warehouse_id')
    {
        return $this->_toOptionArray($valueField, 'title');
    }
    /**
     * Get options hash array
     * 
     * @param string $valueField
     * 
     * @return array
     */
    public function toOptionHash($valueField = 'warehouse_id')
    {
        return $this->_toOptionHash($valueField, 'title');
    }
    /**
     * Before load
     * 
     * @return $this
     */
    protected function _beforeLoad()
    {
        Mage::dispatchEvent('warehouse_collection_load_before', array('collection' => $this));
        parent::_beforeLoad();
        return $this;
    }
    /**
     * After load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        if (count($this) > 0) {
            Mage::dispatchEvent('warehouse_collection_load_after', array('collection' => $this));
        }

        return $this;
    }
}
