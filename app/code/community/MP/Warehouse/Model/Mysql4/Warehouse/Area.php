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
 * Warehouse area resource
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Warehouse_Area 
    extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Constructor
     */
    protected function _construct() 
    {
        $this->_init('warehouse/warehouse_area', 'warehouse_area_id');
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
     * Load warehouse area by request
     * 
     * @param MP_Warehouse_Model_Warehouse_Area $warehouseArea
     * @param Varien_Object $request
     * 
     * @return $this
     */
    public function loadByRequest($warehouseArea, $request)
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        $adapter                = $this->_getReadAdapter();
        $select                 = $adapter
            ->select()
            ->from($this->getMainTable());
        $conditions             = array();
        if ($request->getId()) {
            array_push(
                $conditions, 
                '(warehouse_area_id != '.$adapter->quote($request->getId()).')'
            );
        }

        $warehouseId            = $request->getWarehouseId();
        $countryId              = ($request->getCountryId()) ? $request->getCountryId() : '0';
        $regionId               = ($request->getRegionId()) ? $request->getRegionId() : '0';
        $zip                    = ($request->getZip()) ? $request->getZip() : '';
        $priority               = ($request->getPriority() !== null) ? $request->getPriority() : null;
        array_push(
            $conditions, 
            '(warehouse_id = '.$adapter->quote($warehouseId).')'
        );
        array_push(
            $conditions, 
            '(country_id = '.$adapter->quote($countryId).')'
        );
        array_push(
            $conditions, 
            '(region_id = '.$adapter->quote($regionId).')'
        );
        array_push(
            $conditions, 
            ($zip) ? '(zip = '.$adapter->quote($zip).')' : "((zip = '') OR (zip IS NULL))"
        );
        if ($config->isMultipleMode()) {
            array_push($conditions, '(priority IS NOT NULL)');
        } else {
            array_push($conditions, '(priority IS NULL)');
        }

        $select->where(implode(' AND ', $conditions));
        $select->limit(1);
        $row                    = $adapter->fetchRow($select);
        if ($row && !empty($row)) {
            $warehouseArea->setData($row);
        }

        $this->_afterLoad($warehouseArea);
        return $this;
    }
}
