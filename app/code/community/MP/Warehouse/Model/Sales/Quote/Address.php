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
 * Quote address
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Sales_Quote_Address 
    extends Mage_Sales_Model_Quote_Address
{
    /**
     * Customer model
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;
    /**
     * Customer address model
     *
     * @var Mage_Customer_Model_Address
     */
    protected $_customerAddress;
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
     * Get version helper
     * 
     * @return MP_Warehouse_Helper_Core_Version
     */
    protected function getVersionHelper()
    {
        return $this->getWarehouseHelper()->getVersionHelper();
    }
    /**
     * Clone
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Address
     */
    public function __clone()
    {
        parent::__clone();
        $this->_items = null;
        $this->unsetItems();
        $this->unsetShippingRates();
        return $this;
    }
    /**
     * Get warehouse
     * 
     * @return MP_Warehouse_Model_Warehouse
     */
    public function getWarehouse()
    {
        $warehouse = null;
        if ($stockId = $this->getStockId()) {
            $warehouse = $this->getWarehouseHelper()->getWarehouseByStockId($stockId);
        }

        return $warehouse;
    }
    /**
     * Get warehouse title
     * 
     * @return string
     */
    public function getWarehouseTitle()
    {
        $warehouse = $this->getWarehouse();
        return ($warehouse) ? $warehouse->getTitle() : null;
    }
    /**
     * Check if address type is shipping
     * 
     * @return boolean
     */
    public function isShippingAddressType()
    {
        return ($this->getAddressType() == self::TYPE_SHIPPING) ? true : false;
    }
    /**
     * Check if address type is billing
     * 
     * @return boolean
     */
    public function isBillingAddressType()
    {
        return ($this->getAddressType() == self::TYPE_BILLING) ? true : false;
    }
    /**
     * Get all available address items
     * 
     * @return array
     */
    public function getAllItems()
    {
        $helper = $this->getWarehouseHelper();
        $cachedItems = $this->_nominalOnly ? 'nominal' : ($this->_nominalOnly === false ? 'nonnominal' : 'all');
        $key = 'cached_items_' . $cachedItems;
        if (!$this->hasData($key)) {
            $wasNominal = $this->_nominalOnly;
            $this->_nominalOnly = true;
            $quoteItems = $this->getQuote()->getItemsCollection();
            $addressItems = $this->getItemsCollection();
            $items = array();
            $nominalItems = array();
            $nonNominalItems = array();
            $addressType = $this->getAddressType();
            $canAddItems = ($this->getQuote()->isVirtual()) ? ($addressType == self::TYPE_BILLING) : ($addressType == self::TYPE_SHIPPING);
            if ($canAddItems) {
                foreach ($quoteItems as $qItem) {
                    if ($qItem->isDeleted() || (
                        $this->getStockId() && 
                        ($qItem->getStockId() != $this->getStockId()))
                    ) {
                        continue;
                    }

                    $items[] = $qItem;
                    if ($this->_filterNominal($qItem)) {
                        $nominalItems[] = $qItem;
                    } else {
                        $nonNominalItems[] = $qItem;
                    }
                }
            }

            $this->setData('cached_items_all', $items);
            $this->setData('cached_items_nominal', $nominalItems);
            $this->setData('cached_items_nonnominal', $nonNominalItems);
            $this->_nominalOnly = $wasNominal;
        }

        $items = $this->getData($key);
        return $items;
    }
    /**
     * Unset items
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Address
     */
    public function unsetItems()
    {
        $this->unsetData('cached_items_all');
        $this->unsetData('cached_items_nominal');
        $this->unsetData('cached_items_nonnominal');
        return $this;
    }
    /**
     * Unset shipping rates
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Address
     */
    public function unsetShippingRates()
    {
        $this->_rates = null;
        return $this;
    }
    /**
     * Check if address is virtual
     * 
     * @return bool
     */
    public function isVirtual()
    {
        $isVirtual = true;
        $count = 0;
        foreach ($this->getAllItems() as $item) {
            if ($item->isDeleted() || $item->getParentItemId()) {
                continue;
            }

            $count++;
            if (!$item->getProduct()->getIsVirtual()) {
                $isVirtual = false;
                break;
            }
        }

        if (!$count) {
            $isVirtual = false;
        }

        return $isVirtual;
    }
    /**
     * Get item free method weight
     * 
     * @param Mage_Sales_Model_Quote_Item $item
     * @param float $weight
     * @param float $qty
     * 
     * @return float
     */
    protected function _getItemFreeMethodWeight($item, $weight, $qty)
    {
        $freeMethodWeight       = $weight * $qty;
        if ($this->getFreeShipping() || $item->getFreeShipping() === true) {
            $freeMethodWeight       = 0;
        } elseif (is_numeric($item->getFreeShipping())) {
            $freeQty                = $item->getFreeShipping();
            if ($qty > $freeQty) {
                $freeMethodWeight       = $weight * ($qty - $freeQty);
            } else {
                $freeMethodWeight       = 0;
            }
        }

        return $freeMethodWeight;
    }
    /**
     * Get item free method weight
     * 
     * @param Mage_Sales_Model_Quote_Item $item
     * 
     * @return float
     */
    protected function getItemFreeMethodWeight($item)
    {
        $freeMethodWeight       = 0;
        $product                = $item->getProduct();
        if ($product->isVirtual() || $item->getParentItem()) {
            return $freeMethodWeight;
        }

        if ($item->getHasChildren() && $item->isShipSeparately()) {
            foreach ($item->getChildren() as $childItem) {
                $childProduct           = $childItem->getProduct();
                if ($childProduct->isVirtual()) {
                    continue;
                }

                if (!$product->getWeightType()) {
                    $freeMethodWeight       += $this->_getItemFreeMethodWeight($childItem, $childItem->getWeight(), $childItem->getTotalQty());
                }
            }

            if ($product->getWeightType()) {
                $freeMethodWeight       = $this->_getItemFreeMethodWeight($item, $item->getWeight(), $item->getQty());
            }
        } else {
            $freeMethodWeight       = $this->_getItemFreeMethodWeight($item, $item->getWeight(), $item->getQty());
        }

        return $freeMethodWeight;
    }
    /**
     * Get item package weight
     * 
     * @param Mage_Sales_Model_Quote_Item $item
     * 
     * @return float
     */
    protected function getItemPackageWeight($item)
    {
        $packageWeight          = 0;
        $product                = $item->getProduct();
        if ($product->isVirtual() || $item->getParentItem()) {
            return $packageWeight;
        }

        if ($item->getHasChildren() && $item->isShipSeparately()) {
            foreach ($item->getChildren() as $childItem) {
                $childProduct           = $childItem->getProduct();
                if ($childProduct->isVirtual()) {
                    continue;
                }

                if (!$product->getWeightType()) {
                    $packageWeight          += $childItem->getWeight() * $childItem->getTotalQty();
                }
            }

            if ($product->getWeightType()) {
                $packageWeight          = $item->getWeight() * $item->getQty();
            }
        } else {
            $packageWeight          = $item->getWeight() * $item->getQty();
        }

        return $packageWeight;
    }
    /**
     * Get item package qty
     * 
     * @param Mage_Sales_Model_Quote_Item $item
     * 
     * @return float
     */
    protected function getItemPackageQty($item)
    {
        $packageQty             = 0;
        $product                = $item->getProduct();
        if ($product->isVirtual() || $item->getParentItem()) {
            return $packageQty;
        }

        if ($item->getHasChildren() && $item->isShipSeparately()) {
            foreach ($item->getChildren() as $childItem) {
                $childProduct           = $childItem->getProduct();
                if ($childProduct->isVirtual()) {
                    continue;
                }

                $packageQty             += $childItem->getTotalQty();
            }
        } else {
            $packageQty             = $item->getQty();
        }

        return $packageQty;
    }
    /**
     * Get shipping carriers
     * 
     * @param array $items
     * 
     * @return array
     */
    public function getShippingCarriers($items = null)
    {
        $productHelper = $this->getWarehouseHelper()->getProductHelper();
        if (!is_null($items) && ($items instanceof Mage_Sales_Model_Quote_Item_Abstract)) {
            $items = array($items);
        }

        if (is_null($items)) {
            $items = $this->getAllItems();
        }

        $shippingCarriers = null;
        foreach ($items as $item) {
            $stockId = $item->getStockId();
            $product = $item->getProduct();
            $_shippingCarriers = $productHelper->getStockShippingCarriers($product, $stockId);
            if (is_null($shippingCarriers)) {
                $shippingCarriers = $_shippingCarriers;
            } else {
                $shippingCarriers = array_intersect($shippingCarriers, $_shippingCarriers);
            }
        }

        if (is_null($shippingCarriers)) {
            $shippingCarriers = array();
        }

        return $shippingCarriers;
    }
    /**
     * Get limited shipping carriers
     * 
     * @param array $items
     * 
     * @return array
     */
    public function getLimitedShippingCarriers($items = null)
    {
        $helper = $this->getWarehouseHelper();
        if ($helper->getConfig()->isShippingCarrierFilterEnabled()) {
            $shippingCarriers = $this->getShippingCarriers($items);
        } else {
            $shippingCarriers = null;
        }

        $limitCarrier = $this->getLimitCarrier();
        if (!empty($limitCarrier)) {
            if (!is_array($limitCarrier)) {
                $limitCarrier = array($limitCarrier);
            }

            if (is_null($shippingCarriers)) {
                $shippingCarriers = $limitCarrier;
            } else {
                $shippingCarriers = array_intersect($shippingCarriers, $limitCarrier);
            }
        }

        return $shippingCarriers;
    }
    /**
     * Retrieve shipping rates result
     * 
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @param array $warehouseIds
     * 
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function getShippingRatesResult($item = null)
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        $quote                  = $this->getQuote();
        $store                  = $quote->getStore();
        $request                = Mage::getModel('shipping/rate_request');
        $items                  = $item ? array($item) : $this->getAllItems();
        $request->setAllItems($items);
        $request->setDestCountryId($this->getCountryId());
        $request->setDestRegionId($this->getRegionId());
        $request->setDestRegionCode($this->getRegionCode());
        $request->setDestStreet($this->getStreet(-1));
        $request->setDestCity($this->getCity());
        $request->setDestPostcode($this->getPostcode());
        $request->setPackageValue($item ? $item->getBaseRowTotal() : $this->getBaseSubtotal());
        $packageValueWithDiscount = $item ? 
            $item->getBaseRowTotal() - $item->getBaseDiscountAmount() : 
            $this->getBaseSubtotalWithDiscount();
        $request->setPackageValueWithDiscount($packageValueWithDiscount);
        $packagePhysicalValue   = $item ? 
            $item->getBaseRowTotal() : 
            $this->getBaseSubtotal() - $this->getBaseVirtualAmount();
        $request->setPackagePhysicalValue($packagePhysicalValue);
        $request->setPackageWeight($item ? $item->getRowWeight() : $this->getWeight());
        $request->setPackageQty($item ? $item->getQty() : $this->getItemQty());
        $request->setFreeMethodWeight($item ? 0 : $this->getFreeMethodWeight());
        $request->setStoreId($store->getId());
        $request->setWebsiteId($store->getWebsiteId());
        $request->setFreeShipping($this->getFreeShipping());
        $request->setBaseCurrency($store->getBaseCurrency());
        $request->setPackageCurrency($store->getCurrentCurrency());
        $shippingCarriers       = $this->getLimitedShippingCarriers($item);
        if (!is_null($shippingCarriers)) {
            if (!$shippingCarriers) {
                $request->setLimitCarrier('none');
            } else {
                $request->setLimitCarrier($shippingCarriers);
            }
        }

        if ($this->getVersionHelper()->isGe1700()) {
            if ($this->getVersionHelper()->isGe1800()) {
                $request->setBaseSubtotalInclTax($this->getBaseSubtotalInclTax() + $this->getBaseExtraTaxAmount());
            } else {
                $request->setBaseSubtotalInclTax($this->getBaseSubtotalInclTax());
            }
        }

        if ($config->isMultipleMode() && !$config->isSplitOrderEnabled()) {
            if ($item) {
                $request->setStockIds(array($item->getStockId()));
            } else {
                $stockIds               = $this->getStockIds();
                $request->setStockIds($stockIds);
                if (count($stockIds)) {
                    $childRequests          = array();
                    foreach ($stockIds as $stockId) {
                        $packageValue           = 0;
                        $packageValueWithDiscount = 0;
                        $packagePhysicalValue   = 0;
                        $packageWeight          = 0;
                        $packageQty             = 0;
                        $freeMethodWeight       = 0;
                        if ($this->getVersionHelper()->isGe1700()) {
                            $baseSubtotalInclTax    = 0;
                        }

                        foreach ($this->getAllItems() as $item) {
                            if ($item->getStockId() && ($item->getStockId() == $stockId)) {
                                if ($item->getBaseRowTotal() > 0) {
                                    $packageValue           += $item->getBaseRowTotal();
                                    $packageValueWithDiscount += ($item->getBaseRowTotal() - $item->getBaseDiscountAmount());
                                    if (!$item->getProduct()->isVirtual()) {
                                        $packagePhysicalValue   += $item->getBaseRowTotal();
                                    }
                                }

                                $packageWeight          += $this->getItemPackageWeight($item);
                                $packageQty             += $this->getItemPackageQty($item);
                                $freeMethodWeight       += $this->getItemFreeMethodWeight($item);
                                if ($this->getVersionHelper()->isGe1700()) {
                                   if ($this->getVersionHelper()->isGe1800()) {
                                       $baseSubtotalInclTax     += $item->getBaseRowTotalInclTax() + $item->getBaseExtraTaxAmount();
                                   } else {
                                       $baseSubtotalInclTax     += $item->getBaseRowTotalInclTax();
                                   }
                                }

                                $childRequest           = new Varien_Object();
                                $childRequest->setPackageValue($packageValue)
                                    ->setPackageValueWithDiscount($packageValueWithDiscount)
                                    ->setPackagePhysicalValue($packagePhysicalValue)
                                    ->setPackageWeight($packageWeight)
                                    ->setPackageQty($packageQty)
                                    ->setFreeMethodWeight($freeMethodWeight)
                                    ->setStockId($stockId);
                                if ($this->getVersionHelper()->isGe1700()) {
                                    $childRequest->setBaseSubtotalInclTax($baseSubtotalInclTax);
                                }

                                $childRequests[$stockId] = $childRequest;
                            }
                        }
                    }

                    $request->setChildren($childRequests);
                }
            }
        } else {
            $stockId                = ($item) ? $item->getStockId() :  $this->getStockId();
            if ($stockId) {
                $request->setStockId($stockId);
            }
        }

        if (($request->getStockIds() && count($request->getStockIds())) || $request->getStockId()) {
            $result                 = Mage::getModel('shipping/shipping')
                ->collectRates($request)
                ->getResult();
        } else {
            $result                 = null;
        }

        if ($result) {
            return $result;
        } else {
            return null;
        }
    }
    /**
     * Request shipping rates for entire address or specified address item
     * Returns true if current selected shipping method code corresponds to one of the found rates
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * 
     * @return boolean
     */
    public function requestShippingRates(Mage_Sales_Model_Quote_Item_Abstract $item = null)
    {
        $helper = $this->getWarehouseHelper();
        $found = false;
        $result = $this->getShippingRatesResult($item);
        if ($result) {
            $shippingRates = $result->getAllRates();
            foreach ($shippingRates as $shippingRate) {
                $rate = Mage::getModel('sales/quote_address_rate')->importShippingRate($shippingRate);
                if (!$item) {
                    $this->addShippingRate($rate);
                }

                if ($this->getShippingMethod() == $rate->getCode()) {
                    if ($item) {
                        $item->setBaseShippingAmount($rate->getPrice());
                    } else {
                        $this->setShippingAmount($rate->getPrice());
                    }

                    $found = true;
                }
            }
        }

        return $found;
    }
    /**
     * Check if quote address is default
     * 
     * @return bool
     */
    public function isDefault()
    {
        $quote = $this->getQuote();
        if ($quote) {
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingAddress) {
                return (
                    ($this->getId() && ($this->getId() == $shippingAddress->getId())) ||
                    ($this->getStockId() && ($this->getStockId() == $shippingAddress->getStockId()))
                ) ? true : false;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * Set collect shipping rates flag
     * 
     * @param bool $value
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Address
     */
    public function setCollectShippingRates($value)
    {
        $this->setData('collect_shipping_rates', $value);
        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isMultipleMode() && $config->isSplitOrderEnabled()) {
            if ($value && ($this->isDefault()) && ($quote = $this->getQuote())) {
                foreach ($quote->getAllShippingAddresses() as $address) {
                    if (!$address->isDefault()) {
                        $address->setData('collect_shipping_rates', $value);
                    }
                }
            }
        }

        return $this;
    }
    /**
     * Recalculate stock
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Address
     */
    public function recalculateStockId()
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        $assignmentMethodHelper = $helper->getAssignmentMethodHelper();
        $quote                  = $this->getQuote();
        if ($config->isMultipleMode() || !$this->isShippingAddressType() || !$quote) {
            return $this;
        }

        $this->setStockId($assignmentMethodHelper->getQuoteStockId($quote));
        return $this;
    }
    /**
     * Get stock identifiers
     *
     * @return array
     */
    public function getStockIds()
    {
        $stockIds = array();
        foreach ($this->getAllItems() as $item) {
            $stockId = $item->getStockId();
            if ($stockId && !in_array($stockId, $stockIds)) {
                array_push($stockIds, $stockId);
            }
        }

        return $stockIds;
    }
    /**
     * Retrieve customer
     *
     * @return Mage_Customer_Model_Customer | false
     */
    public function getCustomer()
    {
        if (!$this->getCustomerId()) {
            return false;
        }

        if (!$this->_customer) {
            $this->_customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
        }

        return $this->_customer;
    }
    /**
     * Specify customer
     * 
     * @param Mage_Customer_Model_Customer $customer
     */
    public function setCustomer(Mage_Customer_Model_Customer $customer)
    {
        $this->_customer = $customer;
        $this->setCustomerId($customer->getId());
        return $this;
    }
    /**
     * Retrieve customer address
     *
     * @return Mage_Customer_Model_Address | false
     */
    public function getCustomerAddress()
    {
        if (!$this->getCustomerAddressId()) {
            return false;
        }

        if (!$this->_customerAddress) {
            $this->_customerAddress = Mage::getModel('customer/address')->load($this->getCustomerAddressId());
        }

        return $this->_customerAddress;
    }
    /**
     * Specify customer address
     *
     * @param Mage_Customer_Model_Address $customerAddress
     */
    public function setCustomerAddress(Mage_Customer_Model_Address $customerAddress)
    {
        $this->_customerAddress = $customerAddress;
        $this->setCustomerAddressId($customerAddress->getId());
        return $this;
    }
    /**
     * Clear order object data
     *
     * @param string $key data key
     * 
     * @return MP_Warehouse_Model_Sales_Quote_Address
     */
    public function unsetData($key=null)
    {
        parent::unsetData($key);
        if (is_null($key)) {
            $this->_customer = null;
            $this->_customerAddress = null;
            $this->unsetItems();
            $this->unsetShippingRates();
        }

        return $this;
    }
}
