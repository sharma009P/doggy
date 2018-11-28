var Open4DevModules = Open4DevModules || {};

(function(context, undefined) {
	context.utils = context.utils || {};
	
	context.utils.newOptionFilter = context.utils.newOptionFilter || function() { // create a new function only if it is not defined yet
		// returns a helper object that keeps track of options during option iteration. 
		// This is used when examining all options for valid values before submitting a request for reloading. 
		// If there is any option without a valid value we should abort reloading and just update the user display as needed.
		return {
			options : {}, // used as a map of {option-name : true/false} pairs for all available options
			// returns true if we should continue iterate options or false if an option found that is empty.
			// (this can be the case only for a select option). Otherwise after all options are iterated we can call the hasEmpty() 
			// to decide if there are any empty options or not
			add: function(option) {  
				var isSelect = option.prop("tagName").toUpperCase() === 'select'.toUpperCase();
				var optionName = option.prop('name');
				var valueExists = this.options[optionName] || false;
				var hasValue = isSelect ? !!option.val() : option.is(':checked'); // we only care for existence so for selects convert value to true/false, for radios use :checked attribute
				if (!valueExists) {
					this.options[optionName] = hasValue;
				}
				// for selects we can bail out early if we find no value, for radios each value is a separate input field, so we need to examine 
				// all of them before deciding if an option is checked
				return isSelect ? hasValue : true; 
			},
			hasEmpty: function() {
				for (var o in this.options) {
					if (!this.options[o]) {
						return true;	
					}
				}
				return false;
			}
		};	
	};
	
	context.utils.preloader = context.utils.preloader || {
		images: {},
		addImage: function(imageUrl) {
			if (!this.images[imageUrl]) {
				var img = new Image();
				img.src = imageUrl;
				this.images[imageUrl] = img;
				//console.log('Added image for preload: ' + imageUrl);
			} 
		}
	};
	
	context.utils.parseUrlParam = context.utils.parseUrlParam || function(url, paramName) {
		var result = '';
		if (!url) {
			return result;	
		}
		var query = url.split('?');
		if (query.length > 1) {
			query = query[1];
		}
		var params = query.split('&');
		var val;
		for (var i = 0; i < params.length; i++) {
			val = params[i].split("=");
			if (val[0] == paramName) {
  				result = val[1];
  				break;
			}
		}
		return result;
	};
	
})(Open4DevModules);

(function(context, undefined) { 
	
	var mReloadUrl;
	
	// modules that wish to reload some information using json
	// each module should register itself using a unique name by calling registerModule()
	// the functions each module needs to support are:
	// - getReloadUrl() (required): the returned url should contain a parameter named 'route' like all opencart urls
	// - getFieldsToSubmit() (required): The data that need to be submitted for the module (jQuery object)
	// - onBeforeReload() (optional): the function to call before issuing the request (i.e. the one corresponding to ajax beforeSend)
	// - onReloadSuccess() (required): the function to call before issuing the request (i.e. the one corresponding to ajax success)
	// - onReloadComplete() (optional): the function to call after the request returns (i.e. the one corresponding to ajax complete)
	
	var mModules = {}; 

	// temp map to use from the first event triggered that might cause a reload until a reload request is issued.
	// these values live only during event handler execution and re-initialized after that
	var mReloadRequests = {};
	var mRequestsCount = 0;
	
	// a map that holds string keys of the form <field-name>_<event-name> and values are string arrays with all the names of the modules that have installed 
	// such a handler. E.g. if 2 modules have installed an event handler for 'change' on the same field, the entry in this map would be 
	// something like: 'myfield_change' : ['module1', 'module2']
	// This is crucial for the reloader to be able to wait until all handlers are executed. Each module's handler should call the 
	// requestReload() with the final argument either true (to participate in the reload request) or false (if the module does not need reloading)
	var mBoundEvents = {};
	
	var iterate = function(modules, functionName, json) {
		var module;
		for (var mName in modules) {
			module = modules[mName];
			if (module[functionName]) {
				if (json) {
					if (json[mName]) {
						module[functionName].call(module, json[mName]);
					} 
				} else {
					module[functionName].call(module);
				}
			} 
		}
	}
	
	// prefixes all parameters to be submitted with a specified value.
	// params is an array of {name, value} objects as returned by jQuery serializeArray
	// For example [route=value1, option[200]=15} prefixed with 'module' will become: module[route]=value1, module[option][200]=15
	var prefixParams = function(paramPrefix, params, resultArray) {
		var param, newKey, index, prefix, suffix;
		for (var k = 0; k < params.length; k++) {
			param = params[k];
			// wrap in brackets the name only, ignore any brackets already there i.e. option[200] is wrapped as: module[option][200]
			index = param.name.indexOf('[');
			prefix = param.name.substring(0, index);
			suffix = param.name.substring(index);
			newKey = index >= 0 ? paramPrefix + '[' + prefix + ']' + suffix : paramPrefix + '[' + param.name + ']';
			resultArray.push( {name: newKey, value: param.value} );	
		}
	};
	
	context.utils.reloader = context.utils.reloader || {
		configure: function(config) {
			var config = config || {};
			mReloadUrl = config.reloadUrl || mReloadUrl || 'index.php?route=extension/module/reload';
		}, 
		
		registerModule: function(name, module) {
			//console.log('Reloader: Registering module: ' + name);
			mModules[name] = module;
		},
		
		unregisterModule: function(name) {
			if (mModules[name]) {
				//console.log('Reloader: Unregistering module: ' + name );
				//console.log('Bound Events before: ' + JSON.stringify(mBoundEvents));
				delete mModules[name];
				
				for (var eventKey in mBoundEvents) {
					var modules = mBoundEvents[eventKey];
					var pos = $.inArray(name, modules);
					if (pos > -1) {
						modules.splice(pos, 1);
						if (modules.length == 0) {
							delete mBoundEvents[eventKey];
						}
					}	
				}
				//console.log('Bound Events after: ' + JSON.stringify(mBoundEvents));
			}
		},
		
		trackElements: function(eventName, moduleName, elements) {
			//console.log('Tracking event: ' + eventName + ' for module: ' + moduleName + ' for ' + elements.length + ' elements');
			elements.each(function() {
				var fieldName = $(this).prop('name');
				var eventKey = fieldName + '_' + eventName;
				if (!mBoundEvents[eventKey]) {
					mBoundEvents[eventKey] = [];
				}
				var modules = mBoundEvents[eventKey];
				if ($.inArray(moduleName, modules) === -1) {
					modules.push(moduleName);
				}
			});
		},
		
		dump: function() { // for testing
			var o = {
				mReloadUrl: mReloadUrl,
				mModules: mModules,
				mReloadRequests: mReloadRequests,
				mRequestsCount: mRequestsCount,
				mBoundEvents: mBoundEvents
			};
			return o;
		},
		
		getAllFieldsToSubmit: function(modules) {
			var modulesToReload = modules || mModules;
			var wrappedFields = [];
			var module, fields, url;
			for (var mName in modulesToReload) {
				var module = modulesToReload[mName];
				if (module) {
					if (!module.getFieldsToSubmit || !module.getReloadUrl) {
						//console.warn('Module ' + key + ' does not support getFieldsToSubmit() and/or getReloadUrl()');
						continue;	
					}
					fields = module.getFieldsToSubmit().serializeArray();
					url = context.utils.parseUrlParam(module.getReloadUrl(), 'route');
					fields.push({name: 'module_route', value: url});
					
					prefixParams(mName, fields, wrappedFields);
					//console.log('Reloader: Module:' + mName + ', fields:' + JSON.stringify(fields));
				}
			}
			return wrappedFields;
		},
		
		requestReload: function(moduleName, event, include) {
			if (mModules[moduleName]) {
				//console.log('Reloader: Scheduling request for reload for event: ' + event.type + ', module: ' + moduleName + ', include? ' + include);
				mReloadRequests[moduleName] = include ? mModules[moduleName] : false;
				mRequestsCount++;
				this.invokeReloadIfReady(event);
			} else {
				//console.warn('Module: ' + moduleName + ' requested reload for event: ' + event.type +', but this module is not registered');	
			}
		},
		
		clearRequests: function() {
			mReloadRequests = {};
			mRequestsCount = 0;
		},
		
		invokeReloadIfReady: function(event) {
			var fieldName = $(event.target).prop('name');
			var eventKey = fieldName + '_' + event.type;
			
			if (!mBoundEvents[eventKey]) {
				//console.warn('Unexpected condition: The event key: ' + eventKey + ' is not included in bound events! Some module requests reload for an event it did not call trackElements() for!');
				return;	
			}
			var handlers = mBoundEvents[eventKey].length || 0;
			//console.log('Reloader: Event key: ' + eventKey + ' is assigned to ' + handlers + ' handlers. Requests so far: ' + mRequestsCount);
			if (handlers > 0 && mRequestsCount == handlers) {
				//console.log('Reloader: All handlers executed. Checking if there are data to submit');
				var fields = this.getAllFieldsToSubmit(mReloadRequests);
				if (fields.length > 0) {
					this.reload(mReloadRequests, fields);
					this.clearRequests();
					//console.log('Reloader: Reload request completed');
				}
				// if we get here we need to clear the requests count even if we didn't issue a request
				this.clearRequests();
			}
		},
		
		reload : function (modulesToReload, dataFields) {
			var modules = modulesToReload || mModules;  // the modules specified or all registered modules
			var fields = dataFields || this.getAllFieldsToSubmit(modules); // the fields specified or all fields returned by each module
			
			$.ajax({
				url: mReloadUrl,
				type: 'post',
				data: fields,
				dataType: 'json',
				beforeSend: function() {
					iterate(modules, 'onBeforeReload');
				},
				success: function(json) {
					iterate(modules, 'onReloadSuccess', json);
				},
				complete: function() {
					iterate(modules, 'onReloadComplete');
				}
			});
		}
	
	};
	context.utils.reloader.configure(); // configure with default options
})(Open4DevModules);
