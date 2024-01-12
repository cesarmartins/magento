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
 * Catalog rule observer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Catalogrule_Observer extends Mage_CatalogRule_Model_Observer
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
     * Get config
     *
     * @return MP_Warehouse_Model_Config
     */
    protected function getConfig()
    {
        return $this->getWarehouseHelper()->getConfig();
    }

    /**
     * Get catalog rule helper
     *
     * @return MP_Warehouse_Helper_Catalogrule_Rule
     */
    protected function getCatalogRuleHelper()
    {
        return $this->getWarehouseHelper()->getCatalogRuleHelper();
    }

    /**
     * Get version helper
     *
     * @return MP_Warehouse_Helper_Core_Version
     */
    protected function getVersionHelper()
    {
        return $this->getWarehouseHelper()->getVersionHelper();
    }

    /**
     * Apply all catalog price rules for specific product
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function applyAllRulesOnProduct($observer)
    {
        $product = $observer->getEvent()->getProduct();

        if (!$product || $product->getIsMassupdate()) {
            return $this;
        }

        if (!$this->getVersionHelper()->isGe1800()) {
            $productWebsiteIds = $product->getWebsiteIds();

            /** @var Mage_SalesRule_Model_Resource_Rule_Collection $rules */
            $rules = Mage::getModel('catalogrule/rule')
                ->getCollection()
                ->addFieldToFilter('is_active', 1);

            /** @var MP_Warehouse_Model_Catalogrule_Rule $rule */
            foreach ($rules as $rule) {
                if ($this->getVersionHelper()->isGe1700()) {
                    $websiteIds = array_intersect($productWebsiteIds, (array) $rule->getWebsiteIds());
                } else {
                    if (!is_array($rule->getWebsiteIds())) {
                        $ruleWebsiteIds = (array) explode(',', $rule->getWebsiteIds());
                    } else {
                        $ruleWebsiteIds = $rule->getWebsiteIds();
                    }

                    $websiteIds = array_intersect($productWebsiteIds, $ruleWebsiteIds);
                }

                $rule->applyToProduct2($product, $websiteIds);
            }
        } else {
            Mage::getModel('catalogrule/rule')->applyAllRulesToProduct($product);
        }

        return $this;
    }

    /**
     * Apply catalog price rules to product on frontend
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function processFrontFinalPrice($observer)
    {
        $helper        = $this->getWarehouseHelper();
        $productHelper = $helper->getProductHelper();
        $event         = $observer->getEvent();
        $product       = $event->getProduct();
        $productId     = (int) $product->getId();
        $storeId       = (int) $product->getStoreId();

        if ($event->hasDate()) {
            $date = $event->getDate();
        } else {
            $date = $helper->getCoreHelper()->getLocaleHelper()->storeTimeStamp($storeId);
        }

        if ($event->hasWebsiteId()) {
            $websiteId = $event->getWebsiteId();
        } else {
            $websiteId = $helper->getCoreHelper()->getWebsiteIdByStoreId($storeId);
        }

        if ($event->hasCustomerGroupId()) {
            $customerGroupId = $event->getCustomerGroupId();
        } elseif ($product->hasCustomerGroupId()) {
            $customerGroupId = $product->getCustomerGroupId();
        } else {
            $customerGroupId = $helper->getCoreHelper()->getCustomerHelper()->getCustomerGroupId();
        }

        if ($event->hasStockId()) {
            $stockId = $event->getStockId();
        } elseif ($product->hasStockId()) {
            $stockId = $product->getStockId();
        } else {
            $stockId = $productHelper->getCurrentStockId($product);
        }

        $key = implode('|', array($date, $websiteId, $customerGroupId, $stockId, $productId));

        if (!isset($this->_rulePrices[$key])) {
            /** @var MP_Warehouse_Model_Mysql4_Catalogrule_Rule $resource */
            $resource  = Mage::getResourceModel('catalogrule/rule');
            $rulePrice = $resource->getRulePrice2($date, $websiteId, $customerGroupId, $stockId, $productId);

            $this->_rulePrices[$key] = $rulePrice;
        }

        if ($this->_rulePrices[$key] !== false) {
            $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
            $product->setFinalPrice($finalPrice);
        }

        return $this;
    }

    /**
     * Apply catalog price rules to product in admin
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function processAdminFinalPrice($observer)
    {
        $helper        = $this->getWarehouseHelper();
        $productHelper = $helper->getProductHelper();
        $product       = $observer->getEvent()->getProduct();
        $storeId       = (int) $product->getStoreId();
        $date          = $helper->getCoreHelper()->getLocaleHelper()->storeDate($storeId);
        $key           = false;
        $ruleData      = Mage::registry('rule_data');

        if ($ruleData) {
            $websiteId       = $ruleData->getWebsiteId();
            $customerGroupId = $ruleData->getCustomerGroupId();
            $stockId         = $productHelper->getStockId($product);
            $productId       = (int) $product->getId();
            $key             = implode('|', array($date, $websiteId, $customerGroupId, $stockId, $productId));
        } elseif (!is_null($product->getWebsiteId()) &&
            !is_null($product->getCustomerGroupId())
        ) {
            $websiteId       = $product->getWebsiteId();
            $customerGroupId = $product->getCustomerGroupId();
            $stockId         = ($product->getStockId())
                ? $product->getStockId()
                : $productHelper->getStockId($product);
            $productId       = (int) $product->getId();
            $key             = implode('|', array($date, $websiteId, $customerGroupId, $stockId, $productId));
        }

        if ($key) {
            if (!isset($this->_rulePrices[$key])) {
                /** @var MP_Warehouse_Model_Mysql4_Catalogrule_Rule $resource */
                $resource  = Mage::getResourceModel('catalogrule/rule');
                $rulePrice = $resource
                    ->getRulePrice2($date, $websiteId, $customerGroupId, $stockId, $productId);

                $this->_rulePrices[$key] = $rulePrice;
            }

            if ($this->_rulePrices[$key] !== false) {
                $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
                $product->setFinalPrice($finalPrice);
            }
        }

        return $this;
    }

    /**
     * Calculate minimal final price with catalog rule price
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function prepareCatalogProductPriceIndexTable(Varien_Event_Observer $observer)
    {
        $event           = $observer->getEvent();
        $select          = $event->getSelect();
        $indexTable      = $event->getIndexTable();
        $entityId        = $event->getEntityId();
        $customerGroupId = $event->getCustomerGroupId();
        $websiteId       = $event->getWebsiteId();
        $stockId         = $event->getStockId();
        $websiteDate     = $event->getWebsiteDate();
        $updateFields    = $event->getUpdateFields();

        if ($entityId && $customerGroupId && $websiteId && $stockId && $websiteDate) {
            Mage::getSingleton('warehouse/catalogrule_rule_product_price')->applyPriceRuleToIndexTable2(
                $select,
                $indexTable,
                $entityId,
                $customerGroupId,
                $stockId,
                $websiteId,
                $updateFields,
                $websiteDate
            );
        }

        return $this;
    }

    /**
     * Prepare catalog product collection prices
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function prepareCatalogProductCollectionPrices(Varien_Event_Observer $observer)
    {
        $helper        = $this->getWarehouseHelper();
        $productHelper = $helper->getProductHelper();
        $event         = $observer->getEvent();
        $collection    = $event->getCollection();
        $store         = $helper->getCoreHelper()->getStoreById($event->getStoreId());
        $websiteId     = $store->getWebsiteId();

        if ($event->hasCustomerGroupId()) {
            $customerGroupId = $event->getCustomerGroupId();
        } else {
            $customerGroupId = $helper->getCoreHelper()->getCustomerHelper()->getCustomerGroupId();
        }

        if ($event->hasStockId()) {
            $stockId = $event->getStockId();
        } else {
            $stockId = $productHelper->getCollectionStockId($collection);
        }

        if ($event->hasDate()) {
            $date = $event->getDate();
        } else {
            $date = $helper->getCoreHelper()->getLocaleHelper()->storeTimeStamp($store);
        }

        $productIds = array();

        foreach ($collection as $product) {
            $key = implode(
                '|',
                array(
                    $date,
                    $websiteId,
                    $customerGroupId,
                    $stockId,
                    $product->getId()
                )
            );

            if (!isset($this->_rulePrices[$key])) {
                $productIds[] = $product->getId();
            }
        }

        if ($productIds) {
            $rulePrices = Mage::getResourceModel('catalogrule/rule')->getRulePrices2(
                $date,
                $websiteId,
                $customerGroupId,
                $stockId,
                $productIds
            );

            foreach ($productIds as $productId) {
                $key = implode(
                    '|',
                    array(
                        $date,
                        $websiteId,
                        $customerGroupId,
                        $stockId,
                        $productId
                    )
                );

                $this->_rulePrices[$key] = isset($rulePrices[$productId]) ? $rulePrices[$productId] : false;
            }
        }

        return $this;
    }
}
