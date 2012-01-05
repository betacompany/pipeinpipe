/**
 * @author Artyom Grigoriev
 */

var errorHandler = {

	library: {},

	loadLibrary: function (errorPackage, callback) {
		$.ajax({
			url: '/api.php',
			data: {
				'handler': 'error_library',
				'method': 'load_library',
				'package': errorPackage
			},
			dataType: 'json',
			success: function (json) {
				errorHandler.library[errorPackage] = json;
				if (callback != undefined) callback();
			},
			error: function(ao, rt) {
				debug(ao, rt);
			}
		});		
	},

	handle: function (errorPackage, errorId) {
		try {
			if (this.library[errorPackage] == undefined) {
				this.loadLibrary(errorPackage, function () {
					var error = errorHandler.library[errorPackage][errorId];
					debug('[error-handler.js] ' + errorPackage + '.' + errorId + ' occured');
					debug('[error-handler.js] Error text: ' + error.text);

					if (error.callback != undefined) {
						debug('[error-handler.js] Built-in error callback found');
						error.callback = eval('(' + error.callback + ')');
						debug('[error-handler.js] Built-in error callback called');
						error.callback();
						debug('[error-handler.js] Built-in error callback finished');
					}

					var defaultHandler = errorHandler.library[errorPackage].handleError;
					if (defaultHandler) {
						try {
							defaultHandler = eval('(' + defaultHandler + ')');
							debug('[error-handler.js] Default handler called');
							defaultHandler(errorId, error);
							debug('[error-handler.js] Default handler finished');
						} catch (e) {}
					}
				});
			}
		} catch (e) {
			debug(e);
		}
	}
};

$(document).ready(function () {
	try {
		var error = getAnchorParam('error');
		if (error == null) return;
		var errorName = error.split('.'),
			errorPackage = errorName[0],
			errorId = errorName[1];
			
		errorHandler.handle(errorPackage, errorId);		
	} catch (e) {
		debug('[error-handler.js] ' + e);
	}
});