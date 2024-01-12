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
 * Order create
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Adminhtml_Sales_Order_Create 
    extends Mage_Adminhtml_Model_Sales_Order_Create
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
     * Initialize from order
     * 
     * @param Mage_Sales_Model_Order $order
     * 
     * @return $this
     */
    public function initFromOrder(Mage_Sales_Model_Order $order)
    {
        $helper                 = $this->getWarehouseHelper();
        $session                = $this->getSession();
        if (!$order->getReordered()) {
            $session->setOrderId($order->getId());
        } else {
            $session->setReordered($order->getId());
        }

        $session->setCurrencyId($order->getOrderCurrencyCode());
        if ($order->getCustomerId()) {
            $session->setCustomerId($order->getCustomerId());
        } else {
            $session->setCustomerId(false);
        }

        $session->setStoreId($order->getStoreId());

        Mage::dispatchEvent('init_from_order_session_quote_initialized', array('session_quote' => $session));
        
        if (!$helper->getConfig()->isMultipleMode()) {
            $session->setStockId($order->getStockId());
        }
        
        $this->initRuleData();
        $availableProductTypes  = Mage::getConfig()
            ->getNode('adminhtml/sales/order/create/available_product_types')
            ->asArray();
        foreach ($order->getItemsCollection(array_keys($availableProductTypes), true) as $orderItem) {
            if (!$orderItem->getParentItem()) {
                if ($order->getReordered()) {
                    $qty                    = $orderItem->getQtyOrdered();
                } else {
                    $qty                    = $orderItem->getQtyOrdered() - $orderItem->getQtyShipped() - $orderItem->getQtyInvoiced();
                }

                if ($qty > 0) {
                    $item                   = $this->initFromOrderItem($orderItem, $qty);
                    if (is_string($item)) {
                        Mage::throwException($item);
                    }
                }
            }
        }

        if ($helper->getVersionHelper()->isGe1800()) {
            $shippingAddress        = $order->getShippingAddress();
            if ($shippingAddress) {
                $addressDiff            = array_diff_assoc($shippingAddress->getData(), $order->getBillingAddress()->getData());
                unset($addressDiff['address_type'], $addressDiff['entity_id']);
                $shippingAddress->setSameAsBilling(empty($addressDiff));
            }
        }

        $this->_initBillingAddressFromOrder($order);
        $this->_initShippingAddressFromOrder($order);
        $quote                  = $this->getQuote();
        if (!$quote->isVirtual() && $this->getShippingAddress()->getSameAsBilling()) {
            $this->setShippingAsBilling(1);
        }

        $this->setShippingMethod($order->getShippingMethod());
        $quote->getShippingAddress()->setShippingDescription($order->getShippingDescription());
        $quote->getPayment()->addData($order->getPayment()->getData());
        $orderCouponCode = $order->getCouponCode();
        if ($orderCouponCode) {
            $quote->setCouponCode($orderCouponCode);
        }

        if ($quote->getCouponCode()) {
            $quote->collectTotals();
        }

        Mage::helper('core')->copyFieldset('sales_copy_order', 'to_edit', $order, $quote);
        Mage::dispatchEvent('sales_convert_order_to_quote', array('order' => $order, 'quote' => $quote));
        if (!$order->getCustomerId()) {
            $quote->setCustomerIsGuest(true);
        }
       
        $quote->applyStocks();
        
        if ($session->getUseOldShippingMethod(true)) {
            $this->collectShippingRates();
        } else {
            $this->collectRates();
        }

        $quote->save();
        return $this;
    }
    /**
     * Set shipping method
     * 
     * @return MP_Warehouse_Model_Adminhtml_Sales_Order_Create
     */
    public function setShippingMethod($method)
    {
        $config = $this->getWarehouseHelper()->getConfig();
        if (!$config->isMultipleMode() || !$config->isSplitOrderEnabled()) {
            parent::setShippingMethod($method);
            return $this;
        }

        foreach ($this->getQuote()->getAllShippingAddresses() as $address) {
            $stockId = $address->getStockId();
            $shippingMethod = (is_array($method)) ? ((isset($method[$stockId])) ? $method[$stockId] : null) : $method;
            if ($shippingMethod) {
                $address->setShippingMethod($shippingMethod);
            }
        }

        $this->setRecollect(true);
        return $this;
    }
    /**
     * Reset shipping method
     * 
     * @return MP_Warehouse_Model_Adminhtml_Sales_Order_Create
     */
    public function resetShippingMethod()
    {
        $config = $this->getWarehouseHelper()->getConfig();
        if (!$config->isMultipleMode() || !$config->isSplitOrderEnabled()) {
            parent::resetShippingMethod();
            return $this;
        }

        foreach ($this->getQuote()->getAllShippingAddresses() as $address) {
            $address->setShippingMethod(false);
            $address->removeAllShippingRates();
        }

        return $this;
    }
    /**
     * Set shipping address
     * 
     * @return MP_Warehouse_Model_Adminhtml_Sales_Order_Create
     */
    public function setShippingAddress($address)
    {
        parent::setShippingAddress($address);
        $config = $this->getWarehouseHelper()->getConfig();
        if (!$config->isMultipleMode() || !$config->isSplitOrderEnabled()) {
            return $this;
        }

        $shippingAddress = $this->getQuote()->getShippingAddress();
        foreach ($this->getQuote()->getAllShippingAddresses() as $_address) {
            $this->getQuote()->copyAddress($shippingAddress, $_address);
        }

        return $this;
    }
    /**
     * Set shipping as billing
     * 
     * @return MP_Warehouse_Model_Adminhtml_Sales_Order_Create
     */
    public function setShippingAsBilling($flag)
    {
        $config = $this->getWarehouseHelper()->getConfig();
        $tmpAddress = clone $this->getBillingAddress();
        $tmpAddress->unsAddressId()->unsAddressType()->unsStockId();
        $data = $tmpAddress->getData();
        $data['save_in_address_book'] = 0;
        unset($data['shipping_method']);
        if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
            foreach ($this->getQuote()->getAllShippingAddresses() as $address) {
                if ($flag) {
                    $address->addData($data);
                }

                $address->setSameAsBilling($flag);
            }
        } else {
            if ($flag) {
                $this->getShippingAddress()->addData($data);
            }

            $this->getShippingAddress()->setSameAsBilling($flag);
        }

        $this->setRecollect(true);
        return $this;
    }
    /**
     * Update quantity of order quote items
     *
     * @param   array $data
     * 
     * @return  Mage_Adminhtml_Model_Sales_Order_Create
     */
    public function updateQuoteItems($data)
    {
        $helper         = $this->getWarehouseHelper();
        $config         = $helper->getConfig();
        $productHelper  = $helper->getProductHelper();
        if (is_array($data)) {
            try {
                foreach ($data as $itemId => $info) {
                    if (!empty($info['configured'])) {
                        $item = $this->getQuote()->updateItem($itemId, new Varien_Object($info));
                        $itemQty = (float) $item->getQty();
                    } else {
                        $item = $this->getQuote()->getItemById($itemId);
                        $itemQty = (float) $info['qty'];
                    }

                    if ($item) {
                        if (isset($info['stock_id'])) {
                            $product    = $item->getProduct();
                            $stockId    = (int) $info['stock_id'];
                            if ($helper->isStockIdExists($stockId)) {
                                $productHelper->setSessionStockId($product, $stockId);
                            }
                        }
                        
                        $stockItem = $item->getStockItem();
                        if ($stockItem) {
                            if (!$stockItem->getIsQtyDecimal()) {
                                $itemQty = (int) $itemQty;
                            } else {
                                $item->setIsQtyDecimal(1);
                            }
                        }

                        $itemQty = ($itemQty > 0) ? $itemQty : 1;
                        if (isset($info['custom_price'])) {
                            $itemPrice  = $this->_parseCustomPrice($info['custom_price']);
                        } else {
                            $itemPrice = null;
                        }

                        $noDiscount = !isset($info['use_discount']);
                        if (empty($info['action']) || !empty($info['configured'])) {
                            $item->setQty($itemQty);
                            $item->setCustomPrice($itemPrice);
                            $item->setOriginalCustomPrice($itemPrice);
                            $item->setNoDiscount($noDiscount);
                            $item->getProduct()->setIsSuperMode(true);
                            $item->getProduct()->unsSkipCheckRequiredOption();
                            $item->checkData();
                        } else {
                            if (!$helper->getVersionHelper()->isGe1800()) {
                                $this->moveQuoteItem($item->getId(), $info['action'], $itemQty);
                            }
                        }

                        if ($helper->getVersionHelper()->isGe1800()) {
                            if (!empty($info['action'])) {
                                $this->moveQuoteItem($item, $info['action'], $itemQty);
                            }
                        }
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->recollectCart();
                throw $e;
            } catch (Exception $e) {
                Mage::logException($e);
            }

            $this->recollectCart();
        }

        return $this;
    }
    /**
     * Reset quote items
     *
     * @return  Mage_Adminhtml_Model_Sales_Order_Create
     */
    public function resetQuoteItems()
    {
        $helper         = $this->getWarehouseHelper();
        $productHelper  = $helper->getProductHelper();
        $quote          = $this->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if (!$product) {
                continue;
            }

            $productHelper->setSessionStockId($product, null);
        }

        $quote->reapplyStocks();
        return $this;
    }
    /**
     * Validate quote data before order creation
     * 
     * @return MP_Warehouse_Model_Adminhtml_Sales_Order_Create
     */
    protected function _validate()
    {
        $helper = $this->getWarehouseHelper();
        $config = $helper->getConfig();
        $customerId = $this->getSession()->getCustomerId();
        if (is_null($customerId)) {
            Mage::throwException(Mage::helper('adminhtml')->__('Please select a customer.'));
        }

        if (!$this->getSession()->getStore()->getId()) {
            Mage::throwException(Mage::helper('adminhtml')->__('Please select a store.'));
        }

        $items = $this->getQuote()->getAllItems();
        if (count($items) == 0) {
            $this->_errors[] = Mage::helper('adminhtml')->__('You need to specify order items.');
        }

        foreach ($items as $item) {
            $messages = $item->getMessage(false);
            if ($item->getHasError() && is_array($messages) && !empty($messages)) {
                $this->_errors = array_merge($this->_errors, $messages);
            }
        }

        if (!$this->getQuote()->isVirtual()) {
            if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
                foreach ($this->getQuote()->getAllShippingAddresses() as $address) {
                    if (!$address->getShippingMethod()) {
                        $this->_errors[] = $helper->__('Shipping method must be specified for %s warehouse.', $address->getWarehouseTitle());
                    }
                }
            } else {
                if (!$this->getQuote()->getShippingAddress()->getShippingMethod()) {
                    $this->_errors[] = Mage::helper('adminhtml')->__('Shipping method must be specified.');
                }
            }
        }

        if (!$this->getQuote()->getPayment()->getMethod()) {
            $this->_errors[] = Mage::helper('adminhtml')->__('Payment method must be specified.');
        } else {
            $method = $this->getQuote()->getPayment()->getMethodInstance();
            if (!$method) {
                $this->_errors[] = Mage::helper('adminhtml')->__('Payment method instance is not available.');
            } else {
                if (!$method->isAvailable($this->getQuote())) {
                    $this->_errors[] = Mage::helper('adminhtml')->__('Payment method is not available.');
                } else {
                    try {
                        $method->validate();
                    } catch (Mage_Core_Exception $e) {
                        $this->_errors[] = $e->getMessage();
                    }
                }
            }
        }

        if (!empty($this->_errors)) {
            foreach ($this->_errors as $error) {
                $this->getSession()->addError($error);
            }

            Mage::throwException('');
        }

        return $this;
    }
    /**
     * Create new order
     * 
     * @return array of MP_Warehouse_Model_Sales_Order
     */
    public function createOrder()
    {
        $this->_prepareCustomer();
        $this->_validate();
        $quote = $this->getQuote();
        $this->_prepareQuoteItems();
        $service = Mage::getModel('sales/service_quote', $quote);
        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();
            $originalId = $oldOrder->getOriginalIncrementId();
            if (!$originalId) {
                $originalId = $oldOrder->getIncrementId();
            }

            $orderData = array(
                'original_increment_id'     => $originalId,
                'relation_parent_id'        => $oldOrder->getId(),
                'relation_parent_real_id'   => $oldOrder->getIncrementId(),
                'edit_increment'            => $oldOrder->getEditIncrement()+1,
                'increment_id'              => $originalId.'-'.($oldOrder->getEditIncrement()+1)
            );
            $quote->setReservedOrderId($orderData['increment_id']);
            $service->setOrderData($orderData);
            
            $oldOrder->cancel();
        }

        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
            $orders = $service->submitOrders();
        } else {
            $orders = array(0 => $service->submit());
        }

        foreach ($orders as $_order) {
            $order = $_order;
            break;
        }

        if ((!$quote->getCustomer()->getId() || !$quote->getCustomer()->isInStore($this->getSession()->getStore())) && !$quote->getCustomerIsGuest()) {
            $quote->getCustomer()->setCreatedAt($order->getCreatedAt());
            $quote->getCustomer()->save()->sendNewAccountEmail('registered', '', $quote->getStoreId());;
        }

        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder               = $this->getSession()->getOrder();
            
            $oldOrder->setRelationChildId($order->getId());
            $oldOrder->setRelationChildRealId($order->getIncrementId());
            $oldOrder->save();
            $order->save();
        }

        foreach ($orders as $_order) {
            if ($this->getSendConfirmation()) {
                if ($this->getVersionHelper()->isGe1910()) {
                    $_order->queueNewOrderEmail();
                } else {
                    $_order->sendNewOrderEmail();
                }
            }

            Mage::dispatchEvent('checkout_submit_all_after', array('order' => $_order, 'quote' => $quote));
        }

        return $orders;
    }
    /**
     * Get stock identifier
     * 
     * @return int
     */
    public function getStockId()
    {
        $stockId = null;
        $helper = $this->getWarehouseHelper();
        if (!$helper->getConfig()->isMultipleMode()) {
            $stockId = (int) $this->getSession()->getStockId();
            if (!$stockId) {
                $stockId = $this->getWarehouseHelper()
                    ->getAssignmentMethodHelper()
                    ->getQuoteStockId($this->getQuote());
            }
        }

        return $stockId;
    }
    /**
     * Set stock id
     * 
     * @param int $stockId
     * 
     * @return MP_Warehouse_Model_Adminhtml_Sales_Order_Create
     */
    public function setStockId($stockId)
    {
        $this->getSession()->setStockId($stockId);
        return $this;
    }
    /**
     * Quote saving
     * 
     * @return MP_Warehouse_Model_Adminhtml_Sales_Order_Create
     */
    public function saveQuote()
    {
        if (!$this->getQuote()->getId()) {
            return $this;
        }

        if ($this->_needCollect) {
            $this->getQuote()->reapplyStocks();
            $this->getQuote()->save();
            $this->getQuote()->collectTotals();
        }

        $this->getQuote()->save();
        return $this;
    }
    /**
     * Add product to current order quote
     * $product can be either product id or product model
     * $config can be either buyRequest config, or just qty
     *
     * @param   int|Mage_Catalog_Model_Product $product
     * @param   float|array|Varien_Object $config
     * @return  Mage_Adminhtml_Model_Sales_Order_Create
     */
    public function addProduct($product, $config = 1)
    {
        $helper = $this->getWarehouseHelper();
        if (!is_array($config) && !($config instanceof Varien_Object)) {
            $config = array('qty' => $config);
        }

        $config = new Varien_Object($config);
        if (!($product instanceof Mage_Catalog_Model_Product)) {
            $productId = $product;
            $product = Mage::getModel('catalog/product')
                ->setStore($this->getSession()->getStore())
                ->setStoreId($this->getSession()->getStoreId())
                ->load($product);
            if (!$product->getId()) {
                Mage::throwException(
                    Mage::helper('adminhtml')->__('Failed to add a product to cart by id "%s".', $productId)
                );
            }
        }
        
        $stockId = null;
        if ($helper->getConfig()->isMultipleMode()) {
            if (isset($config['stock_id']) && intval($config['stock_id'])) {
                $stockId = intval($config['stock_id']);
            }
        } else {
            $stockId = $this->getStockId();
        }

        if (!is_null($stockId)) {
            $stockItem = $helper->getCatalogInventoryHelper()->getStockItemCached($product->getId(), $stockId);
            $stockItem->assignProduct($product);
        }
        
        $stockItem = $product->getStockItem();
        if ($stockItem && $stockItem->getIsQtyDecimal()) {
            $product->setIsQtyDecimal(1);
        } else {
            if ($this->getVersionHelper()->isGe1700()) {
                $config->setQty((int) $config->getQty());
            }
        }

        $product->setCartQty($config->getQty());
        $item = $this->getQuote()->addProductAdvanced(
            $product, $config, Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_FULL
        );
        if (is_string($item)) {
            if ($product->getTypeId() != Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE) {
                $item = $this->getQuote()->addProductAdvanced(
                    $product, $config, Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_LITE
                );
            }

            if (is_string($item)) {
                Mage::throwException($item);
            }
        }

        $item->checkData();
        $this->setRecollect(true);
        return $this;
    }
}
