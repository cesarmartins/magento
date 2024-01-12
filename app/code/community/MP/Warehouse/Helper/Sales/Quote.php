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
 * Quote helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Sales_Quote 
    extends Mage_Core_Helper_Abstract
{
    /**
     * Get warehouse helper
     * 
     * @return MP_Warehouse_Helper_Data
     */
    public function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Get stock items combinations
     * 
     * @param Mage_Sales_Model_Quote $quote
     * @param array of Varien_Object $stockData
     * 
     * @return array
     */
    public function getStockItemsCombinations($quote, $stockData = null)
    {
        $combinations = array();
        if (is_null($stockData)) {
            $stockData = $this->getStockData($quote);
        }

        $itemsStockIds = array();
        if (count($stockData)) {
            foreach ($stockData as $itemKey => $itemStockData) {
                if ($itemStockData->getIsInStock()) {
                    if (!$itemStockData->getSessionStockId()) {
                        foreach ($itemStockData->getStockIds() as $stockId) {
                            $itemsStockIds[$itemKey][] = $stockId;
                        }
                    } else {
                        $itemsStockIds[$itemKey][] = $itemStockData->getSessionStockId();
                    }
                } else {
                    $itemsStockIds[$itemKey] = array();
                }
            }
        }

        if (count($itemsStockIds)) {
            foreach ($itemsStockIds as $itemKey => $itemStockIds) {
                $_combinations = array();
                if (count($itemStockIds)) {
                    foreach ($itemStockIds as $itemStockId) {
                        if (count($combinations)) {
                            foreach ($combinations as $combination) {
                                $combination[$itemKey] = $itemStockId;
                                $_combinations[] = $combination;
                            }
                        } else {
                            array_push($_combinations, array($itemKey => $itemStockId));
                        }
                    }
                } else {
                    $_combinations = $combinations;
                }

                $combinations = $_combinations;
            }
        }

        return $combinations;
    }
    /**
     * Get shipping rates combinations
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return array
     */
    public function getShippingRateCombinations($quote)
    {
        $combinations = array();
        foreach ($quote->getAllShippingAddresses() as $shippingAddress) {
            $stockId        = (int) $shippingAddress->getStockId();
            $_combinations  = array();
            if (count($shippingAddress->getAllShippingRates())) {
                foreach ($shippingAddress->getAllShippingRates() as $shippingRate) {
                    if (count($combinations)) {
                        foreach ($combinations as $combination) {
                            $combination[$stockId] = $shippingRate->getCode();
                            $_combinations[] = $combination;
                        }
                    } else {
                        array_push($_combinations, array($stockId => $shippingRate->getCode()));
                    }
                }
            } else {
                $_combinations = $combinations;
            }

            $combinations = $_combinations;
        }

        return $combinations;
    }
    /**
     * Get min value
     * 
     * @param Mage_Sales_Model_Quote $quote
     * @param string $valueGetter
     * 
     * @return float
     */
    public function getMinValue($quote, $valueGetter)
    {
        $value = null;
        $shippingRateCombinations = $this->getShippingRateCombinations($quote);
        if (count($shippingRateCombinations)) {
            foreach ($shippingRateCombinations as $shippingRateCombination) {
                $shippingAddresses = $quote->getAllShippingAddresses();
                $enabled = true;
                if (count($shippingAddresses) == count($shippingRateCombination)) {
                    foreach ($shippingAddresses as $shippingAddress) {
                        $stockId = (int) $shippingAddress->getStockId();
                        if (isset($shippingRateCombination[$stockId])) {
                            $shippingRateCode = $shippingRateCombination[$stockId];
                            $shippingAddress->setShippingMethod($shippingRateCode);
                        } else {
                            $enabled = false;
                            break;
                        }
                    }
                } else {
                    $enabled = false;
                }

                if ($enabled) {
                    $quote->setTotalsCollectedFlag(false);
                    $quote->collectTotals();
                    $combinationValue = $this->$valueGetter($quote);
                    if (!is_null($combinationValue) && ((is_null($value)) || ($combinationValue < $value))) {
                        $value = $combinationValue;
                    }
                }
            }
        } else {
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            $combinationValue = $this->$valueGetter($quote);
            if (!is_null($combinationValue) && ((is_null($value)) || ($combinationValue < $value))) {
                $value = $combinationValue; 
            }
        }

        return $value;
    }
    /**
     * Get stock items combination min value
     * 
     * @param Mage_Sales_Model_Quote $quote
     * @param array of Varien_Object $stockData
     * @param array $combinations
     * @param string $valueGetter
     * 
     * @return float
     */
    public function getStockItemsCombinationMinValue($quote, $stockData, $combination, $valueGetter)
    {
        if (!$quote) {
            return null;
        }

        $helper         = $this->getWarehouseHelper();
        $quoteClone     = clone $quote;
        $quoteClone->applyStockItemsCombination($stockData, $combination);
        $quoteClone->applyStockAddresses();
        foreach ($quoteClone->getAllShippingAddresses() as $shippingAddress) {
            $this->getWarehouseHelper()->copyCustomerAddressIfEmpty($shippingAddress);
            $shippingAddress->collectTotals();
            
            if ($helper->getAssignmentMethodHelper()->getCurrentMethod()->isBasedOnShipping()) {
                $shippingAddress->setCollectShippingRates(true);
            } else {
                $shippingAddress->setCollectShippingRates(false);
            }
            
            $shippingAddress->collectShippingRates();
        }

        $value = $this->getMinValue($quoteClone, $valueGetter);
        unset($quoteClone);
        return $value;
    }
    /**
     * Get min value stock items combination
     * 
     * @param Mage_Sales_Model_Quote $quote
     * @param array of Varien_Object $stockData
     * @param array $combinations
     * @param string $valueGetter
     * 
     * @return array
     */
    public function getMinValueStockItemsCombination($quote, $stockData, $combinations, $valueGetter)
    {
        $combination = null;
        if (count($combinations)) {
            $minValue   = null; 
            $index      = null;
            foreach ($combinations as $combinationIndex => $combination) {
                $value = $this->getStockItemsCombinationMinValue($quote, $stockData, $combination, $valueGetter);
                if (!is_null($value) && ((is_null($minValue)) || ($value < $minValue))) {
                    $minValue   = $value;
                    $index      = $combinationIndex;
                }
            }

            if (isset($combinations[$index])) {
                $combination = $combinations[$index];
            } else {
                $combination = current($combinations);
            }
        }

        return $combination;
    }
    /**
     * Apply stock items
     * 
     * @param Mage_Sales_Model_Quote $quote
     * @param array of Varien_Object $stockData
     * @param string $valueGetter
     * 
     * @return MP_Warehouse_Helper_Sales_Quote
     */
    public function applyStockItems($quote, $stockData, $valueGetter)
    {
        if (!$quote || !$quote->checkStockData($stockData)) {
            return $this;
        }

        $combinations = $this->getStockItemsCombinations($quote, $stockData);
        if (!count($combinations)) {
            return $this;
        }

        $combination = null;
        if (count($combinations) > 1) {
            $combination = $this->getMinValueStockItemsCombination(
                $quote, 
                $stockData, 
                $combinations, 
                $valueGetter
            );
        } else {
            $combination = current($combinations);
        }

        if (!is_null($combination)) {
            $quote->applyStockItemsCombination($stockData, $combination);
        }

        return $this;
    }
    /**
     * Get shipping amount
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return float
     */
    public function getShippingAmount($quote)
    {
        $shippingAmount = null;
        foreach ($quote->getAllAddresses() as $address) {
            $addressShippingAmount = $address->getShippingAmount();
            if (!is_null($addressShippingAmount)) {
                if (is_null($shippingAmount)) {
                    $shippingAmount = 0;
                }

                $shippingAmount += $addressShippingAmount;
            }
        }

        return $shippingAmount;
    }
    /**
     * Get tax amount
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return float
     */
    public function getTaxAmount($quote)
    {
        $taxAmount = null;
        foreach ($quote->getAllAddresses() as $address) {
            $addressTaxAmount = $address->getTaxAmount();
            if (!is_null($addressTaxAmount)) {
                if (is_null($taxAmount)) {
                    $taxAmount = 0;
                }

                $taxAmount += $addressTaxAmount;
            }
        }

        return $taxAmount;
    }
    /**
     * Get subtotal
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return float
     */
    public function getSubtotal($quote)
    {
        $subtotal = null;
        foreach ($quote->getAllAddresses() as $address) {
            $addressSubtotal = $address->getSubtotal();
            if (!is_null($addressSubtotal)) {
                if (is_null($subtotal)) {
                    $subtotal = 0;
                }

                $subtotal += $addressSubtotal;
            }
        }

        return $subtotal;
    }
    /**
     * Get grand total
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return float
     */
    public function getGrandTotal($quote)
    {
        $grandTotal = null;
        foreach ($quote->getAllAddresses() as $address) {
            $addressGrandTotal = $address->getGrandTotal();
            if (!is_null($addressGrandTotal)) {
                if (is_null($grandTotal)) {
                    $grandTotal = 0;
                }

                $grandTotal += $addressGrandTotal;
            }
        }

        return $grandTotal;
    }
    /**
     * Get stock data
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return array of Varien_Object
     */
    public function getStockData($quote)
    {
        $stockData = null;
        if (!$quote) {
            return $stockData;
        }

        $config                     = $this->getWarehouseHelper()->getConfig();
        $splitQty                   = $config->isSplitQtyEnabled();
        $forceCartNoBackorders      = $config->isForceCartNoBackordersEnabled();
        $forceCartItemNoBackorders  = $config->isForceCartItemNoBackordersEnabled();
        if ($forceCartNoBackorders) {
            $_stockData = $quote->getStockData($splitQty, true, false);
            if ($quote->checkStockData($_stockData)) {
                $stockData = $_stockData;
            }
        }

        if (is_null($stockData) && $forceCartItemNoBackorders) {
            $_stockData = $quote->getStockData($splitQty, false, true);
            if ($quote->checkStockData($_stockData)) {
                $stockData = $_stockData;
            }
        }

        if (is_null($stockData)) {
            $_stockData = $quote->getStockData($splitQty);
            if ($quote->checkStockData($_stockData)) {
                $stockData = $_stockData;
            }
        }

        return $stockData;
    }
    /**
     * Get quote by product and stock identifier
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param int $stockId
     * @raram Varien_Object $buyRequest
     * 
     * @return Mage_Sales_Model_Quote
     */
    public function getQuoteByProductAndStockId($product, $stockId, $buyRequest)
    {
        $helper     = $this->getWarehouseHelper();
        try {
            $quote      = Mage::getModel('sales/quote');
            $quote->setStoreId($product->getStoreId());
            $quote->setIsStockIdStatic(true);
            $quote->setIsSuperMode(true);
            $item = $quote->addProduct($product, $buyRequest);
            if (is_string($item)) {
                return $quote;
            }

            $items      = $quote->getAllVisibleItems();
            $itemsCount = count($items);
            foreach ($items as $_item) {
                if (($itemsCount == 1) || ($_item->isParentItem())) {
                    $item = $_item;
                    break;
                }
            }

            $item->setIsStockIdStatic(true);
            $item->setStockId($stockId);
            $item->setData('qty', $buyRequest->getQty());
            $item->setIsStockIdApplied(true);
            if ($item->isParentItem()) {
                foreach ($item->getChildren() as $childItem) {
                    $childItem->setStockId($stockId);
                    $childItem->setIsStockIdApplied(true);
                }
            }

            $quote->getShippingAddress();
            if (!$quote->isVirtual()) {
                $shippingAddress = $quote->getShippingAddress();
                $helper->copyCustomerAddressIfEmpty($shippingAddress);
                $shippingAddress->setStockId($stockId);
                //$shippingAddress->collectTotals();
                
                if (($helper->getAssignmentMethodHelper()->getCurrentMethod()->isBasedOnShipping()) || 
                    ($helper->getConfig()->isCatalogShippingVisible())
                ) {
                   // $shippingAddress->setCollectShippingRates(true);
                } else {
                  //  $shippingAddress->setCollectShippingRates(false);
                }
                
               // $shippingAddress->collectShippingRates();
            } else {
                $billingAddress = $quote->getBillingAddress();
                $helper->copyCustomerAddressIfEmpty($billingAddress);
                $billingAddress->setStockId($stockId);
                //$billingAddress->collectTotals();
            }
        } catch (Mage_Core_Exception $e) {  
        }

        return $quote;
    }
    /**
     * Sort shipping rates
     * 
     * @param Varien_Object $shippingRate1
     * @param Varien_Object $shippingRate2
     * 
     * @return int
     */
    protected function sortShippingRates($shippingRate1, $shippingRate2)
    {
        $sortOrder1 = $shippingRate1->getCarrierSortOrder();
        $sortOrder2 = $shippingRate2->getCarrierSortOrder();
        if ($sortOrder1 != $sortOrder2) {
            return $sortOrder1 < $sortOrder2 ? -1 : 1;
        }

        return 0;
    }
    /**
     * Get grouped shipping rates
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return array
     */
    public function getGroupedShippingRates($quote)
    {
        if (!$quote->hasGroupedShippingRates()) {
            $shippingRates          = array();
            $groupedShippingRates   = array();
            $helper                 = $this->getWarehouseHelper();
            $taxHelper              = $helper->getTaxHelper();
            $shippingAddress        = $quote->getShippingAddress();
            $collection             = $shippingAddress->getShippingRatesCollection();
            if (count($collection)) {
                foreach ($collection as $rate) {
                    $carrier = $rate->getCarrierInstance();
                    if ($rate->isDeleted() || !$carrier) {
                        continue;
                    }

                    $shippingRate = new Varien_Object();
                    $shippingRate
                        ->setCode($rate->getCode())
                        ->setCarrier($rate->getCarrier())
                        ->setCarrierTitle($rate->getCarrierTitle())
                        ->setCarrierSortOrder($carrier->getSortOrder())
                        ->setMethod($rate->getMethod())
                        ->setMethodTitle($rate->getMethodTitle())
                        ->setPrice($rate->getPrice())
                        ->setPriceExcTax($taxHelper->getShippingPrice($rate->getPrice(), false, $shippingAddress))
                        ->setPriceIncTax($taxHelper->getShippingPrice($rate->getPrice(), true, $shippingAddress))
                        ->setErrorMessage($rate->getErrorMessage());
                    $shippingRates[] = $shippingRate;
                }
            }

            if (count($shippingRates)) {
                if (count($shippingRates) > 1) {
                    usort($shippingRates, array($this, 'sortShippingRates'));
                }

                foreach ($shippingRates as $shippingRate) {
                    $groupedShippingRates[$shippingRate->getCarrier()][$shippingRate->getMethod()] = $shippingRate;
                }
            }

            $quote->setGroupedShippingRates($groupedShippingRates);
        }

        return $quote->getGroupedShippingRates();
    }
    /**
     * Get min shipping rate
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return Varien_Object
     */
    public function getMinShippingRate($quote)
    {
        $minShippingRate    = null;
        $minPrice           = null;
        $groupedShippingRates = $this->getGroupedShippingRates($quote);
        if (count($groupedShippingRates)) {
            foreach ($groupedShippingRates as $carrierShippingRates) {
                if (count($carrierShippingRates)) {
                    foreach ($carrierShippingRates as $shippingRate) {
                        if (!$shippingRate->getErrorMessage()) {
                            $price = $shippingRate->getPrice();
                            if (!is_null($price) && (is_null($minPrice) || ($price < $minPrice))) {
                                $minPrice           = $price;
                                $minShippingRate    = $shippingRate;
                            }
                        }
                    }
                }
            }
        }

        return $minShippingRate;
    }
    /**
     * Get min shipping amount
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return float
     */
    public function getMinShippingAmount($quote)
    {
        return $this->getMinValue($quote, 'getShippingAmount');
    }
    /**
     * Get min tax amount
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return float
     */
    public function getMinTaxAmount($quote)
    {
        return $this->getMinValue($quote, 'getTaxAmount');
    }
    /**
     * Get min subtotal
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return float
     */
    public function getMinSubtotal($quote)
    {
        return $this->getMinValue($quote, 'getSubtotal');
    }
    /**
     * Get min grand total
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return float
     */
    public function getMinGrandTotal($quote)
    {
        return $this->getMinValue($quote, 'getGrandTotal');
    }
}
