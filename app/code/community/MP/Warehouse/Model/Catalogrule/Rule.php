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
 * Catalog rule
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Catalogrule_Rule extends Mage_CatalogRule_Model_Rule
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
     * Get version helper
     *
     * @return MP_Warehouse_Helper_Core_Version
     */
    protected function getVersionHelper()
    {
        return $this->getWarehouseHelper()->getVersionHelper();
    }

    /**
     * Process rule related data after rule save
     *
     * @return $this
     */
    protected function _afterSave()
    {
        if ($this->getVersionHelper()->isGe1700()) {
            parent::_afterSave();
        } else {
            Mage_Core_Model_Abstract::_afterSave();

            $this->_getResource()->updateRuleProductData($this);
        }

        return $this;
    }
    /**
     * Apply rule to product
     *
     * @param int|Mage_Catalog_Model_Product $product
     * @param array|null $websiteIds
     * @return void
     */
    public function applyToProduct2($product, $websiteIds = null)
    {
        if (is_numeric($product)) {
            $product = Mage::getModel('catalog/product')->load($product);
        }

        if (is_null($websiteIds)) {
            if ($this->getVersionHelper()->isGe1700()) {
                $websiteIds = $this->getWebsiteIds();
            } else {
                $websiteIds = explode(',', $this->getWebsiteIds());
            }
        }

        /** @var MP_Warehouse_Model_Mysql4_Catalogrule_Rule $resource */
        $resource = $this->getResource();
        $resource->applyToProduct2($this, $product, $websiteIds);
    }

    /**
     * Apply all price rules to product
     *
     * @param int|Mage_Catalog_Model_Product $product
     * @return $this
     */
    public function applyAllRulesToProduct($product)
    {
        if ($this->getVersionHelper()->isGe1800()) {
            if (is_numeric($product)) {
                $product = Mage::getModel('catalog/product')->load($product);
            }

            $productWebsiteIds = $product->getWebsiteIds();

            /** @var Mage_CatalogRule_Model_Resource_Rule_Collection $rules */
            $rules = Mage::getModel('catalogrule/rule')
                ->getCollection()
                ->addFieldToFilter('is_active', 1);

            /** @var MP_Warehouse_Model_Mysql4_Catalogrule_Rule $resource */
            $resource = $this->getResource();

            foreach ($rules as $rule) {
                $websiteIds = array_intersect($productWebsiteIds, $rule->getWebsiteIds());
                $resource->applyToProduct2($rule, $product, $websiteIds);
            }

            $this->getResource()->applyAllRules($product);
            $this->_invalidateCache();

            Mage::getSingleton('index/indexer')->processEntityAction(
                new Varien_Object(array('id' => $product->getId())),
                Mage_Catalog_Model_Product::ENTITY,
                Mage_Catalog_Model_Product_Indexer_Price::EVENT_TYPE_REINDEX_PRICE
            );

            return $this;
        }

        parent::applyAllRulesToProduct($product);

        return $this;
    }

    /**
     * Calculate price using catalog price rule of product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param float $price
     * @return float|null
     */
    public function calcProductPriceRule(Mage_Catalog_Model_Product $product, $price)
    {
        $helper        = $this->getWarehouseHelper();
        $productHelper = $helper->getProductHelper();
        $priceRules    = null;
        $productId     = (int) $product->getId();
        $storeId       = (int) $product->getStoreId();
        $websiteId     = $helper->getCoreHelper()->getWebsiteIdByStoreId($storeId);

        if ($product->hasCustomerGroupId()) {
            $customerGroupId = $product->getCustomerGroupId();
        } else {
            $customerGroupId = $helper->getCoreHelper()->getCustomerHelper()->getCustomerGroupId();
        }

        if ($product->hasStockId()) {
            $stockId = $product->getStockId();
        } else {
            $stockId = $productHelper->getStockId($product);
        }

        $dateTs   = Mage::app()->getLocale()->storeTimeStamp($storeId);
        $cacheKey = date('Y-m-d', $dateTs) .
            implode(
                '|', array(
                    $websiteId,
                    $customerGroupId,
                    $stockId,
                    $productId,
                    $price
                )
            );

        /** @var MP_Warehouse_Model_Mysql4_Catalogrule_Rule $resource */
        $resource = $this->_getResource();

        if (!array_key_exists($cacheKey, self::$_priceRulesData)) {
            $rulesData = $resource->getRulesFromProduct2(
                $dateTs,
                $websiteId,
                $customerGroupId,
                $stockId,
                $productId
            );

            if ($rulesData) {
                foreach ($rulesData as $ruleData) {
                    if ($this->getVersionHelper()->isGe1610() && $product->getParentId()) {
                        if (($this->getVersionHelper()->isGe1700() && !empty($ruleData['sub_simple_action'])) ||
                            (!$this->getVersionHelper()->isGe1700() && $ruleData['sub_is_enable'])
                        ) {
                            $priceRules = Mage::helper('catalogrule')->calcPriceRule(
                                $ruleData['sub_simple_action'],
                                $ruleData['sub_discount_amount'],
                                $priceRules ? $priceRules : $price
                            );
                        } else {
                            $priceRules = $price;
                        }

                        if (($this->getVersionHelper()->isGe1700() && $ruleData['action_stop']) ||
                            (!$this->getVersionHelper()->isGe1700() && $ruleData['stop_rules_processing'])
                        ) {
                            break;
                        }
                    } else {
                        if ($this->getVersionHelper()->isGe1700()) {
                            $priceRules = Mage::helper('catalogrule')->calcPriceRule(
                                $ruleData['action_operator'],
                                $ruleData['action_amount'],
                                $priceRules ? $priceRules : $price
                            );
                        } else {
                            $priceRules = Mage::helper('catalogrule')->calcPriceRule(
                                $ruleData['simple_action'],
                                $ruleData['discount_amount'],
                                $priceRules ? $priceRules :$price
                            );
                        }

                        if (($this->getVersionHelper()->isGe1700() && $ruleData['action_stop']) ||
                            (!$this->getVersionHelper()->isGe1700() && $ruleData['stop_rules_processing'])
                        ) {
                            break;
                        }
                    }
                }

                return self::$_priceRulesData[$cacheKey] = $priceRules;
            } else {
                self::$_priceRulesData[$cacheKey] = null;
            }
        } else {
            return self::$_priceRulesData[$cacheKey];
        }

        return null;
    }
}
