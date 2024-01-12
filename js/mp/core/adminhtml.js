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

var EditableGridFormControl = Class.create(
    varienForm, {

    initialize : function($super, grid, formId, elementIdPrefix, elementNames, defaults, validationUrl) {
        $super(formId, validationUrl);
        this.grid = grid;
        this.elementIdPrefix = elementIdPrefix;
        this.elementNames = elementNames;
        this.defaults = defaults;
        this.grid.rowClickCallback = this.rowClick.bind(this);
        this.grid.initRowCallback = this.rowInit.bind(this);
        this.grid.rows.each(function(row) { this.rowInit(this.grid, row); }.bind(this));
    }, 
    rowClick : function(grid, event) {
    return; }, 
    rowInit : function(grid, row) {
        var select = $(row).down('.action-select');
        if (select) {
            select.writeAttribute('onchange', null);
            select.observe('change', this.actionSelectChange.bind(this));
        }
    }, 
    actionSelectChange: function(event) {
        var select = Event.element(event);
        if (!select.value || !select.value.isJSON()) {
            return;
        }

        var config = select.value.evalJSON();
        if (config.confirm && !window.confirm(config.confirm)) {
            return;
        }
     
        if (config.href) {
            if (config.name == 'edit') {
                this.doEdit(config.href);
            } else if (config.name == 'delete') {
                this.doDelete(config.href);
            }    
        }
    
        select.options[0].selected = true;
        return;
    }, 
    doDelete : function(url) {
        if (url) {
            new Ajax.Request(
                url, {
                area : $(this.grid.containerId), 
                onComplete : this.doDeleteComplete.bind(this)
                }
            );
        }
    }, 
    doDeleteComplete : function(transport) {
        var result = transport.responseText.evalJSON();
        if (result.messages) {
            $('messages').update(result.messages);
        }

        if (!result.error) {
            this.grid.reload();
        }
    }, 
    getElementNames : function() {
        return this.elementNames;
    }, 
    getDefaults : function() {
        return this.defaults;
    }, 
    getDefault : function(name) {
        var defaults = this.getDefaults();
        if (defaults[name]) {
            return defaults[name];
        } else {
            return null;
        }
    }, 
    getElementIdPrefix : function() {
        return this.elementIdPrefix;
    }, 
    getElement : function(name) {
        return $(this.getElementIdPrefix() + name);
    }, 
    hasElement : function(name) {
        return (this.getElement(name)) ? true : false;
    }, 
    setValue : function(name, value) {
        if (this.hasElement(name)) {
            this.getElement(name).setValue(value);
        }
    }, 
    setValues : function(values) {
        var self = this;
        this.getElementNames().each(
            function(elementName) {
            self.setValue(
                elementName, (
                (values[elementName] && values[elementName] !== null) ? values[elementName] : self.getDefault(elementName)
                )
            );
            }
        );
    }, 
    doAdd : function() {
        this.setValues(this.getDefaults());
    }, 
    doEdit : function(url) {
        if (url) {
            new Ajax.Request(
                url, {
                area : $(this.grid.containerId), 
                onComplete : this.doEditComplete.bind(this)
                }
            );
        }
    }, 
    doEditComplete : function(transport) {
        var result = transport.responseText.evalJSON();
        if (result.messages) $('messages').update(result.messages);
        if (result.data) this.setValues(result.data);
    }, 
    _submit : function() {
        if (this.submitUrl) {
            var params = Form.serializeElements($(this.formId).select('input', 'select', 'textarea'), true);
            params.form_key = FORM_KEY;
            $('messages').update();
            new Ajax.Request(
                this.submitUrl, {
                parameters : params, 
                method : 'post', 
                area : $(this.formId), 
                onComplete : this.doSubmitComplete.bind(this)
                }
            );
        }
    }, 
    doSubmitComplete : function(transport) {
        var result = transport.responseText.evalJSON();
        if (result.messages) $('messages').update(result.messages);
        if (!result.error) this.grid.reload();
    }
    }
);

/**
 * Form Element Control
 */
var FormElementControl = new Class.create();

FormElementControl.prototype = {

    initialize : function(data) {
        if (!data) data = {};
        if (data.elementId) {
            this.elementId          = data.elementId;
        }

        this.render();
    }, 
    
    getElement : function() {
        return $(this.elementId);
    }, 
    
    getActionsElement : function() {
        return $(this.getElement().select('.control-actions').first());
    }, 
    
    getBodyElement : function() {
        return $(this.getElement().select('.control-body').first());
    }, 
            
    toggleActions : function(flag) {
        var el = this.getActionsElement();
        if (flag) {
            el.show();
        } else {
            el.hide();
        }
    }, 
    
    hideActions : function() {
        this.toggleActions(false);
    }, 
    
    showActions : function() {
        this.toggleActions(true);
    }, 
    
    enableElement: function(el) { 
        $(el).enable(); 
        $(el).addClassName('enabled'); 
    }, 
    
    disableElement: function(el) { 
        $(el).disable();
        $(el).addClassName('disabled');
    }, 
    
    render : function() {
        
    }
};

/**
 * Checkbox Control
 */
var CheckboxControl = Class.create(
    FormElementControl, {

    initialize : function($super, data) {
        if (!data) data = {};
        if (data.dependent) {
            this.dependent = data.dependent;
        }

        $super(data);
        var self = this;
        this.getInputElement().observe(
            'click', function () {
            self.render(); }
        );
    }, 
    
    getInputElement : function() {
        return $(this.getBodyElement().select('.checkbox').first());
    }, 
    
    render : function() {
        if (this.dependent) {
            var inputEl = this.getInputElement();
            this.dependent.each(
                function (el) {
                if (!inputEl.checked) {
                    $(el).enable();
                } else {
                    $(el).disable();
                }
                }
            );
        }
    }
    }
);
    

/**
 * Multiple Checkbox Control
 */
var MultipleCheckboxControl = Class.create(
    FormElementControl, {

    initialize : function($super, data) {
        $super(data);
        var self = this;
        this.getSelectAllElement().observe('click', function () { self.selectAll(); });
        this.getUnselectAllElement().observe(
            'click', function () {
            self.unselectAll(); }
        );
    }, 
    
    getSelectAllElement : function() {
        return $(this.getActionsElement().select('.select-all').first());
    }, 
    
    getUnselectAllElement : function() {
        return $(this.getActionsElement().select('.unselect-all').first());
    }, 
    
    selectAll : function() {
        this.getBodyElement().select('.checkbox').each(
            function(el) {
            el.checked = true;
            }
        );
    }, 
    
    unselectAll : function() {
        this.getBodyElement().select('.checkbox').each(
            function(el) {
            el.checked = false;
            }
        );
    }
    }
);

/**
 * Tree Control
 */
var TreeControl = Class.create(
    FormElementControl, {

    initialize : function($super, data) {
        if (!data) data = {};
        if (data.tree) {
            this.tree = data.tree;
        }

        $super(data);
        var self = this;
        this.getCollapseAllElement().observe(
            'click', function () {
            self.collapseAll(); }
        );
        this.getExpandAllElement().observe(
            'click', function () {
            self.expandAll(); }
        );
        this.getSelectAllElement().observe(
            'click', function () {
            self.selectAll(); }
        );
        this.getUnselectAllElement().observe(
            'click', function () {
            self.unselectAll(); }
        );
    }, 
    
    getCollapseAllElement : function() {
        return $(this.getActionsElement().select('.collapse-all').first());
    }, 
    
    getExpandAllElement : function() {
        return $(this.getActionsElement().select('.expand-all').first());
    }, 
    
    getSelectAllElement : function() {
        return $(this.getActionsElement().select('.select-all').first());
    }, 
    
    getUnselectAllElement : function() {
        return $(this.getActionsElement().select('.unselect-all').first());
    }, 
    
    collapseAll : function() {
        this.tree.collapseAll();
    }, 
    
    expandAll : function() {
        this.tree.expandAll();
    }, 
    
    onSelectAll : function(treeNode) {
        var inputEl = treeNode.getUI().getEl().select('input').first();
        if (!inputEl.checked) {
            $(inputEl).click();
        }
    }, 
    
    onUnselectAll : function(treeNode) {
        var inputEl = treeNode.getUI().getEl().select('input').first();
        if (inputEl.checked) {
            $(inputEl).click();
        }
    }, 
    
    selectAll : function() {
        this.tree.getRootNode().cascade(this.onSelectAll, this);
    }, 
    
    unselectAll : function() {
        this.tree.getRootNode().cascade(this.onUnselectAll, this);
    }
    }
);


/**
 * Grid Control
 */
var GridControl = Class.create(
    FormElementControl, {

    initialize : function($super, data) {
        if (!data) {
            data                = {};
        }

        if (data.columns) {
            this.columns        = data.columns;
        }

        if (data.items) {
            this.items          = data.items;
        }

        if (data.defaultItem) {
            this.defaultItem    = data.defaultItem;
        }

        if (data.readonly) {
            this.readonly       = data.readonly;
        }

        this.itemsCount = 0;
        this.getColumns().each(
            function(column) {
            column.template = new Template(
                (('template' in column) ? column.template : ''), 
                new RegExp('(^|.|\\r|\\n)({{\\s*(\\w+)\\s*}})', "")
            );
            }, this
        );
        this.getItems().each(
            function(item) {
            item.index = this.itemsCount;
            this.itemsCount++;
            }, this
        );
        $super(data);
    }, 
            
    getColumns : function() {
        return (('columns' in this) && Object.isArray(this.columns)) ? this.columns : [];
    }, 
            
    getColumnByName : function(name) {
        var column = null;
        this.getColumns().each(
            function(col) {
            if (('name' in col) && (col.name === name)) {
                column = col;
            }
            }, this
        );
        return column;
    }, 
    
    getColumnElementByName : function(trEl, name) {
        var el      = null;
        var column  = this.getColumnByName(name);
        if (column && ('class' in column)) {
            el = $(trEl.select('.' + column.class).first());
        }

        return el;
    }, 
            
    getItems : function() {
        return (('items' in this) && Object.isArray(this.items)) ? this.items : [];
    }, 
            
    getDefaultItem : function() {
        return ('defaultItem' in this) ? this.defaultItem : {};
    }, 
            
    isReadonly : function() {
        return (('readonly' in this) && this.readonly) ? true : false;
    }, 
    
    getTableElement : function() {
        return $(this.getBodyElement().select('table').first());
    }, 
    
    getTableHeaderElement : function() {
        return $(this.getTableElement().select('thead').first());
    }, 
    
    getTableBodyElement : function() {
        return $(this.getTableElement().select('tbody').first());
    }, 
    
    getTableFooterElement : function() {
        return $(this.getTableElement().select('tfoot').first());
    }, 
    
    renderItem : function(item) {
        var trEl = new Element('tr');
        this.getColumns().each(
            function(col) {
            var tdEl = new Element('td');
            if ('template' in col) {
                tdEl.insert(col.template.evaluate(item));
            }

            if ('name' in col) {
                if ('class' in col) {
                    var el = $(tdEl.select('.' + col.class).first());
                    if (el) {
                        if (col.name in item) {
                            el.setValue(item[col.name]);
                        }

                        if (this.isReadonly() || 
                            (('readonly' in item) && item.readonly) || 
                            (('readonly' in col) && col.readonly)
                        ) {
                            this.disableElement(el);
                        }

                        if (('hidden' in col) && col.hidden) {
                            el.hide();
                            if (String(el.tagName).toLowerCase() === 'select') {
                                var title = el.options[el.selectedIndex].text;
                                el.insert(
                                    {
                                    after: '<span class="' + col.class + '-name">' + title + '</span>'
                                    }
                                );
                            }
                        }

                        el.observe('change', el.setHasChanges.bind(el));
                    }
                }

                if (col.name === 'delete') {
                    var deleteButtonEl = $(tdEl.select('.delete-button').first());
                    if (deleteButtonEl) {
                        var self = this;
                        deleteButtonEl.observe(
                            'click', function () {
                            self.deleteItem(trEl); }
                        );
                        if (this.isReadonly() || (('readonly' in item) && item.readonly)) {
                            deleteButtonEl.hide();
                        }
                    }
                }
            }

            if (('hiddenContainer' in col) && col.hiddenContainer) {
                tdEl.hide();
            }

            trEl.insert(tdEl);
            }, this
        );
        this.getTableBodyElement().insert(trEl);
        return trEl;
    }, 
            
    deleteItem : function(trEl)
    {
        trEl.select('input.delete').each(
            function(el) { 
            $(el).setValue('1');
            }
        );
        trEl.select(['input', 'select', 'textarea']).each(
            function(el) {
            $(el).hide();
            }
        );
        trEl.hide();
        trEl.addClassName('no-display template');
    }, 
    
    addItem : function() {
        var item = this.getDefaultItem();
        this.itemsCount++;
        item.index = this.itemsCount;
        this.renderItem(item);
    }, 
    
    renderItems : function() {
        this.getItems().each(
            function(item) {
            this.renderItem(item);
            }, this
        );
    }, 
    
    renderTable : function() {
        this.renderItems();
        var addButtonEl = $(this.getTableFooterElement().select('.add-button').first());
        if (addButtonEl) {
            var self = this;
            addButtonEl.observe(
                'click', function () {
                self.addItem(); }
            );
            if (this.isReadonly()) {
                addButtonEl.hide();
                this.getTableFooterElement().hide();
            }
        }
    }, 
    
    render : function() {
        this.renderTable();
    }
    }
);
