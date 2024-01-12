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
 * Shipping helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Warehouse_Assignment_Method 
    extends Mage_Core_Helper_Abstract
{
    /**
     * Single methods
     * 
     * @var array of MP_Warehouse_Model_Warehouse_Assignment_Method_Single_Abstract
     */
    protected $_singleMethods;
    /**
     * Multiple methods
     * 
     * @var array of MP_Warehouse_Model_Warehouse_Assignment_Method_Multiple_Abstract
     */
    protected $_multipleMethods;
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
     * Get single methods
     * 
     * @return array of MP_Warehouse_Model_Warehouse_Assignment_Method_Single_Abstract
     */
    public function getSingleMethods()
    {
        if (is_null($this->_singleMethods)) {
            $helper     = $this->getWarehouseHelper();
            $methods    = array();
            $config     = Mage::getStoreConfig('single_assignment_methods');
            foreach ($config as $code => $methodConfig) {
                if (!isset($methodConfig['model'])) {
                    Mage::throwException($helper->__('Invalid model for single assignment method: %', $code));
                }

                $modelName = $methodConfig['model'];
                try {
                    $method = Mage::getModel($modelName, $methodConfig);
                } catch (Exception $e) {
                    Mage::logException($e);
                    return false;
                }

                $method->setId($code);
                $methods[$code] = $method;
            }

            $this->_singleMethods = $methods;
        }

        return $this->_singleMethods;
    }
    /**
     * Get single method
     * 
     * @param string $code
     * 
     * @return MP_Warehouse_Model_Warehouse_Assignment_Method_Single_Abstract
     */
    public function getSingleMethod($code)
    {
        $methods = $this->getSingleMethods();
        if (isset($methods[$code])) {
            return $methods[$code];
        } else {
            return null;
        }
    }
    /**
     * Get current single method
     * 
     * @return MP_Warehouse_Model_Warehouse_Assignment_Method_Single_Abstract
     */
    public function getCurrentSingleMethod()
    {
        $config = $this->getWarehouseHelper()->getConfig();
        return $this->getSingleMethod($config->getSingleAssignmentMethodCode());
    }
    /**
     * Get multiple methods
     * 
     * @return array of MP_Warehouse_Model_Warehouse_Assignment_Method_Multiple_Abstract
     */
    public function getMultipleMethods()
    {
        if (is_null($this->_multipleMethods)) {
            $helper     = $this->getWarehouseHelper();
            $methods    = array();
            $config     = Mage::getStoreConfig('multiple_assignment_methods');
            foreach ($config as $code => $methodConfig) {
                if (!isset($methodConfig['model'])) {
                    Mage::throwException($helper->__('Invalid model for multiple assignment method: %', $code));
                }

                $modelName = $methodConfig['model'];
                try {
                    $method = Mage::getModel($modelName, $methodConfig);
                } catch (Exception $e) {
                    Mage::logException($e);
                    return false;
                }

                $method->setId($code);
                $methods[$code] = $method;
            }

            $this->_multipleMethods = $methods;
        }

        return $this->_multipleMethods;
    }
    /**
     * Get multiple method
     * 
     * @param string $code
     * 
     * @return MP_Warehouse_Model_Warehouse_Assignment_Method_Multiple_Abstract
     */
    public function getMultipleMethod($code)
    {
        $methods = $this->getMultipleMethods();
        if (isset($methods[$code])) {
            return $methods[$code];
        } else {
            return null;
        }
    }
    /**
     * Get current multiple method
     * 
     * @return MP_Warehouse_Model_Warehouse_Assignment_Method_Multiple_Abstract
     */
    public function getCurrentMultipleMethod()
    {
        $config = $this->getWarehouseHelper()->getConfig();
        return $this->getMultipleMethod($config->getMultipleAssignmentMethodCode());
    }
    /**
     * Get current method
     * 
     * @return MP_Warehouse_Model_Warehouse_Assignment_Method_Abstract
     */
    public function getCurrentMethod()
    {
        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isMultipleMode()) {
            return $this->getCurrentMultipleMethod();
        } else {
            return $this->getCurrentSingleMethod();
        }
    }
    /**
     * Apply quote stock items
     * 
     * @param MP_Warehouse_Model_Sales_Quote $quote
     * 
     * @return MP_Warehouse_Helper_Warehouse_Assignment_Method
     */
    public function applyQuoteStockItems($quote)
    {
        $method = $this->getCurrentMethod();
        if (!$method) {
            return $this;
        }

        $method->setQuote($quote)->applyQuoteStockItems();
        return $this;
    }
    /**
     * Get quote stock identifier
     * 
     * @param MP_Warehouse_Model_Sales_Quote $quote
     * 
     * @return int|null
     */
    public function getQuoteStockId($quote = null)
    {
        $method     = $this->getCurrentMethod();
        if (!$method) {
            return null;
        }

        return $method->setQuote($quote)->getStockId();
    }
    /**
     * Get product stock identifier
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return int
     */
    public function getProductStockId($product)
    {
        $method = $this->getCurrentMethod();
        if (!$method) {
            return null;
        }

        return $method->getProductStockId($product);
    }
}
