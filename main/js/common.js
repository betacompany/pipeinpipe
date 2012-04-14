var CONFIG = {
	DEBUG: true
};

var COLOR = {
	FIRST: '#007ca7',			// blue
	FIRST_LIGHT: '#c7dce3',		// light blue
	SECOND: '#8fbc13',			// green
	SECOND_LIGHT: '#b8d46c',	// light green
	THIRD: '#8fbc13',			// violet
	THIRD_LIGHT: '#c4a1bf'		// light violet
};

var common = {
	cookies: {},
	cookiesInitialized: false,
	params: {},
	paramsInitialized: false,

	months : [
		{name: 'Январь', name_gen: 'января', name_short: 'янв', length: 31, length_leap: 31},
		{name: 'Февраль', name_gen: 'февраля', name_short: 'фев', length: 28, length_leap: 29},
		{name: 'Март', name_gen: 'марта', name_short: 'мар', length: 31, length_leap: 31},
		{name: 'Апрель', name_gen: 'апреля', name_short: 'апр', length: 30, length_leap: 30},
		{name: 'Май', name_gen: 'мая', name_short: 'май', length: 31, length_leap: 31},
		{name: 'Июнь', name_gen: 'июня', name_short: 'июн', length: 30, length_leap: 30},
		{name: 'Июль', name_gen: 'июля', name_short: 'июл', length: 31, length_leap: 31},
		{name: 'Август', name_gen: 'августа', name_short: 'авг', length: 31, length_leap: 31},
		{name: 'Сентябрь', name_gen: 'сентября', name_short: 'сен', length: 30, length_leap: 30},
		{name: 'Октябрь', name_gen: 'октября', name_short: 'окт', length: 31, length_leap: 31},
		{name: 'Ноябрь', name_gen: 'ноября', name_short: 'ноя', length: 30, length_leap: 30},
		{name: 'Декабрь', name_gen: 'декабря', name_short: 'дек', length: 31, length_leap: 31}
	],

	monthDaysCount: function (date) {
		var y = date.getFullYear(),
			isLeap = (y % 4 == 0 && y % 100 != 0 || y % 400 == 0),
			mon = this.months[date.getMonth()];
		return isLeap ? mon.length_leap : mon.length;
	},

	monthName: function (date) {
		return this.months[date.getMonth()].name;
	}
};

function ce(tag) {
	return document.createElement(tag);
}

function ge(id) {
	return document.getElementById(id);
}

function gtm() {
	return (new Date()).getMilliseconds();
}

function tm() {
	return (new Date()).getTime();
}

common.startTime = tm();

function initCookies() {
	common.cookies = {};
	var cookiesArray = document.cookie.split(';');
	var regexp = /^[\s]*([^\s]+?)$/i;
	for (var i = 0; i < cookiesArray.length; i++) {
		var c = cookiesArray[i].split("=");
		if (c.length == 2) {
			common.cookies[c[0].match(regexp)[1]] = unescape(c[1].match(regexp) ? c[1].match(regexp)[1] : '');
		}
	}
}

function getCookie(name) {
	if(!common.cookiesInitialized) initCookies();
	return common.cookies[name] != undefined ? common.cookies[name] : null;
}

function setCookie(name, value, days) {
	if(!common.cookiesInitialized) initCookies();
	common.cookies[name] = value;
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
		var expires = "; expires=" + date.toGMTString();
	} else var expires = "";
	var domain = location.host.match(/[^.]+\.[^.]+$/);
	document.cookie = name + "=" + escape(value) + expires + "; path=/" + (domain ? '; domain=.' + domain : '');
}

function debug(text) {
	try {
		var time = (tm() - common.startTime) / 1000 + '',
			splitted = time.split('.', 2),
			resultTime = ''
			;

		for (var i = 0; i < 5 - splitted[0].length; ++i) {
			resultTime += '0';
		}
		resultTime += time;
		for (var j = 0; j < 3 - splitted[1].length; ++j) {
			resultTime += '0';
		}

        if (CONFIG.DEBUG) {
            if (typeof text === 'string') {
                console && console.debug(''+resultTime+': ' + text);
            } else {
                console && console.debug(''+resultTime+': ');
                console && console.debug(text);
            }
        }
	} catch (e) {}
}

function loadScript(name) {
	var s = ce('script');
	s.src = '/js/' + name + '.js';
	document.getElementsByTagName('head')[0].appendChild(s);
}

var params = {};
var paramsInitialized = false;

function initAnchorParams() {
	common.params = {};
	var url = document.URL,
		anchorIndex = url.indexOf('#');

	if (anchorIndex >= 0) {
		var anchor = url.split('#')[1],
			pairs = anchor.split('&');
		for (var i = 0; i < pairs.length; i++) {
			var pair = pairs[i].split('=');
			common.params[pair[0]] = unescape(pair[1]);
			// TODO change encoding?
		}
	}

	paramsInitialized = true;
}

function getAnchorParam(key) {
	if (!common.paramsInitialized) initAnchorParams();
	return common.params[key] == undefined ? null : common.params[key];
}

function setAnchorParam(key, value) {
	if (!common.paramsInitialized) initAnchorParams();
	common.params[key] = value;
	setAnchorParams();
}

function getAnchorParams() {
	if (!common.paramsInitialized) initAnchorParams();
	return common.params;
}

function setAnchorParams() {
	var anchor = '';
	for (var key in common.params) {
		if (key) {
			anchor += '&' + key + '=' + common.params[key];
		}
	}
	if (anchor.length > 0) {
		anchor = anchor.substr(1, anchor.length - 1);
	}
	window.location = document.URL.split('#')[0] + '#' + anchor;
}

function getKeys(obj) {
	var keys = new Array();
	for (var key in obj) keys.push(key);
	return keys;
}

function getTrueKeys(obj) {
	var keys = new Array();
	for (var key in obj)
		if (obj[key]) keys.push(key);
	return keys;
}

function loading(domElement, enable, x, y, inline) {
	if (!(domElement instanceof jQuery))
		domElement = $(domElement);

	if (enable) {
		var w = domElement.innerWidth(),
			h = domElement.innerHeight(),
			loadingBar = $('<div/>').addClass('loading');

		if (inline) {
			loadingBar
			.css({
				top: (y == undefined) ? (h - 20) / 2 : y,
				left: (x == undefined) ? (w - 100) / 2 : x
			})
			.appendTo(domElement);
		} else {
			var offset = domElement.offset();
			loadingBar
			.css({
				top: offset.top + ((y == undefined) ? (h - 20) / 2 : y),
				left: offset.left + ((x == undefined) ? (w - 100) / 2 : x)
			})
			.appendTo($('body'));
		}

		return loadingBar;
	} else {
		return $('.loading').remove();
	}
}

function datecmp(d1, m1, y1, d2, m2, y2) {
	if (!y1 || !y2) return 0;
	if (y1 != y2) return y1 < y2 ? -1 : 1;
	if (!m1 || !m2) return 0;
	if (m1 != m2) return m1 < m2 ? -1 : 1;
	if (!d1 || !d2) return 0;
	if (d1 != d2) return d1 < d2 ? -1 : 1;
	return 0;
}

function myParseInt(s) {
	if (parseInt(s) === s) return s;
	var str = "0", i = 0;
	while (i < s.length && s[i++] == '0') {}
	if (i <= s.length) str = s.substr(i - 1);
	return parseInt(str);
}

/**
 * @link http://habrahabr.ru/blogs/webdev/18080/
 */
function preventSelection(element){
	var preventSelection = false;

	function addHandler(element, event, handler){
		if (element.attachEvent)
			element.attachEvent('on' + event, handler);
		else
		if (element.addEventListener)
			element.addEventListener(event, handler, false);
	}
	function removeSelection(){
		if (window.getSelection) {
			window.getSelection().removeAllRanges();
		}
		else if (document.selection && document.selection.clear)
			document.selection.clear();
	}
	function killCtrlA(event){
		var event = event || window.event;
		var sender = event.target || event.srcElement;

		if (sender.tagName.match(/INPUT|TEXTAREA/i))
			return;

		var key = event.keyCode || event.which;
		if (event.ctrlKey && key == 'A'.charCodeAt(0))  // 'A'.charCodeAt(0) можно заменить на 65
		{
			removeSelection();

			if (event.preventDefault)
				event.preventDefault();
			else
				event.returnValue = false;
		}
	}

	// не даем выделять текст мышкой
	addHandler(element, 'mousemove', function(){
		if(preventSelection)
			removeSelection();
	});
	addHandler(element, 'mousedown', function(event){
		var event = event || window.event;
		var sender = event.target || event.srcElement;
		preventSelection = !sender.tagName.match(/INPUT|TEXTAREA/i);
	});

	addHandler(element, 'mouseup', function(){
		if (preventSelection)
			removeSelection();
		preventSelection = false;
	});

	addHandler(element, 'keydown', killCtrlA);
	addHandler(element, 'keyup', killCtrlA);
}