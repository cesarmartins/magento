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
 * Warehouse area collection
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Warehouse_Area_Collection 
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * directory/country table name
     *
     * @var string
     */
    protected $_countryTable;
    /**
     * directory/country_region table name
     *
     * @var string
     */
    protected $_regionTable;
    
    /**
     * Constructor
     */
    protected function _construct() 
    {
        $this->_init('warehouse/warehouse_area');
        $this->_countryTable    = $this->getTable('directory/country');
        $this->_regionTable     = $this->getTable('directory/country_region');
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
     * Initialize select
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $this->_select
            ->joinLeft(
                array('country_table' => $this->_countryTable), 
                'country_table.country_id = main_table.country_id', 
                array('country' => 'iso2_code')
            )
            ->joinLeft(
                array('region_table' => $this->_regionTable),
                'region_table.region_id = main_table.region_id',
                array('region' => 'code')
            );
    }
    /**
     * Set warehouse filter
     *
     * @param int $warehouseId
     * 
     * @return $this
     */
    public function setWarehouseFilter($warehouseId)
    {
        $this->addFieldToFilter('warehouse_id', $warehouseId);
        return $this;
    }
    /**
     * Set single mode priority filter
     * 
     * @return $this
     */
    public function setSingleModePriorityFilter()
    {
        $this->addFieldToFilter('priority', array('null' => null));
        return $this;
    }
    /**
     * Set multiple mode priority filter
     * 
     * @return $this
     */
    public function setMultipleModePriorityFilter()
    {
        $this->addFieldToFilter('priority', array('notnull' => null));
        return $this;
    }
    /**
     * Set priority filter
     * 
     * @return $this
     */
    public function setPriorityFilter()
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        if ($config->isMultipleMode()) {
            $this->setMultipleModePriorityFilter();
        } else {
            $this->setSingleModePriorityFilter();
        }

        return $this;
    }
}
