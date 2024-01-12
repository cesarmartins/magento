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
 * Abstract session model
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Core_Session_Abstract
    extends Mage_Core_Model_Session_Abstract
{
    /**
     * Namespace
     * 
     * @var string
     */
    protected $_namespace = 'mp_core';
    /**
     * Constructor
     */
    public function __construct()
    {
        $namespace = $this->_namespace;
        if ($this->getCustomerConfigShare()->isWebsiteScope()) {
            $namespace .= '_' . ($this->getCoreHelper()->getStore()->getWebsite()->getCode());
        }

        $this->init($namespace);
        Mage::dispatchEvent($this->_namespace.'_session_init', array('session' => $this));
    }
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
     * Get customer sharing configuration
     *
     * @return Mage_Customer_Model_Config_Share
     */
    public function getCustomerConfigShare()
    {
        return $this->getCoreHelper()->getCustomerHelper()->getCustomerConfigShare();
    }
}
