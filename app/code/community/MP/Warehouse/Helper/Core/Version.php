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
 * Version helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Version
    extends MP_Warehouse_Helper_Core_Abstract
{
    /**
     * Get the current Magento version
     *
     * @return string
     */
    public function getCurrent()
    {
        return Mage::getVersion();
    }
    /**
     * Compare versions
     * 
     * @param string $version1
     * @param string $version2
     * @param string $operator
     * 
     * @return int
     */
    protected function _compare($version1, $version2, $operator = null)
    {
        return version_compare($version1, $version2, $operator);
    }
    /**
     * Compare version to the current
     * 
     * @param string $version
     * @param string $operator
     * 
     * @return int
     */
    public function compare($version, $operator = null)
    {
        return $this->_compare($this->getCurrent(), $version, $operator);
    }
    /**
     * Check if current version is greater or equal
     * 
     * @return bool
     */
    public function isGe($version)
    {
        return $this->compare($version, '>=');
    }
    /**
     * Check if current version is less or equal
     * 
     * @return bool
     */
    public function isLe($version)
    {
        return $this->compare($version, '<=');
    }
    /**
     * Check if current version is greater
     * 
     * @return bool
     */
    public function isGt($version)
    {
        return $this->compare($version, '>');
    }
    /**
     * Check if current version is less
     * 
     * @return bool
     */
    public function isLt($version)
    {
        return $this->compare($version, '<');
    }
    /**
     * Check if current version is equal
     * 
     * @return bool
     */
    public function isEq($version)
    {
        return $this->compare($version, '==');
    }
    /**
     * Check if EE version is running
     * 
     * @return bool
     */
    public function isEE()
    {
        return Mage::getConfig()->getModuleConfig('Enterprise_Enterprise');
    }
    /**
     * Check if current version is equal or greater then 1.5.0.0 
     * 
     * @return bool
     */
    public function isGe1500()
    {
        return (($this->isGe('1.5.0.0') && !$this->isEE()) || ($this->isGe('1.10.0.0') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.5.1.0 
     * 
     * @return bool
     */
    public function isGe1510()
    {
        return (($this->isGe('1.5.1.0') && !$this->isEE()) || ($this->isGe('1.10.1.0') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.6.0.0 
     * 
     * @return bool
     */
    public function isGe1600()
    {
        return (($this->isGe('1.6.0.0') && !$this->isEE()) || ($this->isGe('1.11.0.0') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.6.1.0 
     * 
     * @return bool
     */
    public function isGe1610()
    {
        return (($this->isGe('1.6.1.0') && !$this->isEE()) || ($this->isGe('1.11.1.0') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.6.2.0 
     * 
     * @return bool
     */
    public function isGe1620()
    {
        return (($this->isGe('1.6.2.0') && !$this->isEE()) || ($this->isGe('1.11.2.0') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.7.0.0 
     * 
     * @return bool
     */
    public function isGe1700()
    {
        return (($this->isGe('1.7.0.0') && !$this->isEE()) || ($this->isGe('1.12.0.0') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.7.0.1 
     * 
     * @return bool
     */
    public function isGe1701()
    {
        return (($this->isGe('1.7.0.1') && !$this->isEE()) || ($this->isGe('1.12.0.1') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.7.0.2 
     * 
     * @return bool
     */
    public function isGe1702()
    {
        return (($this->isGe('1.7.0.2') && !$this->isEE()) || ($this->isGe('1.12.0.2') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.8.0.0 
     * 
     * @return bool
     */
    public function isGe1800()
    {
        return (($this->isGe('1.8.0.0') && !$this->isEE()) || ($this->isGe('1.13.0.0') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.8.1.0 
     * 
     * @return bool
     */
    public function isGe1810()
    {
        return (($this->isGe('1.8.1.0') && !$this->isEE()) || ($this->isGe('1.13.1.0') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.9.0.0
     *
     * @return bool
     */
    public function isGe1900()
    {
        return (($this->isGe('1.9.0.0') && !$this->isEE()) || ($this->isGe('1.14.0.0') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.9.0.1
     *
     * @return bool
     */
    public function isGe1901()
    {
        return (($this->isGe('1.9.0.1') && !$this->isEE()) || ($this->isGe('1.14.0.1') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.9.1.0
     *
     * @return bool
     */
    public function isGe1910()
    {
        return (($this->isGe('1.9.1.0') && !$this->isEE()) || ($this->isGe('1.14.1.0') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.9.1.1
     *
     * @return bool
     */
    public function isGe1911()
    {
        return (($this->isGe('1.9.1.1') && !$this->isEE()) || ($this->isGe('1.14.1.1') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.9.2.0
     *
     * @return bool
     */
    public function isGe1920()
    {
        return (($this->isGe('1.9.2.0') && !$this->isEE()) || ($this->isGe('1.14.2.0') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.9.2.1
     *
     * @return bool
     */
    public function isGe1921()
    {
        return (($this->isGe('1.9.2.1') && !$this->isEE()) || ($this->isGe('1.14.2.1') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.9.2.2
     *
     * @return bool
     */
    public function isGe1922()
    {
        return (($this->isGe('1.9.2.2') && !$this->isEE()) || ($this->isGe('1.14.2.2') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.9.2.3
     *
     * @return bool
     */
    public function isGe1923()
    {
        return (($this->isGe('1.9.2.3') && !$this->isEE()) || ($this->isGe('1.14.2.3') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.9.2.4
     *
     * @return bool
     */
    public function isGe1924()
    {
        return (($this->isGe('1.9.2.4') && !$this->isEE()) || ($this->isGe('1.14.2.4') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.9.3.0
     *
     * @return bool
     */
    public function isGe1930()
    {
        return (($this->isGe('1.9.3.0') && !$this->isEE()) || ($this->isGe('1.14.3.0') && $this->isEE()));
    }
    /**
     * Check if current version is equal or greater then 1.9.3.1
     *
     * @return bool
     */
    public function isGe1931()
    {
        return (($this->isGe('1.9.3.1') && !$this->isEE()) || ($this->isGe('1.14.3.1') && $this->isEE()));
    }
}