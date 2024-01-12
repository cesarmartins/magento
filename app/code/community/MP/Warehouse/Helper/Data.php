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
 * Warehouse data helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Data 
    extends Mage_Core_Helper_Abstract
{
    /**
     * Warehouses
     * 
     * @var array
     */
    protected $_warehouses;
    /**
     * Address stock ids
     * 
     * @var array
     */
    protected $_addressStockIds = array();
    /**
     * Nearest address stock ids
     * 
     * @var array
     */
    protected $_nearestAddressStockIds = array();
    
    /**
     * Stock priorities
     * 
     * @var array
     */
    protected $_stockPriorities;
    /**
     * Area stock priorities
     * 
     * @var array
     */
    protected $_areaStockPriorities = array();
    /**
     * Nearest stock priorities
     * 
     * @var array
     */
    protected $_nearestStockPriorities = array();
    
    /**
     * Address stock distances
     * 
     * @var array
     */
    protected $_addressStockDistances = array();
    /**
     * Shipping tablerate methods
     * 
     * @var array of MP_Warehouse_Model_ShippingTablerate_Tablerate_Method
     */
    protected $_shippingTablerateMethods;
    
    /**
     * Max priority
     */
    const MAX_PRIORITY      = 2147483647;
    
    /**
     * Get core helper
     * 
     * @return MP_Warehouse_Helper_Core_Data
     */
    public function getCoreHelper()
    {
        return Mage::helper('warehouse/core_data');
    }
    /**
     * Get math helper
     * 
     * @return MP_Warehouse_Helper_Core_Math
     */
    public function getMathHelper()
    {
        return $this->getCoreHelper()->getMathHelper();
    }
    /**
     * Get address helper
     * 
     * @return MP_Warehouse_Helper_Core_Address
     */
    public function getAddressHelper()
    {
        return $this->getCoreHelper()->getAddressHelper();
    }
    /**
     * Get version helper
     * 
     * @return MP_Warehouse_Helper_Core_Version
     */
    public function getVersionHelper()
    {
        return $this->getCoreHelper()->getVersionHelper();
    }
    /**
     * Get database helper
     * 
     * @return MP_Warehouse_Helper_Core_Database
     */
    public function getDatabaseHelper()
    {
        return $this->getCoreHelper()->getDatabaseHelper();
    }
    /**
     * Get model helper
     * 
     * @return MP_Warehouse_Helper_Core_Model
     */
    public function getModelHelper()
    {
        return $this->getCoreHelper()->getModelHelper();
    }
    /**
     * Get customer locator helper
     * 
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    public function getCustomerLocatorHelper()
    {
        return Mage::helper('warehouse/customerLocator_data');
    }
    /**
     * Get geo coder helper
     * 
     * @return MP_Warehouse_Helper_GeoCoder_Data
     */
    public function getGeoCoderHelper()
    {
        return Mage::helper('warehouse/geoCoder_data');
    }
    /**
     * Get catalog inventory helper
     * 
     * @return MP_Warehouse_Helper_Cataloginventory
     */
    public function getCatalogInventoryHelper()
    {
        return Mage::helper('warehouse/cataloginventory');
    }
    /**
     * Get assignment method helper
     * 
     * @return MP_Warehouse_Helper_Warehouse_Assignment_Method
     */
    public function getAssignmentMethodHelper()
    {
        return Mage::helper('warehouse/warehouse_assignment_method');
    }
    /**
     * Get product helper
     * 
     * @return MP_Warehouse_Helper_Catalog_Product
     */
    public function getProductHelper()
    {
        return Mage::helper('warehouse/catalog_product');
    }
    /**
     * Get process helper
     * 
     * @return MP_Warehouse_Helper_Index_Process
     */
    public function getProcessHelper()
    {
        return Mage::helper('warehouse/index_process');
    }
    /**
     * Get product price helper
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price
     */
    public function getProductPriceHelper()
    {
        return $this->getProductHelper()->getPriceHelper();
    }
    /**
     * Get product price indexer helper
     * 
     * @return MP_Warehouse_Helper_Catalog_Product_Price_Indexer
     */
    public function getProductPriceIndexerHelper()
    {
        return $this->getProductPriceHelper()->getIndexerHelper();
    }
    /**
     * Get shipping helper
     * 
     * @return MP_Warehouse_Helper_Shipping
     */
    public function getShippingHelper()
    {
        return Mage::helper('warehouse/shipping');
    }
    /**
     * Get quote helper
     * 
     * @return MP_Warehouse_Helper_Sales_Quote
     */
    public function getQuoteHelper()
    {
        return Mage::helper('warehouse/sales_quote');
    }
    /**
     * Get order helper
     * 
     * @return MP_Warehouse_Helper_Sales_Order
     */
    public function getOrderHelper()
    {
        return Mage::helper('warehouse/sales_order');
    }
    /**
     * Get tax helper
     * 
     * @return Mage_Tax_Helper_Data
     */
    public function getTaxHelper()
    {
        return Mage::helper('tax');
    }
    /**
     * Get customer helper
     * 
     * @return MP_Warehouse_Helper_Customer
     */
    public function getCustomerHelper()
    {
        return Mage::helper('warehouse/customer');
    }
    /**
     * Get currency helper
     * 
     * @return MP_Warehouse_Helper_Directory_Currency
     */
    public function getCurrencyHelper()
    {
        return Mage::helper('warehouse/directory_currency');
    }
    /**
     * Get adminhtml helper
     * 
     * @return MP_Warehouse_Helper_Adminhtml
     */
    public function getAdminhtmlHelper()
    {
        return Mage::helper('warehouse/adminhtml');
    }
    /**
     * Get config
     * 
     * @return MP_Warehouse_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('warehouse/config');
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
     * Get session
     * 
     * @return MP_Warehouse_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('warehouse/session');
    }
    /**
     * Get warehouse singleton
     * 
     * @return Mage_Cataloginventory_Model_Stock
     */
    public function getWarehouseSingleton()
    {
        return Mage::getSingleton('warehouse/warehouse');
    }
    /**
     * Get warehouse resource
     * 
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    public function getWarehouseResource()
    {
        return Mage::getResourceSingleton('warehouse/warehouse');
    }
    /**
     * Get warehouse collection
     * 
     * @return MP_Warehouse_Model_Mysql4_Warehouse_Collection
     */
    public function getWarehouseCollection()
    {
        return $this
            ->getWarehouseSingleton()
            ->getCollection()
            ->addIdFilter(0, true);
    }
    /**
     * Get stock ids
     * 
     * @return array
     */
    public function getStockIds()
    {
        return $this
            ->getCatalogInventoryHelper()
            ->getStockIds();
    }
    /**
     * Get default stock id
     * 
     * @return integer
     */
    public function getDefaultStockId()
    {
        return $this
            ->getCatalogInventoryHelper()
            ->getDefaultStockId();
    }
    /**
     * Get admin stock id
     * 
     * @return integer
     */
    public function getAdminStockId()
    {
        return $this
            ->getCatalogInventoryHelper()
            ->getAdminStockId();
    }
    /**
     * Check if stock id exists
     * 
     * @param integer $stockId
     * 
     * @return boolean
     */
    public function isStockIdExists($stockId)
    {
        return $this->getCatalogInventoryHelper()->isStockIdExists($stockId);
    }
    /**
     * Get default warehouse identifier
     * 
     * @return int
     */
    public function getDefaultWarehouseId()
    {
        return $this->getWarehouseIdByStockId($this->getDefaultStockId());
    }
    /**
     * Compare warehouses
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse1
     * @param MP_Warehouse_Model_Warehouse $warehouse2
     * 
     * @return integer
     */
    public function compareWarehouses($warehouse1, $warehouse2)
    {
        $config                 = $this->getConfig();
        $storeId                = $this->getCurrentStoreId();
        $value1                 = $warehouse1->getId();
        $value2                 = $warehouse2->getId();
        if ($config->isSortByCode($storeId)) {
            $value1                 = $warehouse1->getCode();
            $value2                 = $warehouse2->getCode();
        } else if ($config->isSortByTitle($storeId)) {
            $value1                 = $warehouse1->getTitle();
            $value2                 = $warehouse2->getTitle();
        } else if ($config->isSortByPriority($storeId)) {
            $value1                 = $warehouse1->getPriority();
            $value2                 = $warehouse2->getPriority();
        } else if ($config->isSortByOrigin($storeId)) {
            $value1                 = $warehouse1->getOriginString();
            $value2                 = $warehouse2->getOriginString();
        }

        if ($value1 != $value2) {
            return $value1 < $value2 ? -1 : 1;
        }

        return 0;
    }
    /**
     * Sort warehouses
     * 
     * @param array $warehouses
     * 
     * @return array
     */
    public function sortWarehouses($warehouses)
    {
        $_warehouses = $warehouses;
        $warehouses = array();
        usort($_warehouses, array($this, 'compareWarehouses'));
        foreach ($_warehouses as $warehouse) {
            $warehouses[(int) $warehouse->getId()] = $warehouse;
        }

        return $warehouses;
    }
    /**
     * Get warehouses
     * 
     * @return array
     */
    public function getWarehouses()
    {
        if (is_null($this->_warehouses)) {
            $warehouses = array();
            foreach ($this->getWarehouseCollection() as $warehouse) {
                $warehouses[$warehouse->getId()] = $warehouse;
            }

            $this->_warehouses = $this->sortWarehouses($warehouses);
        }

        return $this->_warehouses;
    }
    /**
     * Get warehouse
     * 
     * @param int $warehouseId
     * 
     * @return MP_Warehouse_Model_Warehouse
     */
    public function getWarehouse($warehouseId)
    {
        $warehouses = $this->getWarehouses();
        if (isset($warehouses[$warehouseId])) {
            return $warehouses[$warehouseId];
        } else {
            return null;
        }
    }
    /**
     * Get stock identifier by warehouse identifier
     * 
     * @param int $warehouseId
     * 
     * @return int
     */
    public function getStockIdByWarehouseId($warehouseId)
    {
        $stockId = null;
        $warehouse  = $this->getWarehouse($warehouseId);
        if ($warehouse) {
            $stockId = $warehouse->getStockId();
        }

        return $stockId;
    }
    /**
     * Get warehouse by stock identifier
     * 
     * @param int $stockId
     * 
     * @return MP_Warehouse_Model_Warehouse
     */
    public function getWarehouseByStockId($stockId)
    {
        if (!$stockId) {
            return null;
        }

        $warehouse = null;
        $warehouses = $this->getWarehouses();
        foreach ($warehouses as $_warehouse) {
            if ($_warehouse->getStockId() == $stockId) { 
                $warehouse = $_warehouse; 
                break; 
            }
        }

        return $warehouse;
    }
    /**
     * Get warehouse by stock identifier
     * 
     * @param string $code
     * 
     * @return MP_Warehouse_Model_Warehouse
     */
    public function getWarehouseByCode($code)
    {
        $warehouse = null;
        $warehouses = $this->getWarehouses();
        foreach ($warehouses as $_warehouse) {
            if ($_warehouse->getCode() == $code) {
                $warehouse = $_warehouse;
                break; 
            }
        }

        return $warehouse;
    }
    /**
     * Get warehouse title by stock identifier
     * 
     * @param int $stockId
     * 
     * @return string
     */
    public function getWarehouseTitleByStockId($stockId)
    {
        $warehouse = $this->getWarehouseByStockId($stockId);
        if ($warehouse) {
            return $warehouse->getTitle();
        } else {
            return null;
        }
    }
    /**
     * Get warehouse code by stock identifier
     * 
     * @param int $stockId
     * 
     * @return string
     */
    public function getWarehouseCodeByStockId($stockId)
    {
        $warehouse = $this->getWarehouseByStockId($stockId);
        if ($warehouse) {
            return $warehouse->getCode();
        } else {
            return null;
        }
    }
    /**
     * Get warehouse identifier by stock identifier
     * 
     * @param int $stockId
     * 
     * @return int|null
     */
    public function getWarehouseIdByStockId($stockId)
    {
        $warehouse = $this->getWarehouseByStockId($stockId);
        if ($warehouse) {
            return $warehouse->getId();
        } else {
            return null;
        }
    }
    /**
     * Get warehouses by stock identifiers
     * 
     * @param array $stocksIds
     * 
     * @return array
     */
    public function getWarehousesByStockIds($stockIds)
    {
        $warehouses = array();
        foreach ($this->getWarehouses() as $warehouse) {
            if (in_array($warehouse->getStockId(), $stockIds)) { 
                array_push($warehouses, $warehouse);
            }
        }

        return $warehouses;
    }
    /**
     * Get warehouses options
     * 
     * @param bool $required
     * @param string $emptyLabel
     * @param string $emptyValue
     * 
     * @return array
     */
    public function getWarehousesOptions($required = true, $emptyLabel = '', $emptyValue = '')
    {
        $options = $this->getWarehouseCollection()->toOptionArray();
        if (count($options) > 0 && !$required) {
            array_unshift($options, array('value' => $emptyValue, 'label' => $emptyLabel));
        }

        return $options;
    }
    /**
     * Get stocks options
     * 
     * @param bool $required
     * @param string $emptyLabel
     * 
     * @return array
     */
    public function getStocksOptions($required = true, $emptyLabel = '')
    {
        $options = $this->getWarehouseCollection()->toOptionArray('stock_id');
        if (count($options) > 0 && !$required) {
            array_unshift($options, array('value' => '', 'label' => $emptyLabel));
        }

        return $options;
    }
    /**
     * Get warehouses hash
     * 
     * @return array
     */
    public function getWarehousesHash()
    {
        return $this->getWarehouseCollection()->toOptionHash();
    }
    /**
     * Get stocks hash
     * 
     * @return array
     */
    public function getStocksHash()
    {
        return $this->getWarehouseCollection()->toOptionHash('stock_id');
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
        return Mage::getSingleton('core/resource')->getTableName($entityName);
    }
    /**
     * Check if multiple mode is enabled
     * 
     * @return boolean
     */
    public function isMultipleMode()
    {
        return $this
            ->getConfig()
            ->isMultipleMode($this->getCurrentStoreId());
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
     * Get request
     * 
     * @return Mage_Core_Controller_Request_Http
     */
    public function getRequest() 
    {
        return Mage::app()->getRequest();
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
     * Check if PayPal Express request is active
     * 
     * @return boolean
     */
    public function isPayPalExpressRequest()
    {
        return (in_array(
            $this->getFullControllerName(), array(
            'paypal_express', 
            'paypal_payflowadvanced', 
            'paypaluk_express', 
            )
        )) ? true : false;
    }
    /**
     * Get request action
     * 
     * @return string
     */
    public function getRequestAction()
    {
        $request = Mage::app()->getRequest();
        $action = $request->getModuleName();
        if ($request->getControllerName()) {
            $action .= '_'.$request->getControllerName();
        }

        if ($request->getActionName()) {
            $action .= '_'.$request->getActionName();
        }

        return $action;
    }
    /**
     * Get store by identifier
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
     * Get website by store identifier
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
     * Get website identifier by store identifier 
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
     * Get current store
     * 
     * @param Varien_Object|Mage_Catalog_Model_Resource_Collection_Abstract $object
     * 
     * @return Mage_Core_Model_Store
     */
    public function getCurrentStore($object = null)
    {
        $coreHelper             = $this->getCoreHelper();
        if ($object && 
            (
                ($object instanceof Varien_Object) || 
                ($object instanceof Mage_Catalog_Model_Resource_Collection_Abstract)
            )
        ) {
            $storeId                = (int) $object->getStoreId();
            if ($storeId) {
                return $coreHelper->getStoreById($storeId);
            }
        }

        if ($coreHelper->isAdmin()) {
            $storeId                = $coreHelper->getRequestStoreId();
            if ($storeId) {
                return $coreHelper->getStoreById($storeId);
            }
        }

        return $coreHelper->getCurrentStore();
    }
    /**
     * Get current store id
     * 
     * @param Varien_Object $object
     * 
     * @return integer
     */
    public function getCurrentStoreId($object = null)
    {
        return $this
            ->getCurrentStore($object)
            ->getId();
    }
    /**
     * Get customer address
     * 
     * @return Varien_Object
     */
    public function getCustomerAddress()
    {
        return $this->getCustomerLocatorHelper()->getCustomerAddress();
    }
    /**
     * Set customer shipping address
     * 
     * @param Varien_Object $shippingAddress
     * 
     * @return $this
     */
    public function setCustomerShippingAddress($shippingAddress)
    {
        $this->getCustomerLocatorHelper()->setCustomerAddress($shippingAddress);
        return $this;
    }
    /**
     * Copy customer address
     * 
     * @param Varien_Object $address
     * 
     * @return $this
     */
    public function copyCustomerAddress($address)
    {
        return $this->getAddressHelper()->copy($this->getCustomerAddress(), $address);
    }
    /**
     * Copy customer address if destination address is empty
     * 
     * @param Varien_Object $address
     * 
     * @return $this
     */
    public function copyCustomerAddressIfEmpty($address)
    {
        if ($this->getAddressHelper()->isEmpty($address)) {
            $this->copyCustomerAddress($address);
        }

        return $this;
    }
    /**
     * Set warehouse coordinates
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return MP_Warehouse_Helper_Warehouse_Data
     */
    public function setWarehouseCoordinates($warehouse)
    {
        $coordinates = $this->getGeoCoderHelper()->getCoordinates($warehouse->getOrigin());
        if ($coordinates->getLatitude() && $coordinates->getLongitude()) {
            $warehouse->setOriginLatitude($coordinates->getLatitude());
            $warehouse->setOriginLongitude($coordinates->getLongitude());
        }

        return $this;
    }
    /**
     * Update warehouses coordinates
     * 
     * @return MP_Warehouse_Helper_Warehouse_Data
     */
    public function updateWarehousesCoordinates()
    {
        foreach ($this->getWarehouses() as $warehouse) {
            $this->setWarehouseCoordinates($warehouse);
            $warehouse->save();
            sleep(1);
        }

        return $this;
    }
    /**
     * Set session stock id
     * 
     * @param int $stockId
     * 
     * @return $this
     */
    public function setSessionStockId($stockId)
    {
        $this->getSession()->setStockId($stockId);
        return $this;
    }
    /**
     * Get session stock id
     * 
     * @return int
     */
    public function getSessionStockId()
    {
        return $this->getSession()->getStockId();
    }
    /**
     * Remove session stock id
     * 
     * @return $this
     */
    public function removeSessionStockId()
    {
        $this->getSession()->removeStockId();
        return $this;
    }
    /**
     * Get stock id by store id
     * 
     * @param mixed $storeId
     * 
     * @return int
     */
    public function getStockIdByStoreId($storeId)
    {
        $stockId = null;
        foreach ($this->getWarehouses() as $warehouse) {
            $storeIds = $warehouse->getStoreIds();
            if ($storeIds && count($storeIds) && in_array($storeId, $storeIds)) {
                $stockId = (int) $warehouse->getStockId();
                break;
            }
        }

        return $stockId;
    }
    /**
     * Get stock id by customer group id
     * 
     * @param mixed $customerGroupId
     * 
     * @return int
     */
    public function getStockIdByCustomerGroupId($customerGroupId)
    {
        $stockId = null;
        foreach ($this->getWarehouses() as $warehouse) {
            $customerGroupIds = $warehouse->getCustomerGroupIds();
            if ($customerGroupIds && count($customerGroupIds) && in_array($customerGroupId, $customerGroupIds)) {
                $stockId = (int) $warehouse->getStockId();
                break;
            }
        }

        return $stockId;
    }
    /**
     * Get stock id by currency code
     * 
     * @param string $currencyCode
     * 
     * @return int
     */
    public function getStockIdByCurrencyCode($currencyCode)
    {
        $stockId = null;
        foreach ($this->getWarehouses() as $warehouse) {
            $currencyCodes = $warehouse->getCurrencies();
            if ($currencyCodes && count($currencyCodes) && in_array($currencyCode, $currencyCodes)) {
                $stockId = (int) $warehouse->getStockId();
                break;
            }
        }

        return $stockId;
    }
    /**
     * Get stock id by address
     * 
     * @param Varien_Object $address
     * 
     * @return integer
     */
    public function getStockIdByAddress($address)
    {
        $hash                   = $this->getAddressHelper()
            ->getHash($address);
        if (!isset($this->_addressStockIds[$hash])) {
            $stockId                = $this->getWarehouseResource()
                ->getAreaStockId($address);
            if (!$stockId) {
                $stockId                = $this->getDefaultStockId();
            }

            $this->_addressStockIds[$hash] = $stockId;
        }

        return $this->_addressStockIds[$hash];
    }
    /**
     * Get nearest stock id by address
     * 
     * @param Varien_Object $address
     * 
     * @return int
     */
    public function getNearestStockIdByAddress($address)
    {
        $hash                   = $this->getAddressHelper()
            ->getHash($address);
        if (!isset($this->_nearestAddressStockIds[$hash])) {
            $stockId                = null;
            $coordinates            = $this->getGeoCoderHelper()
                ->getCoordinates($address);
            if ($coordinates && 
                $coordinates->getLatitude() && 
                $coordinates->getLongitude()
            ) {
                $mathHelper             = $this->getMathHelper();
                $latitude1              = (float) $coordinates->getLatitude();
                $longitude1             = (float) $coordinates->getLongitude();
                $minStockId             = null;
                $minDistance            = null;
                foreach ($this->getWarehouses() as $warehouse) {
                    $_stockId               = (int) $warehouse->getStockId();
                    $latitude2              = (float) $warehouse->getOriginLatitude();
                    $longitude2             = (float) $warehouse->getOriginLongitude();
                    $distance               = $mathHelper->getDistance(
                        $latitude1, $longitude1, $latitude2, $longitude2
                    );
                    if ((is_null($minDistance) || ($distance < $minDistance))) {
                        $minDistance            = $distance;
                        $minStockId             = $_stockId;
                    }
                }

                if ($minStockId) {
                    $stockId                = $minStockId;
                }
            }

            if (!$stockId) {
                $stockId                = $this->getDefaultStockId();
            }

            $this->_nearestAddressStockIds[$hash] = $stockId;
        }

        return $this->_nearestAddressStockIds[$hash];
    }
    /**
     * Get stock priority
     * 
     * @param array $priorities
     * @param integer $stockId
     * 
     * @return integer
     */
    public function getStockPriority($priorities, $stockId)
    {
        $priority               = null;
        if (array_key_exists($stockId, $priorities) && !is_null($priorities[$stockId])) {
            $priority               = (int) $priorities[$stockId];
        }

        if ($priority === null) {
            $priority               = self::MAX_PRIORITY;
        }

        return $priority;
    }
    /**
     * Get min priority stock id
     * 
     * @param array $priorities
     * @param array $stockIds
     * 
     * @return integer
     */
    public function getMinPriorityStockId($priorities, $stockIds)
    {
        $minPriorityStockId     = null;
        $minPriority            = null;
        foreach ($stockIds as $stockId) {
            $priority               = $this->getStockPriority($priorities, $stockId);
            if (($priority !== null) && 
                (
                    ($minPriority === null) || 
                    ($priority < $minPriority)
                )
            ) {
                $minPriority            = $priority;
                $minPriorityStockId     = $stockId;
            }
        }

        return $minPriorityStockId;
    }
    /**
     * Get stock priorities
     * 
     * @return array
     */
    public function getStockPriorities()
    {
        if (!isset($this->_stockPriorities)) {
            $priorities             = array();
            foreach ($this->getWarehouses() as $warehouse) {
                $stockId                = (int) $warehouse->getStockId();
                $priority               = (int) $warehouse->getPriority();
                $priorities[$stockId]   = $priority;
            }

            $this->_stockPriorities = $priorities;
        }

        return $this->_stockPriorities;
    }
    /**
     * Get area stock priorities
     * 
     * @param Varien_Object $address
     * 
     * @return array
     */
    public function getAreaStockPriorities($address)
    {
        $hash                   = $this->getAddressHelper()
            ->getHash($address);
        if (!array_key_exists($hash, $this->_areaStockPriorities)) {
            $priorities             = $this->getWarehouseResource()
                ->getAreaStockPriorities($address);
            $this->_areaStockPriorities[$hash] = $priorities;
        }

        return $this->_areaStockPriorities[$hash];
    }
    /**
     * Get nearest stock priorities
     * 
     * @param Varien_Object $address
     * 
     * @return array
     */
    public function getNearestStockPriorities($address)
    {
        $hash                   = $this->getAddressHelper()
            ->getHash($address);
        if (!array_key_exists($hash, $this->_nearestStockPriorities)) {
            $priorities             = array();
            $coordinates            = $this->getGeoCoderHelper()
                ->getCoordinates($address);
            if ($coordinates->getLatitude() && $coordinates->getLongitude()) {
                $mathHelper             = $this->getMathHelper();
                $latitude1              = (float) $coordinates->getLatitude();
                $longitude1             = (float) $coordinates->getLongitude();
                foreach ($this->getWarehouses() as $warehouse) {
                    if ($warehouse->getOriginLatitude() && $warehouse->getOriginLongitude()) {
                        $stockId                = (int) $warehouse->getStockId();
                        $latitude2              = (float) $warehouse->getOriginLatitude();
                        $longitude2             = (float) $warehouse->getOriginLongitude();
                        $distance               = (int) $mathHelper->getDistance(
                            $latitude1, $longitude1, $latitude2, $longitude2
                        );
                        $priorities[$stockId]   = $distance;
                    }
                }
            } else {
                $priorities             = $this->getStockPriorities();
            }

            $this->_nearestStockPriorities[$hash] = $priorities;
        }

        return $this->_nearestStockPriorities[$hash];
    }
    /**
     * Get address stock distances
     * 
     * @param Varien_Object $address
     * 
     * @return array
     */
    public function getAddressStockDistances($address)
    {
        $hash = $this->getAddressHelper()->getHash($address);
        if (!isset($this->_addressStockDistances[$hash])) {
            $stockDistances     = array();
            $mathHelper         = $this->getMathHelper();
            $distanceUnits      = $mathHelper->getDistanceUnits();
            $coordinates        = $this->getGeoCoderHelper()->getCoordinates($address);
            if ($coordinates->getLatitude() && $coordinates->getLongitude()) {
                $latitude1      = (float) $coordinates->getLatitude();
                $longitude1     = (float) $coordinates->getLongitude();
                foreach ($this->getWarehouses() as $warehouse) {
                    $stockId        = (int) $warehouse->getStockId();
                    $latitude2      = (float) $warehouse->getOriginLatitude();
                    $longitude2     = (float) $warehouse->getOriginLongitude();
                    $stockDistance  = array();
                    foreach ($distanceUnits as $unitCode => $unit) {
                        $stockDistance[$unitCode] = $mathHelper->getDistance(
                            $latitude1, $longitude1, $latitude2, $longitude2, $unitCode
                        );
                    }

                    $stockDistances[$stockId] = $stockDistance;
                }
            }

            $this->_addressStockDistances[$hash] = $stockDistances;
        }

        return $this->_addressStockDistances[$hash];
    }
    /**
     * Get address stock distance
     * 
     * @param Varien_Object $address
     * @param int $stockId
     * 
     * @return array
     */
    public function getAddressStockDistance($address, $stockId)
    {
        $stockDistance = array();
        $stockDistances = $this->getAddressStockDistances($address);
        if (isset($stockDistances[$stockId])) {
            $stockDistance = $stockDistances[$stockId];
        }

        return $stockDistance;
    }
    /**
     * Get address stock unit distance
     * 
     * @param Varien_Object $address
     * @param int $stockId
     * @param string $unitCode
     * 
     * @return float
     */
    public function getAddressStockUnitDistance($address, $stockId, $unitCode)
    {
        $unitStockDistance = null;
        $stockDistance = $this->getAddressStockDistance($address, $stockId);
        if (isset($stockDistance[$unitCode])) {
            $unitStockDistance = $stockDistance[$unitCode];
        }

        return $unitStockDistance;
    }
    /**
     * Get address stock unit distance string
     * 
     * @param Varien_Object $address
     * @param int $stockId
     * @param string $unitCode
     * 
     * @return string
     */
    public function getAddressStockUnitDistanceString($address, $stockId, $unitCode)
    {
        $distance = $this->getAddressStockUnitDistance($address, $stockId, $unitCode);
        if (!$distance) {
            return null;
        }

        $template = null;
        switch ($unitCode) {
            case 'mi': 
                $template = '%s miles away';
                break;
            case 'nmi': 
                $template = '%s nautical miles away';
                break;
            case 'km': 
                $template = '%s kilometers away';
                break;
            default: 
                $template = '%s away';
                break;
        }

        return sprintf($this->__($template), round($distance));
    }
    /**
     * Get address stock distance string
     * 
     * @param Varien_Object $address
     * @param int $stockId
     * 
     * @return string
     */
    public function getAddressStockDistanceString($address, $stockId)
    {
        $config                 = $this->getConfig();
        $storeId                = $this->getCurrentStoreId();
        return $this->getAddressStockUnitDistanceString(
            $address, 
            $stockId, 
            $config->getDistanceUnit($storeId)
        );
    }
    /**
     * Get customer address stock distance string
     * 
     * @param int $stockId
     * 
     * @return string
     */
    public function getCustomerAddressStockDistanceString($stockId)
    {
        return $this->getAddressStockDistanceString($this->getCustomerAddress(), $stockId);
    }
    /**
     * Save child data
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return $this
     */
    protected function saveChildData(
        $warehouse,
        $dataTableName,
        $dataAttributeCode,
        $dataValueAttributeCode,
        $dataValueType = 'string'
    ) {
        $this->getModelHelper()->saveChildData(
            $warehouse, 'MP_Warehouse_Model_Warehouse', 'warehouse_id', 
            $dataTableName, $dataAttributeCode, $dataValueAttributeCode, $dataValueType
        );
        return $this;
    }
    /**
     * Add child data
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * @param array $array
     * @param string $dataAttributeCode
     * 
     * @return $this
     */
    protected function addChildData($warehouse, $array, $dataAttributeCode)
    {
        $this->getModelHelper()->addChildData(
            $warehouse, 'MP_Warehouse_Model_Warehouse', $array, $dataAttributeCode
        );
        return $this;
    }
    /**
     * Load child data
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return $this
     */
    protected function loadChildData(
        $warehouse,
        $dataTableName,
        $dataAttributeCode,
        $dataValueAttributeCode,
        $dataValueType = 'string'
    ) {
        $this->getModelHelper()->loadChildData(
            $warehouse, 'MP_Warehouse_Model_Warehouse', 'warehouse_id', 
            $dataTableName, $dataAttributeCode, $dataValueAttributeCode, $dataValueType
        );
        return $this;
    }
    /**
     * Load collection child data
     * 
     * @param Varien_Data_Collection_Db $collection
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return $this
     */
    protected function loadCollectionChildData(
        $collection,
        $dataTableName,
        $dataAttributeCode,
        $dataValueAttributeCode,
        $dataValueType = 'string'
    ) {
        $this->getModelHelper()->loadCollectionChildData(
            $collection, 'warehouse_id', 
            $dataTableName, $dataAttributeCode, $dataValueAttributeCode, $dataValueType
        );
        return $this;
    }
    /**
     * Remove child data
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * @param string $dataAttributeCode
     * 
     * @return MP_Warehouse_Helper_Core_Model
     */
    protected function removeChildData($warehouse, $dataAttributeCode)
    {
        $this->getModelHelper()->removeChildData($warehouse, 'MP_Warehouse_Model_Warehouse', $dataAttributeCode);
        return $this;
    }
    /**
     * Save stores
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return $this
     */
    public function saveStores($warehouse)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedStoreSingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->saveChildData(
            $warehouse, 
            'warehouse/warehouse_store', 
            'store_ids', 
            'store_id', 
            'int'
        );
    }
    /**
     * Add data stores
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * @param array $array
     * 
     * @return $this
     */
    public function addDataStores($warehouse, $array)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedStoreSingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->addChildData($warehouse, $array, 'store_ids');
    }
    /**
     * Load stores
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return $this
     */
    public function loadStores($warehouse)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedStoreSingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->loadChildData(
            $warehouse, 
            'warehouse/warehouse_store', 
            'store_ids', 
            'store_id', 
            'int'
        );
    }
    /**
     * Load collection stores
     * 
     * @param Varien_Data_Collection_Db $collection
     * 
     * @return $this
     */
    public function loadCollectionStores($collection)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedStoreSingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->loadCollectionChildData(
            $collection, 
            'warehouse/warehouse_store', 
            'store_ids', 
            'store_id', 
            'int'
        );
    }
    /**
     * Remove stores
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return $this
     */
    protected function removeStores($warehouse)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedStoreSingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->removeChildData($warehouse, 'store_ids');
    }
    /**
     * Save customer groups
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return $this
     */
    public function saveCustomerGroups($warehouse)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedCustomerGroupSingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->saveChildData(
            $warehouse, 
            'warehouse/warehouse_customer_group', 
            'customer_group_ids', 
            'customer_group_id', 
            'int'
        );
    }
    /**
     * Add data customer groups
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * @param array $array
     * 
     * @return $this
     */
    public function addDataCustomerGroups($warehouse, $array)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedCustomerGroupSingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->addChildData($warehouse, $array, 'customer_group_ids');
    }
    /**
     * Load customer groups
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return $this
     */
    public function loadCustomerGroups($warehouse)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedCustomerGroupSingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->loadChildData(
            $warehouse, 
            'warehouse/warehouse_customer_group', 
            'customer_group_ids', 
            'customer_group_id', 
            'int'
        );
    }
    /**
     * Load collection customer groups
     * 
     * @param Varien_Data_Collection_Db $collection
     * 
     * @return $this
     */
    public function loadCollectionCustomerGroups($collection)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedCustomerGroupSingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->loadCollectionChildData(
            $collection, 
            'warehouse/warehouse_customer_group', 
            'customer_group_ids', 
            'customer_group_id', 
            'int'
        );
    }
    /**
     * Remove customer groups
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return $this
     */
    protected function removeCustomerGroups($warehouse)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedCustomerGroupSingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->removeChildData($warehouse, 'customer_group_ids');
    }
    /**
     * Save currencies
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return $this
     */
    public function saveCurrencies($warehouse)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedCurrencySingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->saveChildData(
            $warehouse, 
            'warehouse/warehouse_currency', 
            'currencies', 
            'currency', 
            'string'
        );
    }
    /**
     * Add data currencies
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * @param array $array
     * 
     * @return $this
     */
    public function addDataCurrencies($warehouse, $array)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedCurrencySingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->addChildData($warehouse, $array, 'currencies');
    }
    /**
     * Load currencies
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return $this
     */
    public function loadCurrencies($warehouse)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedCurrencySingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->loadChildData(
            $warehouse, 
            'warehouse/warehouse_currency', 
            'currencies', 
            'currency', 
            'string'
        );
    }
    /**
     * Load collection currencies
     * 
     * @param Varien_Data_Collection_Db $collection
     * 
     * @return $this
     */
    public function loadCollectionCurrencies($collection)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedCurrencySingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->loadCollectionChildData(
            $collection, 
            'warehouse/warehouse_currency', 
            'currencies', 
            'currency', 
            'string'
        );
    }
    /**
     * Remove currencies
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return $this
     */
    protected function removeCurrencies($warehouse)
    {
        $config                 = $this->getConfig();
        if (!$config->isAssignedCurrencySingleAssignmentMethodInAnyStore()) {
            return $this;
        }

        return $this->removeChildData($warehouse, 'currencies');
    }
    /**
     * Save shipping carriers
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return $this
     */
    public function saveShippingCarriers($warehouse)
    {
        $config                 = $this->getConfig();
        if (!$config->isShippingCarrierFilterEnabledInAnyStore()) {
            return $this;
        }

        return $this->saveChildData(
            $warehouse, 
            'warehouse/warehouse_shipping_carrier', 
            'shipping_carriers', 
            'shipping_carrier', 
            'string'
        );
    }
    /**
     * Add data shipping carriers
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * @param array $array
     * 
     * @return $this
     */
    public function addDataShippingCarriers($warehouse, $array)
    {
        $config                 = $this->getConfig();
        if (!$config->isShippingCarrierFilterEnabledInAnyStore()) {
            return $this;
        }

        return $this->addChildData($warehouse, $array, 'shipping_carriers');
    }
    /**
     * Load shipping carriers
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return $this
     */
    public function loadShippingCarriers($warehouse)
    {
        $config                 = $this->getConfig();
        if (!$config->isShippingCarrierFilterEnabledInAnyStore()) {
            return $this;
        }

        $this->loadChildData(
            $warehouse, 
            'warehouse/warehouse_shipping_carrier', 
            'shipping_carriers', 
            'shipping_carrier', 
            'string'
        );
        return $this;
    }
    /**
     * Load collection shipping carriers
     * 
     * @param Varien_Data_Collection_Db $collection
     * 
     * @return $this
     */
    public function loadCollectionShippingCarriers($collection)
    {
        $config                 = $this->getConfig();
        if (!$config->isShippingCarrierFilterEnabledInAnyStore()) {
            return $this;
        }

        return $this->loadCollectionChildData(
            $collection, 
            'warehouse/warehouse_shipping_carrier', 
            'shipping_carriers', 
            'shipping_carrier', 
            'string'
        );
    }
    /**
     * Remove shipping carriers
     * 
     * @param MP_Warehouse_Model_Warehouse $warehouse
     * 
     * @return $this
     */
    protected function removeShippingCarriers($warehouse)
    {
        $config                 = $this->getConfig();
        if (!$config->isShippingCarrierFilterEnabledInAnyStore()) {
            return $this;
        }

        return $this->removeChildData($warehouse, 'shipping_carriers');
    }
    
    
    
    /**
     * Get shipping tablerate methods collection
     * 
     * @return MP_Warehouse_Model_Mysql4_Shippingtablerate_Tablerate_Method_Collection
     */
    public function getShippingTablerateMethodsCollection()
    {
        return Mage::getSingleton('warehouse/shippingTablerate_tablerate_method')->getCollection();
    }
    /**
     * Get shipping tablerate methods options
     * 
     * @param bool $required
     * @param string $emptyLabel
     * @param string $emptyValue
     * 
     * @return array
     */
    public function getShippingTablerateMethodsOptions($required = true, $emptyLabel = '', $emptyValue = '')
    {
        $options = $this->getShippingTablerateMethodsCollection()->toOptionArray();
        if (count($options) > 0 && !$required) {
            array_unshift($options, array('value' => $emptyValue, 'label' => $emptyLabel));
        }

        return $options;
    }
    /**
     * Get shipping tablerate methods hash
     * 
     * @return array
     */
    public function getShippingTablerateMethodsHash()
    {
        return $this->getShippingTablerateMethodsCollection()->toOptionHash();
    }
    /**
     * Get shipping tablerate methods
     * 
     * @return array of MP_Warehouse_Model_ShippingTablerate_Tablerate_Method
     */
    public function getShippingTablerateMethods()
    {
        if (is_null($this->_shippingTablerateMethods)) {
            $shippingTablerateMethods = array();
            foreach ($this->getShippingTablerateMethodsCollection() as $tablerateMethod) {
                $shippingTablerateMethods[$tablerateMethod->getId()] = $tablerateMethod;
            }

            $this->_shippingTablerateMethods = $shippingTablerateMethods;
        }

        return $this->_shippingTablerateMethods;
    }
    /**
     * Get shipping tablerate method
     * 
     * @param int $methodId
     * 
     * @return MP_Warehouse_Model_ShippingTablerate_Tablerate_Method
     */
    public function getShippingTablerateMethod($methodId)
    {
        $shippingTablerateMethods = $this->getShippingTablerateMethods();
        if (isset($shippingTablerateMethods[$methodId])) {
            return $shippingTablerateMethods[$methodId];
        } else {
            return null;
        }
    }
    /**
     * Get shipping tablerate method by code
     * 
     * @param string $code
     * 
     * @return MP_Warehouse_Model_ShippingTablerate_Tablerate_Method
     */
    public function getShippingTablerateMethodByCode($code)
    {
        $shippingTablerateMethod = null;
        $shippingTablerateMethods = $this->getShippingTablerateMethods();
        foreach ($shippingTablerateMethods as $_shippingTablerateMethod) {
            if ($code == $_shippingTablerateMethod->getCode()) {
                $shippingTablerateMethod = $_shippingTablerateMethod;
                break;
            }
        }

        return $shippingTablerateMethod;
    }
}
