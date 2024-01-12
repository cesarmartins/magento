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
 * Warehouse controller
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_WarehouseController 
    extends Mage_Core_Controller_Front_Action
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
     * Set stock
     */
    public function setStockAction()
    {
        $helper     = $this->getWarehouseHelper();
        $config     = $helper->getConfig();
        if (!$config->isMultipleMode() && $config->isAllowAdjustment()) {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $coreSession    = $helper->getCoreSession();
                if ($request->getParam('set_stock') == 'update') {
                    $stockId        = (int) $request->getParam('stock_id');
                    if ($helper->isStockIdExists($stockId)) {
                        $helper->setSessionStockId($stockId);
                        $coreSession->addSuccess($helper->__('Warehouse has been updated.'));
                    }
                } else {
                    $helper->removeSessionStockId();
                    $coreSession->addSuccess($helper->__('Warehouse has been reset.'));
                }
            }
        }

        $this->_redirectReferer();
    }
    /**
     * Refresh product quote
     */
    public function refreshProductQuoteAction()
    {
        $helper     = $this->getWarehouseHelper();
        $config     = $helper->getConfig();
        $result     = array();
        $request    = $this->getRequest();
        if ($request->isPost()) {
            $productId      = (int) $request->getParam('product');
            $params         = new Varien_Object();
            $buyRequest     = new Varien_Object();
            $buyRequest->setData($request->getParams());
            $params->setBuyRequest($buyRequest);
            $productHelper  = Mage::helper('catalog/product');
            $product        = $productHelper->initProduct($productId, $this, $params);
            if ($product) {
                $productHelper->prepareProductOptions($product, $buyRequest);
                Mage::dispatchEvent('catalog_controller_product_view', array('product' => $product));
                $productQuoteBlock  = $this->getLayout()->createBlock('warehouse/catalog_product_view_quote');
                $result['error']    = false;
                $result['html']     = $productQuoteBlock->toHtml();
            } else {
                $result['error']    = true;
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    /**
     * Update product quote
     */
    public function updateProductQuoteAction()
    {
        $helper     = $this->getWarehouseHelper();
        $config     = $helper->getConfig();
        if ($config->isMultipleMode() && $config->isAllowAdjustment()) {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $productId      = (int) $request->getParam('product');
                $product        = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
                if ($product->getId()) {
                    $coreSession    = $helper->getCoreSession();
                    if ($request->getParam('update_product_quote') == 'update') {
                        $stockId        = (int) $request->getParam('stock_id');
                        if ($helper->isStockIdExists($stockId)) {
                            $helper->getProductHelper()->setSessionStockId($product, $stockId);
                            $coreSession->addSuccess($helper->__('Product quote has been updated.'));
                        }
                    } else {
                        $helper->getProductHelper()->setSessionStockId($product, null);
                        $coreSession->addSuccess($helper->__('Product quote has been reset.'));
                    }
                }
            }
        }

        $this->_redirectReferer();
    }


    
    public function coAction()
    {
       $product = $this->_initProduct();
       if (!empty($product)) {
           $this->loadLayout(false);
           $this->renderLayout();
       }
    }

    public function imageAction()
    {
       $product = $this->_initProduct();
       if (!empty($product)) {
           $this->loadLayout(false);
           $this->renderLayout();
       }
    }

    public function galleryAction()
    {
       $product = $this->_initProduct();
       if (!empty($product)) {
           #$this->_initProductLayout($product);
           $this->loadLayout();
           $this->renderLayout();
       }
    }

    //Copy of parent _initProduct but changes visibility checks.
    //Reproducing functionality like this is far from great for future compatibilty
    //but at the moment I don't see a better alternative.
    protected function _initProduct()
    {
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $this->getRequest()->getParam('id');
        $parentId   = (int) $this->getRequest()->getParam('pid');

        if (!$productId || !$parentId) {
            return false;
        }

        $parent = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($parentId);

        if (!Mage::helper('catalog/product')->canShow($parent)) {
            return false;
        }

        $childIds = $parent->getTypeInstance()->getUsedProductIds();
        if (!is_array($childIds) || !in_array($productId, $childIds)) {
            return false;
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
        // @var $product Mage_Catalog_Model_Product
        if (!$product->getId()) {
            return false;
        }
        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            Mage::register('current_category', $category);
        }
        $product->setCpid($parentId);
        Mage::register('current_product', $product);
        Mage::register('product', $product);
        return $product;
    }
}
