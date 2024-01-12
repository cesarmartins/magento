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
 * Table rate edit
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_ShippingTablerate_Tablerate_Edit
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Form_Container
{
    /**
     * Object identifier
     *
     * @var string
     */
    protected $_objectId = 'tablerate_id';
    /**
     * Block group
     *
     * @var string
     */
    protected $_blockGroup = 'warehouse';
    /**
     * Block sub group
     *
     * @var string
     */
    protected $_blockSubGroup = 'adminhtml';
    /**
     * Controller
     *
     * @var string
     */
    protected $_controller = 'shippingTablerate_tablerate';
    /**
     * Add Label
     *
     * @var string
     */
    protected $_addLabel = 'New Rate';
    /**
     * Edit label
     *
     * @var string
     */
    protected $_editLabel = "Edit Rate '%s'";
    /**
     * Save label
     *
     * @var string
     */
    protected $_saveLabel = 'Save Rate';
    /**
     * Delete label
     *
     * @var string
     */
    protected $_deleteLabel = 'Delete Rate';
    /**
     * Model name
     *
     * @var string
     */
    protected $_modelName = 'shippingtablerate';
    /**
     * Website
     *
     * @var Mage_Core_Model_Website
     */
    protected $_website;
    /**
     * Retrieve shipping table rate helper
     *
     * @return MP_Warehouse_Helper_ShippingTablerate_Data
     */
    protected function getShippingTablerateHelper()
    {
        return Mage::helper('warehouse/shippingTablerate_data');
    }
    /**
     * Retrieve text helper
     *
     * @return MP_Warehouse_Helper_ShippingTablerate_Data
     */
    public function getTextHelper()
    {
        return $this->getShippingTablerateHelper();
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
     * Preparing block layout
     *
     * @return MP_Warehouse_Block_Adminhtml_ShippingTablerate_Tablerate_Edit
     */
    protected function _prepareLayout()
    {
        $json = Mage::helper('warehouse/core_data')->getDirectoryHelper()->getRegionJson2();
        $this->_formScripts[] = 'var updater = new RegionUpdater("shippingtablerate_dest_country_id", "none", "shippingtablerate_dest_region_id", '.$json.', "disable")';
        parent::_prepareLayout();
        return $this;
    }
    /**
     * Get Url parameters
     *
     * @return array
     */
    protected function getUrlParams()
    {
        return array('website' => $this->getWebsiteId());
    }
    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/', $this->getUrlParams());
    }
    /**
     * Get URL for delete button
     */
    public function getDeleteUrl()
    {
        return $this->getUrl(
            '*/*/delete', array_merge(
                array($this->_objectId => $this->getRequest()->getParam($this->_objectId)), $this->getUrlParams()
            )
        );
    }
    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }

        return $this->getUrl('*/tablerate/save', $this->getUrlParams());
    }
    /**
     * Check is allowed action
     * 
     * @param   string $action
     * 
     * @return  bool
     */
    protected function isAllowedAction($action)
    {
        return $this->getAdminSession()
            ->isAllowed('sales/shipping/tablerates/tablerates');
    }
}
