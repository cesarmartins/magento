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

if (typeof quoteBaseGrandTotal == 'undefined') {
    quoteBaseGrandTotal = null;
}

if (typeof checkQuoteBaseGrandTotal == 'undefined') {
    checkQuoteBaseGrandTotal = null;
}

/**
 * Available Abstract
 */
var ShippingMethodAvailableAbstract = Class.create();

ShippingMethodAvailableAbstract.prototype = {

    initialize: function(elementId, shippingPrices, currentShippingPrice) {
        this.elementId = elementId;
        this.shippingPrices = shippingPrices;
        this.currentShippingPrice = currentShippingPrice;
        this.update();
    }, 
    
    getShippingPrice: function(shippingMethodCode) {
        if (typeof this.shippingPrices[shippingMethodCode] !== 'undefined') {
            return this.shippingPrices[shippingMethodCode];
        } else {
            return 0;
        }
    }, 
    
    resetCurrentShippingPrice: function() {
        this.currentShippingPrice = null;
    }, 
    
    isCurrentShippingPriceSet: function() {
        if (this.currentShippingPrice !== null) {
            return true;
        } else {
            return false;
        }
    }, 

    getCurrentShippingPrice: function() {
        if (this.isCurrentShippingPriceSet()) {
            return this.currentShippingPrice;
        } else {
            return 0;
        }
    }, 

    setCurrentShippingPrice: function(price) {
        this.currentShippingPrice = price;
    }, 

    update: function() {
        return false;
    }, 
    
    validate: function() {
        return true;
    }
};
/**
 * Single Mode
 */
var ShippingMethodSingleMode = Class.create(
    ShippingMethodAvailableAbstract, {

    update: function() {
        var self = this;
        $(this.elementId).select('.shipping-method').each(
            function (shippingMethodElement) {
            var shippingMethod = $(shippingMethodElement);
            shippingMethod.observe(
                'click', function() {
                if (shippingMethodElement.checked) {
                    var shippingMethodCode = shippingMethod.getValue();
                    var price = self.getShippingPrice(shippingMethodCode);
                    if (!self.isCurrentShippingPriceSet()) {
                        self.setCurrentShippingPrice(price);
                        quoteBaseGrandTotal += price;
                    }

                    var currentPrice = self.getCurrentShippingPrice();
                    if (price != currentPrice) {
                        quoteBaseGrandTotal += (price - currentPrice);
                        self.setCurrentShippingPrice(price);
                    }

                    checkQuoteBaseGrandTotal = quoteBaseGrandTotal;
                    return false;
                }
                }
            );
            }
        );
    }, 
    
    validate: function() {
        var shippingMethods = $(this.elementId).select('.shipping-method');
        if (shippingMethods.length == 0) {
            alert(Translator.translate('Your order cannot be completed at this time as there is no shipping methods available for it. Please make necessary changes in your shipping address.'));
            return false;
        }

        var isSelected = false;
        shippingMethods.each(
            function (shippingMethodElement) {
            if (shippingMethodElement.checked) {
                isSelected = true;
            }
            }
        );
        if (!isSelected) {
            alert(Translator.translate('Please specify shipping method.'));
            return false;
        } else {
            return true;
        }
    }
    }
);
/**
 * Multiple Mode
 */
var ShippingMethodMultipleMode = Class.create(
    ShippingMethodAvailableAbstract, {

    getShippingPrice: function(stockId, shippingMethodCode) {
        if ((typeof this.shippingPrices[stockId] !== 'undefined') && 
           (typeof this.shippingPrices[stockId][shippingMethodCode] !== 'undefined')
        ) {
            return this.shippingPrices[stockId][shippingMethodCode];
        } else {
            return 0;
        }
    }, 
    
    resetCurrentShippingPrice: function() {
        this.currentShippingPrice = [];
    }, 

    isCurrentShippingPriceSet: function(stockId) {
        if (typeof this.currentShippingPrice[stockId] !== 'undefined') {
            return true;
        } else {
            return false;
        }
    }, 

    getCurrentShippingPrice: function(stockId) {
        if (this.isCurrentShippingPriceSet(stockId)) {
            return this.currentShippingPrice[stockId];
        } else {
            return 0;
        }
    }, 

    setCurrentShippingPrice: function(stockId, price) {
        this.currentShippingPrice[stockId] = price;
    }, 

    update: function() {
        var self = this;
        $(this.elementId).select('.warehouse').each(
            function (warehouseElement) {
            var warehouse = $(warehouseElement);
            var stockId = warehouse.readAttribute('warehouse:stockid');
            warehouse.select('.shipping-method').each(
                function (shippingMethodElement) {
                var shippingMethod = $(shippingMethodElement);
                shippingMethod.observe(
                    'click', function() {
                    if (shippingMethodElement.checked) {
                        var shippingMethodCode = shippingMethod.getValue();
                        var price = self.getShippingPrice(stockId, shippingMethodCode);
                        if (!self.isCurrentShippingPriceSet(stockId)) {
                            self.setCurrentShippingPrice(stockId, price);
                            quoteBaseGrandTotal += price;
                        }

                        var currentPrice = self.getCurrentShippingPrice(stockId);
                        if (price != currentPrice) {
                            quoteBaseGrandTotal += (price - currentPrice);
                            self.setCurrentShippingPrice(stockId, price);
                        }

                        checkQuoteBaseGrandTotal = quoteBaseGrandTotal;
                        return false;
                    }
                    }
                );
                }
            );
            }
        );
    }, 
    
    validate: function() {
        var errors = new Array();
        var isError = false;
        $(this.elementId).select('.warehouse').each(
            function (warehouseElement) {
            var warehouse = $(warehouseElement);
            var stockId = warehouse.readAttribute('warehouse:stockid');
            var warehouseTitle = warehouse.readAttribute('warehouse:title');
            var shippingMethods = warehouse.select('.shipping-method');
            if (shippingMethods.size() == 0) {
                if (warehouseTitle) {
                    errors.push(Translator.translate('There are no shipping methods available for %s warehouse.').sub('%s', warehouseTitle));
                } else {
                    errors.push(Translator.translate('There are no shipping methods available.'));
                }

                isError = true;
            }
            }
        );
        if (!isError) {
            $(this.elementId).select('.warehouse').each(
                function (warehouseElement) {
                var warehouse = $(warehouseElement);
                var warehouseTitle = warehouse.readAttribute('warehouse:title');
                var _checked = false;
                warehouse.select('.shipping-method').each(
                    function (shippingMethodElement) {
                    if (shippingMethodElement.checked) {
                        _checked = true;
                    }
                    }
                );
                if (!_checked) {
                    if (warehouseTitle) {
                        errors.push(Translator.translate('Please specify shipping method for %s warehouse.').sub('%s', warehouseTitle));
                    } else {
                        errors.push(Translator.translate('Please specify shipping method.'));
                    }

                    isError = true;
                }
                }
            );
        }

        if (isError) {
            alert(errors.join("\r\n"));
            return false;
        } else {
            return true;
        }
    }
    }
);
/**
 * Warehouse Shipping Method
 */ 
var WarehouseShippingMethod = Class.create(
    ShippingMethod, {
    
    initialize: function($super, form, saveUrl) {
        $super(form, saveUrl);
        this.methodSelector = null;
    }, 
    
    setMethodSelector: function(methodSelector) {
        this.methodSelector = methodSelector;
    }, 
    
    validate: function() {
        if (this.methodSelector && this.methodSelector.validate) {
            return this.methodSelector.validate();
        } else {
            return true;
        }
    }
    }
);
