<?php

include_once dirname(__FILE__) . '/global-properties.php';
include_once dirname(__FILE__) . '/local-properties.php';

global $COMMON_AUTH_PROPERTIES;

if (!isset($COMMON_AUTH_PROPERTIES['database_host']) || !isset($COMMON_AUTH_PROPERTIES['database_name'])) {
	die("No database defined");
}

if (!isset($COMMON_AUTH_PROPERTIES['database_user']) || !isset($COMMON_AUTH_PROPERTIES['database_password'])) {
	die("No database user/password defined!");
}

if (!isset($COMMON_AUTH_PROPERTIES['users_table'])) {
	die("No users table name defined!");
}

if (!isset($COMMON_AUTH_PROPERTIES['tokens_table'])) {
	die("No tokens table defined");
}

if (!isset($COMMON_AUTH_PROPERTIES['cookie_name'])) {
	$COMMON_AUTH_PROPERTIES['cookie_name'] = 'at';
}

foreach ($COMMON_AUTH_PROPERTIES as $key => $value) {
	define('COMMON_AUTH_' . strtoupper($key), $value);
}

$mysqli_link = mysqli_init();
mysqli_real_connect(
	$mysqli_link,
	COMMON_AUTH_DATABASE_HOST,
	COMMON_AUTH_DATABASE_USER,
	COMMON_AUTH_DATABASE_PASSWORD,
	COMMON_AUTH_DATABASE_NAME
) or die("Could not connect to database");

/**
 * @param $login
 * @param $hash
 * @return int
 */
function select_uid($login, $hash) {
	global $mysqli_link;
	$lg = mysqli_real_escape_string($mysqli_link, $login);
	$ps = mysqli_real_escape_string($mysqli_link, $hash);
	$query = 'SELECT * FROM ' . COMMON_AUTH_USERS_TABLE . ' WHERE `login`=\'' . $lg . '\' AND `hash`=\'' . $ps . '\'';
	$result = mysqli_query($mysqli_link, $query);
	if (!mysqli_num_rows($result)) {
		return 0;
	}
	$row = mysqli_fetch_assoc($result);
	return $row['id'];
}

/**
 * @param $token
 * @return int
 */
function get_uid($token) {
	global $mysqli_link;
	$tk = mysqli_real_escape_string($mysqli_link, $token);
	$result = mysqli_query($mysqli_link, 'SELECT uid FROM ' . COMMON_AUTH_TOKENS_TABLE . ' WHERE token=\'' . $tk . '\'');
	if (!mysqli_num_rows($result)) {
		return 0;
	}
	$row = mysqli_fetch_assoc($result);
	return $row['uid'];
}

function get_login_password($uid) {
	global $mysqli_link;
	$result = mysqli_query($mysqli_link, 'SELECT `login`, `hash` FROM ' . COMMON_AUTH_USERS_TABLE . ' WHERE `id`=' . intval($uid));
	if (!mysqli_num_rows($result)) {
		return null;
	}
	return mysqli_fetch_assoc($result);
}

function get_token() {
	return $_COOKIE[COMMON_AUTH_COOKIE_NAME];
}

function set_token($uid, $token, $is_session = false) {
	$_COOKIE[COMMON_AUTH_COOKIE_NAME] = $token;
	setcookie(COMMON_AUTH_COOKIE_NAME, $token, $is_session ? null : time() + COMMON_AUTH_EXPIRING_PERIOD, "/", COMMON_AUTH_DOMAIN, false, true);
	if ($is_session) {
		setcookie(COMMON_AUTH_COOKIE_NAME . "_s", "y", null, "/", COMMON_AUTH_DOMAIN, false, true);
	} else {
		setcookie(COMMON_AUTH_COOKIE_NAME . "_s", "n", 69, "/", COMMON_AUTH_DOMAIN, false, true);
	}

	global $mysqli_link;
	$ip = mysqli_real_escape_string($mysqli_link, $_SERVER['REMOTE_ADDR']);
	mysqli_query($mysqli_link, 'INSERT IGNORE INTO ' . COMMON_AUTH_TOKENS_TABLE .
		" SET uid='$uid', token='$token', ip_address='$ip'");
}

function delete_token($token) {
	setcookie(COMMON_AUTH_COOKIE_NAME, $token, 69, "/", COMMON_AUTH_DOMAIN, false, true);
	setcookie(COMMON_AUTH_COOKIE_NAME . "_s", $token, 69, "/", COMMON_AUTH_DOMAIN, false, true);

	global $mysqli_link;
	$tk = mysqli_real_escape_string($mysqli_link, $token);
	mysqli_query($mysqli_link, 'DELETE FROM ' . COMMON_AUTH_TOKENS_TABLE . ' WHERE token=\'' . $tk . '\'');
}

function get_secret() {
	return COMMON_AUTH_SECRET;
}

function is_session_only() {
	return isset($_COOKIE[COMMON_AUTH_COOKIE_NAME . "_s"]);
}

function filter_chars($str) {
	return preg_replace("/[^a-zA-Z]/", "", $str);
}
