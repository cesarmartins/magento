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
 * Currency helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Directory_Currency
    extends MP_Warehouse_Helper_Core_Abstract
{
    /**
     * Website base currency codes
     * 
     * @var array
     */
    protected $_websiteBaseCodes;
    /**
     * Сodes
     * 
     * @var array
     */
    protected $_сodes;
    /**
     * Base currency
     * 
     * @var Mage_Directory_Model_Currency
     */
    protected $_base;
    /**
     * Current store base currency
     * 
     * @var Mage_Directory_Model_Currency
     */
    protected $_currentStoreBase;
    /**
     * Current currency
     * 
     * @var Mage_Directory_Model_Currency
     */
    protected $_current;
    /**
     * Get currency
     * 
     * @return Mage_Directory_Model_Currency
     */
    public function getCurrency()
    {
        return Mage::getModel('directory/currency');
    }
    /**
     * Get website base currency codes
     * 
     * @return array
     */
    public function getWebsiteBaseCodes()
    {
        if (is_null($this->_websiteBaseCodes)) {
            $helper             = $this->getCoreHelper();
            $baseCodes          = array();
            foreach ($helper->getWebsites() as $websiteId => $website) {
                $baseCodes[$websiteId]  = $website->getBaseCurrencyCode();
            }

            $this->_websiteBaseCodes = $baseCodes;
        }

        return $this->_websiteBaseCodes;
    }
    /**
     * Get currency codes
     * 
     * @return array
     */
    public function getCodes()
    {
        if (is_null($this->_сodes)) {
            $_сodes = $this->getCurrency()->getConfigAllowCurrencies();
            sort($_сodes);
            if (count($_сodes)) {
                $codes = array();
                foreach ($_сodes as $code) {
                    $code = strtoupper($code);
                    $codes[$code] = $code;
                }

                $this->_сodes   = $codes;
            }
        }

        return $this->_сodes;
    }
    /**
     * Check if currency exists
     * 
     * @param string $code
     * 
     * @return bool
     */
    public function isCurrencyExists($code)
    {
        return in_array($code, $this->getCodes());
    }
    /**
     * Get base currency code
     * 
     * @return string
     */
    public function getBaseCode()
    {
        return Mage::app()->getBaseCurrencyCode();
    }
    /**
     * Get base currency
     * 
     * @return Mage_Directory_Model_Currency
     */
    public function getBase()
    {
        if (is_null($this->_base)) {
            $this->_base = $this->getCurrency()->load($this->getBaseCode());
        }

        return $this->_base;
    }
    /**
     * Get current store base currency code
     * 
     * @return string
     */
    public function getCurrentStoreBaseCode()
    {
        return $this->getCoreHelper()->getCurrentStore()->getBaseCurrencyCode();
    }
    /**
     * Get current store base currency
     * 
     * @return Mage_Directory_Model_Currency
     */
    public function getCurrentStoreBase()
    {
        if (is_null($this->_currentStoreBase)) {
            $this->_currentStoreBase = $this->getCurrency()->load($this->getCurrentStoreBaseCode());
        }

        return $this->_currentStoreBase;
    }
    /**
     * Get current currency code
     * 
     * @return string
     */
    public function getCurrentCode()
    {
        return $this->getCoreHelper()->getCurrentStore()->getCurrentCurrencyCode();
    }
    /**
     * Get current currency
     * 
     * @return Mage_Directory_Model_Currency
     */
    public function getCurrent()
    {
        if (is_null($this->_current)) {
            $this->_current = $this->getCurrency()->load($this->getCurrentCode());
        }

        return $this->_current;
    }
    /**
     * Get currency rate
     * 
     * @param string $from
     * @param string $to
     * 
     * @return float
     */
    public function getRate($from, $to)
    {
        $rate = $this->getCurrency()->load($from)->getRate($to);
        if (!$rate) {
            $rate = $this->getCurrency()->load($to)->getRate($from);
            if (!$rate) {
                $base           = $this->getBase();
                $fromRate       = $base->getRate($from);
                $toRate         = $base->getRate($to);
                if (!$fromRate) {
                    $fromRate = 1;
                }

                if (!$toRate) {
                    $toRate = 1;
                }

                $rate = $toRate / $fromRate;
            } else {
                $rate = 1 / $rate;
            }
        }

        return $rate;
    }
    /**
     * Get options
     * 
     * @param bool $required
     * @param string $emptyLabel
     * @param string $emptyValue
     * 
     * @return array
     */
    public function getOptions($required = true, $emptyLabel = '', $emptyValue = '')
    {
        $options    = array();
        $codes      = $this->getCodes();
        $names      = Mage::app()->getLocale()->getTranslationList('currencytoname');
        foreach ($names as $name => $code) {
            if (!in_array($code, $codes)) {
                continue;
            }

            array_push(
                $options, 
                array(
                    'value' => $code, 
                    'label' => $name, 
                )
            );
        }

        $this->getCoreHelper()->prepareOptions($options, $required, $emptyLabel, $emptyValue);
        return $options;
    }
    /**
     * Get base currency expression
     * 
     * @param Zend_Db_Expr|string $websiteExpr
     * 
     * @return Zend_Db_Expr
     */
    public function getBaseDbExpr($websiteExpr)
    {
        $pieces     = array();
        foreach ($this->getWebsiteBaseCodes() as $websiteId => $code) {
            array_push($pieces, "WHEN {$websiteExpr} = {$websiteId} THEN '{$code}'");
        }

        return new Zend_Db_Expr('(CASE '.implode(' ', $pieces).' END)');
    }
    /**
     * Get currency expression
     * 
     * @param string $currencyTableAlias
     * @param Zend_Db_Expr|string $websiteExpr
     * 
     * @return Zend_Db_Expr
     */
    public function getDbExpr($websiteExpr, $tableAlias = 'cr')
    {
        return new Zend_Db_Expr("IF ({$tableAlias}.currency_to IS NULL, {$this->getBaseDbExpr($websiteExpr)}, {$tableAlias}.currency_to)");
    }
}
