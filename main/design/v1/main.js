// resize
function setWidth(w) {
	var margin = "0 " + w / 8 + "px";

	$('body').width(w / 0.8);
	$('#header').width(w).css("margin", margin);
	$('#body').width(w).css("margin", margin);
	$('#footer').width(w).css("margin", margin);

	$('#menu__forum').width(
		w	- $('#menu__index').width()
		- $('#menu__sport').width()
		- $('#menu__life').width()
		- $('#menu__media').width()
	);
}

function resize() {
	setWidth($(document).width() < 1000 ? 800 : $(document).width() * 0.8);
}

var menuItems = ["index", "sport", "life", "media", "forum"];

// menu
function menu() {
	// TODO implement this function
	// fading menu item and appearing submenu items
}

$(document).ready(
	function () {
		window.onresize = resize;
		resize();
		menu();
	}
);