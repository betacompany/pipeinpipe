function ToolTip(options) {
	var defaultOptions = {
		orientation: 'left', // 'right', 'top', 'bottom'
		
		html: ''
	};
	
	$.extend(true, options, defaultOptions);
	
	return {

	}
}

var slideBlock = {
	getOpenedParts: function () {
		return getCookie("opened_parts").split(",");
	},

	addOpenedPart: function (part_id) {
		var op = getCookie("opened_parts");
		var n = (op == undefined || op == '');
		setCookie("opened_parts", n ? ""+part_id : getCookie("opened_parts") + "," + part_id, 365);
	},

	removeOpenedPart: function (part_id) {
		var result = '';
		var partIds = this.getOpenedParts();
		for (var i = 0; i < partIds.length; i++) {
			if (partIds[i] != part_id) {
				result += (result.length == 0) ? partIds[i] : ',' + partIds[i];
			}
		}

		setCookie("opened_parts", result, 365);
	},

	togglePart: function (part_name ) {
		if ($('#' + part_name + ' > .title').hasClass('opened')) {
			$('#' + part_name + ' > .body').slideUp();
			$('#' + part_name + ' > .title').removeClass('opened');
			slideBlock.removeOpenedPart('#' + part_name);
			return;
		}

		$('#' + part_name + ' > .body').slideDown();
		$('#' + part_name + ' > .title').addClass('opened');
		slideBlock.addOpenedPart('#' + part_name);
	}
}