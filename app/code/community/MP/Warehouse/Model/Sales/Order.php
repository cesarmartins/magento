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
 * Order
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Sales_Order 
    extends Mage_Sales_Model_Order
{
    /**
     * Event type names for order emails
     */
    const EMAIL_EVENT_NAME_NEW_WAREHOUSE_ORDER              = 'new_warehouse_order';
    const EMAIL_EVENT_NAME_UPDATE_WAREHOUSE_ORDER           = 'update_warehouse_order';
    /**
     * XML configuration paths
     */
    const XML_PATH_WAREHOUSE_EMAIL_TEMPLATE                 = 'sales_email/warehouse_order/template';
    const XML_PATH_WAREHOUSE_EMAIL_GUEST_TEMPLATE           = 'sales_email/warehouse_order/guest_template';
    const XML_PATH_WAREHOUSE_EMAIL_IDENTITY                 = 'sales_email/warehouse_order/identity';
    const XML_PATH_WAREHOUSE_EMAIL_COPY_TO                  = 'sales_email/warehouse_order/copy_to';
    const XML_PATH_WAREHOUSE_EMAIL_COPY_METHOD              = 'sales_email/warehouse_order/copy_method';
    const XML_PATH_WAREHOUSE_EMAIL_ENABLED                  = 'sales_email/warehouse_order/enabled';
    /**
     * Warehouses
     * 
     * @var array of MP_Warehouse_Model_Warehouse
     */
    protected $_warehouses;
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
     * Get stock identifiers
     * 
     * @return array
     */
    public function getStockIds()
    {
        $stockIds = $this->getData('stock_ids');
        if (is_null($stockIds)) {
            $stockIds = array();
            foreach ($this->getAllItems() as $item) {
                $stockId = $item->getStockId();
                if ($stockId) {
                    array_push($stockIds, $stockId);
                }
            }
        }

        return $stockIds;
    }
    /**
     * Get stock identifier
     * 
     * @return int
     */
    public function getStockId()
    {
        $stockIds = $this->getStockIds();
        return array_shift($stockIds);
    }
    /**
     * Get warehouses
     * 
     * @return array of MP_Warehouse_Model_Warehouse
     */
    public function getWarehouses()
    {
        $helper = $this->getWarehouseHelper();
        if (is_null($this->_warehouses)) {
            $warehouses = array();
            foreach ($this->getStockIds() as $stockId) {
                $warehouse = $helper->getWarehouseByStockId($stockId);
                if ($warehouse) {
                    $warehouses[$warehouse->getId()] = $warehouse;
                }
            }

            $this->_warehouses = $helper->sortWarehouses($warehouses);
        }

        return $this->_warehouses;
    }
    /**
     * Get warehouse titles
     * 
     * @return array
     */
    public function getWarehouseTitles()
    {
        $titles = array();
        foreach ($this->getWarehouses() as $warehouse) {
            array_push($titles, $warehouse->getTitle());
        }

        return $titles;
    }
    /**
     * Get warehouse
     * 
     * @return MP_Warehouse_Model_Warehouse
     */
    public function getWarehouse()
    {
        $warehouses = $this->getWarehouses();
        if (count($warehouses)) {
            return current($warehouses);
        } else {
            return null;
        }
    }
    /**
     * Get warehouse title
     * 
     * @return string
     */
    public function getWarehouseTitle()
    {
        $warehouse  = $this->getWarehouse();
        if ($warehouse) {
            return $warehouse->getTitle();
        } else {
            return null;
        }
    }
    /**
     * Check if order has several warehouses
     * 
     * @return bool
     */
    public function isMultipleWarehouse()
    {
        $warehouses = $this->getWarehouses();
        if (count($warehouses) > 1) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Clear order object data
     *
     * @param string $key data key
     * 
     * @return this
     */
    public function unsetData($key=null)
    {
        parent::unsetData($key);
        if (is_null($key)) {
            $this->_warehouses = null;
            $this->unsData('stock_ids');
        }

        return $this;
    }
    /**
     * Resets all data in object
     * so after another load it will be complete new object
     *
     * @return this
     */
    public function reset()
    {
        parent::reset();
        $this->_warehouses = null;
        return $this;
    }
    /**
     * Send email with order data to warehouse
     *
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return this
     */
    public function sendWarehouseNewOrderEmail($warehouse)
    {
        if (!$warehouse || !$warehouse->isNotify() || !$warehouse->isContactSet()) {
            return $this;
        }

        $storeId = $this->getStore()->getId();
        if (!Mage::getStoreConfigFlag(self::XML_PATH_WAREHOUSE_EMAIL_ENABLED, $storeId)) {
            return $this;
        }

        $copyTo = $this->_getEmails(self::XML_PATH_WAREHOUSE_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_WAREHOUSE_EMAIL_COPY_METHOD, $storeId);
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
        try {
            $paymentBlock = Mage::helper('payment')->getInfoBlock($this->getPayment())->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        if ($this->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_WAREHOUSE_EMAIL_GUEST_TEMPLATE, $storeId);
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_WAREHOUSE_EMAIL_TEMPLATE, $storeId);
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($warehouse->getContactEmail(), $warehouse->getContactName());
        if ($copyTo && $copyMethod == 'bcc') { foreach ($copyTo as $email) $emailInfo->addBcc($email); 
        }

        $mailer->addEmailInfo($emailInfo);
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_WAREHOUSE_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(
            array(
            'order'        => $this, 
            'warehouse'    => $warehouse, 
            'billing'      => $this->getBillingAddress(), 
            'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();
        return $this;
    }
    /**
     * Queue email with new order data to warehouse
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * @param bool $forceMode
     *
     * @return this
     * 
     * @throws Exception
     */
    public function queueWarehouseNewOrderEmail($warehouse, $forceMode = false)
    {
        if (!$warehouse || !$warehouse->isNotify() || !$warehouse->isContactSet()) {
            return $this;
        }

        $storeId                = $this->getStore()
            ->getId();
        if (!Mage::getStoreConfigFlag(self::XML_PATH_WAREHOUSE_EMAIL_ENABLED, $storeId)) {
            return $this;
        }

        $copyTo                 = $this->_getEmails(self::XML_PATH_WAREHOUSE_EMAIL_COPY_TO);
        $copyMethod             = Mage::getStoreConfig(self::XML_PATH_WAREHOUSE_EMAIL_COPY_METHOD, $storeId);
        $appEmulation           = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
        try {
            $paymentBlock           = Mage::helper('payment')->getInfoBlock($this->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()
                ->setStore($storeId);
            $paymentBlockHtml       = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        if ($this->getCustomerIsGuest()) {
            $templateId             = Mage::getStoreConfig(self::XML_PATH_WAREHOUSE_EMAIL_GUEST_TEMPLATE, $storeId);
        } else {
            $templateId             = Mage::getStoreConfig(self::XML_PATH_WAREHOUSE_EMAIL_TEMPLATE, $storeId);
        }

        $mailer                 = Mage::getModel('core/email_template_mailer');
        $emailInfo              = Mage::getModel('core/email_info');
        $emailInfo->addTo($warehouse->getContactEmail(), $warehouse->getContactName());
        if ($copyTo && $copyMethod == 'bcc') {
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }

        $mailer->addEmailInfo($emailInfo);
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo              = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_WAREHOUSE_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(
            array(
            'order'                 => $this, 
            'order'                 => $this, 
            'billing'               => $this->getBillingAddress(), 
            'payment_html'          => $paymentBlockHtml, 
            )
        );
        $emailQueue             = Mage::getModel('core/email_queue');
        $emailQueue->setEntityId($this->getId())
            ->setEntityType(self::ENTITY)
            ->setEventType(self::EMAIL_EVENT_NAME_NEW_WAREHOUSE_ORDER)
            ->setIsForceCheck(!$forceMode);
        $mailer->setQueue($emailQueue)->send();
        $this->setEmailSent(true);
        $this->_getResource()
            ->saveAttribute($this, 'email_sent');
        return $this;
    }
    /**
     * Send email with order data to warehouses
     * 
     * @return this
     */
    public function sendWarehousesNewOrderEmail()
    {
        foreach ($this->getWarehouses() as $warehouse) {
            if ($this->getWarehouseHelper()->getVersionHelper()->isGe1910()) {
                $this->queueWarehouseNewOrderEmail($warehouse);
            } else {
                $this->sendWarehouseNewOrderEmail($warehouse);
            }
        }

        return $this;
    }
    /**
     * Send email with order data
     *
     * @return this
     */
    public function sendNewOrderEmail()
    {
        parent::sendNewOrderEmail();
        $this->sendWarehousesNewOrderEmail();
        return $this;
    }
}
