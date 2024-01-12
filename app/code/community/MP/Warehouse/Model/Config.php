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
 * Warehouse config
 * 
 * @category    MP
 * @package     MP_Warehouse
 * @author      Mage Plugins Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Config 
    extends Varien_Object
{
    /**
     * Config path constants
     */
    /**
     * Options
     */
    const XML_PATH_OPTIONS_MODE                             = 'warehouse/options/mode';
    const XML_PATH_OPTIONS_DISPLAY_INFORMATION              = 'warehouse/options/display_information';
    const XML_PATH_OPTIONS_SORT_BY                          = 'warehouse/options/sort_by';
    const XML_PATH_OPTIONS_DISPLAY_ORIGIN                   = 'warehouse/options/display_origin';
    const XML_PATH_OPTIONS_DISPLAY_DISTANCE                 = 'warehouse/options/display_distance';
    const XML_PATH_OPTIONS_DISTANCE_UNIT                    = 'warehouse/options/distance_unit';
    const XML_PATH_OPTIONS_DISPLAY_DESCRIPTION              = 'warehouse/options/display_description';
    const XML_PATH_OPTIONS_SINGLE_ASSIGNMENT_METHOD         = 'warehouse/options/single_assignment_method';
    const XML_PATH_OPTIONS_MULTIPLE_ASSIGNMENT_METHOD       = 'warehouse/options/multiple_assignment_method';
    const XML_PATH_OPTIONS_MULTIPLE_ASSIGNMENT_TYPE         = 'warehouse/options/multiple_assignment_type';
    const XML_PATH_OPTIONS_SPLIT_ORDER                      = 'warehouse/options/split_order';
    const XML_PATH_OPTIONS_SPLIT_QTY                        = 'warehouse/options/split_qty';
    const XML_PATH_OPTIONS_FORCE_CART_NO_BACKORDERS         = 'warehouse/options/force_cart_no_backorders';
    const XML_PATH_OPTIONS_FORCE_CART_ITEM_NO_BACKORDERS    = 'warehouse/options/force_cart_item_no_backorders';
    const XML_PATH_OPTIONS_ALLOW_ADJUSTMENT                 = 'warehouse/options/allow_adjustment';
    /**
     * Catalog
     */
    const XML_PATH_CATALOG_DISPLAY_INFORMATION              = 'warehouse/catalog/display_information';
    const XML_PATH_CATALOG_DISPLAY_OUT_OF_STOCK             = 'warehouse/catalog/display_out_of_stock';
    const XML_PATH_CATALOG_DISPLAY_ORIGIN                   = 'warehouse/catalog/display_origin';
    const XML_PATH_CATALOG_DISPLAY_DISTANCE                 = 'warehouse/catalog/display_distance';
    const XML_PATH_CATALOG_DISPLAY_DESCRIPTION              = 'warehouse/catalog/display_description';
    const XML_PATH_CATALOG_DISPLAY_AVAILABILITY             = 'warehouse/catalog/display_availability';
    const XML_PATH_CATALOG_DISPLAY_QTY                      = 'warehouse/catalog/display_qty';
    const XML_PATH_CATALOG_DISPLAY_TAX                      = 'warehouse/catalog/display_tax';
    const XML_PATH_CATALOG_DISPLAY_SHIPPING                 = 'warehouse/catalog/display_shipping';
    
    const XML_PATH_CATALOG_DISPLAY_BACKEND_MANAGE_STOCK     = 'warehouse/catalog/display_backend_manage_stock';
    const XML_PATH_CATALOG_DISPLAY_BACKEND_MIN_QTY          = 'warehouse/catalog/display_backend_min_qty';
    const XML_PATH_CATALOG_DISPLAY_BACKEND_MIN_SALE_QTY     = 'warehouse/catalog/display_backend_min_sale_qty';
    const XML_PATH_CATALOG_DISPLAY_BACKEND_MAX_SALE_QTY     = 'warehouse/catalog/display_backend_max_sale_qty';
    const XML_PATH_CATALOG_DISPLAY_BACKEND_IS_QTY_DECIMAL   = 'warehouse/catalog/display_backend_is_qty_decimal';
    const XML_PATH_CATALOG_DISPLAY_BACKEND_BACKORDERS       = 'warehouse/catalog/display_backend_backorders';
    const XML_PATH_CATALOG_DISPLAY_BACKEND_NOTIFY_STOCK_QTY = 'warehouse/catalog/display_backend_notify_stock_qty';
    const XML_PATH_CATALOG_DISPLAY_BACKEND_ENABLE_QTY_INCREMENTS = 'warehouse/catalog/display_backend_enable_qty_increments';
    const XML_PATH_CATALOG_DISPLAY_BACKEND_QTY_INCREMENTS   = 'warehouse/catalog/display_backend_qty_increments';
    
    const XML_PATH_CATALOG_DISPLAY_BACKEND_GRID_QTY         = 'warehouse/catalog/display_backend_grid_qty';
    const XML_PATH_CATALOG_DISPLAY_BACKEND_GRID_BATCH_PRICES = 'warehouse/catalog/display_backend_grid_batch_prices';
    const XML_PATH_CATALOG_ENABLE_SHELVES                   = 'warehouse/catalog/enable_shelves';
    /**
     * Shipping
     */
    const XML_PATH_SHIPPING_ENABLE_CARRIER_FILTER           = 'warehouse/shipping/enable_carrier_filter';
    
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
     * Get store ids
     * 
     * @return array
     */
    protected function getStoreIds()
    {
        return $this
            ->getWarehouseHelper()
            ->getCoreHelper()
            ->getStoreIds();
    }    
    /**
     * Get mode
     * 
     * @param mixed $store
     * 
     * @return string
     */
    public function getMode($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_OPTIONS_MODE, $store);
    }
    /**
     * Check if single mode is enabled
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isSingleMode($store = null)
    {
        return ($this->getMode($store) == 'single') ? true : false;
    }
    /**
     * Check if single mode is enabled in any store
     * 
     * @return boolean
     */
    public function isSingleModeInAnyStore()
    {
        $isEnabled              = false;
        foreach ($this->getStoreIds() as $storeId) {
            if ($this->isSingleMode($storeId)) {
                $isEnabled              = true;
                break;
            }
        }

        return $isEnabled;
    }
    /**
     * Check if single mode is enabled in all stores
     * 
     * @return boolean
     */
    public function isSingleModeInAllStores()
    {
        $isEnabled              = true;
        foreach ($this->getStoreIds() as $storeId) {
            if (!$this->isSingleMode($storeId)) {
                $isEnabled              = false;
                break;
            }
        }

        return $isEnabled;
    }
    /**
     * Check if multiple mode is enabled
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isMultipleMode($store = null)
    {
        return ($this->getMode($store) == 'multiple') ? true : false;
    }
    /**
     * Check if multiple mode is enabled in any store
     * 
     * @return boolean
     */
    public function isMultipleModeInAnyStore()
    {
        $isEnabled              = false;
        foreach ($this->getStoreIds() as $storeId) {
            if ($this->isMultipleMode($storeId)) {
                $isEnabled              = true;
                break;
            }
        }

        return $isEnabled;
    }
    /**
     * Check if multiple mode is enabled in all stores
     * 
     * @return boolean
     */
    public function isMultipleModeInAllStores()
    {
        $isEnabled              = true;
        foreach ($this->getStoreIds() as $storeId) {
            if (!$this->isMultipleMode($storeId)) {
                $isEnabled              = false;
                break;
            }
        }

        return $isEnabled;
    }
    /**
     * Check if information is visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isInformationVisible($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_OPTIONS_DISPLAY_INFORMATION, $store);
    }
    /**
     * Get sort by
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function getSortBy($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_OPTIONS_SORT_BY, $store);
    }
    /**
     * Check if sort by id
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isSortById($store = null)
    {
        return ($this->getSortBy($store) == 'id') ? true : false;
    }
    /**
     * Check if sort by code
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isSortByCode($store = null)
    {
        return ($this->getSortBy($store) == 'code') ? true : false;
    }
    /**
     * Check if sort by title
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isSortByTitle($store = null)
    {
        return ($this->getSortBy($store) == 'title') ? true : false;
    }
    /**
     * Check if sort by priority
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isSortByPriority($store = null)
    {
        return ($this->getSortBy($store) == 'priority') ? true : false;
    }
    /**
     * Check if sort by origin
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isSortByOrigin($store = null)
    {
        return ($this->getSortBy($store) == 'origin') ? true : false;
    }
    /**
     * Check if origin is visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isOriginVisible($store = null)
    {
        return (
            $this->isInformationVisible($store) && 
            Mage::getStoreConfigFlag(self::XML_PATH_OPTIONS_DISPLAY_ORIGIN, $store)
        ) ? true : false;
    }
    /**
     * Check if distance is visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isDistanceVisible($store = null)
    {
        return (
            $this->isInformationVisible($store) && 
            Mage::getStoreConfigFlag(self::XML_PATH_OPTIONS_DISPLAY_DISTANCE, $store)
        ) ? true : false;
    }
    /**
     * Get distance unit
     * 
     * @param mixed $store
     * 
     * @return string
     */
    public function getDistanceUnit($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_OPTIONS_DISTANCE_UNIT, $store);
    }
    /**
     * Check if mile distance unit is enabled
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isMileDistanceUnit($store = null)
    {
        return ($this->getDistanceUnit($store) == 'mi') ? true : false;
    }
    /**
     * Check if kilometer distance unit is enabled
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isKilometerDistanceUnit($store = null)
    {
        return ($this->getDistanceUnit($store) == 'km') ? true : false;
    }
    /**
     * Check if description is visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isDescriptionVisible($store = null)
    {
        return (
            $this->isInformationVisible($store) && 
            Mage::getStoreConfigFlag(self::XML_PATH_OPTIONS_DISPLAY_DESCRIPTION, $store)
        ) ? true : false;
    }
    /**
     * Check if split order is enabled
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isSplitOrderEnabled($store = null)
    {
        if ($this->isMultipleMode($store)) {
            return (
                (Mage::getStoreConfigFlag(self::XML_PATH_OPTIONS_SPLIT_ORDER, $store)) && 
                !($this->getWarehouseHelper()->isPayPalExpressRequest())
            ) ? true : false;
        } else {
            return false;
        }
    }
    /**
     * Check if split quantity is enabled
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isSplitQtyEnabled($store = null)
    {
        if ($this->isMultipleMode($store)) {
            return Mage::getStoreConfigFlag(self::XML_PATH_OPTIONS_SPLIT_QTY, $store);
        } else {
            return false;
        }
    }
    /**
     * Check if force cart no backorders is enabled
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isForceCartNoBackordersEnabled($store = null)
    {
        if ($this->isMultipleMode($store)) {
            return Mage::getStoreConfigFlag(self::XML_PATH_OPTIONS_FORCE_CART_NO_BACKORDERS, $store);
        } else {
            return false;
        }
    }
    /**
     * Check if force cart item no backorders is enabled
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isForceCartItemNoBackordersEnabled($store = null)
    {
        if ($this->isMultipleMode($store)) {
            return Mage::getStoreConfigFlag(self::XML_PATH_OPTIONS_FORCE_CART_ITEM_NO_BACKORDERS, $store);
        } else {
            return false;
        }
    }
    /**
     * Get single assignment method code
     * 
     * @param mixed $store
     * 
     * @return string
     */
    public function getSingleAssignmentMethodCode($store = null)
    {
        if ($this->isSingleMode($store)) {
            return Mage::getStoreConfig(self::XML_PATH_OPTIONS_SINGLE_ASSIGNMENT_METHOD, $store);
        } else {
            return null;
        }
    }
    /**
     * Check if assigned areas is the current single assignment method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isAssignedAreaSingleAssignmentMethod($store = null)
    {
        return ($this->getSingleAssignmentMethodCode($store) == 'assigned_area') ? true : false;
    }
    /**
     * Check if assigned areas is the current single assignment method in any store
     * 
     * @return boolean
     */
    public function isAssignedAreaSingleAssignmentMethodInAnyStore()
    {
        $isEnabled              = false;
        foreach ($this->getStoreIds() as $storeId) {
            if ($this->isAssignedAreaSingleAssignmentMethod($storeId)) {
                $isEnabled              = true;
                break;
            }
        }

        return $isEnabled;
    }
    /**
     * Check if nearest is the current single assignment method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isNearestSingleAssignmentMethod($store = null)
    {
        return ($this->getSingleAssignmentMethodCode($store) == 'nearest') ? true : false;
    }
    /**
     * Check if nearest is the current single assignment method in any store
     * 
     * @return boolean
     */
    public function isNearestSingleAssignmentMethodInAnyStore()
    {
        $isEnabled              = false;
        foreach ($this->getStoreIds() as $storeId) {
            if ($this->isNearestSingleAssignmentMethod($storeId)) {
                $isEnabled              = true;
                break;
            }
        }

        return $isEnabled;
    }
    /**
     * Check if assigned store is the current single assignment method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isAssignedStoreSingleAssignmentMethod($store = null)
    {
        return ($this->getSingleAssignmentMethodCode($store) == 'assigned_store') ? true : false;
    }
    /**
     * Check if assigned store is the current single assignment method in any store
     * 
     * @return boolean
     */
    public function isAssignedStoreSingleAssignmentMethodInAnyStore()
    {
        $isEnabled              = false;
        foreach ($this->getStoreIds() as $storeId) {
            if ($this->isAssignedStoreSingleAssignmentMethod($storeId)) {
                $isEnabled              = true;
                break;
            }
        }

        return $isEnabled;
    }
    /**
     * Check if assigned customer group is the current single assignment method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isAssignedCustomerGroupSingleAssignmentMethod($store = null)
    {
        return ($this->getSingleAssignmentMethodCode($store) == 'assigned_customer_group') ? true : false;
    }
    /**
     * Check if assigned customer group is the current single assignment method in any store
     * 
     * @return boolean
     */
    public function isAssignedCustomerGroupSingleAssignmentMethodInAnyStore()
    {
        $isEnabled              = false;
        foreach ($this->getStoreIds() as $storeId) {
            if ($this->isAssignedCustomerGroupSingleAssignmentMethod($storeId)) {
                $isEnabled              = true;
                break;
            }
        }

        return $isEnabled;
    }
    /**
     * Check if assigned currency is the current single assignment method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isAssignedCurrencySingleAssignmentMethod($store = null)
    {
        return ($this->getSingleAssignmentMethodCode($store) == 'assigned_currency') ? true : false;
    }
    /**
     * Check if assigned currency is the current single assignment method in any store
     * 
     * @return boolean
     */
    public function isAssignedCurrencySingleAssignmentMethodInAnyStore()
    {
        $isEnabled              = false;
        foreach ($this->getStoreIds() as $storeId) {
            if ($this->isAssignedCurrencySingleAssignmentMethod($storeId)) {
                $isEnabled              = true;
                break;
            }
        }

        return $isEnabled;
    }
    /**
     * Check if manual is the current single assignment method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isManualSingleAssignmentMethod($store = null)
    {
        return ($this->getSingleAssignmentMethodCode($store) == 'manual') ? true : false;
    }
    /**
     * Get multiple assignment method code
     * 
     * @param mixed $store
     * 
     * @return string
     */
    public function getMultipleAssignmentMethodCode($store = null)
    {
        if ($this->isMultipleMode($store)) {
            return Mage::getStoreConfig(self::XML_PATH_OPTIONS_MULTIPLE_ASSIGNMENT_METHOD, $store);
        } else {
            return null;
        }
    }
    /**
     * Check if assigned areas is the current multiple assignment method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isAssignedAreaMultipleAssignmentMethod($store = null)
    {
        return ($this->getMultipleAssignmentMethodCode($store) == 'assigned_area') ? true : false;
    }
    /**
     * Check if assigned areas is the current multiple assignment method in any store
     * 
     * @return boolean
     */
    public function isAssignedAreaMultipleAssignmentMethodInAnyStore()
    {
        $isEnabled              = false;
        foreach ($this->getStoreIds() as $storeId) {
            if ($this->isAssignedAreaMultipleAssignmentMethod($storeId)) {
                $isEnabled              = true;
                break;
            }
        }

        return $isEnabled;
    }
    /**
     * Check if lowest shipping is the current multiple assignment method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isLowestShippingMultipleAssignmentMethod($store = null)
    {
        return ($this->getMultipleAssignmentMethodCode($store) == 'lowest_shipping') ? true : false;
    }
    /**
     * Check if lowest tax is the current multiple assignment method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isLowestTaxMultipleAssignmentMethod($store = null)
    {
        return ($this->getMultipleAssignmentMethodCode($store) == 'lowest_tax') ? true : false;
    }
    /**
     * Check if lowest subtotal is the current multiple assignment method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isLowestSubtotalMultipleAssignmentMethod($store = null)
    {
        return ($this->getMultipleAssignmentMethodCode($store) == 'lowest_subtotal') ? true : false;
    }
    /**
     * Check if lowest grand total is the current multiple assignment method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isLowestGrandTotalMultipleAssignmentMethod($store = null)
    {
        return ($this->getMultipleAssignmentMethodCode($store) == 'lowest_grand_total') ? true : false;
    }
    /**
     * Check if nearest is the current multiple assignment method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isNearestMultipleAssignmentMethod($store = null)
    {
        return ($this->getMultipleAssignmentMethodCode($store) == 'nearest') ? true : false;
    }
    /**
     * Check if nearest is the current multiple assignment method in any store
     * 
     * @return boolean
     */
    public function isNearestMultipleAssignmentMethodInAnyStore()
    {
        $isEnabled              = false;
        foreach ($this->getStoreIds() as $storeId) {
            if ($this->isNearestMultipleAssignmentMethod($storeId)) {
                $isEnabled              = true;
                break;
            }
        }

        return $isEnabled;
    }
    /**
     * Check if priority is the current multiple assignment method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isPriorityMultipleAssignmentMethod($store = null)
    {
        return ($this->getMultipleAssignmentMethodCode($store) == 'priority') ? true : false;
    }
    /**
     * Check if manual is the current multiple assignment method
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isManualMultipleAssignmentMethod($store = null)
    {
        return ($this->getMultipleAssignmentMethodCode($store) == 'manual') ? true : false;
    }
    /**
     * Get multiple assignment type code
     * 
     * @param mixed $store
     * 
     * @return string
     */
    public function getMultipleAssignmentTypeCode($store = null)
    {
        if ($this->isMultipleMode($store)) {
            return Mage::getStoreConfig(self::XML_PATH_OPTIONS_MULTIPLE_ASSIGNMENT_TYPE, $store);
        } else {
            return null;
        }
    }
    /**
     * Check if cart is the current multiple assignment type
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCartMultipleAssignmentType($store = null)
    {
        return ($this->getMultipleAssignmentTypeCode($store) == 'cart') ? true : false;
    }
    /**
     * Check if cart item is the current multiple assignment type
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCartItemMultipleAssignmentType($store = null)
    {
        return ($this->getMultipleAssignmentTypeCode($store) == 'cart_item') ? true : false;
    }
    /**
     * Check if adjustment is allowed
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isAllowAdjustment($store = null)
    {
        $helper                 = $this->getWarehouseHelper();
        return (
            $helper->isAdmin() || 
            Mage::getStoreConfigFlag(self::XML_PATH_OPTIONS_ALLOW_ADJUSTMENT, $store) || 
            $this->isManualSingleAssignmentMethod($store) || 
            $this->isManualMultipleAssignmentMethod($store)
        ) ? true : false;
    }
    /**
     * Check if priority is enabled
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isPriorityEnabled($store = null)
    {
        return (
            $this->isPriorityMultipleAssignmentMethod($store) || 
            $this->isSortByPriority($store) || 
            $this->isSplitQtyEnabled($store)
        ) ? true : false;
    }
    /**
     * Check if priority is enabled in any store
     * 
     * @return boolean
     */
    public function isPriorityEnabledInAnyStore()
    {
        $isEnabled              = false;
        foreach ($this->getStoreIds() as $storeId) {
            if ($this->isPriorityEnabled($storeId)) {
                $isEnabled              = true;
                break;
            }
        }

        return $isEnabled;
    }
    /**
     * Check if catalog information visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogInformationVisible($store = null)
    {
        return (
            $this->isInformationVisible($store) && 
                Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_INFORMATION, $store)
        ) ? true : false;
    }
    /**
     * Check if catalog out of stock visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogOutOfStockVisible($store = null)
    {
        return (
            $this->isCatalogInformationVisible($store) && 
                Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_OUT_OF_STOCK, $store)
        ) ? true : false;
    }
    /**
     * Check if catalog origin visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogOriginVisible($store = null)
    {
        return (
            $this->isCatalogInformationVisible($store) && 
                Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_ORIGIN, $store)
        ) ? true : false;
    }
    /**
     * Check if catalog distance visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogDistanceVisible($store = null)
    {
        return (
            $this->isCatalogInformationVisible($store) && 
                Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_DISTANCE, $store)
        ) ? true : false;
    }
    /**
     * Check if catalog description visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogDescriptionVisible($store = null)
    {
        return (
            $this->isCatalogInformationVisible($store) && 
                Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_DESCRIPTION, $store)
        ) ? true : false;
    }
    /**
     * Check if catalog availability visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogAvailabilityVisible($store = null)
    {
        return (
            $this->isCatalogInformationVisible($store) && 
                Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_AVAILABILITY, $store)
        ) ? true : false;
    }
    /**
     * Check if catalog qty visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogQtyVisible($store = null)
    {
        return (
            $this->isCatalogInformationVisible($store) && 
                Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_QTY, $store)
        ) ? true : false;
    }
    /**
     * Check if catalog tax visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogTaxVisible($store = null)
    {
        return (
            $this->isCatalogInformationVisible($store) && 
                Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_TAX, $store)
        ) ? true : false;
    }
    /**
     * Check if catalog shipping visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogShippingVisible($store = null)
    {
        return (
            $this->isCatalogInformationVisible($store) && 
                Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_SHIPPING, $store)
        ) ? true : false;
    }
    /**
     * Check if catalog backend manage stock visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogBackendManageStockVisible($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_BACKEND_MANAGE_STOCK, $store);
    }
    /**
     * Check if catalog backend min qty visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogBackendMinQtyVisible($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_BACKEND_MIN_QTY, $store);
    }
    /**
     * Check if catalog backend min sale qty visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogBackendMinSaleQtyVisible($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_BACKEND_MIN_SALE_QTY, $store);
    }
    /**
     * Check if catalog backend max sale qty visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogBackendMaxSaleQtyVisible($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_BACKEND_MAX_SALE_QTY, $store);
    }
    /**
     * Check if catalog backend is qty decimal visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogBackendIsQtyDecimalVisible($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_BACKEND_IS_QTY_DECIMAL, $store);
    }
    /**
     * Check if catalog backend backorders visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogBackendBackordersVisible($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_BACKEND_BACKORDERS, $store);
    }
    /**
     * Check if catalog backend notify stock qty visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogBackendNotifyStockQtyVisible($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_BACKEND_NOTIFY_STOCK_QTY, $store);
    }
    /**
     * Check if catalog backend enable qty increments visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogBackendEnableQtyIncrementsVisible($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_BACKEND_ENABLE_QTY_INCREMENTS, $store);
    }
    /**
     * Check if catalog backend qty increments visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogBackendQtyIncrementsVisible($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_BACKEND_QTY_INCREMENTS, $store);
    }
    /**
     * Check if catalog backend grid qty visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogBackendGridQtyVisible($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_BACKEND_GRID_QTY, $store);
    }
    /**
     * Check if catalog backend grid batch prices visible
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isCatalogBackendGridBatchPricesVisible($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_DISPLAY_BACKEND_GRID_BATCH_PRICES, $store);
    }
    /**
     * Check if shelves function is enabled
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isShelvesEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CATALOG_ENABLE_SHELVES, $store);
    }
    /**
     * Check if shipping methods filter is enabled
     * 
     * @param mixed $store
     * 
     * @return boolean
     */
    public function isShippingCarrierFilterEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SHIPPING_ENABLE_CARRIER_FILTER, $store);
    }
    /**
     * Check if shipping methods filter is enabled in any store
     * 
     * @return boolean
     */
    public function isShippingCarrierFilterEnabledInAnyStore()
    {
        $isEnabled              = false;
        foreach ($this->getStoreIds() as $storeId) {
            if ($this->isShippingCarrierFilterEnabled($storeId)) {
                $isEnabled              = true;
                break;
            }
        }

        return $isEnabled;
    }
}
