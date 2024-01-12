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

//if(Mage::helper('core')->isModuleEnabled('IWD_Opc'))
//{
//	require_once 'IWD/Opc/controllers/Checkout/OnepageController.php';
//	class MP_Warehouse_Opc_JsonController_Extends
//		extends IWD_Opc_Checkout_OnepageController {}
//
//} else {
//
	require_once 'Mage/Checkout/controllers/OnepageController.php';
//	class MP_Warehouse_Opc_JsonController_Extends
//		extends Mage_Checkout_OnepageController {}
//
//}

/**
 * One page checkout controller
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Checkout_OnepageController
    extends Mage_Checkout_OnepageController
//    extends MP_Warehouse_Opc_JsonController_Extends
{
    /**
     * Orders
     * 
     * @var array of MP_Warehouse_Model_Sales_Order
     */
    protected $_orders;
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
     * Get version helper
     * 
     * @return MP_Warehouse_Helper_Core_Version
     */
    protected function getVersionHelper()
    {
        return $this->getWarehouseHelper()->getVersionHelper();
    }
    /**
     * Get orders by quoteId
     *
     * @return array of MP_Warehouse_Model_Sales_Order
     */
    protected function _getOrders()
    {
        if (is_null($this->_orders)) {
            $quoteId = $this->getOnepage()->getQuote()->getId();
            $collection = Mage::getModel('sales/order')->getCollection();
            $collection->getSelect()->where('quote_id = ?', $quoteId);
            $orders = array();
            foreach ($collection as $order) {
                $orders[$order->getId()] = $order;
            }

            if (!count($orders)) {
                throw new Mage_Payment_Model_Info_Exception(Mage::helper('core')->__("Can not create invoice. Order was not found."));
            }

            $this->_orders = $orders;
        }

        return $this->_orders;
    }
    /**
     * Create invoice
     * 
     * @param MP_Warehouse_Model_Sales_Order $order
     * 
     * @return Mage_Sales_Model_Order_Invoice
     */
    protected function __initInvoice($order)
    {
        $items = array();
        foreach ($order->getAllItems() as $item) {
            $items[$item->getId()] = $item->getQtyOrdered();
        }

        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($items);
        $invoice->setEmailSent(true)->register();
        return $invoice;
    }
    /**
     * Create order action
     */
    public function saveOrderAction()
    {
        $helper = $this->getWarehouseHelper();
        if ($this->_expireAjax()) { 
            return; 
        }

        $config = $helper->getConfig();
        $result = array();
        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $diff = array_diff($requiredAgreements, $postedAgreements);
                if ($diff) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }

            $data = $this->getRequest()->getPost('payment', false);
            if ($data) {
                if ($helper->getVersionHelper()->isGe1800()) {
                    $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                        | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                        | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                        | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                        | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                }
                
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }

            if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
                $this->getOnepage()->saveOrders();
                
                if ($this->getVersionHelper()->isGe1510() && !$this->getVersionHelper()->isGe1700()) {
                    $storeId = Mage::app()->getStore()->getId();
                    $paymentHelper = Mage::helper("payment");
                    $zeroSubTotalPaymentAction = $paymentHelper->getZeroSubTotalPaymentAutomaticInvoice($storeId);
                    foreach ($this->_getOrders() as $orderId => $order) {
                        if (($paymentHelper->isZeroSubTotal($storeId) && ($order->getGrandTotal() == 0)) && 
                            ($zeroSubTotalPaymentAction == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE) && 
                            ($paymentHelper->getZeroSubTotalOrderStatus($storeId) == 'pending')
                        ) {
                            $invoice = $this->__initInvoice($order);
                            $invoice->getOrder()->setIsInProcess(true);
                            
                            if ($this->getVersionHelper()->isGe1610()) {
                                $transactionSave = Mage::getModel('core/resource_transaction')
                                    ->addObject($invoice)->addObject($invoice->getOrder());
                                $transactionSave->save();
                            } else {
                                $invoice->save();
                            }
                        }
                    }
                }
            } else {
                $this->getOnepage()->saveOrder();
                
                if ($this->getVersionHelper()->isGe1510() && !$this->getVersionHelper()->isGe1700()) {
                    $storeId = Mage::app()->getStore()->getId();
                    $paymentHelper = Mage::helper("payment");
                    $zeroSubTotalPaymentAction = $paymentHelper->getZeroSubTotalPaymentAutomaticInvoice($storeId);
                    if ($paymentHelper->isZeroSubTotal($storeId)
                            && $this->_getOrder()->getGrandTotal() == 0
                            && $zeroSubTotalPaymentAction == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE
                            && $paymentHelper->getZeroSubTotalOrderStatus($storeId) == 'pending') {
                        $invoice = $this->_initInvoice();
                        $invoice->getOrder()->setIsInProcess(true);
                        
                        if ($this->getVersionHelper()->isGe1610()) {
                            $transactionSave = Mage::getModel('core/resource_transaction')
                                ->addObject($invoice)->addObject($invoice->getOrder());
                            $transactionSave->save();
                        } else {
                            $invoice->save();
                        }
                    }
                }
            }

            $redirectUrls   = $this->getOnepage()->getCheckout()->getRedirectUrls();
            $redirectUrl    = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error'] = false;
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if(!empty($message)) { $result['error_messages'] = $message; 
            }

            $result['goto_section'] = 'payment';
            $result['update_section'] = array('name' => 'payment-method', 'html' => $this->_getPaymentMethodsHtml());
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();
            $gotoSection = $this->getOnepage()->getCheckout()->getGotoSection();
            if ($gotoSection) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()->getCheckout()->setGotoSection(null);
            }

            $updateSection = $this->getOnepage()->getCheckout()->getUpdateSection();
            if ($updateSection) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                        'name' => $updateSection,
                        'html' => $this->$updateSectionFunction()
                    );
                }

                $this->getOnepage()->getCheckout()->setUpdateSection(null);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }

        $this->getOnepage()->getQuote()->save();

        if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
            if (isset($redirectUrls) && count($redirectUrls) == 1) {
                $result['redirect'] = current($redirectUrls);
            }
        } else {
            if (isset($redirectUrl)) {
                $result['redirect'] = $redirectUrl;
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    /**
     * Order success action
     */
    public function successAction()
    {
        $helper = $this->getWarehouseHelper();
        if ($helper->getConfig()->isMultipleMode() && $helper->getConfig()->isSplitOrderEnabled()) {
            $session        = $this->getOnepage()->getCheckout();
            if (!$session->getLastSuccessQuoteId()) {
                $this->_redirect('checkout/cart');
                return;
            }

            $lastQuoteId    = $session->getLastQuoteId();
            $lastOrderId    = $session->getLastOrderId();
            $lastRecurringProfiles = $session->getLastRecurringProfileIds();
            if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
                $this->_redirect('checkout/cart');
                return;
            }

            $orderIds       = $session->getOrderIds();
            $session->clear();
            $this->loadLayout();
            $this->_initLayoutMessages('checkout/session');
            Mage::dispatchEvent(
                'checkout_onepage_controller_success_action', 
                array(
                    'order_id'      => ((count($orderIds)) ? current($orderIds) : $lastOrderId), 
                    'order_ids'     => $orderIds
                )
            );
            $this->renderLayout();
        } else {
            parent::successAction();
        }
    }
}
