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
 * Product price helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Catalog_Product_Price
    extends MP_Warehouse_Helper_Core_Abstract
{
    /**
     * Tier price attribute
     * 
     * @var Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected $_tierPriceAttribute;
    /**
     * Group price attribute
     * 
     * @var Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected $_groupPriceAttribute;
    /**
     * Price attribute
     * 
     * @var Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected $_priceAttribute;
    /**
     * Get product helper
     * 
     * @return MP_Warehouse_Helper_Core_Catalog_Product
     */
    public function getProductHelper()
    {
        return Mage::helper('warehouse/core_catalog_product');
    }
    /**
     * Get indexer helper
     * 
     * @return MP_Warehouse_Helper_Core_Catalog_Product
     */
    public function getIndexerHelper()
    {
        return Mage::helper('warehouse/core_catalog_product_price_indexer');
    }
    /**
     * Get version helper
     * 
     * @return MP_Warehouse_Helper_Core_Version
     */
    public function getVersionHelper()
    {
        return $this->getCoreHelper()->getVersionHelper();
    }
    /**
     * Check if group price is fixed
     * 
     * @param string $productTypeId
     * 
     * @return bool
     */
    public function isGroupPriceFixed($productTypeId)
    {
        $price = Mage::getSingleton('catalog/product_type')->priceFactory($productTypeId);
        if ($this->getVersionHelper()->isGe1700()) {
            return $price->isGroupPriceFixed();
        } else {
            return $price->isTierPriceFixed();
        }
    }
    /**
     * Get attributes codes
     * 
     * @return array
     */
    protected function getAttributesCodes()
    {
        return array(
            'price', 
            'special_price', 
            'special_from_date', 
            'special_to_date', 
            'tier_price', 
            'group_price', 
        );
    }
    /**
     * Get scope
     * 
     * @return int
     */
    public function getScope()
    {
        return Mage::helper('catalog')->getPriceScope();
    }
    /**
     * Check if global scope is active
     * 
     * @return bool 
     */
    public function isGlobalScope()
    {
        return ($this->getScope() == 0)  ? true : false;
    }
    /**
     * Check if website scope is active
     * 
     * @return bool
     */
    public function isWebsiteScope()
    {
        return ($this->getScope() == 1)  ? true : false;
    }
    /**
     * Check if store scope is active
     * 
     * @return bool
     */
    public function isStoreScope()
    {
        return ($this->getScope() == 2)  ? true : false;
    }
    /**
     * Get attribute scope
     * 
     * @param int $scope
     * 
     * @return int 
     */
    protected function getAttributeScope($scope)
    {
        $attributeScope = null;
        switch ($scope) {
            case 0: 
                $attributeScope = Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL;
                break;
            case 1: 
                $attributeScope = Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE;
                break;
            case 2: 
                $attributeScope = Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE;
                break;
            default: 
                $attributeScope = Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL;
                break;
        }

        return $attributeScope;
    }
    /**
     * Set attribute scope
     * 
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param int $scope
     * 
     * @return self
     */
    public function setAttributeScope($attribute, $scope = null)
    {
        if (is_null($scope)) {
            $scope = $this->getScope();
        }

        $attribute->setIsGlobal($this->getAttributeScope($scope));
        return $this;
    }
    /**
     * Change scope
     * 
     * @param int $scope
     * 
     * @return self
     */
    public function changeScope($scope)
    {
        $productHelper      = $this->getProductHelper();
        $attributeScope     = $this->getAttributeScope($scope);
        $attributesCodes    = $this->getAttributesCodes();
        foreach ($attributesCodes as $attributeCode) {
            $attribute          = $productHelper->getAttribute($attributeCode);
            $attribute->setIsGlobal($attributeScope);
            $attribute->save();
        }

        return $this;
    }
    /**
     * Get tier price attribute
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getTierPriceAttribute()
    {
        if (is_null($this->_tierPriceAttribute)) {
            $attribute = $this->getProductHelper()->getAttribute('tier_price');
            if ($attribute) {
                $this->_tierPriceAttribute = $attribute;
            }
        }

        return $this->_tierPriceAttribute;
    }
    /**
     * Get group price attribute
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getGroupPriceAttribute()
    {
        if (is_null($this->_groupPriceAttribute)) {
            $attribute = $this->getProductHelper()->getAttribute('group_price');
            if ($attribute) {
                $this->_groupPriceAttribute = $attribute;
            }
        }

        return $this->_groupPriceAttribute;
    }
    /**
     * Get price attribute
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Attribute 
     */
    public function getPriceAttribute()
    {
        if (is_null($this->_priceAttribute)) {
            $attribute = $this->getProductHelper()->getAttribute('price');
            if ($attribute) {
                $this->_priceAttribute = $attribute;
            }
        }

        return $this->_priceAttribute;
    }
    /**
     * Get price attribute identifier
     * 
     * @return mixed 
     */
    public function getPriceAttributeId()
    {
        return $this->getPriceAttribute()->getId();
    }
    /**
     * Get price attribute table
     * 
     * @return string 
     */
    public function getPriceAttributeTable()
    {
        return $this->getPriceAttribute()->getBackend()->getTable();
    }
    /**
     * Escape price
     * 
     * @param float $price
     * 
     * @return float
     */
    public function escapedPrice($price)
    {
        if (!is_numeric($price)) {
            return null;
        }

        return number_format($price, 2, null, '');
    }
    /**
     * Set group price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param array $prices
     * 
     * @return self
     */
    public function _setGroupPrice($product, $attribute, $prices)
    {
        return $this;
    }
    /**
     * Set tier price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return self
     */
    public function setTierPrice($product)
    {
        $this->_setGroupPrice($product, $this->getTierPriceAttribute(), $product->getTierPrices());
        return $this;
    }
    /**
     * Set group price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return self
     */
    public function setGroupPrice($product)
    {
        $this->_setGroupPrice($product, $this->getGroupPriceAttribute(), $product->getGroupPrices());
        return $this;
    }
    /**
     * Get default compound price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $priceAttributeCode
     * @param string $attributeCode
     * @param array $parameters
     * 
     * @return mixed
     */
    public function _getDefaultCompoundPrice($product, $priceAttributeCode, $attributeCode, $parameters = array())
    {
        return $product->getDataUsingMethod($priceAttributeCode);
    }
    /**
     * Get default compound price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param array $parameters
     * 
     * @return mixed
     */
    public function getDefaultCompoundPrice($product, $parameters = array())
    {
        return $this->_getDefaultCompoundPrice($product, 'price', 'compound_prices', $parameters);
    }
    /**
     * Get default compound special price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param array $parameters
     * 
     * @return mixed
     */
    public function getDefaultCompoundSpecialPrice($product, $parameters = array())
    {
        return $this->_getDefaultCompoundPrice($product, 'special_price', 'compound_special_prices', $parameters);
    }
    /**
     * Get compound price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $attributeCode
     * @param array $parameters
     * 
     * @return mixed
     */
    protected function _getCompoundPrice($product, $attributeCode, $parameters = array())
    {
        return null;
    }
    /**
     * Get compound price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param array $parameters
     * 
     * @return mixed
     */
    public function getCompoundPrice($product, $parameters = array())
    {
        return $this->_getCompoundPrice($product, 'compound_prices', $parameters);
    }
    /**
     * Get compound special price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param array $parameters
     * 
     * @return mixed
     */
    public function getCompoundSpecialPrice($product, $parameters = array())
    {
        return $this->_getCompoundPrice($product, 'compound_special_prices', $parameters);
    }
    /**
     * Set price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return self
     */
    public function setPrice($product)
    {
        if ($product->getData('_edit_mode')) {
            return $this;
        }

        $price          = $product->getPrice();
        $defaultPrice   = $product->getDefaultPrice();
        if (is_null($defaultPrice) && !is_null($price)) {
            $product->setDefaultPrice($price);
        }

        $price          = $this->getCompoundPrice($product);
        if (is_null($price)) {
            $price          = $product->getDefaultPrice();
        }

        if (!is_null($price)) {
            $product->setFinalPrice(null);
            $product->setPrice($price);
        }

        return $this;
    }
    /**
     * Set special price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return self
     */
    public function setSpecialPrice($product)
    {
        if ($product->getData('_edit_mode')) {
            return $this;
        }

        $price          = $product->getSpecialPrice();
        $defaultPrice   = $product->getDefaultSpecialPrice();
        if (is_null($defaultPrice) && !is_null($price)) {
            $product->setDefaultSpecialPrice($price);
        }

        $price          = $this->getCompoundSpecialPrice($product);
        if (is_null($price)) {
            $price          = $product->getDefaultSpecialPrice();
        }

        if (!is_null($price)) {
            $product->setFinalPrice(null);
            $product->setSpecialPrice($price);
        } else {
            $product->setFinalPrice(null);
            $product->setSpecialPrice(null);
        }

        return $this;
    }
    /**
     * Save compound price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    protected function _saveCompoundPrice($product, $dataTableName, $dataAttributeCode)
    {
        return $this;
    }
    /**
     * Save compound price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return self
     */
    public function saveCompoundPrice($product)
    {
        return $this->_saveCompoundPrice(
            $product, 
            'catalog/product_compound_price', 
            'compound_prices'
        );
    }
    /**
     * Save compound special price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return self
     */
    public function saveCompoundSpecialPrice($product)
    {
        return $this->_saveCompoundPrice(
            $product, 
            'catalog/product_compound_special_price', 
            'compound_special_prices'
        );
    }
    /**
     * Load compound price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    protected function _loadCompoundPrice($product, $dataTableName, $dataAttributeCode)
    {
        return $this;
    }
    /**
     * Load compound price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return self
     */
    public function loadCompoundPrice($product)
    {
        $this->_loadCompoundPrice($product, 'catalog/product_compound_price', 'compound_prices');
        $this->setPrice($product);
        return $this;
    }
    /**
     * Load compound special price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return self
     */
    public function loadCompoundSpecialPrice($product)
    {
        $this->_loadCompoundPrice($product, 'catalog/product_compound_special_price', 'compound_special_prices');
        $this->setSpecialPrice($product);
        return $this;
    }
    /**
     * Load collection compound price
     * 
     * @param Varien_Data_Collection_Db $collection
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    protected function _loadCollectionCompoundPrice($collection, $dataTableName, $dataAttributeCode)
    {
        return $this;
    }
    /**
     * Load collection compound price
     * 
     * @param Varien_Data_Collection_Db $collection
     * 
     * @return self
     */
    public function loadCollectionCompoundPrice($collection)
    {
        $this->_loadCollectionCompoundPrice(
            $collection, 
            'catalog/product_compound_price', 
            'compound_prices'
        );
        foreach ($collection as $product) {
            $this->setPrice($product);
        }

        return $this;
    }
    /**
     * Load collection compound special price
     * 
     * @param Varien_Data_Collection_Db $collection
     * 
     * @return self
     */
    public function loadCollectionCompoundSpecialPrice($collection)
    {
        $this->_loadCollectionCompoundPrice(
            $collection, 
            'catalog/product_compound_special_price', 
            'compound_special_prices'
        );
        foreach ($collection as $product) {
            $this->setSpecialPrice($product);
        }

        return $this;
    }
    /**
     * Remove compound price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    protected function _removeCompoundPrice($product, $dataAttributeCode)
    {
        $this->getProductHelper()->removeChildData($product, $dataAttributeCode);
        return $this;
    }
    /**
     * Remove compound price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return self
     */
    public function removeCompoundPrice($product)
    {
        return $this->_removeCompoundPrice($product, 'compound_prices');
    }
    /**
     * Remove compound special price
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return self
     */
    public function removeCompoundSpecialPrice($product)
    {
        return $this->_removeCompoundPrice($product, 'compound_special_prices');
    }
    /**
     * Round
     * 
     * @param float $price
     * 
     * @return float
     */
    public function round($price)
    {
        return round($price, 2);
    }
    /**
     * Set zone price
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Varien_Object $address
     *
     * @return self
     */
    public function setZonePrice($product, $address)
    {
        if (!$product || !($product instanceof Mage_Catalog_Model_Product)) {
            return $this;
        }

        $zonePrice      = Mage::getModel('catalog/product_zone_price');
        $zonePrice->loadByProductIdAndAddress($product->getId(), $address);
        if ($zonePrice->getId()) {
            $product->setFinalPrice(
                $zonePrice->getFinalPrice((float) $product->getFinalPrice())
            );
        }

        return $this;
    }
    /**
     * Set collection zone price
     *
     * @param Varien_Data_Collection_Db $collection
     * @param Varien_Object $address
     *
     * @return self
     */
    public function setCollectionZonePrice($collection, $address)
    {
        if (!$collection) {
            return $this;
        }

        $zonePriceCollection    = Mage::getSingleton('catalog/product_zone_price')->getCollection();
        $zonePriceCollection->setProductsFilter($collection);
        $zonePriceCollection->setAddressFilter($address);
        $zonePrices             = array();
        foreach ($zonePriceCollection as $zonePrice) {
            $productId              = (int) $zonePrice->getProductId();
            if (!isset($zonePrices[$productId])) {
                $zonePrices[$productId] = $zonePrice;
            }
        }

        if (!count($zonePrices)) {
            return $this;
        }

        foreach ($collection as $product) {
            $productId              = (int) $product->getId();
            if (isset($zonePrices[$productId])) {
                $zonePrice              = $zonePrices[$productId];
                $product->setFinalPrice(
                    $zonePrice->getFinalPrice((float) $product->getFinalPrice())
                );
            }
        }

        return $this;
    }
}
