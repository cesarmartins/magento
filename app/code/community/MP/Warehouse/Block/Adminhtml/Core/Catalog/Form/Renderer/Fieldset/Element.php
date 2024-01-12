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
 * Catalog fieldset element renderer
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Core_Catalog_Form_Renderer_Fieldset_Element
    extends Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element
{
    /**
     * Store
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store;
    /**
     * Get core helper
     *
     * @return MP_Warehouse_Helper_Core_Data
     */
    protected function getCoreHelper()
    {
        return Mage::helper('warehouse/core_data');
    }
    /**
     * Get product price helper
     *
     * @return MP_Warehouse_Helper_Core_Catalog_Product_Price
     */
    public function getProductPriceHelper()
    {
        return $this->getCoreHelper()
            ->getProductHelper()
            ->getPriceHelper();
    }
    /**
     * Set form element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return self
     */
    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }
    /**
     * Get store
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            $storeId = (int) $this->getRequest()->getParam('store', 0);
            $this->_store = Mage::app()->getStore($storeId);
        }

        return $this->_store;
    }
    /**
     * Get website id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->getStore()->getWebsiteId();
    }
    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }
    /**
     * Check if global price scope is active
     *
     * @return bool
     */
    public function isGlobalPriceScope()
    {
        return $this->getProductPriceHelper()->isGlobalScope();
    }
    /**
     * Check if website price scope is active
     *
     * @return bool
     */
    public function isWebsitePriceScope()
    {
        return $this->getProductPriceHelper()->isWebsiteScope();
    }
    /**
     * Check if store price scope is active
     *
     * @return bool
     */
    public function isStorePriceScope()
    {
        return $this->getProductPriceHelper()->isStoreScope();
    }
    /**
     * Get control HTML id
     *
     * @return string
     */
    public function getControlHtmlId()
    {
        return ($this->getElement()) ? $this->getElement()->getHtmlId().'_control' : 'control';
    }
    /**
     * Get control JS object name
     *
     * @return string
     */
    public function getControlJsObjectName()
    {
        return $this->_camelize($this->getControlHtmlId());
    }
}
