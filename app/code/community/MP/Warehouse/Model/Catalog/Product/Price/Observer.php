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
 * Product price observer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Catalog_Product_Price_Observer
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
     * Batch Price
     */

    /**
     * Save batch prices
     *
     * @param Varien_Event_Observer $observer
     *
     * @return MP_Warehouse_Model_Catalog_Product_Price_Observer
     */
    public function saveBatchPrices(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()
            ->getProductPriceHelper()
            ->saveBatchPrices($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Load batch prices
     *
     * @param Varien_Event_Observer $observer
     *
     * @return MP_Warehouse_Model_Catalog_Product_Price_Observer
     */
    public function loadBatchPrices(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()
            ->getProductPriceHelper()
            ->loadBatchPrices($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Load collection batch prices
     *
     * @param Varien_Event_Observer $observer
     *
     * @return MP_Warehouse_Model_Catalog_Product_Price_Observer
     */
    public function loadCollectionBatchPrices(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()
            ->getProductPriceHelper()
            ->loadCollectionBatchPrices($observer->getEvent()->getCollection());
        return $this;
    }
    /**
     * Remove batch prices
     *
     * @param Varien_Event_Observer $observer
     *
     * @return MP_Warehouse_Model_Catalog_Product_Price_Observer
     */
    public function removeBatchPrices(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()
            ->getProductPriceHelper()
            ->removeBatchPrices($observer->getEvent()->getProduct());
        return $this;
    }

    /**
     * Batch Special Price
     */

    /**
     * Save batch special prices
     *
     * @param Varien_Event_Observer $observer
     *
     * @return MP_Warehouse_Model_Catalog_Product_Price_Observer
     */
    public function saveBatchSpecialPrices(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()
            ->getProductPriceHelper()
            ->saveBatchSpecialPrices($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Load batch special prices
     *
     * @param Varien_Event_Observer $observer
     *
     * @return MP_Warehouse_Model_Catalog_Product_Price_Observer
     */
    public function loadBatchSpecialPrices(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()
            ->getProductPriceHelper()
            ->loadBatchSpecialPrices($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Load collection batch special prices
     *
     * @param Varien_Event_Observer $observer
     *
     * @return MP_Warehouse_Model_Catalog_Product_Price_Observer
     */
    public function loadCollectionBatchSpecialPrices(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()
            ->getProductPriceHelper()
            ->loadCollectionBatchSpecialPrices($observer->getEvent()->getCollection());
        return $this;
    }
    /**
     * Remove batch special prices
     *
     * @param Varien_Event_Observer $observer
     *
     * @return MP_Warehouse_Model_Catalog_Product_Price_Observer
     */
    public function removeBatchSpecialPrices(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()
            ->getProductPriceHelper()
            ->removeBatchSpecialPrices($observer->getEvent()->getProduct());
        return $this;
    }

    /**
     * Before collection load
     *
     * @param Varien_Event_Observer $observer
     *
     * @return MP_Warehouse_Model_Observer
     */
    public function beforeCollectionLoad(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()
            ->getProductPriceIndexerHelper()
            ->addPriceIndexFilter($observer->getEvent()->getCollection());
        return $this;
    }
    /**
     * After collection apply limitations
     *
     * @param Varien_Event_Observer $observer
     *
     * @return MP_Warehouse_Model_Observer
     */
    public function afterCollectionApplyLimitations(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()
            ->getProductPriceIndexerHelper()
            ->addPriceIndexFilter($observer->getEvent()->getCollection());
        return $this;
    }
}
