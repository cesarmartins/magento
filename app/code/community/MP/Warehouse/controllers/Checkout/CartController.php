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

require_once 'Mage/Checkout/controllers/CartController.php';
/**
 * Cart controller
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Checkout_CartController 
    extends Mage_Checkout_CartController
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
     * Set current address action
     */
    public function setCurrentAddressAction()
    {
        $helper = $this->getWarehouseHelper();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $session = Mage::getSingleton('core/session');
            $shippingAddress = new Varien_Object(
                array(
                'country_id'   => trim($request->getPost('country_id')), 
                'region_id'    => trim($request->getPost('region_id')), 
                'region'       => trim($request->getPost('region')), 
                'city'         => trim($request->getPost('city')), 
                'postcode'     => trim($request->getPost('postcode')), 
                )
            );
            $helper->setCustomerShippingAddress($shippingAddress);
            $session->addSuccess($helper->__('Your shipping address has been changed.'));
        }

        $this->_redirectReferer();
    }
    /**
     * Estimate post
     */
    public function estimatePostAction()
    {
        $country    = (string) $this->getRequest()->getParam('country_id');
        $postcode   = (string) $this->getRequest()->getParam('estimate_postcode');
        $city       = (string) $this->getRequest()->getParam('estimate_city');
        $regionId   = (string) $this->getRequest()->getParam('region_id');
        $region     = (string) $this->getRequest()->getParam('region');
        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
            foreach ($this->_getQuote()->getAllShippingAddresses() as $address) {
                $address->setCountryId($country)->setCity($city)->setPostcode($postcode)
                    ->setRegionId($regionId)->setRegion($region)->setCollectShippingRates(true);
            }
        } else {
            $this->_getQuote()->getShippingAddress()->setCountryId($country)->setCity($city)->setPostcode($postcode)
                ->setRegionId($regionId)->setRegion($region)->setCollectShippingRates(true);
        }

        $this->_getQuote()->save();
        $this->_goBack();
    }
    /**
     * Estimate update post 
     */
    public function estimateUpdatePostAction()
    {
        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
            $codes = $this->getRequest()->getParam('estimate_method');
            if (count($codes)) {
                foreach ($codes as $stockId => $code) {
                    $address = $this->_getQuote()->getShippingAddressByStockId($stockId);
                    if ($address) {
                        $address->setShippingMethod($code)->save();
                    }
                }
            }
        } else {
            $code = (string) $this->getRequest()->getParam('estimate_method');
            if (!empty($code)) {
                $this->_getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
            }
        }

        $this->_goBack();
    }
    /**
     * Shopping cart display action
     */
    public function indexAction()
    {
        $cart = $this->_getCart();
        $cart->getQuote()->setItemsQtys(null);
        parent::indexAction();
    }
    /**
     * Update shopping cart data action
     */
    public function updatePostAction()
    {
        $this->_getCart()->getQuote()->removeErrors();
        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');
        if ($updateAction != 'reset_cart') {
            parent::updatePostAction();
        } else {
            $this->_resetCart();
            $this->_goBack();
        }
    }
    /**
     * Reset cart
     */
    protected function _resetCart()
    {
        $helper     = $this->getWarehouseHelper();
        $session    = $this->_getSession();
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $cart = $this->_getCart();
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                $cart->resetStocks($cartData)->save();
            }

            $session->setCartWasUpdated(true);
            $session->addSuccess($helper->__('Cart has been reset.'));
        } catch (Mage_Core_Exception $e) {
            $session->addError(Mage::helper('core')->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
            $session->addException($e, $helper->__('Cannot reset cart.'));
            Mage::logException($e);
        }
    }
    /**
     * Action to reconfigure cart item
     */
    public function configureAction()
    {
        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isSplitQtyEnabled()) {
            $cart = $this->_getCart();
            $quote = $cart->getQuote();
            $id = (int) $this->getRequest()->getParam('id');
            if ($id) {
                $quoteItem = $quote->getItemById($id);
            }

            if ($quoteItem) {
                $quoteItem = $quote->getOrigionalItem($quoteItem);
                $this->getRequest()->setParam('id', $quoteItem->getId());
            }

            $quote->mergeItems();
        }

        parent::configureAction();
    }
    /**
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction()
    {
        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isSplitQtyEnabled()) {
            $quote = $this->_getCart()->getQuote();
            $quote->mergeItems();
        }

        parent::updateItemOptionsAction();
    }
}
