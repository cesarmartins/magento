/**
 * Add XHR compatibility to Magento action buttoms
 *
 * Author:
 *   Rafael Patro <rafaelpatro@gmail.com>
 *
 * Requirements:
 *   DOMParser HTML fixes
 *   jQuery Growl library
 *
 * Intallation:
 *   Install requirements
 *   Add a CMS Static Block applying the entire script below.
 *   Add a Widget to product list pages.
 *
 * License:
 *   GNU General Public License <http://www.gnu.org/licenses/>.
 */
;(function($) {
  XHRCart = function() {
    this.doc = null;
    this.response = null;
    this.selectors = ['.account-cart-wrapper'];
    this.messageTypes = ['error','warning','notice','success'];
    this.progressLoader = '<img src="/skin/frontend/rwd/default/images/opc-ajax-loader.gif" alt="progress" class="xhr-loader" style="margin: 0 auto;"/>';
    this.callback = function(){};
  };

  XHRCart.prototype = {
    
    success : function(message) {
      $.growl.notice({'message':message});
    },
    
    notice : function(message) {
      $.growl.notice({'message':message});
    },
    
    error : function(message) {
      $.growl.error({'message':message});
    },
    
    warning : function(message) {
      $.growl.warning({'message':message});
    },
    
    clear : function() {
      $('.xhr-loader').remove();
    },
    
    progress : function() {
      console.log('XHRCart.progress');
      var _this = this;
      this.selectors.each(function(index){
        $(index).html(_this.progressLoader);
      });
    },
    
    loadBlocks : function() {
      console.log('XHRCart.loadBlocks');
      var _this = this;
      this.selectors.each(function(selector){
        $(selector).html($(_this.doc).find(selector).html());
      });
    },
    
    loadMessages : function() {
      console.log('XHRCart.loadMessages');
      if (typeof $.growl == 'function') {
        var _this = this;
        var message;
        var messages;
        if (messages = $(_this.doc).find('.messages')[0]) {
          this.messageTypes.each(function(type){
            if (message = $(_this.doc).find('.messages .' + type + '-msg')[0]) {
              var notify = new Function('elem', 'msg', 'return elem.' + type + '(msg);');
              notify(_this, message.textContent);
            }
          });
          if (typeof message == 'undefined') {
            this.notice(messages.textContent);
          }
        } else if (typeof coShippingMethodForm != 'undefined') {
          this.notice('Concluído!');
        } else {
          this.warning('Para calcular o frete, acesse o carrinho no topo do site.');
        }
      }
    },
    
    loadParser : function() {
      console.log('XHRCart.loadParser');
      var parser = new DOMParser();
      this.doc = parser.parseFromString(this.response, "text/html");
    },
    
    loadCallback : function() {
      console.log('XHRCart.loadCallback');
      if (typeof this.callback == 'function') {
        this.callback(this);
      }
    },
    
    load : function(response) {
      console.log('XHRCart.load');
      this.response = response;
      this.loadParser();
      this.loadBlocks();
      this.loadMessages();
      this.clear();
      this.loadCallback();
      return true;
    },
    
    send : function(elem,param  = null) {
      console.log('XHRCart.send');
      var _this = this;
      //var param = null;
      var url = elem;
      if (typeof url != 'string') {
        url = $(elem).attr('href');
        if (typeof url != 'string') {
          var formObj = $(elem).closest('form');
          url = formObj.attr('action');
          param = formObj.serialize();
        }
      }
      jQuery.ajax({
        url: url.replace(/http:/, location.protocol),
        method: 'POST',
        data: param,
        beforeSend: function() {
          _this.progress();
        },
        success: function(response) {
          _this.load(response);
        }
      });
    }
  };
})(jQuery);


(function($){
  $(document).on('click', '#co-shipping-method-form button', function(evt){
    var totalsUpdate = new XHRCart();
    totalsUpdate.selectors = ['.box-finzalizar']; // Atualiza o bloco de totais
    totalsUpdate.callback = fixSubmitButtoms;
    totalsUpdate.send(evt.target);
    return false;
  });



  $(document).on('change', '.qty_input input.qty', function(evt){
  	var pÍd = $(evt.target).attr('id').replace('qty-item-','');
  	
  	$('#update_cart_action').value = 'update_qty';

  	var itemAction = new XHRCart();
  	itemAction.selectors = ['.cart tr[data-pid="' + pÍd + '"]', '.warehouse-shipping-methods', '.box-finzalizar .totals', '.header-minicart'];
  	itemAction.callback = function() {
      var cartHtml = $(itemAction.doc).find('#shopping-cart-table').html();
      if (typeof cartHtml != 'undefined') {
        $('#shopping-cart-table').html(cartHtml);
      }
    };

    itemAction.send($(evt.target));
    return false;
  });
  $(document).on('click', '.qty_input .plus,.qty_input .less', function(evt){
  	var span = $(evt.target).parent();
  	var spanclass = span.attr("class");
  	var pId = span.closest('tr').data('pid');
  	var qtdElm = $('#qty-item-'+pId);
  	if(spanclass =='plus'){
  		qtdElm.val(parseFloat(qtdElm.val())+1)
	}else if(spanclass == 'less'){
		qtdElm.val(parseFloat(qtdElm.val())-1)    
	}
	qtdElm.trigger('change');

  });
})(jQuery);