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
 * Quote submit service
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Sales_Service_Quote 
    extends Mage_Sales_Model_Service_Quote
{
    /**
     * Orders
     * 
     * @var array of MP_Warehouse_Model_Sales_Order
     */
    protected $_orders = array();
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
     * Get convertor
     * 
     * @return Mage_Sales_Model_Convert_Quote
     */
    protected function getConvertor()
    {
        return $this->_convertor;
    }
    /**
     * Set order
     * 
     * @param int $stockId
     * @param MP_Warehouse_Model_Sales_Order $order
     * 
     * @return MP_Warehouse_Model_Sales_Service_Quote
     */
    protected function setOrder($stockId, $order)
    {
        $this->_orders[$stockId] = $order;
        return $this;
    }
    /**
     * Clear orders
     * 
     * @return MP_Warehouse_Model_Sales_Service_Quote
     */
    protected function clearOrders()
    {
        $this->_orders = array();
        return $this;
    }
    /**
     * Get orders
     * 
     * @return array of MP_Warehouse_Model_Sales_Order
     */
    public function getOrders()
    {
        return $this->_orders;
    }
    /**
     * Validate quote data before converting to order
     *
     * @return MP_Warehouse_Model_Sales_Service_Quote
     */
    protected function _validateMultiple()
    {
        $helper = Mage::helper('sales');
        $quote = $this->getQuote();
        if (!$quote->isVirtual()) {
            $address = $quote->getShippingAddress();
            $addressValidation = $address->validate();
            if ($addressValidation !== true) {
                Mage::throwException($helper->__('Please check shipping address information. %s', implode(' ', $addressValidation)));
            }

            foreach ($quote->getAllShippingAddresses() as $address) {
                if ($address->isVirtual()) {
                    continue;
                }

                $method = $address->getShippingMethod();
                $rate = $address->getShippingRateByCode($method);
                if (!$quote->isVirtual() && (!$method || !$rate)) {
                    Mage::throwException($helper->__('Please specify a shipping method for %s warehouse.', $address->getWarehouseTitle()));
                }
            }
        }

        $addressValidation = $quote->getBillingAddress()->validate();
        if ($addressValidation !== true) {
            Mage::throwException($helper->__('Please check billing address information. %s', implode(' ', $addressValidation)));
        }

        if (!($quote->getPayment()->getMethod())) {
            Mage::throwException($helper->__('Please select a valid payment method.'));
        }

        return $this;
    }
    /**
     * Submit nominal items
     *
     * @return MP_Warehouse_Model_Sales_Service_Quote
     */
    public function submitNominalItemsMultiple()
    {
        $this->_validateMultiple();
        $this->_submitRecurringPaymentProfiles();
        $this->_inactivateQuote();
        $this->_deleteNominalItems();
        return $this;
    }
    /**
     * Submit all available items
     * 
     * @return MP_Warehouse_Model_Sales_Service_Quote
     */
    public function submitAllMultiple()
    {
        $shouldInactivateQuoteOld = $this->_shouldInactivateQuote;
        $this->_shouldInactivateQuote = false;
        try {
            $this->submitNominalItemsMultiple();
            $this->_shouldInactivateQuote = $shouldInactivateQuoteOld;
        } catch (Exception $e) {
            $this->_shouldInactivateQuote = $shouldInactivateQuoteOld;
            throw $e;
        }

        if (!$this->getQuote()->getAllVisibleItems()) {
            $this->_inactivateQuote();
            return;
        }

        $this->submitOrders();
        return $this;
    }
    /**
     * Submit the quote. Quote submit process will create the orders based on quote data
     * 
     * @return array of MP_Warehouse_Model_Sales_Order
     */
    public function submitOrders()
    {
        $this->_deleteNominalItems();
        $this->_validateMultiple();
        $this->clearOrders();
        $quote = $this->getQuote();
        $billingAddress = $quote->getBillingAddress();
        $convertor = $this->getConvertor();
        $isVirtual = $quote->isVirtual();
        $transaction = Mage::getModel('core/resource_transaction');
        if ($quote->getCustomerId()) {
            $transaction->addObject($quote->getCustomer());
        }

        $transaction->addObject($quote);
        $addresses = array();
        if (!$isVirtual) {
            foreach ($quote->getAllShippingAddresses() as $address) {
                array_push($addresses, $address);
            }
        } else {
            array_push($addresses, $quote->getBillingAddress());
        }

        $addresses = array_reverse($addresses);
        foreach ($addresses as $address) {
            $stockId = intval($address->getStockId());
            $quote->unsReservedOrderId();
            $quote->reserveOrderId();
            $quote->collectTotals();
            $order = $convertor->addressToOrder($address);
            $orderBillingAddress = $convertor->addressToOrderAddress($quote->getBillingAddress());
            if ($billingAddress->getCustomerAddress()) {
                $orderBillingAddress->setCustomerAddress($billingAddress->getCustomerAddress());
            }

            $order->setBillingAddress($orderBillingAddress);
            if (!$isVirtual) {
                if (!$address->isVirtual()) {
                    $orderShippingAddress = $convertor->addressToOrderAddress($address);
                    if ($address->getCustomerAddress()) {
                        $orderShippingAddress->setCustomerAddress($address->getCustomerAddress());
                    }

                    $order->setShippingAddress($orderShippingAddress);
                } else {
                    $order->setIsVirtual(1);
                }
            } else {
                $order->setIsVirtual(1);
            }

            $order->setPayment($convertor->paymentToOrderPayment($quote->getPayment()));
            if (Mage::app()->getStore()->roundPrice($address->getGrandTotal()) == 0) {
                $order->getPayment()->setMethod('free');
            }

            foreach ($this->_orderData as $key => $value) {
                $order->setData($key, $value);
            }

            foreach ($quote->getAllItems() as $item) {
                if ($isVirtual || ($item->getStockId() == $stockId)) {
                    $orderItem = $convertor->itemToOrderItem($item);
                    if ($item->getParentItem()) {
                        $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
                    }

                    $order->addItem($orderItem);
                }
            }

            $order->setQuote($quote);
            $this->setOrder($stockId, $order);
            $transaction->addObject($order);
            $transaction->addCommitCallback(array($order, 'place'));
            $transaction->addCommitCallback(array($order, 'save'));
            Mage::dispatchEvent('checkout_type_onepage_save_order', array('order' => $order, 'quote' => $quote));
            Mage::dispatchEvent('sales_model_service_quote_submit_before', array('order' => $order, 'quote' => $quote));
        }

        try {
            $transaction->save();
            $this->_inactivateQuote();
            foreach ($this->getOrders() as $order) {
                Mage::dispatchEvent('sales_model_service_quote_submit_success', array('order' => $order, 'quote' => $quote));
            }
        } catch (Exception $e) {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                $quote->getCustomer()->setId(null);
            }

            foreach ($this->getOrders() as $order) {
                $order->setId(null);
                foreach ($order->getItemsCollection() as $item) {
                    $item->setOrderId(null);
                    $item->setItemId(null);
                }

                Mage::dispatchEvent('sales_model_service_quote_submit_failure', array('order' => $order, 'quote' => $quote));
            }

            throw $e;
        }

        foreach ($this->getOrders() as $order) {
            Mage::dispatchEvent('sales_model_service_quote_submit_after', array('order' => $order, 'quote' => $quote));
        }

        return $this->getOrders();
    }
}
