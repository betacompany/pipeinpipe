<?php

define(
	'ALLOWED_DESCRIPTION_TAGS',
	'<a><b><u><s><i><tt><ul><ol><li><table><thead><tbody><tr><td><th><small><p><img>'
);

define(
	'ALLOWED_FORUM_TAGS',
	'<a><b><i><tt><ul><ol><li><small>'
);

function string_insert_br($str) {
	return str_replace("\n", "<br />", $str);
}

define('SECURITY_STRICT', 'strict');
define('SECURITY_DESCRIPTION', 'description');
define('SECURITY_FORUM', 'forum');
define('SECURITY_HTML', 'html');
define('SECURITY_JSON', 'json');

/**
 * mode:
 *		- 'strict' (default) - HTML is not allowed
 *		- 'desciption' - allowed some HTML-tags
 *		- 'html' - allowed all HTML-tags
 * @param string $str
 * @param string $mode 
 */
function string_process($str, $mode = 'strict') {
	// TODO script -> scri_pt, for example

	switch ($mode) {
	case SECURITY_STRICT:
		$result = trim($str);
		$result = strip_tags($result);
		$result = htmlspecialchars($result);
		//$result = string_insert_br($result);
		return $result;

	case SECURITY_DESCRIPTION:
		$result = trim($str);
		$result = strip_tags($result, ALLOWED_DESCRIPTION_TAGS);
		//$result = htmlspecialchars($result);
		//$result = string_insert_br($result);
		return $result;

	case SECURITY_FORUM:
		$result = trim($str);
		$result = strip_tags($result, ALLOWED_FORUM_TAGS);
		$result = string_insert_br($result);
		$result = string_replace_script($result);
		return $result;


	case SECURITY_HTML:
		$result = trim($str);
		$result = htmlspecialchars($str);
		return $result;

	case SECURITY_JSON:
		$result = htmlspecialchars($str);
		return $result;

	default:
		return $str;
	}
}

function replace_callback($a) {
	$result = array();
	foreach ($a as $str) {
		$tmp = str_replace("p", "&#0112;", $str);
		$tmp = str_replace("P", "&#0090;", $tmp);
		$result[] = $tmp;
	}
	return $result;
}

function string_replace_script($text) {
	// TODO another regexp!
	return preg_replace_callback("/script/i", "replace_callback", $text);
}
?>