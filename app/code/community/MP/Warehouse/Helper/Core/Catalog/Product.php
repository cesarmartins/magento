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
 * Product helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Catalog_Product
    extends MP_Warehouse_Helper_Core_Abstract
{
    /**
     * Type array
     * 
     * @var array
     */
    protected $_typeArray;
    /**
     * Attribute set array
     * 
     * @var array
     */
    protected $_attributeSetArray;
    /**
     * Visibility array
     * 
     * @var array
     */
    protected $_visibilityArray;
    /**
     * Status array
     * 
     * @var array
     */
    protected $_statusArray;
    /**
     * Product skus
     * 
     * @var array 
     */
    protected $_productSkus = array();
    /**
     * Product ids
     * 
     * @var array
     */
    protected $_productIds = array();
    /**
     * Get price helper
     * 
     * @return MP_Warehouse_Helper_Core_Catalog_Product_Price
     */
    public function getPriceHelper()
    {
        return Mage::helper('warehouse/core_catalog_product_price');
    }
    /**
     * Get configuration helper
     * 
     * @return Mage_Catalog_Helper_Product_Configuration
     */
    public function getConfigurationHelper()
    {
        return Mage::helper('catalog/product_configuration');
    }
    /**
     * Get bundle configuration helper
     * 
     * @return Mage_Bundle_Helper_Catalog_Product_Configuration
     */
    public function getBundleConfigurationHelper()
    {
        return Mage::helper('bundle/catalog_product_configuration');
    }
    /**
     * Get downloadable configuration helper
     * 
     * @return Mage_Bundle_Helper_Catalog_Product_Configuration
     */
    public function getDownloadableConfigurationHelper()
    {
        return Mage::helper('downloadable/catalog_product_configuration');
    }
    /**
     * Get product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::getModel('catalog/product');
    }
    /**
     * Get product resource
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product
     */
    public function getProductResource()
    {
        return Mage::getResourceModel('catalog/product');
    }
    /**
     * Get product attribute by code
     *
     * @param string $code
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttribute($code)
    {
        return Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $code);
    }
    /**
     * Get tier price attribute
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getTierPriceAttribute()
    {
        return $this->getAttribute('tier_price');
    }
    /**
     * Check if group price is fixed
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return bool
     */
    public function isGroupPriceFixed($product)
    {
        return $this->getPriceHelper()->isGroupPriceFixed($product->getTypeId());
    }
    /**
     * Check if inventory is enabled
     * 
     * @return bool
     */
    public function isInventoryEnabled()
    {
        return Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory');
    }
    /**
     * Get product sku by id
     * 
     * @param int $productId
     * 
     * @return string
     */
    public function getProductSkuById($productId)
    {
        if (!isset($this->_productSkus[$productId])) {
            $adapter    = $this->getProductResource()->getReadConnection();
            $select     = $adapter->select()
                ->from($this->getCoreHelper()->getTable('catalog/product'), array('sku'))
                ->where('entity_id = ?', $productId);
            $this->_productSkus[$productId] = $adapter->fetchOne($select);
        }

        return $this->_productSkus[$productId];
    }
    /**
     * Get product id by sku
     * 
     * @param string $sku
     * 
     * @return string
     */
    public function getProductIdBySku($sku)
    {
        if (!isset($this->_productIds[$sku])) {
            $adapter    = $this->getProductResource()->getReadConnection();
            $select     = $adapter->select()
                ->from($this->getCoreHelper()->getTable('catalog/product'), array('entity_id'))
                ->where('sku = ?', $sku);
            $this->_productIds[$sku] = $adapter->fetchOne($select);
        }

        return $this->_productIds[$sku];
    }
    /**
     * Get type array
     * 
     * @return array
     */
    public function getTypeArray()
    {
        if (is_null($this->_typeArray)) {
            $this->_typeArray = Mage::getSingleton('catalog/product_type')->getOptionArray();
        }

        return $this->_typeArray;
    }
    /**
     * Get attribute set array
     * 
     * @return array
     */
    public function getAttributeSetArray()
    {
        if (is_null($this->_attributeSetArray)) {
            $this->_attributeSetArray = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();
        }

        return $this->_attributeSetArray;
    }
    /**
     * Get visibility array
     * 
     * @return array
     */
    public function getVisibilityArray()
    {
        if (is_null($this->_visibilityArray)) {
            $this->_visibilityArray = Mage::getSingleton('catalog/product_visibility')->getOptionArray();
        }

        return $this->_visibilityArray;
    }
    /**
     * Get status array
     * 
     * @return array
     */
    public function getStatusArray()
    {
        if (is_null($this->_statusArray)) {
            $this->_statusArray = Mage::getSingleton('catalog/product_status')->getOptionArray();
        }

        return $this->_statusArray;
    }
    /**
     * Get website id by store id
     * 
     * @param int $storeId
     * 
     * @return int
     */
    public function getWebsiteIdByStoreId($storeId)
    {
        $websiteId = null;
        if (!$this->getPriceHelper()->isGlobalScope()) {
            $websiteId = $this->getCoreHelper()->getWebsiteIdByStoreId($storeId);
        } else {
            $websiteId = 0;
        }

        return $websiteId;
    }
    /**
     * Get website id
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return int
     */
    public function getWebsiteId($product)
    {
        $storeId = $product->getStoreId();
        if ($storeId == 0) {
            $storeId = $this->getPriceHelper()
                ->getCoreHelper()
                ->getCurrentStoreId();
        }

        return $this->getWebsiteIdByStoreId((int) $storeId);
    }
    /**
     * Get store id by store id
     * 
     * @param int $storeId
     * 
     * @return int 
     */
    public function getStoreIdByStoreId($storeId)
    {
        $_storeId       = null;
        $priceHelper    = $this->getPriceHelper();
        if ($priceHelper->isStoreScope()) {
            $_storeId       = $storeId;
        } else if ($priceHelper->isWebsiteScope()) {
            $_storeId       = $this->getCoreHelper()
                ->getDefaultStoreIdByStoreId($storeId);
        } else {
            $_storeId       = 0;
        }

        return $_storeId;
    }
    /**
     * Get store id
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return int
     */
    public function getStoreId($product)
    {
        $storeId = $product->getStoreId();
        if ($storeId == 0) {
            $storeId = $this->getCoreHelper()->getCurrentStoreId();
        }

        return $this->getStoreIdByStoreId((int) $storeId);
    }
    /**
     * Clone product
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return Mage_Catalog_Model_Product
     */
    public function cloneProduct($product)
    {
        $newProduct = clone $product;
        foreach (array_keys($newProduct->getData()) as $key) {
            if (substr($key, 0, 15) == '_cache_instance') {
                $newProduct->unsetData($key);
            }
        }

        return $newProduct;
    }
    /**
     * Check if product is simple
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return boolean
     */
    public function isSimple($product)
    {
        return ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) ? true : false;
    }
    /**
     * Check if product is bundle
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return boolean
     */
    public function isBundle($product)
    {
        return ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) ? true : false;
    }
    /**
     * Check if product is configurable
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return boolean
     */
    public function isConfigurable($product)
    {
        return ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) ? true : false;
    }
    /**
     * Check if product is grouped
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return boolean
     */
    public function isGrouped($product)
    {
        return ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) ? true : false;
    }
    /**
     * Check if product is virtual
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return boolean
     */
    public function isVirtual($product)
    {
        return ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL) ? true : false;
    }
    /**
     * Check if product is downloadable
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return boolean
     */
    public function isDownloadable($product)
    {
        return ($product->getTypeId() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) ? true : false;
    }
    /**
     * Get options
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return array
     */
    public function getCustomOptions($product)
    {
        if ($product->getHasOptions() && (count($product->getOptions()) == 0)) {
            foreach ($product->getProductOptionsCollection() as $option) {
                $option->setProduct($product);
                $product->addOption($option);
            }
        }

        return $product->getOptions();
    }
    /**
     * Get custom option default value
     * 
     * @param Mage_Catalog_Model_Product_Option $option
     * 
     * @return mixed
     */
    protected function getCustomOptionDefaultValue($option)
    {
        $value                  = null;
        $optionGroup            = $option->getGroupByType();
        $optionGroupDate        = Mage_Catalog_Model_Product_Option::OPTION_GROUP_DATE;
        $optionGroupSelect      = Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT;
        if ($optionGroup == $optionGroupSelect) {
            foreach ($option->getValues() as $value) {
                $value = $value->getId();
                break;
            }
        } else if ($optionGroup == $optionGroupDate) {
            $value           = array(
                'month'         => (int) date('m'), 
                'day'           => (int) date('d'), 
                'year'          => (int) date('Y'), 
                'minute'        => (int) date('S'), 
                'hour'          => (int) date('g'), 
                'day_part'      => (int) date('a'), 
            );
        } else {
            $value              = 'Enabled';
        }

        return $value;
    }
    /**
     * Get default qty
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return float
     */
    protected function getDefaultQty($product)
    {
        $qty            = 1;
        if ($product->getDefaultQty()) {
            $qty = $product->getDefaultQty();
        }

        return $qty;
    }
    /**
     * Get default custom options
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return array
     */
    protected function getDefaultCustomOptions($product)
    {
        $values     = array();
        $options    = $this->getCustomOptions($product);
        foreach ($options as $option) {
            if (!$option->getIsRequire()) {
                continue;
            }

            $optionId           = $option->getId();
            $value              = $this->getCustomOptionDefaultValue($option);
            $values[$optionId] = $value;
        }

        return $values;
    }
    /**
     * Get default configurable options
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return array
     */
    protected function getDefaultConfigurableOptions($product)
    {
        $values = array();
        $typeInstance       = $product->getTypeInstance();
        $childProducts      = $typeInstance->getUsedProducts(null, $product);
        $attributes         = $typeInstance->getConfigurableAttributes($product);
        foreach ($childProducts as $childProduct) {
            foreach ($attributes as $attribute) {
                $productAttribute   = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue     = $childProduct->getData($productAttribute->getAttributeCode());
                $values[$productAttributeId] = $attributeValue;
            }

            break;
        }

        return $values;
    }
    /**
     * Get default bundle options
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return array
     */
    protected function getDefaultBundleOptions($product)
    {
        $values = array();
        $typeInstance           = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        $optionCollection       = $typeInstance->getOptionsCollection($product);
        $selectionCollection    = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($product), $product
        );
        if ($this->getCoreHelper()->getVersionHelper()->isGe1700()) {
            $options = $optionCollection->appendSelections(
                $selectionCollection, false, 
                Mage::helper('catalog/product')->getSkipSaleableCheck()
            );
        } else {
            $options = $optionCollection->appendSelections($selectionCollection, false, false);
        }

        foreach ($options as $option) {
            if (!$option->getSelections()) {
                continue;
            }

            $optionId               = $option->getId();
            $isMultipleOption       = $option->isMultiSelection();
            $isRequired             = $option->getRequired();
            $selectionId            = null;
            foreach ($option->getSelections() as $selection) {
                if ($selection->getIsDefault()) {
                    $selectionId = (int) $selection->getSelectionId();
                    break;
                }
            }

            if (!$selectionId && $isRequired) {
                foreach ($option->getSelections() as $selection) {
                    $selectionId = (int) $selection->getSelectionId();
                    break;
                }
            }

            if ($isMultipleOption) {
                $values[$optionId] = array($selectionId);
            } else {
                $values[$optionId] = $selectionId;
            }
        }

        return $values;
    }
    /**
     * Get default grouped options
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return array
     */
    protected function getDefaultGroupedOptions($product)
    {
        $values = array();
        $typeInstance       = $product->getTypeInstance();
        $associatedProducts = $typeInstance->getAssociatedProducts($product);
        foreach ($associatedProducts as $associatedProduct) {
            $values[$associatedProduct->getId()] = 1;
        }

        return $values;
    }
    /**
     * Get default downloadable options
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return array
     */
    protected function getDefaultDownloadableOptions($product)
    {
        $values       = array();
        $typeInstance       = $product->getTypeInstance();
        $links              = $typeInstance->getLinks($product);
        foreach ($links as $link) {
            $values[] = (int) $link->getId();
            break;
        }

        return $values;
    }
    /**
     * Get default buy request
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return Varien_Object
     */
    protected function getDefaultBuyRequest($product)
    {
        $key = 'default_buy_request';
        if (!$product->hasData($key)) {
            $buyRequest     = new Varien_Object();
            $buyRequest->setProduct($product->getId());
            $buyRequest->setQty($this->getDefaultQty($product));
            $buyRequest->setOptions($this->getDefaultCustomOptions($product));
            if ($this->isConfigurable($product)) {
                $buyRequest->setSuperAttribute($this->getDefaultConfigurableOptions($product));
            } else if ($this->isBundle($product)) {
                $buyRequest->setBundleOption($this->getDefaultBundleOptions($product));
            } else if ($this->isGrouped($product)) {
                $buyRequest->setSuperGroup($this->getDefaultGroupedOptions($product));
            } else if ($this->isDownloadable($product)) {
                $buyRequest->setLinks($this->getDefaultDownloadableOptions($product));
            }

            $product->setData($key, $buyRequest);
        }

        return $product->getData($key);
    }
    /**
     * Get buy request
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return Varien_Object
     */
    public function getBuyRequest($product)
    {
        $key = 'buy_request';
        if (!$product->hasData($key)) {
            $values = $product->getPreconfiguredValues();
            if ($values && count($values->getData()) && !count($values->getErrors())) {
                $buyRequest = new Varien_Object();
                foreach ($values->getData() as $key => $value) {
                    if (!in_array($key, array('errors'))) {
                        $buyRequest->setData($key, $value);
                    }
                }
            } else {
                $buyRequest = $this->getDefaultBuyRequest($product);
            }

            $product->setData($key, $buyRequest);
        }

        return $product->getData($key);
    }
    /**
     * Get formatted configuration option value
     * 
     * @param string $optionValue
     * 
     * @return string
     */
    public function getFormatedConfigurationOptionValue($optionValue)
    {
        $helper         = $this->getConfigurationHelper();
        $params         = array(
            'max_length'    => 55, 
            'cut_replacer'  => ' <a href="#" class="dots" onclick="return false">...</a>', 
        );
        return $helper->getFormattedOptionValue($optionValue, $params);
    }
    /**
     * Save child data
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function saveChildData(
        $product, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        $this->getCoreHelper()
            ->getModelHelper()
            ->saveChildData(
                $product, 
                'Mage_Catalog_Model_Product', 
                'product_id', 
                $dataTableName, 
                $dataAttributeCode, 
                $dataValueAttributeCode, 
                $dataValueType
            );
        return $this;
    }
    /**
     * Save child data
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function saveChildData2(
        $product, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        $this->getCoreHelper()
            ->getModelHelper()
            ->saveChildData2(
                $product, 
                'Mage_Catalog_Model_Product', 
                'product_id', 
                $dataTableName, 
                $dataAttributeCode, 
                $dataKeyAttributeCode, 
                $dataValueAttributeCode, 
                $dataValueType
            );
        return $this;
    }
    /**
     * Save child data
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataKey2AttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function saveChildData3(
        $product, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataKey2AttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        $this->getCoreHelper()
            ->getModelHelper()
            ->saveChildData3(
                $product, 
                'Mage_Catalog_Model_Product', 
                'product_id', 
                $dataTableName, 
                $dataAttributeCode, 
                $dataKeyAttributeCode, 
                $dataKey2AttributeCode, 
                $dataValueAttributeCode, 
                $dataValueType
            );
        return $this;
    }
    /**
     * Add child data
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param array $array
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    public function addChildData($product, $array, $dataAttributeCode)
    {
        $this->getCoreHelper()
            ->getModelHelper()
            ->addChildData($product, 'Mage_Catalog_Model_Product', $array, $dataAttributeCode);
        return $this;
    }
    /**
     * Load child data
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function loadChildData(
        $product, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        $this->getCoreHelper()->getModelHelper()->loadChildData(
            $product, 
            'Mage_Catalog_Model_Product', 
            'product_id', 
            $dataTableName, 
            $dataAttributeCode, 
            $dataValueAttributeCode, 
            $dataValueType
        );
        return $this;
    }
    /**
     * Load child data
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function loadChildData2(
        $product, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        $this->getCoreHelper()->getModelHelper()->loadChildData2(
            $product, 
            'Mage_Catalog_Model_Product', 
            'product_id', 
            $dataTableName, 
            $dataAttributeCode, 
            $dataKeyAttributeCode, 
            $dataValueAttributeCode, 
            $dataValueType
        );
        return $this;
    }
    /**
     * Load child data
     * 
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataKey2AttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function loadChildData3(
        $product, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataKey2AttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        $this->getCoreHelper()->getModelHelper()->loadChildData3(
            $product, 
            'Mage_Catalog_Model_Product', 
            'product_id', 
            $dataTableName, 
            $dataAttributeCode, 
            $dataKeyAttributeCode, 
            $dataKey2AttributeCode, 
            $dataValueAttributeCode, 
            $dataValueType
        );
        return $this;
    }
    /**
     * Load collection child data
     * 
     * @param Varien_Data_Collection_Db $collection
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function loadCollectionChildData(
        $collection, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        $this->getCoreHelper()->getModelHelper()->loadCollectionChildData(
            $collection, 
            'product_id', 
            $dataTableName, 
            $dataAttributeCode, 
            $dataValueAttributeCode, 
            $dataValueType
        );
        return $this;
    }
    /**
     * Load collection child data
     * 
     * @param Varien_Data_Collection_Db $collection
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function loadCollectionChildData2(
        $collection, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        $this->getCoreHelper()->getModelHelper()->loadCollectionChildData2(
            $collection, 
            'product_id', 
            $dataTableName, 
            $dataAttributeCode, 
            $dataKeyAttributeCode, 
            $dataValueAttributeCode, 
            $dataValueType
        );
        return $this;
    }
    /**
     * Load collection child data
     * 
     * @param Varien_Data_Collection_Db $collection
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataKey2AttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function loadCollectionChildData3(
        $collection, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataKey2AttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        $this->getCoreHelper()->getModelHelper()->loadCollectionChildData3(
            $collection, 
            'product_id', 
            $dataTableName, 
            $dataAttributeCode, 
            $dataKeyAttributeCode, 
            $dataKey2AttributeCode, 
            $dataValueAttributeCode, 
            $dataValueType
        );
        return $this;
    }
    /**
     * Remove child data
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    public function removeChildData($product, $dataAttributeCode)
    {
        $this->getCoreHelper()
            ->getModelHelper()
            ->removeChildData($product, 'Mage_Catalog_Model_Product', $dataAttributeCode);
        return $this;
    }
    /**
     * Check if edit mode is enabled
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    public function isEditMode($product)
    {
        return ($product->getData('_edit_mode')) ? true : false;
    }
}
