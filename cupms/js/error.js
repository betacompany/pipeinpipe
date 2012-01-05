function showError(str) {
    $('#error_panel .text').text(str);
    $('#error_panel').slideToggle("slow")
                     .delay("5000")
                     .slideToggle("slow");
}

function showErrorXML(xml) {
	var errorCode = $(xml).find('error_code').text();
	var errorMessage = $(xml).find('error_msg').text();
	showError('' + errorMessage + '!! error code: ' + errorCode);
}

function showErrorJSON(json) {
	var errorCode = json.code;
	var errorMessage = json.msg;
	showError(errorMessage + '!! error code: ' + errorCode);
}