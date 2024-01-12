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
 * Core helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Data
    extends Mage_Core_Helper_Abstract
{
    /**
     * Websites
     * 
     * @var array of Mage_Core_Model_Website
     */
    protected $_websites;
    /**
     * Stores
     * 
     * @var array of Mage_Core_Model_Store
     */
    protected $_stores;
    /**
     * Get core helper
     * 
     * @return Mage_Core_Helper_Data
     */
    public function getCoreHelper()
    {
        return Mage::helper('core');
    }
    /**
     * Get string helper
     * 
     * @return Mage_Core_Helper_String
     */
    public function getStringHelper()
    {
        return Mage::helper('core/string');
    }
    /**
     * Get Http helper
     * 
     * @return Mage_Core_Helper_Http
     */
    public function getHttpHelper()
    {
        return Mage::helper('core/http');
    }
    /**
     * Get locale helper
     * 
     * @return Mage_Core_Model_Locale
     */
    public function getLocaleHelper()
    {
        return Mage::app()->getLocale();
    }
    /**
     * Get product category helper
     * 
     * @return MP_Warehouse_Helper_Core_Catalog_Category
     */
    public function getProductCategoryHelper()
    {
        return Mage::helper('warehouse/core_catalog_category');
    }
    /**
     * Get product helper
     * 
     * @return MP_Warehouse_Helper_Core_Catalog_Product
     */
    public function getProductHelper()
    {
        return Mage::helper('warehouse/core_catalog_product');
    }
    /**
     * Get address helper
     * 
     * @return MP_Warehouse_Helper_Core_Address
     */
    public function getAddressHelper()
    {
        return Mage::helper('warehouse/core_address');
    }
    /**
     * Get admin html helper
     * 
     * @return MP_Warehouse_Helper_Core_Adminhtml
     */
    public function getAdminhtmlHelper()
    {
        return Mage::helper('warehouse/core_adminhtml');
    }
    /**
     * Get customer helper
     * 
     * @return MP_Warehouse_Helper_Core_Customer
     */
    public function getCustomerHelper()
    {
        return Mage::helper('warehouse/core_customer');
    }
    /**
     * Get checkout helper
     * 
     * @return MP_Warehouse_Helper_Core_Checkout
     */
    public function getCheckoutHelper()
    {
        return Mage::helper('warehouse/core_checkout');
    }
    /**
     * Get database helper
     * 
     * @return MP_Warehouse_Helper_Core_Database
     */
    public function getDatabaseHelper()
    {
        return Mage::helper('warehouse/core_database');
    }
    /**
     * Get directory helper
     * 
     * @return MP_Warehouse_Helper_Core_Directory
     */
    public function getDirectoryHelper()
    {
        return Mage::helper('warehouse/core_directory');
    }
    /**
     * Get currency helper
     * 
     * @return MP_Warehouse_Helper_Core_Directory_Currency
     */
    public function getCurrencyHelper()
    {
        return Mage::helper('warehouse/core_directory_currency');
    }
    /**
     * Get process helper
     * 
     * @return MP_Warehouse_Helper_Core_Index_Process
     */
    public function getProcessHelper()
    {
        return Mage::helper('warehouse/core_index_process');
    }
    /**
     * Get math helper
     * 
     * @return MP_Warehouse_Helper_Core_Math
     */
    public function getMathHelper()
    {
        return Mage::helper('warehouse/core_math');
    }
    /**
     * Get model helper
     * 
     * @return MP_Warehouse_Helper_Core_Model
     */
    public function getModelHelper()
    {
        return Mage::helper('warehouse/core_model');
    }
    /**
     * Get payment helper
     * 
     * @return MP_Warehouse_Helper_Core_Payment
     */
    public function getPaymentHelper()
    {
        return Mage::helper('warehouse/core_payment');
    }
    /**
     * Get shipping helper
     * 
     * @return MP_Warehouse_Helper_Core_Shipping
     */
    public function getShippingHelper()
    {
        return Mage::helper('warehouse/core_shipping');
    }
    /**
     * Get tax helper
     * 
     * @return MP_Warehouse_Helper_Core_Tax_Data
     */
    public function getTaxHelper()
    {
        return Mage::helper('warehouse/core_tax');
    }
    /**
     * Get version helper
     * 
     * @return MP_Warehouse_Helper_Core_Version
     */
    public function getVersionHelper()
    {
        return Mage::helper('warehouse/core_version');
    }
    /**
     * Get core session
     * 
     * @return Mage_Core_Model_Session
     */
    public function getCoreSession()
    {
        return Mage::getSingleton('core/session');
    }
    /**
     * Get admin session
     * 
     * @return Mage_Admin_Model_Session
     */
    public function getAdminSession()
    {
        return Mage::getSingleton('admin/session');
    }
    /**
     * Check if admin store is active
     * 
     * @return boolean
     */
    public function isAdmin()
    {
        return Mage::app()->getStore()->isAdmin();
    }
    /**
     * Check if single store mode is in effect
     * 
     * @return bool 
     */
    public function isSingleStoreMode()
    {
        return Mage::app()->isSingleStoreMode();
    }
    /**
     * Get full controller name
     * 
     * @return string
     */
    public function getFullControllerName()
    {
        $request = $this->getRequest();
        return $request->getRouteName().'_'.$request->getControllerName();
    }
    /**
     * Check if create order request is active
     * 
     * @return bool
     */
    public function isCreateOrderRequest()
    {
        if ($this->isAdmin()) {
            $controllerName = $this->getRequest()->getControllerName();
            if (in_array(strtolower($controllerName), array('sales_order_edit', 'sales_order_create'))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * Get websites
     * 
     * @return array of Mage_Core_Model_Website
     */
    public function getWebsites()
    {
        if (is_null($this->_websites)) {
            $this->_websites = Mage::app()->getWebsites();
        }

        return $this->_websites;
    }
    /**
     * Get website ids
     * 
     * @return array
     */
    public function getWebsiteIds()
    {
        return array_keys($this->getWebsites());
    }
    /**
     * Check if website id exists
     * 
     * @param int $websiteId
     * 
     * @return bool
     */
    public function isWebsiteIdExists($websiteId)
    {
        return in_array($websiteId, $this->getWebsiteIds());
    }
    /**
     * Get website
     * 
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        return Mage::app()->getWebsite();
    }
    /**
     * Get website id
     * 
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->getWebsite()->getId();
    }
    /**
     * Get website by id
     * 
     * @param mixed $websiteId
     * 
     * @return Mage_Core_Model_Website
     */
    public function getWebsiteById($websiteId)
    {
        return Mage::app()->getWebsite($websiteId);
    }
    /**
     * Get website code by id
     * 
     * @param int $websiteId
     * 
     * @return string
     */
    public function getWebsiteCodeById($websiteId)
    {
        return $this->getWebsiteById($websiteId)->getCode();
    }
    /**
     * Get website by code
     * 
     * @param string $code
     * 
     * @return Mage_Core_Model_Website
     */
    public function getWebsiteByCode($code)
    {
        $website = null;
        foreach ($this->getWebsites() as $_website) {
            if ($_website->getCode() == $code) {
                $website = $_website;
                break;
            }
        }

        return $website;
    }
    /**
     * Get website id by code
     * 
     * @param string $code
     * 
     * @return int
     */
    public function getWebsiteIdByCode($code)
    {
        $website = $this->getWebsiteByCode($code);
        if ($website) {
            return $website->getId();
        } else {
            return null;
        }
    }
    /**
     * Get website id by code or id
     * 
     * @param mixed $codeOrId
     * 
     * @return int
     */
    public function getWebsiteIdByCodeOrId($codeOrId)
    {
        if ($this->isWebsiteIdExists($codeOrId)) {
            return $codeOrId;
        }

        return $this->getWebsiteIdByCode($codeOrId);
    }
    /**
     * Get website by store id
     * 
     * @param mixed $storeId
     * 
     * @return Mage_Core_Model_Website 
     */
    public function getWebsiteByStoreId($storeId)
    {
        return $this->getStoreById($storeId)->getWebsite();
    }
    /**
     * Get website id by store id
     * 
     * @param mixed $storeId
     * 
     * @return int
     */
    public function getWebsiteIdByStoreId($storeId)
    {
        return $this->getStoreById($storeId)->getWebsiteId();
    }
    /**
     * Get website array
     * 
     * @return array
     */
    public function getWebsiteArray()
    {
        $array = array();
        foreach ($this->getWebsites() as $website) {
            $array[(int) $website->getId()] = $website->getName();
        }

        return $array;
    }
    /**
     * Get stores
     * 
     * @return array of Mage_Core_Model_Store
     */
    public function getStores()
    {
        if (is_null($this->_stores)) {
            $this->_stores = Mage::app()->getStores();
        }

        return $this->_stores;
    }
    /**
     * Get store ids
     * 
     * @return array
     */
    public function getStoreIds()
    {
        return array_keys($this->getStores());
    }
    /**
     * Check if store id exists
     * 
     * @param int $storeId
     * 
     * @return bool
     */
    public function isStoreIdExists($storeId)
    {
        return in_array($storeId, $this->getStoreIds());
    }
    /**
     * Get store
     * 
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return Mage::app()->getStore();
    }
    /**
     * Get store id
     * 
     * @return int
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }
    /**
     * Get store by id
     * 
     * @param mixed $storeId
     * 
     * @return Mage_Core_Model_Store
     */
    public function getStoreById($storeId)
    {
        return Mage::app()->getStore($storeId);
    }
    /**
     * Get store code by id
     * 
     * @param int $storeId
     * 
     * @return string
     */
    public function getStoreCodeById($storeId)
    {
        return $this->getStoreById($storeId)->getCode();
    }
    /**
     * Get store by code
     * 
     * @param string $code
     * 
     * @return Mage_Core_Model_Store
     */
    public function getStoreByCode($code)
    {
        $store = null;
        foreach ($this->getStores() as $_store) {
            if ($_store->getCode() == $code) {
                $store = $_store;
                break;
            }
        }

        return $store;
    }
    /**
     * Get store id by code
     * 
     * @param string $code
     * 
     * @return int
     */
    public function getStoreIdByCode($code)
    {
        $store = $this->getStoreByCode($code);
        if ($store) {
            return $store->getId();
        } else {
            return null;
        }
    }
    /**
     * Get store id by code or id
     * 
     * @param mixed $codeOrId
     * 
     * @return int
     */
    public function getStoreIdByCodeOrId($codeOrId)
    {
        if ($this->isStoreIdExists($codeOrId)) {
            return $codeOrId;
        }

        return $this->getStoreIdByCode($codeOrId);
    }
    /**
     * Get default store by store id
     * 
     * @param mixed $storeId
     * 
     * @return Mage_Core_Model_Store
     */
    public function getDefaultStoreByStoreId($storeId)
    {
        return $this->getWebsiteByStoreId($storeId)->getDefaultStore();
    }
    /**
     * Get default store id by store id
     * 
     * @param mixed $storeId
     * 
     * @return int
     */
    public function getDefaultStoreIdByStoreId($storeId)
    {
        return $this->getDefaultStoreByStoreId($storeId)->getId();
    }
    /**
     * Get store identifiers by website identifier
     * 
     * @param mixed $websiteId
     * 
     * @return array
     */
    public function getStoreIdsByWebsiteId($websiteId)
    {
        return $this->getWebsiteById($websiteId)->getStoreIds();
    }
    /**
     * Get current store
     * 
     * @return Mage_Core_Model_Store
     */
    public function getCurrentStore()
    {
        if ($this->isAdmin() && $this->isCreateOrderRequest()) {
            return Mage::getSingleton('adminhtml/session_quote')->getStore();
        } else {
            return Mage::app()->getStore();
        }
    }
    /**
     * Get current store id
     * 
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->getCurrentStore()->getId();
    }
    /**
     * Get request store id
     *
     * @return integer
     */
    public function getRequestStoreId()
    {
        return (int) $this->getRequest()->getParam('store', 0);
    }
    /**
     * Get request store
     *
     * @return Mage_Core_Model_Store
     */
    public function getRequestStore()
    {
        return $this->getStoreById($this->getRequestStoreId());
    }
    /**
     * Get layout
     *
     * @return Mage_Core_Model_Layout
     */
    public function getLayout()
    {
        return Mage::app()->getLayout();
    }
    /**
     * Get table
     * 
     * @param string $entityName
     * 
     * @return string 
     */
    public function getTable($entityName)
    {
        return $this->getDatabaseHelper()->getTable($entityName);
    }
    /**
     * Encode the mixed value
     *
     * @param mixed $valueToEncode
     * @param boolean $cycleCheck
     * @param array $options
     * 
     * @return string
     */
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array())
    {
        return $this->getCoreHelper()->jsonEncode($valueToEncode, $cycleCheck, $options);
    }
    /**
     * Decodes the string
     *
     * @param string $encodedValue
     * 
     * @return mixed
     */
    public function jsonDecode($encodedValue, $objectDecodeType = Zend_Json::TYPE_ARRAY)
    {
        return $this->getCoreHelper()->jsonDecode($encodedValue, $objectDecodeType);
    }
    /**
     * Get request
     * 
     * @return Mage_Core_Controller_Request_Http
     */
    public function getRequest() 
    {
        return Mage::app()->getRequest();
    }
    /**
     * Compare options
     * 
     * @param array $option1
     * @param array $option1
     * 
     * @return int
     */
    public function compareOptions($option1, $option2)
    {
        $value1 = (isset($option1['label'])) ?$option1['label'] : '';
        $value2 = (isset($option2['label'])) ?$option2['label'] : '';
        if ($value1 != $value2) {
            return $value1 < $value2 ? -1 : 1;
        }

        return 0;
    }
    /**
     * Sort Options
     * 
     * @param array $options
     * 
     * @return MP_Warehouse_Helper_Core_Data
     */
    public function sortOptions(&$options)
    {
        usort($options, array($this, 'compareOptions'));
        foreach ($options as $optionIndex => $option) {
            if (isset($option['value']) && is_array($option['value'])) {
                $this->sortOptions($options[$optionIndex]['value']);
            }
        }

        return $this;
    }
    /**
     * Prepare options
     * 
     * @param array $options
     * @param bool $required
     * @param string $emptyLabel
     * @param string $emptyValue
     * 
     * @return MP_Warehouse_Helper_Core_Data
     */
    public function prepareOptions(&$options, $required = true, $emptyLabel = '', $emptyValue = '')
    {
        $this->sortOptions($options);
        if (!$required) {
            array_unshift(
                $options, 
                array(
                    'value' => $emptyValue, 
                    'label' => $emptyLabel
                )
            );
        }

        return $this;
    }
    /**
     * Get store options
     * 
     * @param bool $required
     * @param bool $padding
     * @param string $emptyLabel
     * @param string $emptyValue
     * 
     * @return array
     */
    public function getStoreOptions($required = true, $padding = false, $emptyLabel = '', $emptyValue = '')
    {
        $nonEscapableNbspChar   = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        $options                = array();
        foreach ($this->getWebsites() as $website) {
            $websiteId = $website->getId();
            if (!isset($options[$websiteId])) {
                $options[$websiteId] = array(
                    'value' => array(), 
                    'label' => $website->getName(), 
                );
            }

            foreach ($website->getGroups() as $storeGroup) {
                $storeGroupId   = $storeGroup->getId();
                $storeGroupName = (($padding) ? str_repeat($nonEscapableNbspChar, 4) : '').$storeGroup->getName();
                if (!isset($options[$websiteId]['value'][$storeGroupId])) {
                    $options[$websiteId]['value'][$storeGroupId] = array(
                        'value' => array(), 
                        'label' => $storeGroupName, 
                    );
                }

                foreach ($storeGroup->getStores() as $store) {
                    $storeId    = $store->getId();
                    $storeName  = (($padding) ? str_repeat($nonEscapableNbspChar, 4) : '').$store->getName();
                    $options[$websiteId]['value'][$storeGroupId]['value'][$storeId] = array(
                        'value' => $storeId, 
                        'label' => $storeName, 
                    );
                }
            }
        }

        $this->prepareOptions($options, $required, $emptyLabel, $emptyValue);
        return $options;
    }
}
