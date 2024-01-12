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
 * Warehouse observer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Observer
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
     * Add system config js
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function addSystemConfigJs(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (!$block || !($block instanceof Mage_Adminhtml_Block_System_Config_Edit)) {
            return $this;
        }

        $layout = $block->getLayout();
        if (!$layout) {
            return $this;
        }

        $layout->getBlock('js')->append(
            $layout->createBlock('adminhtml/template')->setTemplate('warehouse/system/config/js.phtml')
        );
        return $this;
    }
    /**
     * Set coordinates
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function setCoordinates(Varien_Event_Observer $observer)
    {
        $helper = $this->getWarehouseHelper();
        $config = $helper->getConfig();
        if (!$config->isNearestSingleAssignmentMethod() && !$config->isNearestMultipleAssignmentMethod()) {
            return $this;
        }

        $warehouse = $observer->getEvent()->getWarehouse();
        if (!$warehouse || !($warehouse instanceof MP_Warehouse_Model_Warehouse)) {
            return $this;
        }

        $helper->setWarehouseCoordinates($warehouse);
        return $this;
    }
    /**
     * Save stores
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function saveStores(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->saveStores($observer->getEvent()->getWarehouse());
        return $this;
    }
    /**
     * Add data stores
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function addDataStores(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->addDataStores(
            $observer->getEvent()->getWarehouse(), 
            $observer->getEvent()->getArray()
        );
        return $this;
    }
    /**
     * Load stores
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function loadStores(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->loadStores($observer->getEvent()->getWarehouse());
        return $this;
    }
    /**
     * Load collection stores
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function loadCollectionStores(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->loadCollectionStores($observer->getEvent()->getCollection());
        return $this;
    }
    /**
     * Remove stores
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function removeStores(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->removeStores($observer->getEvent()->getWarehouse());
        return $this;
    }
    /**
     * Save customer groups
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function saveCustomerGroups(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->saveCustomerGroups($observer->getEvent()->getWarehouse());
        return $this;
    }
    /**
     * Add data customer groups
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function addDataCustomerGroups(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->addDataCustomerGroups(
            $observer->getEvent()->getWarehouse(), 
            $observer->getEvent()->getArray()
        );
        return $this;
    }
    /**
     * Load customer groups
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function loadCustomerGroups(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->loadCustomerGroups($observer->getEvent()->getWarehouse());
        return $this;
    }
    /**
     * Load collection customer groups
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function loadCollectionCustomerGroups(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->loadCollectionCustomerGroups($observer->getEvent()->getCollection());
        return $this;
    }
    /**
     * Remove customer groups
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function removeCustomerGroups(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->removeCustomerGroups($observer->getEvent()->getWarehouse());
        return $this;
    }
    /**
     * Save currencies
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function saveCurrencies(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->saveCurrencies($observer->getEvent()->getWarehouse());
        return $this;
    }
    /**
     * Add data currencies
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function addDataCurrencies(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->addDataCurrencies(
            $observer->getEvent()->getWarehouse(), 
            $observer->getEvent()->getArray()
        );
        return $this;
    }
    /**
     * Load currencies
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function loadCurrencies(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->loadCurrencies($observer->getEvent()->getWarehouse());
        return $this;
    }
    /**
     * Load collection currencies
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function loadCollectionCurrencies(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->loadCollectionCurrencies($observer->getEvent()->getCollection());
        return $this;
    }
    /**
     * Remove currencies
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function removeCurrencies(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->removeCurrencies($observer->getEvent()->getWarehouse());
        return $this;
    }
    /**
     * Save shipping carriers
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function saveShippingCarriers(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->saveShippingCarriers($observer->getEvent()->getWarehouse());
        return $this;
    }
    /**
     * Add data shipping carriers
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function addDataShippingCarriers(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->addDataShippingCarriers(
            $observer->getEvent()->getWarehouse(), 
            $observer->getEvent()->getArray()
        );
        return $this;
    }
    /**
     * Load shipping carriers
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function loadShippingCarriers(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->loadShippingCarriers($observer->getEvent()->getWarehouse());
        return $this;
    }
    /**
     * Load collection shipping carriers
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function loadCollectionShippingCarriers(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->loadCollectionShippingCarriers($observer->getEvent()->getCollection());
        return $this;
    }
    /**
     * Remove shipping carriers
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Observer
     */
    public function removeShippingCarriers(Varien_Event_Observer $observer)
    {
        $this->getWarehouseHelper()->removeShippingCarriers($observer->getEvent()->getWarehouse());
        return $this;
    }
}
