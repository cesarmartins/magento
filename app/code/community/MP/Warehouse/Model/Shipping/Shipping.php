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
 * Shipping
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Shipping_Shipping 
    extends Mage_Shipping_Model_Shipping
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
     * Apply request origin
     * 
     * @param Mage_Shipping_Model_Shipping_Method_Request $request
     * @param int $stockId
     * 
     * @return self
     */
    protected function applyRequestOrigin($request, $stockId = null)
    {
        $helper                 = $this->getWarehouseHelper();
        if (!$stockId) {
            $stockId                = $request->getStockId();
        }

        if (!$stockId) {
            return $this;
        }

        $warehouse              = $helper->getWarehouseByStockId($stockId);
        if (!$warehouse) {
            return $this;
        }

        $origin                 = $warehouse->getOrigin();
        $origRegionId           = ($origin->getRegionId()) ? $origin->getRegionId() : $origin->getRegion();
        $origRegionCode         = $origRegionId;
        if (is_numeric($origRegionId)) {
            $origRegionCode         = $helper->getAddressHelper()
                ->getRegionById($origRegionCode)
                ->getCode();
        } else {
            $origRegionCode         = $origRegionId;
        }

        $request->setCountryId($origin->getCountryId());
        $request->setRegionId($origRegionId);
        $request->setCity($origin->getCity());
        $request->setPostcode($origin->getPostcode());
        $request->setOrigCountryId($origin->getCountryId());
        $request->setOrigRegionId($origRegionId);
        $request->setOrigCity($origin->getCity());
        $request->setOrigPostcode($origin->getPostcode());
        $request->setPrazoEnvio($warehouse->getPrazoEnvio());
        $request->setOrigCountry($origin->getCountryId());
        $request->setOrigRegionCode($origRegionCode);
        $request->setOrig($origin);
        $request->setWarehouseId($warehouse->getId());
        return $this;
    }
    /**
     * Get result rate by carrier and method
     * 
     * @param Mage_Shipping_Model_Rate_Result $result
     * @param string $carrier
     * @param string $method
     * 
     * @return Mage_Shipping_Model_Rate_Result_Method | null
     */
    protected function getResultRate($result, $carrier, $method)
    {
        $rate                   = null;
        if ($result && ($result instanceof Mage_Shipping_Model_Rate_Result)) {
            foreach ($result->getAllRates() as $_rate) {
                if (($_rate->getCarrier() == $carrier) && 
                    ($_rate->getMethod() == $method)
                ) {
                    $rate                   = $_rate;
                    break;
                }
            }
        }

        return $rate;
    }
    /**
     * Compose packages for carrier
     * 
     * @param Mage_Shipping_Model_Carrier_Abstract $carrier
     * @param Mage_Shipping_Model_Rate_Request $request
     * 
     * @return array
     */
    public function composePackagesForCarrier($carrier, $request)
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        $allItems               = $request->getAllItems();
        $fullItems              = array();
        $maxWeight              = (float) $carrier->getConfigData('max_package_weight');
        foreach ($allItems as $item) {
            if (($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) && 
                ($item->getProduct()->getShipmentType())
            ) {
                continue;
            }

            if ($config->isMultipleMode() && 
                !$config->isSplitOrderEnabled() && 
                ($item->getStockId() != $request->getStockId())
            ) {
                continue;
            }

            $qty                    = $item->getQty();
            $changeQty              = true;
            $checkWeight            = true;
            $decimalItems           = array();
            if ($item->getParentItem()) {
                $parentItem             = $item->getParentItem();
                if (!$parentItem->getProduct()->getShipmentType()) {
                    continue;
                }

                $qty                    = $item->getIsQtyDecimal() ? 
                    $parentItem->getQty() : 
                    $parentItem->getQty() * $item->getQty();
            }

            $itemWeight             = $item->getWeight();
            if (($item->getIsQtyDecimal()) && 
                ($item->getProductType() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
            ) {
                $stockItem              = $item->getStockItem();
                if ($stockItem->getIsDecimalDivided()) {
                    if ($stockItem->getEnableQtyIncrements() && $stockItem->getQtyIncrements()) {
                        $itemWeight          = $itemWeight * $stockItem->getQtyIncrements();
                        $qty                 = round(($item->getWeight() / $itemWeight) * $qty);
                        $changeQty           = false;
                    } else {
                        $itemWeight          = $itemWeight * $item->getQty();
                        if ($itemWeight > $maxWeight) {
                            $qtyItem                = floor($itemWeight / $maxWeight);
                            $decimalItems[]         = array('weight' => $maxWeight, 'qty' => $qtyItem);
                            $weightItem             = Mage::helper('core')
                                ->getExactDivision($itemWeight, $maxWeight);
                            if ($weightItem) {
                                $decimalItems[]         = array('weight' => $weightItem, 'qty' => 1);
                            }

                            $checkWeight            = false;
                        } else {
                            $itemWeight             = $itemWeight * $item->getQty();
                        }
                    }
                } else {
                    $itemWeight             = $itemWeight * $item->getQty();
                }
            }

            if ($checkWeight && $maxWeight && ($itemWeight > $maxWeight)) {
                return array();
            }

            if ($changeQty && 
                !$item->getParentItem() && 
                $item->getIsQtyDecimal() && 
                ($item->getProductType() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
            ) {
                $qty                    = 1;
            }

            if (!empty($decimalItems)) {
                foreach ($decimalItems as $decimalItem) {
                    $fullItems              = array_merge(
                        $fullItems,
                        array_fill(0, $decimalItem['qty'] * $qty, $decimalItem['weight'])
                    );
                }
            } else {
                $fullItems              = array_merge($fullItems, array_fill(0, $qty, $itemWeight));
            }
        }

        sort($fullItems);
        return $this->_makePieces($fullItems, $maxWeight);
    }
    /**
     * Collect carrier rates
     * 
     * @param string $carrierCode
     * @param Mage_Shipping_Model_Shipping_Method_Request $request
     * 
     * @return self
     */
    public function collectCarrierRates($carrierCode, $request)
    {
        $helper                 = $this->getWarehouseHelper();
        $config                 = $helper->getConfig();
        $carrier                = $this->getCarrierByCode($carrierCode, $request->getStoreId());
        if (!$carrier) {
            return $this;
        }

        if ($helper->getVersionHelper()->isGe1600()) {
            $carrier->setActiveFlag($this->_availabilityConfigField);
        }

        $result                 = $carrier->checkAvailableShipCountries($request);
        if (($result === false) || ($result instanceof Mage_Shipping_Model_Rate_Result_Error)) {
            return $this;
        }

        $result                 = $carrier->proccessAdditionalValidation($request);
        if ($result === false) {
            return $this;
        }

        if (!$result instanceof Mage_Shipping_Model_Rate_Result_Error) {
            if ($config->isMultipleMode() && !$config->isSplitOrderEnabled()) {
                $result                 = null;
                $childRequests          = $request->getChildren();
                if (count($childRequests)) {
                    foreach ($childRequests as $stockId => $_childRequest) {
                        $childRequest           = clone $request;
                        $childRequest->setPackageValue($_childRequest->getPackageValue())
                            ->setPackageValueWithDiscount($_childRequest->getPackageValueWithDiscount())
                            ->setPackagePhysicalValue($_childRequest->getPackagePhysicalValue())
                            ->setPackageWeight($_childRequest->getPackageWeight())
                            ->setPackageQty($_childRequest->getPackageQty())
                            ->setFreeMethodWeight($_childRequest->getFreeMethodWeight())
                            ->setStockId($_childRequest->getStockId());
                        $this->applyRequestOrigin($childRequest, $stockId);
                        
                        if ($helper->getVersionHelper()->isGe1700()) {
                            if ($carrier->getConfigData('shipment_requesttype')) {
                                $childPackages          = $this->composePackagesForCarrier($carrier, $childRequest);
                                if (!empty($childPackages)) {
                                    $childResults           = array();
                                    foreach ($childPackages as $weight => $packageCount) {
                                        if ($helper->getVersionHelper()->isGe1800()) {
                                            $carrierObj             = clone $carrier;
                                        }

                                        $childRequest->setPackageWeight($weight);
                                        if ($helper->getVersionHelper()->isGe1800()) {
                                            $childResult            = $carrierObj->collectRates($childRequest);
                                        } else {
                                            $childResult            = $carrier->collectRates($childRequest);
                                        }

                                        if (!$childResult) {
                                            return $this;
                                        } else {
                                            $childResult->updateRatePrice($packageCount);
                                        }

                                        $childResults[]         = $childResult;
                                    }

                                    if (!empty($childResults) && count($childResults) > 1) {
                                        $childResult            = array();
                                        foreach ($childResults as $_childResult) {
                                            if (empty($childResult)) {
                                                $childResult            = $_childResult;
                                                continue;
                                            }

                                            foreach ($_childResult->getAllRates() as $_method) {
                                                foreach ($childResult->getAllRates() as $method) {
                                                    if ($_method->getMethod() == $method->getMethod()) {
                                                        $method->setPrice($_method->getPrice() + $method->getPrice());
                                                        continue;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $childResult            = $carrier->collectRates($childRequest);
                                }
                            } else {
                                $childResult            = $carrier->collectRates($childRequest);
                            }
                        } else {
                            $childResult            = $carrier->collectRates($childRequest);
                        }

                        if (!$childResult) {
                            return $this;
                        }

                        if ($result && ($result instanceof Mage_Shipping_Model_Rate_Result)) {
                            foreach ($childResult->getAllRates() as $childRate) {
                                $rate                   = $this->getResultRate(
                                    $result, 
                                    $childRate->getCarrier(), 
                                    $childRate->getMethod()
                                );
                                if ($rate) {
                                    $rate->setPrice((float) $rate->getPrice() + (float) $childRate->getPrice());
                                } else {
                                    $result->append($childRate);
                                }
                            }
                        } else {
                            $result                 = $childResult;
                        }
                    }
                }
            } else {
                $this->applyRequestOrigin($request);
                if ($helper->getVersionHelper()->isGe1700()) {
                    if ($carrier->getConfigData('shipment_requesttype')) {
                        $packages               = $this->composePackagesForCarrier($carrier, $request);
                        if (!empty($packages)) {
                            $results                = array();
                            foreach ($packages as $weight => $packageCount) {
                                $carrierObj             = clone $carrier;
                                $request->setPackageWeight($weight);
                                $result                 = $carrierObj->collectRates($request);
                                if (!$result) {
                                    return $this;
                                } else {
                                    $result->updateRatePrice($packageCount);
                                }

                                $results[]              = $result;
                            }

                            if (!empty($results) && count($results) > 1) {
                                $result                 = array();
                                foreach ($results as $_result) {
                                    if (empty($result)) {
                                        $result                     = $_result;
                                        continue;
                                    }

                                    foreach ($_result->getAllRates() as $_method) {
                                        foreach ($result->getAllRates() as $method) {
                                            if ($_method->getMethod() == $method->getMethod()) {
                                                $method->setPrice($_method->getPrice() + $method->getPrice());
                                                continue;
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $result                 = $carrier->collectRates($request);
                        }
                    } else {
                        $result                 = $carrier->collectRates($request);
                    }
                } else {
                    $result                 = $carrier->collectRates($request);
                }
            }

            if (!$result) {
                return $this;
            }
        }

        if (($carrier->getConfigData('showmethod') == 0) && ($result->getError())) {
            return $this;
        }

        if (method_exists($result, 'sortRatesByPrice')) {
            $result->sortRatesByPrice();
        }

        $this->getResult()
            ->append($result);
        return $this;
    }
    /**
     * Prepare and do request to shipment
     * 
     * @param Mage_Sales_Model_Order_Shipment $orderShipment
     * 
     * @return Varien_Object
     */
    public function requestToShipment(Mage_Sales_Model_Order_Shipment $orderShipment)
    {
        $helper                 = $this->getWarehouseHelper();
        $coreHelper             = $helper->getCoreHelper();
        $addressHelper          = $coreHelper->getAddressHelper();
        $admin                  = $coreHelper->getAdminSession()
            ->getUser();
        $order                  = $orderShipment->getOrder();
        $storeId                = $orderShipment->getStoreId();
        $shippingAddress        = $order->getShippingAddress();
        $shippingMethod         = $order->getShippingMethod(true);
        $shippingCarrier        = $order->getShippingCarrier();
        $baseCurrencyCode       = $coreHelper->getStoreById($storeId)
            ->getBaseCurrencyCode();
        if (!$shippingCarrier) {
            Mage::throwException(sprintf($helper->__('Invalid carrier: %'), $shippingMethod->getCarrierCode()));
        }
        
        $originCountryId        = Mage::getStoreConfig(self::XML_PATH_STORE_COUNTRY_ID, $storeId);
        $originCity             = Mage::getStoreConfig(self::XML_PATH_STORE_CITY, $storeId);
        $originPostcode         = Mage::getStoreConfig(self::XML_PATH_STORE_ZIP, $storeId);
        $originRegionCode       = Mage::getStoreConfig(self::XML_PATH_STORE_REGION_ID, $storeId);
        $originStreet1          = Mage::getStoreConfig(self::XML_PATH_STORE_ADDRESS1, $storeId);
        $originStreet2          = Mage::getStoreConfig(self::XML_PATH_STORE_ADDRESS2, $storeId);
        $isDefaultOrigin        = true;
        if (count($order->getStockIds()) == 1) {
            $stockId                = $order->getStockId();
            if ($stockId) {
                $warehouse              = $helper->getWarehouseByStockId($stockId);
                if ($warehouse) {
                    $origin                 = $warehouse->getOrigin();
                    $originCountryId        = $origin->getCountryId();
                    $originCity             = $origin->getCity();
                    $originPostcode         = $origin->getPostcode();
                    $originRegionCode       = ($origin->getRegionId()) ? 
                        $origin->getRegionId() : 
                        $origin->getRegion();
                    $originStreet1          = $origin->getStreet1();
                    $originStreet2          = $origin->getStreet2();
                    $isDefaultOrigin        = false;
                }
            }
        }

        $originStreet           = trim($originStreet1.' '.$originStreet2);
        
        if (is_numeric($originRegionCode)) {
            $originRegionCode       = $addressHelper->getRegionById($originRegionCode)
                ->getCode();
        }

        $regionCode             = $addressHelper->getRegionById($shippingAddress->getRegionId())
            ->getCode();
        $storeInfo              = new Varien_Object(Mage::getStoreConfig('general/store_information', $storeId));
        
        if (!$admin->getFirstname() || 
            !$admin->getLastname() || 
            !$storeInfo->getName() || 
            !$storeInfo->getPhone() || 
            !$originStreet1 || 
            !$originCity || 
            !$originRegionCode || 
            !$originPostcode || 
            !$originCountryId
        ) {
            if ($isDefaultOrigin) {
                Mage::throwException(
                    $helper->__(
                        'Insufficient information to create shipping label(s). Please verify your Store Information and Shipping Settings.'
                    )
                );
            } else {
                Mage::throwException(
                    $helper->__(
                        'Insufficient information to create shipping label(s). Please verify your Store Information and Warehouse Origin.'
                    )
                );
            }
        }

        $request                = Mage::getModel('shipping/shipment_request');
        $request->setOrderShipment($orderShipment);
        $request->setShipperContactPersonName($admin->getName());
        $request->setShipperContactPersonFirstName($admin->getFirstname());
        $request->setShipperContactPersonLastName($admin->getLastname());
        $request->setShipperContactCompanyName($storeInfo->getName());
        $request->setShipperContactPhoneNumber($storeInfo->getPhone());
        $request->setShipperEmail($admin->getEmail());
        $request->setShipperAddressStreet($originStreet);
        $request->setShipperAddressStreet1($originStreet1);
        $request->setShipperAddressStreet2($originStreet2);
        $request->setShipperAddressCity($originCity);
        $request->setShipperAddressStateOrProvinceCode($originRegionCode);
        $request->setShipperAddressPostalCode($originPostcode);
        $request->setShipperAddressCountryCode($originCountryId);
        $request->setRecipientContactPersonName(trim($shippingAddress->getFirstname().' '.$shippingAddress->getLastname()));
        $request->setRecipientContactPersonFirstName($shippingAddress->getFirstname());
        $request->setRecipientContactPersonLastName($shippingAddress->getLastname());
        $request->setRecipientContactCompanyName($shippingAddress->getCompany());
        $request->setRecipientContactPhoneNumber($shippingAddress->getTelephone());
        $request->setRecipientEmail($shippingAddress->getEmail());
        $request->setRecipientAddressStreet(trim($shippingAddress->getStreet1().' '.$shippingAddress->getStreet2()));
        $request->setRecipientAddressStreet1($shippingAddress->getStreet1());
        $request->setRecipientAddressStreet2($shippingAddress->getStreet2());
        $request->setRecipientAddressCity($shippingAddress->getCity());
        $request->setRecipientAddressStateOrProvinceCode($shippingAddress->getRegionCode());
        $request->setRecipientAddressRegionCode($regionCode);
        $request->setRecipientAddressPostalCode($shippingAddress->getPostcode());
        $request->setRecipientAddressCountryCode($shippingAddress->getCountryId());
        $request->setShippingMethod($shippingMethod->getMethod());
        $request->setPackageWeight($order->getWeight());
        $request->setPackages($orderShipment->getPackages());
        $request->setBaseCurrencyCode($baseCurrencyCode);
        $request->setStoreId($storeId);
        return $shippingCarrier->requestToShipment($request);
    }
}
