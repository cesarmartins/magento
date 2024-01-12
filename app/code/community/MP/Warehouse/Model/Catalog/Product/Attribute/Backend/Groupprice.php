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
 * Product group price backend attribute
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Catalog_Product_Attribute_Backend_Groupprice
    extends Mage_Catalog_Model_Product_Attribute_Backend_Groupprice
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
     * Validate data
     *
     * @param array $data
     * @param int $websiteId
     * @param bool $filterEmpty
     * @param bool $filterInactive
     * @param bool $filterAncestors
     *
     * @return bool
     */
    protected function validateData($data, $websiteId, $filterEmpty = true, $filterInactive = true, $filterAncestors = true)
    {
        $helper         = $this->getWarehouseHelper();
        $priceHelper    = $helper->getProductPriceHelper();
        if ($filterEmpty) {
            if (!isset($data['cust_group']) || !empty($data['delete'])) {
                return false;
            }
        }

        if ($filterInactive) {
            if ($priceHelper->isInactiveData($data, $websiteId)) {
                return false;
            }
        }

        if ($filterAncestors) {
            if ($priceHelper->isAncestorData($data, $websiteId)) {
                return false;
            }
        }

        return true;
    }
    /**
     * Get data key
     *
     * @param array $data
     * @param bool $allWebsites
     *
     * @return string
     */
    protected function getDataKey($data, $allWebsites = false)
    {
        return join(
            '-', array(
            (($allWebsites) ? 0 : $data['website_id']),
            ($data['stock_id']) ? $data['stock_id'] : 0,
            $data['cust_group']
            )
        );
    }
    /**
     * Get short data key
     *
     * @param array $data
     *
     * @return string
     */
    protected function getShortDataKey($data)
    {
        return join(
            '-', array(
            ($data['stock_id']) ? $data['stock_id'] : 0,
            $data['cust_group']
            )
        );
    }
    /**
     * Validate tier price data
     *
     * @param Mage_Catalog_Model_Product $object
     * @throws Mage_Core_Exception
     *
     * @return bool
     */
    public function validate($object)
    {
        $helper             = $this->getWarehouseHelper();
        $priceHelper        = $helper->getProductPriceHelper();
        $attribute          = $this->getAttribute();
        $attributeName      = $attribute->getName();
        $tiers = $object->getData($attributeName);
        if (empty($tiers)) {
            return true;
        }

        $duplicateMessage = $helper->__('Duplicate website group price warehouse, customer group and quantity.');
        $duplicates = array();
        foreach ($tiers as $tier) {
            if (!empty($tier['delete'])) {
                continue;
            }

            $compare = $this->getDataKey($tier);
            if (isset($duplicates[$compare])) {
                Mage::throwException($duplicateMessage);
            }

            $duplicates[$compare] = true;
        }

        if ($priceHelper->isWebsiteScope() && $object->getWebsiteId()) {
            $websiteId = $object->getWebsiteId();
            $origTierPrices = $object->getOrigData($attributeName);
            foreach ($origTierPrices as $tier) {
                if ($priceHelper->isAncestorData($tier, $websiteId)) {
                    $compare = $this->getDataKey($tier);
                    $duplicates[$compare] = true;
                }
            }
        }

        $baseCurrency = Mage::app()->getBaseCurrencyCode();
        $rates = $this->_getWebsiteRates();
        foreach ($tiers as $tier) {
            if (!empty($tier['delete'])) {
                continue;
            }

            if ($tier['website_id'] == 0) {
                continue;
            }

            $websiteCurrency = $rates[$tier['website_id']]['code'];
            $compare = $this->getDataKey($tier);
            $globalCompare = $this->getDataKey($tier, true);
            if ($baseCurrency == $websiteCurrency && isset($duplicates[$globalCompare])) {
                Mage::throwException($duplicateMessage);
            }
        }

        return true;
    }
    /**
     * Sort price data
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function _sortPriceData($a, $b)
    {
        if ($a['website_id'] != $b['website_id']) {
            return $a['website_id'] < $b['website_id'] ? 1 : -1;
        }

        if ($a['stock_id'] != $b['stock_id']) {
            return $a['stock_id'] < $b['stock_id'] ? 1 : -1;
        }

        return 0;
    }
    /**
     * Sort price data by quantity
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function _sortPriceDataByQty($a, $b)
    {
        if ($a['price_qty'] != $b['price_qty']) {
            return $a['price_qty'] < $b['price_qty'] ? -1 : 1;
        }

        return 0;
    }
    /**
     * Prepare tier prices data for website
     *
     * @param array $priceData
     * @param string $productTypeId
     * @param int $websiteId
     * @param int $stockId
     *
     * @return array
     */
    public function preparePriceData2(array $priceData, $productTypeId, $websiteId, $stockId)
    {
        $helper                 = $this->getWarehouseHelper();
        $data                   = array();
        $isGroupPriceFixed      = $helper->getProductPriceHelper()->isGroupPriceFixed($productTypeId);
        $rates                  = $this->_getWebsiteRates();
        usort($priceData, array($this, '_sortPriceData'));
        foreach ($priceData as $v) {
            $key = $this->getShortDataKey($v);
            if (!isset($data[$key]) && (
                    ( $v['website_id'] == $websiteId ) ||
                    ( $v['website_id'] == 0 )
                ) && (
                (
                    ($stockId && (($v['stock_id'] == $stockId) ||
                            (!$v['stock_id']))) ||
                    (!$stockId && !$v['stock_id'])
                )
                )
            ) {
                $data[$key] = $v;
                $data[$key]['website_id'] = $websiteId;
                if ($stockId) {
                    $data[$key]['stock_id'] = $stockId;
                }

                if ($isGroupPriceFixed && ($v['website_id'] == 0) && ($websiteId)) {
                    $data[$key]['price'] = $v['price'] * $rates[$websiteId]['rate'];
                    $data[$key]['website_price'] = $v['price'] * $rates[$websiteId]['rate'];
                }
            }
        }

        return $data;
    }
    /**
     * After load
     *
     * @param Mage_Catalog_Model_Product $object
     *
     * @return MP_Warehouse_Model_Catalog_Product_Attribute_Backend_Tierprice
     */
    public function afterLoad($object)
    {
        $helper             = $this->getWarehouseHelper();
        $productHelper      = $helper->getProductHelper();
        $priceHelper        = $helper->getProductPriceHelper();
        $resource           = $this->_getResource();
        $attribute          = $this->getAttribute();
        $attributeName      = $attribute->getName();
        $isEditMode         = $object->getData('_edit_mode');
        $storeId            = $object->getStoreId();
        $websiteId          = null;
        if ($priceHelper->isGlobalScope()) {
            $websiteId          = 0;
        } else if ($storeId) {
            $websiteId          = $helper->getWebsiteIdByStoreId($storeId);
        }

        if ($isEditMode) {
            $stockId            = null;
        } else {
            $stockId            = $productHelper->getCurrentStockId($object);
        }

        $data = $resource->loadPriceData2($object->getId(), $websiteId, $stockId);
        foreach ($data as $k => $v) {
            $data[$k]['website_price'] = $v['price'];
            $data[$k]['is_percent']    = isset($v['is_percent']) ? isset($v['is_percent']) : 0;
            if ($v['all_groups']) {
                $data[$k]['cust_group'] = Mage_Customer_Model_Group::CUST_GROUP_ALL;
            }
        }

        $object->setGroupPrices($data);
        $priceHelper->setGroupPrice($object);
        $object->setData($attributeName, $data);
        $object->setOrigData($attributeName, $data);
        $valueChangedKey = $attributeName.'_changed';
        $object->setOrigData($valueChangedKey, 0);
        $object->setData($valueChangedKey, 0);
        return $this;
    }
    /**
     * After save
     *
     * @param Mage_Catalog_Model_Product $object
     *
     * @return MP_Warehouse_Model_Catalog_Product_Attribute_Backend_Tierprice
     */
    public function afterSave($object)
    {
        $helper             = $this->getWarehouseHelper();
        $priceHelper        = $helper->getProductPriceHelper();
        $resource           = $this->_getResource();
        $objectId           = $object->getId();
        $storeId            = $object->getStoreId();
        $websiteId          = $helper->getWebsiteIdByStoreId($storeId);
        $attribute          = $this->getAttribute();
        $attributeName      = $attribute->getName();
        $tierPrices         = $object->getData($attributeName);
        if (empty($tierPrices)) {
            if ($priceHelper->isGlobalScope() || ($websiteId == 0)) {
                $resource->deletePriceData2($objectId);
            } else if ($priceHelper->isWebsiteScope()) {
                $resource->deletePriceData2($objectId, $websiteId);
            }

            return $this;
        }

        $old                = array();
        $new                = array();
        $origTierPrices     = $object->getOrigData($attributeName);
        if (!is_array($origTierPrices)) {
            $origTierPrices = array();
        }

        foreach ($origTierPrices as $data) {
            if (!$this->validateData($data, $websiteId, false, false, true)) {
                continue;
            }

            $key = $this->getDataKey($data);
            $old[$key] = $data;
        }

        foreach ($tierPrices as $data) {
            if (!$this->validateData($data, $websiteId, true, true, true)) {
                continue;
            }

            $key = $this->getDataKey($data);
            $useForAllGroups = $data['cust_group'] == Mage_Customer_Model_Group::CUST_GROUP_ALL;
            $customerGroupId = !$useForAllGroups ? $data['cust_group'] : 0;
            $new[$key] = array(
                'website_id'        => $data['website_id'],
                'stock_id'          => ($data['stock_id']) ? $data['stock_id'] : null,
                'all_groups'        => $useForAllGroups ? 1 : 0,
                'customer_group_id' => $customerGroupId,
                'value'             => $data['price'],
                'is_percent'        => isset($data['is_percent']) ? $data['is_percent'] : 0,
            );
        }

        $delete         = array_diff_key($old, $new);
        $insert         = array_diff_key($new, $old);
        $update         = array_intersect_key($new, $old);
        $isChanged      = false;
        $productId      = $objectId;
        if (!empty($delete)) {
            foreach ($delete as $data) {
                $resource->deletePriceData2($productId, null, $data['price_id']);
                $isChanged = true;
            }
        }

        if (!empty($insert)) {
            foreach ($insert as $data) {
                $price = new Varien_Object($data);
                $price->setEntityId($productId);
                $resource->savePriceData($price);
                $isChanged = true;
            }
        }

        if (!empty($update)) {
            foreach ($update as $k => $v) {
                if ($old[$k]['price'] != $v['value'] || $old[$k]['is_percent'] != $v['is_percent']) {
                    $price = new Varien_Object(array('value_id' => $old[$k]['price_id'], 'value' => $v['value'], 'is_percent' => $v['is_percent']));
                    $resource->savePriceData($price);
                    $isChanged = true;
                }
            }
        }

        if ($isChanged) {
            $valueChangedKey = $attributeName.'_changed';
            $object->setData($valueChangedKey, 1);
        }

        return $this;
    }
    /**
     * Retrieve websites rates and base currency codes
     *
     * @deprecated since 1.12.0.0
     * @return array
     */
    public function _getWebsiteRates()
    {
        return $this->_getWebsiteCurrencyRates();
    }
}
