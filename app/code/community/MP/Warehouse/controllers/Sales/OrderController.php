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
 * Order controller
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Sales_OrderController 
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
     * Customer order pending payment
     */
    public function pendingpaymentAction()
    {
        $customerSession = Mage::getSingleton('customer/session');
        if (!$customerSession->isLoggedIn()) {
            $this->_forward('noRoute');
            return false;
        }

        $helper = $this->getWarehouseHelper();
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()
            ->getBlock('head')
            ->setTitle($helper->__('My Pending Payments'));
        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }

        $this->renderLayout();
    }
    /**
     * Make payment action
     */
    public function makepaymentAction()
    {
        $helper         = $this->getWarehouseHelper();
        $orderHelper    = $helper->getOrderHelper();
        $orderId        = (int) $this->getRequest()->getParam('order_id');
        if (!$orderId) {
            $this->_forward('noRoute');
            return false;
        }

        $order          = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getId()) {
            $this->_forward('noRoute');
            return false;
        }

        $currentOrder   = null;
        $customerSession = Mage::getSingleton('customer/session');
        if ($customerSession->isLoggedIn()) {
            if ($order->getCustomerId() == $customerSession->getCustomerId()) {
                $currentOrder = $order;
            }
        } else {
            $orderIds = Mage::getSingleton('core/session')->getOrderIds();
            if ($orderIds && is_array($orderIds) && in_array($orderId, $orderIds)) {
                $currentOrder = $order;
            } else {
                Mage::helper('sales/guest')->loadValidOrder();
                $currentOrder = Mage::registry('current_order');
            }
        }

        if (!$currentOrder || (!$orderHelper->isPendingPayment($currentOrder))) {
            $this->_forward('noRoute');
            return false;
        }

        $paymentMethod = $currentOrder->getPayment()->getMethodInstance();
        if ($paymentMethod) {
            $redirectUrl = $paymentMethod->getOrderPlaceRedirectUrl();
            if ($redirectUrl) {
                $checkoutSession = Mage::getSingleton('checkout/session');
                $checkoutSession->setLastOrderId($order->getId())
                    ->setRedirectUrl($redirectUrl)
                    ->setLastRealOrderId($order->getIncrementId());
                $agreement = $order->getPayment()->getBillingAgreement();
                if ($agreement) {
                    $checkoutSession->setLastBillingAgreementId($agreement->getId());
                }

                $this->_redirectUrl($redirectUrl);
            } else {
                $this->_forward('noRoute');
                return false;
            }
        } else {
            $this->_forward('noRoute');
            return false;
        }
    }
}
