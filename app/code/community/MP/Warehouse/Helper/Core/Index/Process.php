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
class MP_Warehouse_Helper_Core_Index_Process
    extends MP_Warehouse_Helper_Core_Abstract
{
    /**
     * Get product price process 
     * 
     * @return Mage_Index_Model_Process
     */
    protected function getProductPrice()
    {
        return Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_price');
    }
    /**
     * Get product flat process 
     * 
     * @return Mage_Index_Model_Process
     */
    protected function getProductFlat()
    {
        return Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_flat');
    }
    /**
     * Get stock process 
     * 
     * @return Mage_Index_Model_Process
     */
    protected function getStock()
    {
        return Mage::getSingleton('index/indexer')->getProcessByCode('cataloginventory_stock');
    }
    /**
     * Get search process 
     * 
     * @return Mage_Index_Model_Process
     */
    protected function getSearch()
    {
        return Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext');
    }
    /**
     * Reindex product price
     * 
     * @return MP_Warehouse_Helper_Core_Index_Process
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
     * Reindex product flat
     * 
     * @return MP_Warehouse_Helper_Core_Index_Process
     */
    public function reindexProductFlat()
    {
        $process = $this->getProductFlat();
        if ($process) {
            $process->reindexAll();
        }

        return $this;
    }
    /**
     * Reindex stock
     * 
     * @return MP_Warehouse_Helper_Core_Index_Process
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
     * Reindex search
     * 
     * @return MP_Warehouse_Helper_Core_Index_Process
     */
    public function reindexSearch()
    {
        $process = $this->getSearch();
        if ($process) {
            $process->reindexAll();
        }

        return $this;
    }
    /**
     * Change product price process status
     * 
     * @param int $status
     * 
     * @return MP_Warehouse_Helper_Core_Index_Process
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
     * Change product flat process status
     * 
     * @param int $status
     * 
     * @return MP_Warehouse_Helper_Core_Index_Process
     */
    public function changeProductFlatStatus($status)
    {
        $process = $this->getProductFlat();
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
     * @return MP_Warehouse_Helper_Core_Index_Process
     */
    public function changeStockStatus($status)
    {
        $process = $this->getStock();
        if ($process) {
            $process->changeStatus($status);
        }

        return $this;
    }
    /**
     * Change search process status
     * 
     * @param int $status
     * 
     * @return MP_Warehouse_Helper_Core_Index_Process
     */
    public function changeSearchStatus($status)
    {
        $process = $this->getSearch();
        if ($process) {
            $process->changeStatus($status);
        }

        return $this;
    }
}
