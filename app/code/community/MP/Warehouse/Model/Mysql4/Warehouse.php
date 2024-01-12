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
 * Warehouse resource
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Warehouse 
    extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('warehouse/warehouse', 'warehouse_id');
    }
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
     * Get write adapter
     * 
     * @return Varien_Db_Adapter_Interface
     */
    public function getWriteAdapter()
    {
        return $this->_getWriteAdapter();
    }
    /**
     * Get read adapter
     * 
     * @return Varien_Db_Adapter_Interface
     */
    public function getReadAdapter()
    {
        return $this->_getReadAdapter();
    }
    /**
     * Get write connection
     * 
     * @return Varien_Db_Adapter_Interface
     */
    public function getWriteConnection()
    {
        return $this->_getWriteAdapter();
    }
    /**
     * Before save
     *
     * @param Mage_Core_Model_Abstract $object
     * 
     * @return $this
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeSave($object);
        if (!$object->getId() || !$object->getCreatedAt()) {
            $object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }

        $object->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());
        return $this;
    }
    /**
     * Perform actions after object save
     *
     * @param Mage_Core_Model_Abstract $object
     * 
     * @return $this
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);
        return $this;
    }
    /**
     * Load warehouse by code
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * @param string $code
     * @param int $exclude
     * 
     * @return $this
     */
    public function loadByCode(MP_Warehouse_Model_Warehouse $warehouse, $code, $exclude = null)
    {
        $adapter                = $this->_getReadAdapter();
        $select                 = $adapter
            ->select()
            ->from($this->getMainTable());
        $select->where('code = ?', $code);
        if ($exclude) {
            $select->where('warehouse_id <> ?', $exclude);
        }

        $row                    = $adapter->fetchRow($select);
        if ($row && !empty($row)) {
            $warehouse->setData($row);
        }

        $this->_afterLoad($warehouse);
        return $this;
    }
    /**
     * Load warehouse by title
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * @param string $title
     * @param int $exclude
     * 
     * @return $this
     */
    public function loadByTitle(MP_Warehouse_Model_Warehouse $warehouse, $title, $exclude = null)
    {
        $adapter                = $this->_getReadAdapter();
        $select                 = $adapter
            ->select()
            ->from($this->getMainTable());
        $select->where('title = ?', $title);
        if ($exclude) {
            $select->where('warehouse_id <> ?', $exclude);
        }

        $row                    = $adapter->fetchRow($select);
        if ($row && !empty($row)) {
            $warehouse->setData($row);
        }

        $this->_afterLoad($warehouse);
        return $this;
    }
    /**
     * Get stores
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return array
     */
    public function getStores($warehouse)
    {
        $stores                 = array();
        $adapter                = $this->_getReadAdapter();
        $select                 = $adapter->select()
            ->from($this->getTable('warehouse/warehouse_store'))
            ->where('warehouse_id = ?', $warehouse->getId());
        $query                  = $adapter->query($select);
        while ($row = $query->fetch()) {
            array_push($stores, $row['store_id']);
        }

        return $stores;
    }
    /**
     * Get shipping carriers
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return array
     */
    public function getShippingCarriers($warehouse)
    {
        $shippingCarriers       = array();
        $adapter                = $this->_getReadAdapter();
        $select                 = $adapter->select()
            ->from($this->getTable('warehouse/warehouse_shipping_carrier'))
            ->where('warehouse_id = ?', $warehouse->getId());
        $query                  = $adapter->query($select);
        while ($row = $query->fetch()) {
            array_push($shippingCarriers, $row['shipping_carrier']);
        }

        return $shippingCarriers;
    }
    /**
     * Get area stock id
     * 
     * @param Varien_Object $address
     * 
     * @return integer
     */
    public function getAreaStockId($address)
    {
        $adapter                = $this->_getReadAdapter();
        $tableAlias             = 'w';
        $table                  = $this->getMainTable();
        $areaTableAlias         = 'wa';
        $areaTable              = $this->getTable('warehouse/warehouse_area');
        $bind                   = array(
            ':country_id'           => $address->getCountryId(), 
            ':region_id'            => (int) $address->getRegionId(), 
            ':zip'                  => $address->getPostcode(), 
        );
        $warehouseId            = $areaTableAlias.'.warehouse_id';
        $countryId              = $areaTableAlias.'.country_id';
        $regionId               = $areaTableAlias.'.region_id';
        $isZipRange             = $areaTableAlias.'.is_zip_range';
        $zip                    = $areaTableAlias.'.zip';
        $fromZip                = $areaTableAlias.'.from_zip';
        $toZip                  = $areaTableAlias.'.to_zip';
        $priority               = $areaTableAlias.'.priority';
        $countryIdOrder         = "{$countryId} DESC";
        $regionIdOrder          = "{$regionId} DESC";
        $zipOrder               = "(IF ({$isZipRange} = '0', IF (({$zip} IS NULL) OR ({$zip} = ''), 3, 1), 2)) ASC";
        $countryIdWhere         = "{$countryId} = :country_id";
        $countryIdEmptyWhere    = "{$countryId} = '0'";
        $regionIdWhere          = "{$regionId} = :region_id";
        $regionIdEmptyWhere     = "{$regionId} = '0'";
        $zipWhere               = "(
            IF (
                {$isZipRange} <> '0', 
                (:zip >= {$fromZip}) AND (:zip <= {$toZip}), 
                {$zip} = :zip
            )
        )";
        $zipEmptyWhere          = "(({$isZipRange} = '0') AND (({$zip} IS NULL) OR ({$zip} = '')))";
        $where                  = implode(
            ' AND ', array(
            "({$priority} IS NULL)", 
            '('.implode(
                ' OR ', array(
                "({$countryIdWhere} AND {$regionIdWhere} AND {$zipWhere})", 
                "({$countryIdWhere} AND {$regionIdWhere} AND {$zipEmptyWhere})", 
                "({$countryIdWhere} AND {$regionIdEmptyWhere} AND {$zipEmptyWhere})", 
                "({$countryIdWhere} AND {$regionIdEmptyWhere} AND {$zipWhere})", 
                "({$countryIdEmptyWhere} AND {$regionIdEmptyWhere} AND {$zipEmptyWhere})", 
                )
            ).')', 
            )
        );
        $select                 = $adapter
            ->select()
            ->from(
                array($tableAlias => $table), 
                array()
            )
            ->joinInner(
                array($areaTableAlias => $areaTable), 
                "({$tableAlias}.warehouse_id = {$warehouseId})", 
                array()
            )
            ->columns(array('stock_id' => $tableAlias.'.stock_id'))
            ->where($where)
            ->order(array($countryIdOrder, $regionIdOrder, $zipOrder))
            ->limit(1);
        return $adapter->fetchOne($select, $bind);
    }
    /**
     * Get area stock priorities
     * 
     * @param Varien_Object $address
     * 
     * @return array
     */
    public function getAreaStockPriorities($address)
    {
        $priorities             = array();
        $adapter                = $this->_getReadAdapter();
        $tableAlias             = 'w';
        $table                  = $this->getMainTable();
        $areaTableAlias         = 'wa';
        $areaTable              = $this->getTable('warehouse/warehouse_area');
        $bind                   = array(
            ':country_id'           => $address->getCountryId(), 
            ':region_id'            => (int) $address->getRegionId(), 
            ':zip'                  => $address->getPostcode(), 
        );
        $warehouseId            = $areaTableAlias.'.warehouse_id';
        $countryId              = $areaTableAlias.'.country_id';
        $regionId               = $areaTableAlias.'.region_id';
        $isZipRange             = $areaTableAlias.'.is_zip_range';
        $zip                    = $areaTableAlias.'.zip';
        $fromZip                = $areaTableAlias.'.from_zip';
        $toZip                  = $areaTableAlias.'.to_zip';
        $priority               = $areaTableAlias.'.priority';
        $countryIdOrder         = "{$countryId} DESC";
        $regionIdOrder          = "{$regionId} DESC";
        $zipOrder               = "(IF ({$isZipRange} = '0', IF (({$zip} IS NULL) OR ({$zip} = ''), 3, 1), 2)) ASC";
        $priorityOrder          = "{$priority} ASC";
        $countryIdWhere         = "{$countryId} = :country_id";
        $countryIdEmptyWhere    = "{$countryId} = '0'";
        $regionIdWhere          = "{$regionId} = :region_id";
        $regionIdEmptyWhere     = "{$regionId} = '0'";
        $zipWhere               = "(
            IF (
                {$isZipRange} <> '0', 
                (:zip >= {$fromZip}) AND (:zip <= {$toZip}), 
                {$zip} = :zip
            )
        )";
        $zipEmptyWhere        = "(({$isZipRange} = '0') AND (({$zip} IS NULL) OR ({$zip} = '')))";
        $where                  = implode(
            ' AND ', array(
            "({$warehouseId} = {$tableAlias}.warehouse_id)", 
            "({$priority} IS NOT NULL)", 
            '('.implode(
                ' OR ', array(
                "({$countryIdWhere} AND {$regionIdWhere} AND {$zipWhere})", 
                "({$countryIdWhere} AND {$regionIdWhere} AND {$zipEmptyWhere})", 
                "({$countryIdWhere} AND {$regionIdEmptyWhere} AND {$zipEmptyWhere})", 
                "({$countryIdWhere} AND {$regionIdEmptyWhere} AND {$zipWhere})", 
                "({$countryIdEmptyWhere} AND {$regionIdEmptyWhere} AND {$zipEmptyWhere})", 
                )
            ).')', 
            )
        );
        $prioritySelect         = $adapter
            ->select()
            ->from(
                array($areaTableAlias => $areaTable), 
                array()
            )
            ->columns(array('priority' => $areaTableAlias.'.priority'))
            ->where($where)
            ->order(array($countryIdOrder, $regionIdOrder, $zipOrder, $priorityOrder))
            ->limit(1);
        $select                 = $adapter
            ->select()
            ->from(
                array($tableAlias => $table), 
                array()
            )
            ->columns(
                array(
                    'stock_id'              => $tableAlias.'.stock_id', 
                    'priority'              => new Zend_Db_Expr('('.$prioritySelect->assemble().')'), 
                )
            );
        $query                  = $adapter->query($select, $bind);
        while ($row = $query->fetch()) {
            if (array_key_exists('stock_id', $row) && 
                array_key_exists('priority', $row)
            ) {
                $stockId                = (int) $row['stock_id'];
                $priority               = ($row['priority'] !== null) ? 
                    (int) $row['priority'] : 
                    $row['priority'];
                $priorities[$stockId]   = $priority;
            }
        }

        return $priorities;
    }
}
