function showError(hr, text) {
	alert(text);
	//console.debug(hr + ' ' + text);
}

function showErrorXML(xml) {
	alert($(xml).text());
}

function showErrorJSON(json) {
	alert(json);
}

function showErrorText(text) {
	alert(text);
}