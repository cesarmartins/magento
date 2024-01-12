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
 * Order helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Sales_Shipment extends Mage_Core_Helper_Abstract
{
    /**
     * Get all warehouses options and validate selected option
     *
     * @param  null|int $warehouseId
     * @return array
     */
    public function getWarehousesOptions($warehouseId = null)
    {
        $warehouses = Mage::getModel('warehouse/warehouse')->getCollection();
        $options    = array();

        foreach ($warehouses as $warehouse) {
            $selected = false;

            if ($warehouseId == $warehouse->getId()) {
                $selected = true;
            }

            $options[$warehouse->getId()] = array(
                'name' => $warehouse->getTitle(),
                'selected' => $selected
            );
        }

        return $options;
    }
}
