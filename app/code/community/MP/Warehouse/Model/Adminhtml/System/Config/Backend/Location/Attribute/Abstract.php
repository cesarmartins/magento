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
 * Abstract location attribute backend
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Adminhtml_System_Config_Backend_Location_Attribute_Abstract
    extends Mage_Core_Model_Config_Data
{
    /**
     * Get customer locator helper
     * 
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    protected function getCustomerLocatorHelper()
    {
        return Mage::helper('warehouse/customerLocator_data');
    }
    /**
     * Get allowed attributes
     * 
     * @return array
     */
    protected function _getAllowedAttributes()
    {
        if ($this->getData('groups/options/fields/allow_attributes/inherit')) {
            return explode(',', Mage::getConfig()->getNode('mp_customerlocator/options/allow_attributes', $this->getScope(), $this->getScopeId()));
        }

        return $this->getData('groups/options/fields/allow_attributes/value');
    }
    /**
     * Get required attributes
     *
     * @return array
     */
    protected function _getRequiredAttributes()
    {
        if ($this->getData('groups/options/fields/require_attributes/inherit')) {
            return explode(',', Mage::getConfig()->getNode('mp_customerlocator/options/require_attributes', $this->getScope(), $this->getScopeId()));
        }

        return $this->getData('groups/options/fields/require_attributes/value');
    }
}
