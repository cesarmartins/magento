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
 * Customer address block
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_CustomerLocator_Customer_Address
    extends Mage_Core_Block_Template
{
    /**
     * Address
     * 
     * @var Varien_Object
     */
    protected $_address;
    /**
     * Get customer locator helper
     *
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    protected function getCustomerLocatorHelper()
    {
        return Mage::helper('warehouse/customerLocator_data');
    }
    /**
     * Get address helper
     * 
     * @return MP_Warehouse_Helper_Core_Address
     */
    protected function getAddressHelper()
    {
        return $this->getCustomerLocatorHelper()->getAddressHelper();
    }
    /**
     * Get customer helper
     * 
     * @return MP_Warehouse_Helper_Core_Customer
     */
    protected function getCustomerHelper()
    {
        return $this->getCustomerLocatorHelper()->getCustomerHelper();
    }
    /**
     * Get address
     *
     * @return Varien_Object
     */
    public function getAddress()
    {
        if (is_null($this->_address)) {
            $this->_address = $this->getCustomerLocatorHelper()->getCustomerAddress();
        }

        return $this->_address;
    }
    /**
     * Get country identifier
     * 
     * @return string
     */
    public function getCountryId()
    {
        return $this->getAddress()->getCountryId();
    }
    /**
     * Get region identifier
     * 
     * @return string
     */
    public function getRegionId()
    {
        return $this->getAddress()->getRegionId();
    }
    /**
     * Get region
     * 
     * @return string
     */
    public function getRegion()
    {
        return $this->getAddress()->getRegion();
    }
    /**
     * Get city
     * 
     * @return string
     */
    public function getCity()
    {
        return $this->getAddress()->getCity();
    }
    /**
     * Get postal code
     * 
     * @return string
     */
    public function getPostcode()
    {
        return $this->getAddress()->getPostcode();
    }
    /**
     * Get street 1
     * 
     * @return string
     */
    public function getStreet1()
    {
        return $this->getAddressHelper()->getStreet($this->getAddress(), 1);
    }
    /**
     * Get street 2
     * 
     * @return string
     */
    public function getStreet2()
    {
        return $this->getAddressHelper()->getStreet($this->getAddress(), 2);
    }
    /**
     * Get addresses
     * 
     * @return Mage_Customer_Model_Customer
     */
    protected function getAddresses()
    {
        $addresses      = array();
        $customerHelper = $this->getCustomerHelper();
        if ($customerHelper->isLoggedIn()) {
            $addresses = $customerHelper->getCustomer()->getAddresses();
        }

        return $addresses;
    }
    /**
     * Check if customer has addresses
     * 
     * @return bool
     */
    public function hasAddresses()
    {
        return (count($this->getAddresses())) ? true : false;
    }
    /**
     * Get addresses options
     * 
     * @return array 
     */
    protected function getAddressesOptions()
    {
        $helper = $this->getCustomerLocatorHelper();
        $options = array();
        $addresses = $this->getAddresses();
        array_push(
            $options, 
            array(
                'value' => '', 
                'label' => $helper->__('Please select address')
            )
        );
        foreach ($addresses as $address) {
            array_push(
                $options, 
                array(
                    'value' => $address->getId(), 
                    'label' => $address->format('oneline')
                )
            );
        }

        return $options;
    }
    /**
     * Get customer address html select
     * 
     * @return string
     */
    public function getAddressHtmlSelect()
    {
        $helper     = $this->getCustomerLocatorHelper();
        $addressId  = null;
        $select     = $this->getLayout()->createBlock('core/html_select')
            ->setName('address_id')
            ->setId('address_id')
            ->setTitle($helper->__('Address'))
            ->setClass('validate-select')
            ->setValue($addressId)
            ->setOptions($this->getAddressesOptions())
            ->setExtraParams('onchange="customerAddressIdForm.submit();"');
        return $select->getHtml();
    }
}
