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
 * Customer helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Customer
    extends MP_Warehouse_Helper_Core_Abstract
{
    /**
     * Customer groups
     *
     * @var array of Mage_Customer_Model_Group
     */
    protected $_groups;
    /**
     * Get group
     * 
     * @return Mage_Customer_Model_Group
     */
    public function getGroup()
    {
        return Mage::getModel('customer/group');
    }
    /**
     * Get group resource
     * 
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    public function getGroupResource()
    {
        return Mage::getResourceSingleton('customer/group');
    }
    /**
     * Get customer group collection
     * 
     * @return Mage_Customer_Model_Resource_Group_Collection
     */
    public function getGroupCollection()
    {
        return Mage::getModel('customer/group')->getResourceCollection();
    }
    /**
     * Get groups
     * 
     * @return array of Mage_Customer_Model_Group
     */
    public function getGroups()
    {
        if (is_null($this->_groups)) {
            $groups = array();
            foreach ($this->getGroupCollection() as $group) {
                $groups[(int) $group->getId()] = $group;
            }

            $this->_groups = $groups;
        }

        return $this->_groups;
    }
    /**
     * Get group ids
     * 
     * @return array
     */
    public function getGroupIds()
    {
        return array_keys($this->getGroup());
    }
    /**
     * Get group by id
     * 
     * @param int $groupId
     * 
     * @return Mage_Customer_Model_Group
     */
    public function getGroupById($groupId)
    {
        $groups = $this->getGroups();
        if (isset($groups[$groupId])) {
            return $groups[$groupId];
        } else {
            return null;
        }
    }
    /**
     * Check if group id exists
     * 
     * @return bool
     */
    public function isGroupIdExists($groupId)
    {
        return in_array($groupId, $this->getGroupIds());
    }
    /**
     * Get group code by id
     * 
     * @param int $groupId
     * 
     * @return string
     */
    public function getGroupCodeById($groupId)
    {
        $group = $this->getGroupById($groupId);
        if ($group) {
            return $group->getCode();
        } else {
            return null;
        }
    }
    /**
     * Get group by code
     * 
     * @param string $code
     * 
     * @return Mage_Customer_Model_Group
     */
    public function getGroupByCode($code)
    {
        $group = null;
        foreach ($this->getGroups() as $_group) {
            if ($_group->getCode() == $code) {
                $group = $_group;
                break;
            }
        }

        return $group;
    }
    /**
     * Get group id by code
     * 
     * @param string $code
     * 
     * @return int
     */
    public function getGroupIdByCode($code)
    {
        $group = $this->getGroupByCode($code);
        if ($group) {
            return $group->getId();
        } else {
            return null;
        }
    }
    /**
     * Get group id by code or id
     * 
     * @param mixed $codeOrId
     * 
     * @return int
     */
    public function getGroupIdByCodeOrId($codeOrId)
    {
        if ($this->isGroupIdExists($codeOrId)) {
            return $codeOrId;
        }

        return $this->getGroupIdByCode($codeOrId);
    }
    /**
     * Get group options
     * 
     * @param bool $required
     * @param string $emptyLabel
     * @param string $emptyValue
     * 
     * @return array
     */
    public function getGroupOptions($required = true, $emptyLabel = '', $emptyValue = '')
    {
        $options = array();
        foreach ($this->getGroups() as $group) {
            array_push(
                $options, array(
                'value' => (int) $group->getId(), 
                'label' => $group->getCode(), 
                )
            );
        }

        $this->getCoreHelper()->prepareOptions($options, $required, $emptyLabel, $emptyValue);
        return $options;
    }
    /**
     * Get customer tax class options
     * 
     * @param bool $required
     * @param string $emptyLabel
     * @param string $emptyValue
     * 
     * @return array
     */
    public function getCustomerTaxClassOptions($required = true, $emptyLabel = '', $emptyValue = '')
    {
        $options = Mage::getSingleton('tax/class_source_customer')->toOptionArray();
        $this->getCoreHelper()->prepareOptions($options, $required, $emptyLabel, $emptyValue);
        return $options;
    }
    /**
     * Get session
     * 
     * @return Mage_Customer_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('customer/session');
    }
    /**
     * Get customer sharing configuration
     *
     * @return Mage_Customer_Model_Config_Share
     */
    public function getCustomerConfigShare()
    {
        return $this->getSession()->getCustomerConfigShare();
    }
    /**
     * Get customer group id
     * 
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->getSession()->getCustomerGroupId();
    }
    /**
     * Get current customer group id
     * 
     * @return int
     */
    public function getCurrentCustomerGroupId()
    {
        $coreHelper = $this->getCoreHelper();
        if ($coreHelper->isAdmin() && $coreHelper->isCreateOrderRequest()) {
            return $coreHelper->getCheckoutHelper()
                ->getQuote()
                ->getCustomerGroupId();
        } else {
            return $this->getCustomerGroupId();
        }
    }
    /**
     * Get customer id
     * 
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getSession()->getCustomerId();
    }
    /**
     * Get customer
     * 
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return $this->getSession()->getCustomer();
    }
    /**
     * Get customer addresses
     * 
     * @return array
     */
    public function getAddresses()
    {
        if ($this->getCustomer() && count($this->getCustomer()->getAddresses())) {
            return $this->getCustomer()->getAddresses();
        }

        return array();
    }
    /**
     * Get customer address id by address
     * 
     * @param Varien_Object $address
     * 
     * @return int | null
     */
    public function getAddressIdByAddress($address)
    {
        $addressId      = null;
        $addressHelper  = $this->getCoreHelper()->getAddressHelper();
        foreach ($this->getAddresses() as $_address) {
            if ($addressHelper->compare($address, $_address)) {
                $addressId = $_address->getId();
                break;
            }
        }

        return $addressId;
    }
    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->getSession()->isLoggedIn();
    }
}