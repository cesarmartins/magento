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
require_once 'Mage/Adminhtml/controllers/Sales/Order/CreateController.php';
/**
 * Orders creation process controller
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Adminhtml_Sales_Order_CreateController 
    extends Mage_Adminhtml_Sales_Order_CreateController
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
     * Initialize order creation session data
     * 
     * @return self
     */
    protected function _initSession()
    {
        $helper                 = $this->getWarehouseHelper();
        parent::_initSession();
        if (!$helper->getConfig()->isMultipleMode()) {
            $stockId                = (int) $this->getRequest()
                ->getParam('stock_id');
            if ($stockId && $helper->isStockIdExists($stockId)) {
                $helper->setSessionStockId($stockId);
                $orderCreate            = $this->_getOrderCreateModel();
                $orderCreate->setStockId(null);
                $orderCreate->setRecollect(true);
            }
        }

        return $this;
    }
    /**
     * Saving quote and create order
     */
    public function saveAction()
    {
        $helper                 = $this->getWarehouseHelper();
        try {
            if ($helper->getVersionHelper()->isGe1510()) {
                $this->_processActionData('save');
            } else {
                $this->_processData('save');
            }

            $paymentData            = $this->getRequest()->getPost('payment');
            if ($paymentData) {
                if ($helper->getVersionHelper()->isGe1800()) {
                    $paymentData['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_INTERNAL | 
                    Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY | 
                    Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY | 
                    Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX | 
                    Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                }

                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            }

            $orderCreate            = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($this->getRequest()->getPost('order'));
            $orderCreate->getQuote()
                ->reapplyStocks();
            $orderCreate->getQuote()
                ->collectTotals();
            $orders                 = $orderCreate->createOrder();
            $this->_getSession()
                ->clear();
            if (count($orders) > 1) {
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess($helper->__('The orders has been created.'));
                $this->_redirect('*/sales_order');
            } else {
                $order = array_shift($orders);
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess($this->__('The order has been created.'));
                if ($helper->getVersionHelper()->isGe1800()) {
                    if (Mage::getSingleton('admin/session')
                        ->isAllowed('sales/order/actions/view')) {
                        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
                    } else {
                        $this->_redirect('*/sales_order/index');
                    }
                } else {
                    $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
                }
            }
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $this->_getOrderCreateModel()
                ->saveQuote();
            $message = $e->getMessage();
            if(!empty($message) ) {
                $this->_getSession()
                    ->addError($message);
            }

            $this->_redirect('*/*/');
        } catch (Mage_Core_Exception $e){
            $message = $e->getMessage();
            if(!empty($message) ) {
                $this->_getSession()
                    ->addError($message);
            }

            $this->_redirect('*/*/');
        }
        catch (Exception $e){
            $this->_getSession()
                ->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
            $this->_redirect('*/*/');
        }
    }
}
