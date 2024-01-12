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
 * One page checkout
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Checkout_Type_Onepage 
    extends Mage_Checkout_Model_Type_Onepage
{
    /**
     * Error message of "customer already exists"
     *
     * @var string
     */
    protected $_customCustomerEmailExistsMessage = '';
    /**
     * Quotes
     * 
     * @var array of MP_Warehouse_Model_Sales_Quote
     */
    protected $_quotes;
    /**
     * Class constructor
     * Set customer already exists message
     */
    public function __construct()
    {
        parent::__construct();
        $this->_customCustomerEmailExistsMessage = Mage::helper('checkout')->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.');
    }
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
     * Save single mode shipping method
     * 
     * @param   array $shippingMethods
     * 
     * @return  array
     */
    protected function saveSingleModeShippingMethod($shippingMethods)
    {
        return parent::saveShippingMethod($shippingMethods);
    }
    /**
     * Save multiple mode shipping method
     * 
     * @param   array $shippingMethods
     * 
     * @return  array
     */
    protected function saveMultipleModeShippingMethod($shippingMethods)
    {
        $helper             = $this->getWarehouseHelper();
        $config             = $helper->getConfig();
        $quote              = $this->getQuote();
        $result             = array();
        $messages           = array();
        $error              = false;
        if (!count($shippingMethods)) {
            $error = true;
            array_push($messages, $helper->__('No shipping method selected.'));
        }

        if (!$error) {
            foreach ($quote->getAllShippingAddresses() as $address) {
                if ($address->isVirtual()) {
                    continue;
                }

                $stockId = (int) $address->getStockId();
                $warehouse = $helper->getWarehouseByStockId($stockId);
                if (isset($shippingMethods[$stockId])) {
                    $shippingMethod = $shippingMethods[$stockId];
                    $rate = $address->getShippingRateByCode($shippingMethod);
                    if (!$rate) {
                        $error = true;
                        if ($config->isInformationVisible()) {
                            array_push(
                                $messages, sprintf(
                                    $helper->__('Incorrect shipping method selected for %s warehouse.'), 
                                    $warehouse->getTitle()
                                )
                            );
                        } else {
                            array_push($messages, $helper->__('Incorrect shipping method selected.'));
                        }
                    }
                } else {
                    $error = true;
                    if ($config->isInformationVisible()) {
                        array_push(
                            $messages, sprintf(
                                $helper->__('No shipping method selected for %s warehouse.'), 
                                $warehouse->getTitle()
                            )
                        );
                    } else {
                        array_push($messages, $helper->__('No shipping method selected.'));
                    }
                }
            }
        }

        if (!$error) {
            foreach ($quote->getAllShippingAddresses() as $address) {
                if ($address->isVirtual()) {
                    continue;
                }

                $stockId = (int) $address->getStockId();
                if (isset($shippingMethods[$stockId])) {
                    $shippingMethod = $shippingMethods[$stockId];
                    $address->setShippingMethod($shippingMethod);
                }
            }

            $quote->collectTotals()->save();
            $this->getCheckout()->setStepData('shipping_method', 'complete', true)
                ->setStepData('payment', 'allow', true);
        }

        if ($error) {
            $result = array('error' => -1, 'message' => implode("\r\n", $messages));
        }

        return $result;
    }
    /**
     * Specify quote shipping method
     *
     * @param   array $shippingMethods
     * 
     * @return  array
     */
    public function saveShippingMethod($shippingMethods)
    {
        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isMultipleMode()) {
            if ($config->isSplitOrderEnabled()) {
                return $this->saveMultipleModeShippingMethod($shippingMethods);
            } else {
                return $this->saveSingleModeShippingMethod($shippingMethods);
            }
        } else {
            return $this->saveSingleModeShippingMethod($shippingMethods);
        }
    }
    /**
     * Save shipping address
     *
     * @param array $data
     * @param int $customerAddressId
     * 
     * @return $this
     */
    public function saveShipping($data, $customerAddressId)
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        if (empty($data)) {
            return array(
                'error'                 => -1, 
                'message'               => Mage::helper('checkout')->__('Invalid data.')
            );
        }

        $quote                  = $this->getQuote();
        $address                = $quote->getShippingAddress();
        $addresses              = $quote->getAllShippingAddresses();
        $addresses              = array_reverse($addresses);
        $addressForm            = Mage::getModel('customer/form');
        $addressForm
            ->setFormCode('customer_address_edit')
            ->setEntityType('customer_address')
            ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
        
        if (!empty($customerAddressId)) {
            $customerAddress        = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $quote->getCustomerId()) {
                    return array(
                        'error'                 => 1, 
                        'message'               => Mage::helper('checkout')->__('Customer Address is not valid.')
                    );
                }

                if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
                    foreach ($addresses as $_address) {
                        $_address
                            ->importCustomerAddress($customerAddress)
                            ->setSaveInAddressBook(0);
                    }
                } else {
                    $address
                        ->importCustomerAddress($customerAddress)
                        ->setSaveInAddressBook(0);
                }

                $addressForm->setEntity($address);
                $addressErrors          = $addressForm->validateData($address->getData());
                if ($addressErrors !== true) {
                    return array(
                        'error'                 => 1, 
                        'message'               => $addressErrors
                    );
                }
            }
        } else {
            $addressForm->setEntity($address);
            $addressData            = $addressForm->extractData($addressForm->prepareRequest($data));
            $addressErrors          = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                return array(
                    'error'                 => 1, 
                    'message'               => $addressErrors
                );
            }

            if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
                foreach ($addresses as $_address) {
                    $addressForm->setEntity($_address);
                    $addressForm->compactData($addressData);
                }

                $addressForm->setEntity($address);
            } else {
                $addressForm->compactData($addressData);
            }

            if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
                foreach ($addresses as $_address) {
                    foreach ($addressForm->getAttributes() as $attribute) {
                        if (!isset($data[$attribute->getAttributeCode()])) {
                            $_address->setData($attribute->getAttributeCode(), NULL);
                        }
                    }

                    if ($this->getVersionHelper()->isGe1700()) {
                        $_address->setCustomerAddressId(null);
                    }

                    $_address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
                    $_address->setSameAsBilling(empty($data['same_as_billing']) ? 0 : 1);
                }
            } else {
                foreach ($addressForm->getAttributes() as $attribute) {
                    if (!isset($data[$attribute->getAttributeCode()])) {
                        $address->setData($attribute->getAttributeCode(), NULL);
                    }
                }

                if ($this->getVersionHelper()->isGe1700()) {
                    $address->setCustomerAddressId(null);
                }

                $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
                $address->setSameAsBilling(empty($data['same_as_billing']) ? 0 : 1);
            }
        }

        $quote->reapplyStocks();
        $quote->save();
        
        $address                = $quote->getShippingAddress();
        $addresses              = $quote->getAllShippingAddresses();
        if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
            foreach ($addresses as $_address) {
                $_address->implodeStreetAddress();
                $_address->setCollectShippingRates(true);
            }
        } else {
            $address->implodeStreetAddress();
            $address->setCollectShippingRates(true);
        }

        $validateRes            = $address->validate();
        if ($validateRes !== true) {
            return array(
                'error'                 => 1, 
                'message'               => $validateRes
            );
        }

        $quote
            ->collectTotals()
            ->save();
        $this
            ->getCheckout()
            ->setStepData('shipping', 'complete', true)
            ->setStepData('shipping_method', 'allow', true);
        return array();
    }
    /**
     * Save billing address
     *
     * @param array $data
     * @param int $customerAddressId
     * 
     * @return $this
     */
    public function saveBilling($data, $customerAddressId)
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        if (empty($data)) { 
            return array(
                'error'                 => -1, 
                'message'               => Mage::helper('checkout')->__('Invalid data.')
            ); 
        }

        $address                = $this
            ->getQuote()
            ->getBillingAddress();
        $addressForm            = Mage::getModel('customer/form');
        $addressForm
            ->setFormCode('customer_address_edit')
            ->setEntityType('customer_address')
            ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
        if (!empty($customerAddressId)) {
            $customerAddress        = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array(
                        'error'                 => 1, 
                        'message'               => Mage::helper('checkout')->__('Customer Address is not valid.')
                    );
                }

                $address
                    ->importCustomerAddress($customerAddress)
                    ->setSaveInAddressBook(0);
                $addressForm->setEntity($address);
                $addressErrors          = $addressForm->validateData($address->getData());
                if ($addressErrors !== true) {
                    return array(
                        'error'                 => 1, 
                        'message'               => $addressErrors
                    );
                }
            }
        } else {
            $addressForm->setEntity($address);
            $addressData            = $addressForm->extractData($addressForm->prepareRequest($data));
            $addressErrors          = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                return array(
                    'error'                 => 1, 
                    'message'               => array_values($addressErrors)
                );
            }

            $addressForm->compactData($addressData);
            foreach ($addressForm->getAttributes() as $attribute) {
                if (!isset($data[$attribute->getAttributeCode()])) {
                    $address->setData($attribute->getAttributeCode(), NULL);
                }
            }

            if ($this->getVersionHelper()->isGe1700()) {
                $address->setCustomerAddressId(null);
            }

            $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
        }
        
        if ($helper->getVersionHelper()->isGe1800()) {
            if (!$address->getEmail() && $this->getQuote()->getCustomerEmail()) {
                $address->setEmail($this->getQuote()->getCustomerEmail());
            }
        }

        $validateRes            = $address->validate();
        if ($validateRes !== true) {
            return array(
                'error'                 => 1, 
                'message'               => $validateRes
            );
        }

        $address->implodeStreetAddress();
        if (true !== ($result = $this->_validateCustomerData($data))) {
            return $result;
        }

        if (!$this->getQuote()->getCustomerId() && 
            (self::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod())
        ) {
            if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
                return array(
                    'error'                 => 1, 
                    'message'               => $this->_customCustomerEmailExistsMessage
                );
            }
        }

        if (!$this->getQuote()->isVirtual()) {
            $usingCase              = isset($data['use_for_shipping']) ? (int) $data['use_for_shipping'] : 0;
            switch($usingCase) {
                case 0: 
                    foreach ($this->getQuote()->getAllShippingAddresses() as $shipping) {
                        $shipping->setSameAsBilling(0);
                    }
                    break;
                case 1:
                    $billing                = clone $address;
                    $billing->unsAddressId()->unsAddressType()->unsStockId();
                    if ($this->getVersionHelper()->isGe1700()) {
                        $requiredBillingAttributes = array('customer_address_id');
                    }

                    foreach ($this->getQuote()->getShippingAddress()->getData() as $shippingKey => $shippingValue) {
                        if ($this->getVersionHelper()->isGe1700()) {
                            if (!is_null($shippingValue) && !is_null($billing->getData($shippingKey)) && 
                                !isset($data[$shippingKey]) && !in_array($shippingKey, $requiredBillingAttributes)
                            ) {
                                $billing->unsetData($shippingKey);
                            }
                        } else {
                            if (!is_null($shippingValue) && !is_null($billing->getData($shippingKey)) && 
                                !isset($data[$shippingKey])
                            ) {
                                $billing->unsetData($shippingKey);
                            }
                        }
                    }

                    foreach ($this->getQuote()->getAllShippingAddresses() as $shipping) {
                        $shippingMethod         = $shipping->getShippingMethod();
                        $shipping
                            ->addData($billing->getData())
                            ->setSameAsBilling(1)
                            ->setSaveInAddressBook(0)
                            ->setShippingMethod($shippingMethod);
                    }

                    $this->getQuote()->reapplyStocks();
                    $this->getQuote()->save();
                    foreach ($this->getQuote()->getAllShippingAddresses() as $_address) {
                        $_address->implodeStreetAddress();
                        $_address->setCollectShippingRates(true);
                    }

                    $this->getCheckout()->setStepData('shipping', 'complete', true);
                    break;
            }
        }

        $this->getQuote()->collectTotals();
        $this->getQuote()->save();
        $this->getCheckout()
            ->setStepData('billing', 'allow', true)
            ->setStepData('billing', 'complete', true)
            ->setStepData('shipping', 'allow', true);
        return array();
    }
    /**
     * Prepare quote and returns is new customer flag
     *
     * @return bool
     */
    protected function _prepareCheckoutMethodCustomerQuote()
    {
        $isNewCustomer = false;
        switch ($this->getCheckoutMethod()) {
            case self::METHOD_GUEST: 
                $this->_prepareGuestQuote();
                break;
            case self::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();
                $isNewCustomer = true;
                break;
            default: 
                $this->_prepareCustomerQuote();
                break;
        }

        return $isNewCustomer;
    }
    /**
     * Get checkout session
     * 
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }
    /**
     * Create orders based on checkout type.
     *
     * @return MP_Warehouse_Model_Checkout_Type_Onepage
     */
    public function saveOrders()
    {
        $this->validate();
        $isNewCustomer = $this->_prepareCheckoutMethodCustomerQuote();
        $service = Mage::getModel('sales/service_quote', $this->getQuote());
        //$service->submitAll();
        $service->submitAllMultiple();
        $orders = $service->getOrders();
        if ($isNewCustomer) {
            try {
                $this->_involveNewCustomer();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        $quote = $this->getQuote();
        $quoteId = $quote->getId();
        $checkoutSession = $this->getCheckoutSession();
        $checkoutSession
            ->setLastQuoteId($quoteId)
            ->setLastSuccessQuoteId($quoteId)
            ->clearHelperData();
        if (count($orders)) {
            $orderIds       = array();
            $redirectUrls   = array();
            $realOrderIds   = array();

            //colocar o salvamento da venda aqui!!!
            //Mage::getModel('totalmetrica_slipt/data')->insertOrders($orders);
            Mage::getModel('payafter/payafter')->insertOrders($orders);

            foreach ($orders as $order) {
                Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order' => $order, 'quote' => $quote));
                $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
                if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
                    try {
                        if ($this->getVersionHelper()->isGe1910()) {
                            $order->queueNewOrderEmail();
                        } else {
                            $order->sendNewOrderEmail();
                        }
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }

                $checkoutSession
                    ->setLastOrderId($order->getId())
                    ->setRedirectUrl($redirectUrl)
                    ->setLastRealOrderId($order->getIncrementId());
                $agreement = $order->getPayment()->getBillingAgreement();
                if ($agreement) {
                    $checkoutSession->setLastBillingAgreementId($agreement->getId());
                }

                $orderIds[]         = $order->getId();
                $realOrderIds[]     = $order->getIncrementId();
                $redirectUrls[]     = $redirectUrl;
            }

            $checkoutSession->setOrderIds($orderIds)
                ->setRedirectUrls($redirectUrls)
                ->setRealOrderIds($realOrderIds);
            
            Mage::getSingleton('core/session')->setOrderIds($orderIds);
        }

        $profiles = $service->getRecurringPaymentProfiles();
        if ($profiles) {
            $ids = array();
            foreach ($profiles as $profile) { 
                $ids[] = $profile->getId(); 
            }

            $checkoutSession->setLastRecurringProfileIds($ids);
        }

        foreach ($orders as $order) {
            Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote, 'recurring_profiles' => $profiles));
        }

        return $this;
    }
}
