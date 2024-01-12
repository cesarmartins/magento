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
 * Warehouse edit
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Warehouse_Edit 
    extends MP_Warehouse_Block_Adminhtml_Core_Widget_Form_Container
{
    /**
     * Object identifier
     * 
     * @var string
     */
    protected $_objectId = 'warehouse_id';
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
    protected $_controller = 'warehouse';
    /**
     * Add Label
     * 
     * @var string
     */
    protected $_addLabel = 'New Warehouse';
    /**
     * Edit label
     * 
     * @var string
     */
    protected $_editLabel = "Edit Warehouse '%s'";
    /**
     * Save label
     * 
     * @var string
     */
    protected $_saveLabel = 'Save Warehouse';
    /**
     * Save and continue label
     * 
     * @var string
     */
    protected $_saveAndContinueLabel = 'Save Warehouse and Continue Edit';
    /**
     * Delete label
     * 
     * @var string
     */
    protected $_deleteLabel = 'Delete Warehouse';
    /**
     * Save and continue enabled
     * 
     * @var bool
     */
    protected $_saveAndContinueEnabled = true;
    /**
     * Tab enabled
     * 
     * @var bool
     */
    protected $_tabEnabled = true;
    /**
     * Tabs block type
     * 
     * @var string
     */
    protected $_tabsBlockType = 'warehouse/adminhtml_warehouse_edit_tabs';
    /**
     * Tabs block identifier
     * 
     * @var string
     */
    protected $_tabsBlockId = 'warehouse_tabs';
    /**
     * Model name
     * 
     * @var string
     */
    protected $_modelName = 'warehouse';
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
     * Get default stock identifier
     * 
     * @return int
     */
    protected function getDefaultStockId()
    {
        return $this->getWarehouseHelper()->getDefaultStockId();
    }
    /**
     * Get stock identifier
     * 
     * @return int
     */
    protected function getStockId()
    {
        return $this->getModel()->getStockId();
    }
    /**
     * Check is allowed action
     * 
     * @param   string $action
     * @return  bool
     */
    protected function isAllowedAction($action)
    {
        if (($action == 'delete') && ($this->getStockId() == $this->getDefaultStockId())) {
            return false;
        }

        return $this->getAdminSession()->isAllowed('catalog/warehouses/'.$action);
    }
    /**
     * Preparing block layout
     * 
     * @return MP_Warehouse_Block_Adminhtml_Warehouse_Edit
     */
    protected function __prepareLayout()
    {
        parent::__prepareLayout();
        $this->_formScripts[] = <<<END
var warehouseOriginModel = Class.create();
warehouseOriginModel.prototype = {
    initialize : function() {
        this.reload = false;
        this.loader = new varienLoader(true);
        this.regionsUrl = '{$this->getUrl('*/*/regions')}';
        this.bindCountryRegionRelation();
    }, 
    bindCountryRegionRelation : function(parentId) {
        if(parentId) {var countryElements = $$('#'+parentId+' .origin_country_id');}
        else {var countryElements = $$('.origin_country_id');}
        for (var i=0; i<countryElements.size(); i++) {
            Event.observe(countryElements[i], 'change', this.reloadRegionField.bind(this));
            this.initRegionField(countryElements[i]);
            if ($(countryElements[i].id+'_inherit')) {
                Event.observe($(countryElements[i].id+'_inherit'), 'change', this.enableRegionZip.bind(this));
            }
        }
    }, 
    enableRegionZip : function(event) {
        this.reload = true;
        var countryElement = Event.element(event);
        if (countryElement && countryElement.id && !countryElement.checked) {
            var regionElement  = $(countryElement.id.replace(/country_id/, 'region_id'));
            var zipElement  = $(countryElement.id.replace(/country_id/, 'postcode'));
            if (regionElement && regionElement.checked) {regionElement.click();}
            if (zipElement && zipElement.checked) {zipElement.click();}
        }
    },
    initRegionField : function(element) {
        var countryElement = element;
        if (countryElement && countryElement.id) {
            var regionElement = $(countryElement.id.replace(/country_id/, 'region_id'));
            if (regionElement) {
                this.regionElement = regionElement;
                var url = this.regionsUrl+'parent/'+countryElement.value;
                this.loader.load(url, {}, this.refreshRegionField.bind(this));
            }
        }
    },
    reloadRegionField : function(event) {
        this.reload = true;
        var countryElement = Event.element(event);
        if (countryElement && countryElement.id) {
            var regionElement  = $(countryElement.id.replace(/country_id/, 'region_id'));
            if (regionElement) {
                this.regionElement = regionElement;
                var url = this.regionsUrl+'parent/'+countryElement.value;
                this.loader.load(url, {}, this.refreshRegionField.bind(this));
            }
        }
    },
    refreshRegionField : function(serverResponse) {
        if (serverResponse) {
            var data = eval('(' + serverResponse + ')');
            var value = this.regionElement.value;
            var disabled = this.regionElement.disabled;
            if (data.length) {
                var html = '<select name="'+this.regionElement.name+'" id="'+this.regionElement.id+'" class="required-entry select" title="'+this.regionElement.title+'"'+(disabled?" disabled":"")+'>';
                for (var i in data) {
                    if(data[i].label) {
                        html+= '<option value="'+data[i].value+'"';
                        if(this.regionElement.value && (this.regionElement.value == data[i].value || this.regionElement.value == data[i].label)) {
                            html+= ' selected';
                        }
                        html+='>'+data[i].label+'<\/option>';
                    }
                }
                html+= '<\/select>';
                var parentNode = this.regionElement.parentNode;
                var regionElementId = this.regionElement.id;
                parentNode.innerHTML = html;
                this.regionElement = $(regionElementId);
            } else if (this.reload) {
                var html = '<input type="text" name="'+this.regionElement.name+'" id="'+this.regionElement.id+'" class="input-text" title="'+this.regionElement.title+'"'+(disabled?" disabled":"")+'>';
                var parentNode = this.regionElement.parentNode;
                var regionElementId = this.regionElement.id;
                parentNode.innerHTML = html;
                this.regionElement = $(regionElementId);
                //this.regionElement.replace(html);
            }
        }
    }
}
warehouseOrigin = new warehouseOriginModel();
END;
        return $this;
    }
}
