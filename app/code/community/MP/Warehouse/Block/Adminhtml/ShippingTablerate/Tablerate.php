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
 * Table rates
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_ShippingTablerate_Tablerate
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Container
{
    /**
     * Block group
     *
     * @var string
     */
    protected $_blockGroup = 'warehouse';
    /**
     * Controller
     *
     * @var string
     */
    protected $_controller = 'adminhtml_shippingTablerate_tablerate';
    /**
     * Header label
     *
     * @var string
     */
    protected $_headerLabel = 'Shipping Table Rates';

    /**
     * Add Label
     *
     * @var string
     */
    protected $_addLabel = 'Add New Rate';
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
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('shippingtablerate/tablerate.phtml');
    }
    /**
     * Get create URL
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new', array('website' => $this->getWebsiteId()));
    }
}
