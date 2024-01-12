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
 * Shipping table rate method resource
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_ShippingTablerate_Tablerate_Method
    extends Mage_Core_Model_Mysql4_Abstract
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
     * Constructor
     */
    protected function _construct() 
    {
        $this->_init('warehouse/shippingTablerate_tablerate_method', 'method_id');
    }
    /**
     * Load method by code
     * 
     * @param MP_Warehouse_Model_ShippingTablerate_Tablerate_Method $tablerateMethod
     * @param string $code
     * @param int $exclude
     * 
     * @return MP_Warehouse_Model_Mysql4_Shippingtablerate_Tablerate_Method
     */
    public function loadByCode($tablerateMethod, $code, $exclude = null)
    {
        $adapter    = $this->_getReadAdapter();
        $select     = $adapter->select()->from($this->getMainTable());
        $select->where('code = ?', $code);
        if ($exclude) {
            $select->where('method_id <> ?', $exclude);
        }

        $row        = $adapter->fetchRow($select);
        if ($row && !empty($row)) {
            $tablerateMethod->setData($row);
        }

        $this->_afterLoad($tablerateMethod);
        return $this;
    }
    /**
     * Load method by name
     * 
     * @param MP_Warehouse_Model_ShippingTablerate_Tablerate_Method $tablerateMethod
     * @param string $name
     * @param int $exclude
     * 
     * @return MP_Warehouse_Model_Mysql4_ShippingTablerate_Tablerate_Method
     */
    public function loadByName($tablerateMethod, $name, $exclude = null)
    {
        $adapter    = $this->_getReadAdapter();
        $select     = $adapter->select()->from($this->getMainTable());
        $select->where('name = ?', $name);
        if ($exclude) {
            $select->where('method_id <> ?', $exclude);
        }

        $row = $adapter->fetchRow($select);
        if ($row && !empty($row)) {
            $tablerateMethod->setData($row);
        }

        $this->_afterLoad($tablerateMethod);
        return $this;
    }
    /**
     * Get write connection
     * 
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    public function getWriteConnection()
    {
        return $this->_getWriteAdapter();
    }
}
