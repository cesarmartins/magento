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
 * Product adapter
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Catalog_Convert_Adapter_Product
    extends Mage_Catalog_Model_Convert_Adapter_Product
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
     * Get warehouse identifier
     * 
     * @param bool $batchParam
     * 
     * @return int
     */
    protected function getWarehouseId($batchParam = false)
    {
        $helper = $this->getWarehouseHelper();
        if (!$batchParam) {
            return $this->getVar('warehouse', $helper->getDefaultWarehouseId());
        } else {
            $warehouseId = $this->getBatchParams('warehouse');
            if (!$warehouseId) {
                $warehouseId = $helper->getDefaultWarehouseId();
            }

            return $warehouseId;
        }
    }
    /**
     * Get stock identifier
     * 
     * @param bool $batchParam
     * 
     * @return int
     */
    protected function getStockId($batchParam = false)
    {
        $helper = $this->getWarehouseHelper();
        return $helper->getStockIdByWarehouseId($this->getWarehouseId($batchParam));
    }
    /**
     * Load product collection Id(s)
     *
     */
    public function load()
    {
        $attrFilterArray = array();
        $attrFilterArray['name']           = 'like';
        $attrFilterArray['sku']            = 'startsWith';
        $attrFilterArray['type']           = 'eq';
        $attrFilterArray['attribute_set']  = 'eq';
        $attrFilterArray['visibility']     = 'eq';
        $attrFilterArray['status']         = 'eq';
        $attrFilterArray['price']          = 'fromTo';
        $attrFilterArray['qty']            = 'fromTo';
        $attrFilterArray['store_id']       = 'eq';
        $attrToDb   = array('type' => 'type_id', 'attribute_set' => 'attribute_set_id', );
        $filters    = $this->_parseVars();
        $qty        = $this->getFieldValue($filters, 'qty');
        if ($qty) {
            $qtyFrom = isset($qty['from']) ? $qty['from'] : 0;
            $qtyTo   = isset($qty['to']) ? $qty['to'] : 0;
            $qtyAttr = array();
            $qtyAttr['alias']       = 'qty';
            $qtyAttr['attribute']   = 'cataloginventory/stock_item';
            $qtyAttr['field']       = 'qty';
            $qtyAttr['bind']        = 'product_id=entity_id';
            $qtyAttr['cond']        = "({{table}}.qty between '{$qtyFrom}' AND '{$qtyTo}') AND ".
                "({{table}}.stock_id=".intval($this->getStockId()).")";
            $qtyAttr['joinType']    = 'inner';
            $this->setJoinField($qtyAttr);
        }

        $this->setFilter($attrFilterArray, $attrToDb);
        if ($price = $this->getFieldValue($filters, 'price')) {
            $this->_filter[] = array(
                'attribute' => 'price', 
                'from'      => $price['from'], 
                'to'        => $price['to'], 
            );
            $this->setJoinAttr(
                array(
                'alias'     => 'price', 
                'attribute' => 'catalog_product/price', 
                'bind'      => 'entity_id', 
                'joinType'  => 'LEFT', 
                )
            );
        }

        if (!($entityType = $this->getVar('entity_type')) || 
            !(Mage::getResourceSingleton($entityType) instanceof Mage_Eav_Model_Entity_Interface)) {
            $this->addException(Mage::helper('eav')->__('Invalid entity specified'), Varien_Convert_Exception::FATAL);
        }

        try {
            $collection = $this->_getCollectionForLoad($entityType);
            if (isset($this->_joinAttr) && is_array($this->_joinAttr)) {
                foreach ($this->_joinAttr as $val) {
                    $collection->joinAttribute(
                        $val['alias'], $val['attribute'], $val['bind'], null, strtolower($val['joinType']), $val['storeId']
                    );
                }
            }

            $filterQuery = $this->getFilter();
            if (is_array($filterQuery)) {
                foreach ($filterQuery as $val) $collection->addFieldToFilter(array($val));
            }

            $joinFields = $this->_joinField;
            if (isset($joinFields) && is_array($joinFields)) {
                foreach ($joinFields as $field) {
                    $collection->joinField($field['alias'], $field['attribute'], $field['field'], $field['bind'], $field['cond'], $field['joinType']);
                }
            }

            $entityIds = $collection->getAllIds();
            $message = Mage::helper('eav')->__("Loaded %d records", count($entityIds));
            $this->addException($message);
        }
        catch (Varien_Convert_Exception $e) { throw $e; 
        }
        catch (Exception $e) {
            $message = Mage::helper('eav')->__('Problem loading the collection, aborting. Error: %s', $e->getMessage());
            $this->addException($message, Varien_Convert_Exception::FATAL);
        }

        $this->setData($entityIds);
        return $this;
    }
    /**
     * Save
     * 
     * @return MP_Warehouse_Model_Catalog_Convert_Adapter_Product
     */
    public function save()
    {
        return parent::save();
    }
    /**
     * Save row
     * 
     * @return boolean
     */
    public function saveRow(array $importData)
    {
        $product = $this->getProductModel()->reset();
        if (empty($importData['store'])) {
            if (!is_null($this->getBatchParams('store'))) {
                $store = $this->getStoreById($this->getBatchParams('store'));
            } else {
                $message = Mage::helper('catalog')->__(
                    'Skipping import row, required field "%s" is not defined.',
                    'store'
                );
                Mage::throwException($message);
            }
        } else {
            $store = $this->getStoreByCode($importData['store']);
        }

        if ($store === false) {
            $message = Mage::helper('catalog')->__(
                'Skipping import row, store "%s" field does not exist.',
                $importData['store']
            );
            Mage::throwException($message);
        }

        if (empty($importData['sku'])) {
            $message = Mage::helper('catalog')->__('Skipping import row, required field "%s" is not defined.', 'sku');
            Mage::throwException($message);
        }

        $product->setStoreId($store->getId());
        $productId = $product->getIdBySku($importData['sku']);
        if ($productId) {
            $product->load($productId);
        } else {
            $productTypes = $this->getProductTypes();
            $productAttributeSets = $this->getProductAttributeSets();
            if (empty($importData['type']) || !isset($productTypes[strtolower($importData['type'])])) {
                $value = isset($importData['type']) ? $importData['type'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, is not valid value "%s" for field "%s"',
                    $value,
                    'type'
                );
                Mage::throwException($message);
            }

            $product->setTypeId($productTypes[strtolower($importData['type'])]);
            if (empty($importData['attribute_set']) || !isset($productAttributeSets[$importData['attribute_set']])) {
                $value = isset($importData['attribute_set']) ? $importData['attribute_set'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, the value "%s" is invalid for field "%s"',
                    $value,
                    'attribute_set'
                );
                Mage::throwException($message);
            }

            $product->setAttributeSetId($productAttributeSets[$importData['attribute_set']]);
            foreach ($this->_requiredFields as $field) {
                $attribute = $this->getAttribute($field);
                if (!isset($importData[$field]) && $attribute && $attribute->getIsRequired()) {
                    $message = Mage::helper('catalog')->__(
                        'Skipping import row, required field "%s" for new products is not defined.',
                        $field
                    );
                    Mage::throwException($message);
                }
            }
        }

        $this->setProductTypeInstance($product);
        if (isset($importData['category_ids'])) {
            $product->setCategoryIds($importData['category_ids']);
        }

        foreach ($this->_ignoreFields as $field) {
            if (isset($importData[$field])) {
                unset($importData[$field]);
            }
        }

        if ($store->getId() != 0) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }

            if (!in_array($store->getWebsiteId(), $websiteIds)) {
                $websiteIds[] = $store->getWebsiteId();
            }

            $product->setWebsiteIds($websiteIds);
        }

        if (isset($importData['websites'])) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds) || !$store->getId()) {
                $websiteIds = array();
            }

            $websiteCodes = explode(',', $importData['websites']);
            foreach ($websiteCodes as $websiteCode) {
                try {
                    $website = Mage::app()->getWebsite(trim($websiteCode));
                    if (!in_array($website->getId(), $websiteIds)) {
                        $websiteIds[] = $website->getId();
                    }
                }
                catch (Exception $e) {
                }
            }

            $product->setWebsiteIds($websiteIds);
            unset($websiteIds);
        }

        foreach ($importData as $field => $value) {
            if (in_array($field, $this->_inventoryFields)) {
                continue;
            }

            if (is_null($value)) {
                continue;
            }

            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                continue;
            }

            $isArray = false;
            $setValue = $value;
            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(self::MULTI_DELIMITER, $value);
                $isArray = true;
                $setValue = array();
            }

            if ($value && $attribute->getBackendType() == 'decimal') {
                $setValue = $this->getNumber($value);
            }

            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);
                if ($isArray) {
                    foreach ($options as $item) {
                        if (in_array($item['label'], $value)) {
                            $setValue[] = $item['value'];
                        }
                    }
                } else {
                    $setValue = false;
                    foreach ($options as $item) {
                        if ($item['label'] == $value) {
                            $setValue = $item['value'];
                        }
                    }
                }
            }

            $product->setData($field, $setValue);
        }

        if (!$product->getVisibility()) {
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        }

        $stockData = array(
            'stock_id' => $this->getStockId(true)
        );
        $inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()])
            ? $this->_inventoryFieldsProductTypes[$product->getTypeId()]
            : array();
        foreach ($inventoryFields as $field) {
            if (isset($importData[$field])) {
                if (in_array($field, $this->_toNumber)) {
                    $stockData[$field] = $this->getNumber($importData[$field]);
                }
                else {
                    $stockData[$field] = $importData[$field];
                }
            }
        }

        $product->setStocksData(array($stockData));
        $mediaGalleryBackendModel = $this->getAttribute('media_gallery')->getBackend();
        $arrayToMassAdd = array();
        foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
            if (isset($importData[$mediaAttributeCode])) {
                $file = trim($importData[$mediaAttributeCode]);
                if (!empty($file) && !$mediaGalleryBackendModel->getImage($product, $file)) {
                    $arrayToMassAdd[] = array('file' => trim($file), 'mediaAttribute' => $mediaAttributeCode);
                }
            }
        }

        $addedFilesCorrespondence = $mediaGalleryBackendModel->addImagesWithDifferentMediaAttributes(
            $product, $arrayToMassAdd, Mage::getBaseDir('media').DS.'import', false, false
        );
        foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
            $addedFile = '';
            if (isset($importData[$mediaAttributeCode . '_label'])) {
                $fileLabel = trim($importData[$mediaAttributeCode . '_label']);
                if (isset($importData[$mediaAttributeCode])) {
                    $keyInAddedFile = array_search(
                        $importData[$mediaAttributeCode],
                        $addedFilesCorrespondence['alreadyAddedFiles']
                    );
                    if ($keyInAddedFile !== false) {
                        $addedFile = $addedFilesCorrespondence['alreadyAddedFilesNames'][$keyInAddedFile];
                    }
                }

                if (!$addedFile) {
                    $addedFile = $product->getData($mediaAttributeCode);
                }

                if ($fileLabel && $addedFile) {
                    $mediaGalleryBackendModel->updateImage($product, $addedFile, array('label' => $fileLabel));
                }
            }
        }

        $product->setIsMassupdate(true);
        $product->setExcludeUrlRewrite(true);
        $product->save();
        return true;
    }
}
