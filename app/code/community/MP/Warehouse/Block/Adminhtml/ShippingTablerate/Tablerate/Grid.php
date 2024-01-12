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
 * Table rates grid
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_ShippingTablerate_Tablerate_Grid
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Grid_Area_Grid
{
    /**
     * Object identifier
     *
     * @var string
     */
    protected $_objectId = 'tablerate_id';
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
        $this->setId('tablerateGrid');
        $this->setDefaultSort('pk');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->_exportPageSize = 10000;
        $this->setEmptyText($this->getTextHelper()->__('No table rates found.'));
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
     * Prepare collection object
     *
     * @return Varien_Data_Collection
     */
    protected function __prepareCollection()
    {
        $websiteId = $this->getWebsiteId();
        $collection = Mage::getModel('warehouse/shippingTablerate_tablerate')->getCollection();
        $select = $collection->getSelect();
        if ($websiteId) {
            $select->where('website_id = ?', $websiteId);
        } else {
            $select->where('website_id = -1');
        }

        return $collection;
    }
    /**
     * Get condition name options
     *
     * @return array
     */
    protected function getConditionNameOptions()
    {
        $options = array();
        $names = Mage::getModel('adminhtml/system_config_source_shipping_tablerate')->toOptionArray();
        foreach ($names as $name) {
            $options[$name['value']] = $name['label'];
        }

        return $options;
    }
    /**
     * Get store
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
    /**
     * Prepare columns
     *
     * @return MP_Warehouse_Block_Adminhtml_ShippingTablerate_Tablerate_Grid
     */
    protected function _prepareColumns()
    {
        $textHelper = $this->getTextHelper();
        $this->addColumn(
            'warehouse_id', array(
            'header'    => $textHelper->__('Warehouse'),
            'align'     => 'left',
            'index'     => 'warehouse_id',
            'type'      => 'options',
            'options'   => $this->getWarehousesOptions(),
            )
        );
        $this->addColumn(
            'dest_country_id', array(
            'header'        => $textHelper->__('Dest Country'),
            'align'         => 'left',
            'index'         => 'dest_country_id',
            'filter_index'  => 'main_table.dest_country_id',
            'type'          => 'options',
            'options'       => $this->getCountryOptions(),
            )
        );
        $this->addColumn(
            'dest_region', array(
            'header'        => $textHelper->__('Dest Region/State'),
            'align'         => 'left',
            'index'         => 'dest_region',
            'filter_index'  => 'region_table.code',
            'filter'        => $this->getAreaChildBlockTypePrefix().'column_filter_region',
            'default'       => '*',
            )
        );
        $this->addColumn(
            'dest_zip', array(
            'header'        => $textHelper->__('Dest Zip/Postal Code'),
            'align'         => 'left',
            'index'         => 'dest_zip',
            'filter'        => $this->getAreaChildBlockTypePrefix().'column_filter_zip',
            'renderer'        => $this->getAreaChildBlockTypePrefix().'column_renderer_zip',
            'default'       => '*',
            )
        );
        $this->addColumn(
            'condition_name', array(
            'header'        => $textHelper->__('Condition Name'),
            'align'         => 'left',
            'index'         => 'condition_name',
            'type'          => 'options',
            'options'       => $this->getConditionNameOptions(),
            )
        );
        $this->addColumn(
            'condition_value', array(
            'header'        => $textHelper->__('Condition Value'),
            'align'         => 'left',
            'index'         => 'condition_value',
            'type'          => 'number',
            'default'       => '0',
            )
        );
        $this->addColumn(
            'method_id', array(
            'header'    => $textHelper->__('Method'),
            'align'     => 'left',
            'index'     => 'method_id',
            'type'      => 'options',
            'options'   => $this->getMethodsOptions(),
            )
        );
        $store = $this->_getStore();
        $this->addColumn(
            'price', array(
            'header'        => $textHelper->__('Price'),
            'align'         => 'left',
            'index'         => 'price',
            'type'          => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'default'       => '0.00',
            )
        );
        $this->addColumn(
            'cost', array(
            'header'        => $textHelper->__('Cost'),
            'align'         => 'left',
            'index'         => 'cost',
            'type'          => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'default'       => '0.00',
            )
        );
        $this->addColumn(
            'note', array(
            'header'        => $textHelper->__('Notes'),
            'index'         => 'note',
            'getter'        => 'getShortNote',
            )
        );

        $this->addExportType('*/*/exportCsv', $textHelper->__('CSV'));
        $this->addExportType('*/*/exportXml', $textHelper->__('Excel XML'));
        return $this;
    }
    /**
     * Get row URL
     *
     * @param   Varien_Object $row
     *
     * @return  string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array($this->getObjectId() => $row->getId(), 'website' => $this->getWebsiteId(), ));
    }
    /**
     * Prepare mass action
     *
     * @return MP_Warehouse_Block_Adminhtml_ShippingTablerate_Tablerate_Grid
     */
    protected function _prepareMassaction()
    {
        $textHelper = $this->getTextHelper();
        $this->setMassactionIdField('pk');
        $this->getMassactionBlock()->setFormFieldName($this->getObjectId());
        $this->getMassactionBlock()->addItem(
            'delete', array(
            'label'       => $textHelper->__('Delete'),
            'url'         => $this->getUrl('*/*/massDelete', array('website' => $this->getWebsiteId())),
            'confirm'     => $textHelper->__('Are you sure?')
            )
        );
        return $this;
    }
    /**
     * Retrieve warehouse helper
     *
     * @return MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Get warehouses options
     * 
     * @return array
     */
    protected function getWarehousesOptions()
    {
        $helper             = $this->getWarehouseHelper();
        $options            = array();
        $warehouses         = $helper->getWarehousesOptions(false, '*', '0');
        foreach ($warehouses as $warehouse) {
            $options[$warehouse['value']] = $warehouse['label'];
        }

        return $options;
    }
    /**
     * Get methods options
     * 
     * @return array
     */
    protected function getMethodsOptions()
    {
        $helper             = $this->getWarehouseHelper();
        $options            = array();
        $tablerateMethods   = $helper->getShippingTablerateMethodsOptions(true);
        foreach ($tablerateMethods as $tablerateMethod) {
            $options[$tablerateMethod['value']] = $tablerateMethod['label'];
        }

        return $options;
    }
}
