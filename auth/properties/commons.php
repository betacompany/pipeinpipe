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
	$COMMON_AUTH_PROPERTIES['cookie_name'] = 'auth_token';
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
	$ps = mysql_real_escape_string($mysqli_link, $hash);
	$result = mysqli_query($mysqli_link, 'SELECT * FROM ' . COMMON_AUTH_USERS_TABLE . ' WHERE login=' . $lg . ' AND password=' . $ps);
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
	$result = mysqli_query($mysqli_link, 'SELECT uid FROM ' . COMMON_AUTH_TOKENS_TABLE . ' WHERE token=' . $tk);
	if (!mysqli_num_rows($result)) {
		return 0;
	}
	$row = mysqli_fetch_assoc($result);
	return $result['uid'];
}

function get_token() {
	return $_COOKIE[COMMON_AUTH_COOKIE_NAME];
}

function get_secret() {
	return COMMON_AUTH_SECRET;
}

function filter_chars($str) {
	return preg_replace("/[^a-zA-Z]/", "", $str);
}
