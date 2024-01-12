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
 * Configurable product view
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Catalog_Product_View_Type_Configurable 
    extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    /**
     * Get warehouse helper
     *
     * @return  MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
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
     * Get allowed products
     * 
     * @return array of Mage_Catalog_Model_Product
     */
    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $helper                 = $this->getWarehouseHelper();
            $config                 = $helper->getConfig();
            $assignmentMethodHelper = $helper->getAssignmentMethodHelper();
            $inventoryHelper        = $helper->getCatalogInventoryHelper();
            $products               = array();
            $parentProduct          = $this->getProduct();
            $parentStockItem        = $parentProduct->getStockItem();
            $parentProductId        = $parentProduct->getId();
            
            if ($this->getVersionHelper()->isGe1700()) {
                $skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();
            }
            
            $allProducts = $parentProduct->getTypeInstance(true)->getUsedProducts(null, $parentProduct);
            if ($config->isMultipleMode()) {
                $stockIds = $inventoryHelper->getStockIds();
            } else {
                $stockIds = array($assignmentMethodHelper->getQuoteStockId());
            }

            foreach ($allProducts as $product) {
                $productId = $product->getId();
                foreach ($stockIds as $stockId) {
                    $pStockItem = $inventoryHelper->getStockItemCached($parentProductId, $stockId);
                    $pStockItem->assignProduct($parentProduct);
                    $stockItem = $inventoryHelper->getStockItemCached($productId, $stockId);
                    $stockItem->assignProduct($product);
                    if ($this->getVersionHelper()->isGe1700()) {
                        if (($product->isSaleable() && $parentProduct->isSaleable()) || $skipSaleableCheck) {
                            $products[] = $product;
                            break;
                        }
                    } else {
                        if ($product->isSaleable() && $parentProduct->isSaleable()) {
                            $products[] = $product;
                            break;
                        }
                    }
                }
            }

            $parentStockItem->assignProduct($parentProduct);
            $this->setAllowProducts($products);
        }

        return $this->getData('allow_products');
    }
        public function getJsonConfig()
    {
        $config = Zend_Json::decode(parent::getJsonConfig());

        $childProducts = array();

        //Create the extra price and tier price data/html we need.
        foreach ($this->getAllowProducts() as $product) {
            $productId  = $product->getId();
            $childProducts[$productId] = array(
                "price" => $this->_registerJsPrice($this->_convertPrice($product->getPrice())),
                "finalPrice" => $this->_registerJsPrice($this->_convertPrice($product->getFinalPrice()))
            );

            
        }

        //Remove any existing option prices.
        //Removing holes out of existing arrays is not nice,
        //but it keeps the extension's code separate so if Varien's getJsonConfig
        //is added to, things should still work.
        if (is_array($config['attributes'])) {
            foreach ($config['attributes'] as $attributeID => &$info) {
                if (is_array($info['options'])) {
                    foreach ($info['options'] as &$option) {
                        unset($option['price']);
                    }
                    unset($option); //clear foreach var ref
                }
            }
            unset($info); //clear foreach var ref
        }

        $p = $this->getProduct();
        $config['childProducts'] = $childProducts;
        if ($p->getMaxPossibleFinalPrice() != $p->getFinalPrice()) {
            $config['priceFromLabel'] = $this->__('Price From:');
        } else {
            $config['priceFromLabel'] = $this->__('');
        }
        $config['ajaxBaseUrl'] = Mage::getUrl('warehouse/warehouse/');
        $config['productName'] = $p->getName();
        $config['description'] = $this->helper('catalog/output')->productAttribute($p, $p->getDescription(), 'description');
        $config['shortDescription'] = $this->helper('catalog/output')->productAttribute($p, nl2br($p->getShortDescription()), 'short_description');       

        $childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
        $config["productAttributes"] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
            ->setProduct($this->getProduct())
            ->toHtml();

        return Zend_Json::encode($config);
       
    }
}
