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
 * Shipping table rate method collection
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_ShippingTablerate_Tablerate_Method_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Constructor
     */
    protected function _construct() 
    {
        $this->_init('warehouse/shippingTablerate_tablerate_method');
    }
    /**
     * Get options array
     * 
     * @param string $valueField
     * 
     * @return array
     */
    public function toOptionArray($valueField = 'method_id')
    {
        return $this->_toOptionArray($valueField, 'name');
    }
    /**
     * Get options hash array
     * 
     * @param string $valueField
     * 
     * @return array
     */
    public function toOptionHash($valueField = 'method_id')
    {
        return $this->_toOptionHash($valueField, 'name');
    }
}
