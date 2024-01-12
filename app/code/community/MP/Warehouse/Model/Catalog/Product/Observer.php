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
 * Product observer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Catalog_Product_Observer
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
     * Get product helper
     * 
     * @return MP_Warehouse_Helper_Catalog_Product
     */
    protected function getProductHelper()
    {
        return $this->getWarehouseHelper()->getProductHelper();
    }
    /**
     * Get request
     * 
     * @return Mage_Core_Controller_Request_Http
     */
    protected function getRequest() 
    {
        return Mage::app()->getRequest();
    }
    /**
     * Get edit tab html
     * 
     * @param Mage_Core_Model_Layout $layout
     * @param string $tabId
     * 
     * @return string
     */
    protected function getEditTabHtml($layout, $tabId)
    {
        return $layout->createBlock('warehouse/adminhtml_catalog_product_edit_tab_'.$tabId)->toHtml();
    }
    /**
     * Add tabs
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Catalog_Product_Observer
     */
    public function addTabs(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs) {
            $request = $this->getRequest();
            if (($request->getActionName() == 'edit') || ($request->getParam('type'))) {
                $helper     = $this->getWarehouseHelper();
                $config     = $helper->getConfig();
                $tabsIds    = $block->getTabsIds();
                $layout     = $block->getLayout();
                $after = (
                    (array_search('inventory', $tabsIds) !== false) && (array_search('inventory', $tabsIds) > 0)
                ) ? $tabsIds[array_search('inventory', $tabsIds) - 1] : 'categories';
                $block->removeTab('inventory');
                $block->addTab(
                    'inventory', array(
                    'after'     => $after, 
                    'label'     => $helper->__('Inventory'), 
                    'content'   => $this->getEditTabHtml($layout, 'inventory'), 
                    )
                );
                if ($config->isShelvesEnabled()) {
                    $block->addTab(
                        'shelf', array(
                        'after'     => $after, 
                        'label'     => $helper->__('Shelves'), 
                        'content'   => $this->getEditTabHtml($layout, 'shelf'), 
                        )
                    );
                }

                if ($config->isPriorityEnabled()) {
                    $block->addTab(
                        'priority', array(
                        'after'     => $after, 
                        'label'     => $helper->__('Priority'), 
                        'content'   => $this->getEditTabHtml($layout, 'priority'), 
                        )
                    );
                }

                if ($config->isShippingCarrierFilterEnabled()) {
                    $block->addTab(
                        'shipping', array(
                        'after'     => $after, 
                        'label'     => $helper->__('Shipping Carriers'), 
                        'content'   => $this->getEditTabHtml($layout, 'shipping'), 
                        )
                    );
                }
            }
        }

        return $this;
    }
    /**
     * Add stock item
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Catalog_Product_Observer
     */
    public function addStockItem($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if (!($product instanceof Mage_Catalog_Model_Product)) {
            return $this;
        }

        $helper     = $this->getWarehouseHelper();
        $config     = $helper->getConfig();
        if (!$config->isMultipleMode()) {
            return $this;
        }

        $stockId    = $helper->getProductHelper()
            ->getStockId($product);
        if ($stockId) {
            $stockItem  = $helper->getCatalogInventoryHelper()
                ->getStockItemCached(intval($product->getId()), $stockId);
            $stockItem->assignProduct($product);
        }

        return $this;
    }
    /**
     * Save stock shelves
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Catalog_Product_Observer
     */
    public function saveStockShelves(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()->saveStockShelves($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Load stock shelves
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Catalog_Product_Observer
     */
    public function loadStockShelves(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()->loadStockShelves($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Remove stock shelves
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Catalog_Product_Observer
     */
    public function removeStockShelves(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()->removeStockShelves($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Save stock shipping carriers
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Catalog_Product_Observer
     */
    public function saveStockShippingCarriers(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()->saveStockShippingCarriers($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Load stock shipping carriers
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Catalog_Product_Observer
     */
    public function loadStockShippingCarriers(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()->loadStockShippingCarriers($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Load collection stock shipping carriers
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Catalog_Product_Observer
     */
    public function loadCollectionStockShippingCarriers(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()->loadCollectionStockShippingCarriers($observer->getEvent()->getCollection());
        return $this;
    }
    /**
     * Remove stock shipping carriers
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Catalog_Product_Observer
     */
    public function removeStockShippingCarriers(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()->removeStockShippingCarriers($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Save stock priorities
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Catalog_Product_Observer
     */
    public function saveStockPriorities(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()->saveStockPriorities($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Load stock priorities
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Catalog_Product_Observer
     */
    public function loadStockPriorities(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()->loadStockPriorities($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Load collection stock priorities
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Catalog_Product_Observer
     */
    public function loadCollectionStockPriorities(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()->loadCollectionStockPriorities($observer->getEvent()->getCollection());
        return $this;
    }
    /**
     * Remove stock priorities
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return MP_Warehouse_Model_Catalog_Product_Observer
     */
    public function removeStockPriorities(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()->removeStockPriorities($observer->getEvent()->getProduct());
        return $this;
    }

    /**
     * Save stock tax class ids
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function saveStockTaxClassIds(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()
            ->saveStockTaxClassIds($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Load stock tax class ids
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function loadStockTaxClassIds(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()
            ->loadStockTaxClassIds($observer->getEvent()->getProduct());
        return $this;
    }
    /**
     * Load collection stock tax class ids
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function loadCollectionStockTaxClassIds(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()
            ->loadCollectionStockTaxClassIds($observer->getEvent()->getCollection());
        return $this;
    }
    /**
     * Remove stock tax class ids
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return self
     */
    public function removeStockTaxClassIds(Varien_Event_Observer $observer)
    {
        $this->getProductHelper()
            ->removeStockTaxClassIds($observer->getEvent()->getProduct());
        return $this;
    }
    
}
