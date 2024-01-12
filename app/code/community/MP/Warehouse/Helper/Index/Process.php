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
 * Process helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Index_Process 
    extends Mage_Core_Helper_Abstract
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
     * Get product attribute process
     * 
     * @return Mage_Index_Model_Process
     */
    protected function getProductAttribute()
    {
        return Mage::getSingleton('index/indexer')
            ->getProcessByCode('catalog_product_attribute');
    }
    /**
     * Get product price process
     * 
     * @return Mage_Index_Model_Process
     */
    protected function getProductPrice()
    {
        return Mage::getSingleton('index/indexer')
            ->getProcessByCode('catalog_product_price');
    }
    /**
     * Get stock process 
     * 
     * @return Mage_Index_Model_Process
     */
    protected function getStock()
    {
        return Mage::getSingleton('index/indexer')
            ->getProcessByCode('cataloginventory_stock');
    }
    /**
     * Reindex product attribute
     * 
     * @return MP_Warehouse_Helper_Index_Process
     */
    public function reindexProductAttribute()
    {
        $process = $this->getProductAttribute();
        if ($process) {
            $process->reindexAll();
        }

        return $this;
    }
    /**
     * Reindex product price
     * 
     * @return MP_Warehouse_Helper_Index_Process
     */
    public function reindexProductPrice()
    {
        $process = $this->getProductPrice();
        if ($process) {
            $process->reindexAll();
        }

        return $this;
    }
    /**
     * Reindex stock
     * 
     * @return MP_Warehouse_Helper_Index_Process
     */
    public function reindexStock()
    {
        $process = $this->getStock();
        if ($process) {
            $process->reindexAll();
        }

        return $this;
    }
    /**
     * Change product attribute process status
     * 
     * @param int $status
     * 
     * @return MP_Warehouse_Helper_Index_Process
     */
    public function changeProductAttributeStatus($status)
    {
        $process = $this->getProductAttribute();
        if ($process) {
            $process->changeStatus($status);
        }

        return $this;
    }
    /**
     * Change product price process status
     * 
     * @param int $status
     * 
     * @return MP_Warehouse_Helper_Index_Process
     */
    public function changeProductPriceStatus($status)
    {
        $process = $this->getProductPrice();
        if ($process) {
            $process->changeStatus($status);
        }

        return $this;
    }
    /**
     * Change stock process status
     * 
     * @param int $status
     * 
     * @return MP_Warehouse_Helper_Index_Process
     */
    public function changeStockStatus($status)
    {
        $process = $this->getStock();
        if ($process) {
            $process->changeStatus($status);
        }

        return $this;
    }
}
