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

var WarehouseAdminOrder = Class.create(
    AdminOrder, {
    
    initialize : function($super, data) {
        this.stockId = false;
        $super(data);
    }, 
    itemsReset : function(){
        var area = ['sidebar', 'items', 'shipping_method', 'billing_method','totals', 'giftmessage'];
        var fieldsPrepare = {reset_items: 1};
        fieldsPrepare = Object.extend(fieldsPrepare, this.productConfigureAddFields);
        this.productConfigureSubmit('quote_items', area, fieldsPrepare);
        this.orderItemChanged = false;
    }, 
    setStockShippingMethod : function(stockId, method) {
        var data = {};
        data['order[shipping_method][' + stockId +']'] = method;
        this.loadArea(['shipping_method', 'totals', 'billing_method'], true, data);
    }, 
    setStockId : function(id) {
        this.stockId = id;
        this.loadArea(['data'], true);
    }, 
    prepareParams : function($super, params) {
        params = $super(params);
        if (!params) {
            params = {};
        }

        if (!params.stock_id) {
            params.stock_id = this.stockId;
        }

        return params;
    }
    }
);

