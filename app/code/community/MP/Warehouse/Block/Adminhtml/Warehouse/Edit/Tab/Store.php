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
 * Warehouse store tab
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Warehouse_Edit_Tab_Store 
    extends MP_Warehouse_Block_Adminhtml_Warehouse_Edit_Tab_Abstract
{
    /**
     * Form field set identifier
     * 
     * @var string
     */
    protected $_formFieldsetId = 'store_fieldset';
    /**
     * Form field set legend
     * 
     * @var string
     */
    protected $_formFieldsetLegend = 'Stores';
    /**
     * Tab title
     * 
     * @var string
     */
    protected $_title = 'Stores';
    /**
     * Prepare form before rendering HTML
     *
     * @return MP_Warehouse_Block_Adminhtml_Warehouse_Edit_Tab_Store
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $helper             = $this->getWarehouseHelper();
        $model              = $this->getModel();
        $isElementDisabled  = ($this->isSaveAllowed()) ? false : true;
        $fieldset           = $this->getFieldset();
        $storeIdsElement = $fieldset->addField(
            'store_ids', 'text', array(
            'name'      => 'store_ids', 
            'label'     => $helper->__('Stores'), 
            'title'     => $helper->__('Stores'), 
            'required'  => false, 
            'value'     => $model->getStoreIds(), 
            )
        );
        $storeIdsElement->setRenderer(
            $this->getLayout()->createBlock('warehouse/adminhtml_warehouse_edit_tab_store_renderer')
        );
        $this->dispatchPrepareFormEvent();
        return $this;
    }
}
