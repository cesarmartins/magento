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
 * Shipping tablerate helper
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_ShippingTablerate_Data
    extends Mage_Core_Helper_Abstract
{
    /**
     * Table rates
     * 
     * @var array
     */
    protected $_tablerates;
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
     * Get address helper
     * 
     * @return MP_Warehouse_Helper_Core_Address
     */
    public function getAddressHelper()
    {
        return $this->getCoreHelper()->getAddressHelper();
    }
    /**
     * Get table rates
     * 
     * @return array
     */
    public function getTablerates()
    {
        if (is_null($this->_tablerates)) {
            $this->_tablerates = array();
            $tablerateCollection = Mage::getResourceModel('warehouse/shippingTablerate_tablerate_collection');
            foreach ($tablerateCollection as $tablerate) {
                $this->_tablerates[$tablerate->getId()] = $tablerate;
            }
        }

        return $this->_tablerates;
    }
    /**
     * Retrieve table rate by id
     * 
     * @param int $tablerateId
     * 
     * @return MP_Warehouse_Model_ShippingTablerate_Tablerate
     */
    public function getTablerateById($tablerateId)
    {
        $tablerates = $this->getTablerates();
        if (isset($tablerates[$tablerateId])) {
            return $tablerates[$tablerateId];
        } else {
            return null;
        }
    }
    /**
     * Get websites
     *
     * @return array
     */
    public function getWebsites()
    {
        return Mage::app()->getWebsites();
    }
    /**
     * Get default website
     * 
     * @return Mage_Core_Model_Website
     */
    public function getDefaultWebsite()
    {
        $website = null;
        $websites = $this->getWebsites();
        if (count($websites)) {
            $website = array_shift($websites);
        }

        return $website;
    }
    /**
     * Get website
     * 
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        $website = null;
        $websiteId = (int) Mage::app()->getFrontController()->getRequest()->getParam('website', 0);
        if ($websiteId) {
            $website = Mage::app()->getWebsite($websiteId);
        }

        if (!$website) {
            $website = $this->getDefaultWebsite();
        }

        return $website;
    }
    /**
     * Get website identifier
     * 
     * @param $website|null Mage_Core_Model_Website
     * @return mixed
     */
    public function getWebsiteId($website = null)
    {
        if (is_null($website)) {
            $website = $this->getWebsite();
        }

        return ($website) ? $website->getId() : null;
    }
}
