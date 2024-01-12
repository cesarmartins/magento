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
 * Sales order pending payment
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Sales_Order_Pendingpayment 
    extends Mage_Core_Block_Template
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
     * Constructor
     */
    public function __construct()
    {
        $helper         = $this->getWarehouseHelper();
        $orderHelper    = $helper->getOrderHelper();
        parent::__construct();
        $this->setTemplate('warehouse/sales/order/pendingpayment.phtml');
        $customer       = Mage::getSingleton('customer/session')->getCustomer();
        $orders         = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addFieldToFilter('state', $orderHelper->getPendingPaymentState())
            ->setOrder('created_at', 'desc')
            ->setFlag('appendStockIds');
        $this->setOrders($orders);
        $layout         = Mage::app()->getFrontController()->getAction()->getLayout();
        $rootBlock      = $layout->getBlock('root');
        if ($rootBlock) {
            $rootBlock->setHeaderTitle($helper->__('My Pending Payments'));
        }
    }
    /**
     * Prepare layout
     * 
     * @return MP_Warehouse_Block_Sales_Order_Pendingpayment
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()
            ->createBlock('page/html_pager', 'warehouse.sales.order.pendingpayment.pager')
            ->setCollection($this->getOrders());
        $this->setChild('pager', $pager);
        $this->getOrders()->load();
        return $this;
    }
    /**
     * Get pager HTML
     * 
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    /**
     * Get make payment URL
     * 
     * @param Mage_Sales_Model_Order $order
     * 
     * @return string
     */
    public function getMakePaymentUrl($order)
    {
        return $this->getWarehouseHelper()->getOrderHelper()->getMakePaymentUrl($order);
    }
    /**
     * Get back URL
     * 
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
