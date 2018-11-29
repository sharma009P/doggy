
var Open4DevModules = Open4DevModules || {};

(function(context, undefined) { 
	
	var HAS_RELOADER = (Open4DevModules.utils && Open4DevModules.utils.reloader) ? true : false;
	var RELOADER = HAS_RELOADER ? Open4DevModules.utils.reloader : {};
	var MODULE_NAME = 'stock';
	
	// create a new object only if stock module is not defined yet
	context.stock = context.stock || {
		configure : function(config) {
			var	config = config || {};
			
			this.originalStockText = config.originalStockText || this.originalStockText || '';
			this.stockAvailable = config.stockAvailable || this.stockAvailable || false;
			this.textInStock = config.textInStock || this.textInStock || '';
			this.textOutOfStock = config.textOutOfStock || this.textOutOfStock || '';
			this.stockDisplayMethod = config.stockDisplayMethod || this.stockDisplayMethod || 0;
			this.configStockDisplay = config.configStockDisplay || this.configStockDisplay || 0; 
			this.combinationInStockClass = config.combinationInStockClass || this.combinationInStockClass || 'sm-comb-instock';
			this.combinationOutOfStockClass = config.combinationOutOfStockClass || this.combinationOutOfStockClass || 'sm-comb-outofstock';
			this.productInStockClass = config.productInStockClass || this.productInStockClass || 'sm-product-instock';
			this.productOutOfStockClass = config.productOutOfStockClass || this.productOutOfStockClass || 'sm-product-outofstock';
			this.optionMarkerClass = config.optionMarkerClass || this.optionMarkerClass || 'stock-enabled';
			this.stockSelector = config.stockSelector || this.stockSelector || '#stock';
			this.optionSelector = config.optionSelector || this.optionSelector || 'select, input[type=\'radio\']';
			this.changeProductCss = config.changeProductCss || this.changeProductCss || false;
			this.productInfoSelector = config.productInfoSelector || this.productInfoSelector || '#product';
		
			this.findSelector = config.findSelector || this.findSelector || '';
			this.removeSelector = config.removeSelector || this.removeSelector || '';
			this.stockTemplate = config.stockTemplate || this.stockTemplate || '';
			this.decorateTemplate = config.decorateTemplate || this.decorateTemplate || '';
			this.iconUrl = config.iconUrl || this.iconUrl || 'catalog/view/theme/default/image/loading.gif';
			this.iconTemplate = config.iconTemplate || this.iconTemplate || '<img style="margin-left:8px;" src="' + this.iconUrl + '"/>';
			this.reloadUrl = config.reloadUrl || this.reloadUrl ||  'index.php?route=product/product/getStockAvailable';
			
			var dt = $(this.decorateTemplate);
			this.stockProcessedTemplate = ( dt.is(this.stockSelector) ? this.decorateTemplate : dt.find(this.stockSelector).html() ) || '';
			
			this.useReloader = config.useReloader || this.useReloader || HAS_RELOADER; 
		}, 
		
		getReloadUrl: function() {
			return this.reloadUrl;
		}, 
		
		// Checks if the stock field is already decorated (i.e. an id=<stockSelector> has already been added)
		isStockFieldDecorated : function() {
			var e = $(this.stockSelector);
  			return e.length > 0;
		},
		
		decorateStockField : function() {
	  		if (this.findSelector && this.decorateTemplate) {
	    		var found = document.evaluate(this.findSelector, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
	    		var cssClass = this.changeProductCss ? (this.stockAvailable ? this.productInStockClass : this.productOutOfStockClass) : '';
	    		$(found).replaceWith(this.decorateTemplate);
	    		$(this.stockSelector).addClass(cssClass);
	  		}
	    	if (this.removeSelector) {
	    		$(removeSelector).remove();
	  		}
		},
		
		// Installs a change() listener to stock-enabled options so we can update the available stock when a combination changes
		// @param options: an array with the names of the options to track
		trackOptionsChanges : function(options) {
			var module = this;
			$(this.optionSelector).each(function() {
			    var sname = $(this).attr('name');
			    //console.log('Checking name existence: ' + sname);
				if ($.inArray(sname, options) > -1) {
				  $(this).addClass(module.optionMarkerClass);
				  //console.log('Added select class to select field: ' + sname);
				}
			}); 
			var allOptions = this.getMarkedOptions();
			var namespace = '.' + MODULE_NAME;
			allOptions.on('change' + namespace, function(event) {
				module.onOptionChanged(event);
			});
			if (this.useReloader) {
				RELOADER.trackElements('change', MODULE_NAME, allOptions);
			}
		},
		
		getFieldsToSubmit: function() {
			var r = $(this.productInfoSelector + ' input[name="product_id"], select[class*="' + this.optionMarkerClass + '"], input[type="radio"][class*="' + this.optionMarkerClass + '"]:checked'); 
			//console.log('Stock: returning ' + r.length + ' fields to submit');
			return r;
		},
		
		getMarkedOptions: function() {
			return $('.' + this.optionMarkerClass);
		},
		
		areAllOptionsSelected: function(options) {
			var optionFilter = context.utils.newOptionFilter();
			
			var markedOptions = options || this.getMarkedOptions(); // for testing: to be able to call without arguments
			markedOptions.each(function() {
				return optionFilter.add( $(this) );
			});
			//console.log('Options: ' + JSON.stringify(optionFilter.options));
			return ! optionFilter.hasEmpty();
		},
		
		onOptionDeselected: function() {
			this.updateStockStatus('');
		}, 
		
		onOptionChanged: function(event) {
			var allOptions = this.getMarkedOptions();
			if (!this.areAllOptionsSelected(allOptions)) {
				this.onOptionDeselected();
				this.abortReload(event);
			} else {
		  		this.invokeReload(event, allOptions);
		  	}
		}, 
		
		invokeReload: function(event, options) {
			if (this.useReloader) {
				RELOADER.requestReload(MODULE_NAME, event, true);
			} else {
		  		this.reload(options);
		  	}
		},
		
		abortReload: function(event) {
			if (this.useReloader) {
				RELOADER.requestReload(MODULE_NAME, event, false);
		  	}
		}, 

		onBeforeReload: function(allOptions) {
			var options = allOptions || this.getMarkedOptions(); // for testing: to be able to call without arguments
			if ($(this.stockSelector + '>img').length == 0) {
				$(this.stockSelector).append(this.iconTemplate);
			}
			options.attr('disabled', 'disabled');
		},
		onReloadSuccess: function(json) {
			if (json['success']) {
				this.updateStockStatus(json['stock']);
			} 
		},
		onReloadComplete: function(allOptions) {
			var options = allOptions || this.getMarkedOptions(); // for testing: to be able to call without arguments
			options.removeAttr('disabled');
			$(this.stockSelector + '>img').remove();
		},
		
		// Modifies the HTML of the field where the stock/stock status is displayed
		// The original stock status (when the page is loaded) is "rememberred and can be restored by calling this function with a '' argument
		updateStockStatus : function (stockValue) {
			var selector = this.stockSelector;
			//console.log('Updating stock status with value: ' + stockValue);
			var stock = $(selector);
			stock.removeClass(this.combinationInStockClass + ' ' + this.combinationOutOfStockClass);	
			if (stockValue === '') {
				//console.log('New stock status is empty. Restoring original stock status');
				stock.html(this.stockProcessedTemplate);
			} else if (stockValue <= 0) {
				//console.log('New stock status is negative. Adding class out of stock, removing class instock');
				stock.html(this.stockProcessedTemplate.replace(this.originalStockText, this.textOutOfStock));
				stock.addClass(this.combinationOutOfStockClass);  	
			} else {
				// combination has stock
				if (this.stockDisplayMethod) {
					// second display method (show both product and combination availability)
					var template = this.stockTemplate.replace('%stock_value%', stockValue);
					stock.html(template);
				} else {
					// first display method (replace product availability with combination availability)
					// show the text or the quantity depending on the store settings
					var newValue = this.configStockDisplay ? stockValue : this.textInStock;
				    stock.html(this.stockProcessedTemplate.replace(this.originalStockText, newValue));
				}
				//console.log('New stock status is positive. Removing class out of stock, adding class instock');
				stock.addClass(this.combinationInStockClass);
			}
		},
		
		// Invoked when a stock-enabled field (select or radio) changes. If a valid combination is selected in all sotck-enabled fields
		// this function issues an ajax request to the server to retrieve the stock for that combination 
		reload : function (allOptions) {
			var module = this;
			// all stock-enabled options have a value so we need to issue a request to check combination quantity
			$.ajax({
				url: module.reloadUrl,
				type: 'post',
				data: this.getFieldsToSubmit(),
				dataType: 'json',
				beforeSend: function() {
					module.onBeforeReload(allOptions);
				},
				success: function(json) {
					module.onReloadSuccess(json);
				},
				complete: function(json) {
					module.onReloadComplete(allOptions);
				}
			});
		}
	};

	context.stock.configure({}); // configure with default options
	
	context.utils.preloader.addImage('catalog/view/theme/default/image/loading.gif');
	
	if (context.stock.useReloader) {
		RELOADER.registerModule(MODULE_NAME, context.stock);
	}
})(Open4DevModules);

