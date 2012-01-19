<?php

require_once dirname(__FILE__) . '/../classes/user/Auth.php';
require_once dirname(__FILE__) . '/../includes/config-local.php';
require_once dirname(__FILE__) . '/../includes/common.php';

$auth = new Auth();
if (!issetParam('decision')) {
	echo 'Error: no decision';
	exit(0);
}

switch (param('decision')) {
case 'main':
	$auth->cookiePut(Auth::KEY_USE_MOBILE, 0);
	$auth->sessionPut(Auth::KEY_USE_MOBILE_SESSION, 0);
	$url = issetParam('url') ? urldecode(param('url')) : $_SERVER['HTTP_REFERER'];
	Header('Location: ' . $url);
	exit(0);
case 'mobile':
	$auth->cookieGet(Auth::KEY_USE_MOBILE, 1);
	$auth->sessionPut(Auth::KEY_USE_MOBILE_SESSION, 1);
	$url = issetParam('url') ? urldecode(param('url')) : 'http://' . MOBILE_SITE_URL;
	Header('Location: ' . $url);
	exit(0);
}

?>