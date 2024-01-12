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
 * Website switcher
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_ShippingTablerate_Website_Switcher
    extends Mage_Adminhtml_Block_Template
{
    /**
     * Website
     * 
     * @var Mage_Core_Model_Website
     */
    protected $_website;
    /**
     * Website variable name
     * 
     * @var string
     */
    protected $_websiteVarName = 'website';
    /**
     * Whether has default option or not
     * 
     * @var boolean
     */
    protected $_hasDefaultOption = false;
    /**
     * Get shipping table rate helper
     *
     * @return MP_Warehouse_Helper_ShippingTablerate_Data
     */
    protected function getShippingTablerateHelper()
    {
        return Mage::helper('warehouse/shippingTablerate_data');
    }
    /**
     * Get text helper
     *
     * @return MP_Warehouse_Helper_ShippingTablerate_Data
     */
    public function getTextHelper()
    {
        return $this->getShippingTablerateHelper();
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('shippingtablerate/website/switcher.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultWebsiteName($this->getTextHelper()->__('All Websites'));
    }
    /**
     * Get websites
     *
     * @return array
     */
    public function getWebsites()
    {
        return $this->getShippingTablerateHelper()->getWebsites();
    }
    /**
     * Get website
     * 
     * @return Mage_Core_Model_Website
     */
    protected function getWebsite()
    {
        if (is_null($this->_website)) {
            $this->_website = $this->getShippingTablerateHelper()->getWebsite();
        }

        return $this->_website;
    }
    /**
     * Get website identifier
     * 
     * @return mixed
     */
    public function getWebsiteId()
    {
        return $this->getShippingTablerateHelper()->getWebsiteId($this->getWebsite());
    }
    /**
     * Set/Get whether the switcher should show default option
     * 
     * @param bool $hasDefaultOption
     * 
     * @return bool
     */
    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) {
            $this->_hasDefaultOption = $hasDefaultOption;
        }

        return $this->_hasDefaultOption;
    }
    /**
     * Get switch URL
     * 
     * @return string
     */
    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }

        return $this->getUrl('*/*/*', array('_current' => true, $this->_websiteVarName => null));
    }
}
