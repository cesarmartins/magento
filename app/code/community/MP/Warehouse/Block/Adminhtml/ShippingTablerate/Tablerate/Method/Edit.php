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
 * Table rate method edit
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_ShippingTablerate_Tablerate_Method_Edit
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Form_Container
{
    /**
     * Object identifier
     * 
     * @var string
     */
    protected $_objectId = 'method_id';
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
    protected $_controller = 'shippingTablerate_tablerate_method';
    /**
     * Add Label
     * 
     * @var string
     */
    protected $_addLabel = 'New Method';
    /**
     * Edit label
     * 
     * @var string
     */
    protected $_editLabel = "Edit Method '%s'";
    /**
     * Save label
     * 
     * @var string
     */
    protected $_saveLabel = 'Save Method';
    /**
     * Delete label
     * 
     * @var string
     */
    protected $_deleteLabel = 'Delete Method';
    /**
     * Model name
     * 
     * @var string
     */
    protected $_modelName = 'shippingtablerate_method';
    /**
     * Get warehouse helper
     * 
     * @return MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Get text helper
     * 
     * @return MP_Warehouse_Helper_Data
     */
    public function getTextHelper()
    {
        return $this->getWarehouseHelper();
    }
    /**
     * Check is allowed action
     * 
     * @param string $action
     * 
     * @return bool
     */
    protected function isAllowedAction($action)
    {
        if (($action == 'delete') && (1 == $this->getModel()->getId())) {
            return false;
        }

        return $this->getAdminSession()
            ->isAllowed('sales/shipping/tablerates/methods');
    }
    /**
     * Get Url parameters
     * 
     * @return array
     */
    protected function getUrlParams()
    {
        return array();
    }
    /**
     * Get URL for back (reset) button
     * 
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(
            '*/*/', 
            $this->getUrlParams()
        );
    }
    /**
     * Get URL for delete button
     * 
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl(
            '*/*/delete', 
            array_merge(
                array(
                $this->_objectId => $this->getRequest()->getParam($this->_objectId)), 
                $this->getUrlParams()
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

        return $this->getUrl(
            '*/tablerate_method/save',
            $this->getUrlParams()
        );
    }
}
