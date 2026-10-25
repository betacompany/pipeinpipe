<?php

require_once dirname(__FILE__) . '/config-local.php';
require_once dirname(__FILE__) . '/../classes/utils/Logger.php';

/*
 * MySQL access through mysqli (ext/mysql is gone since PHP 7).
 * The db_* functions return what the ext/mysql functions they replaced did,
 * e.g. false rather than null, and accept a failed query's false instead of
 * throwing a TypeError on PHP 8.
 */

if (MYSQL_DEBUG_MODE) {
	$MYSQL_LOGGER = new Logger(Logger::QUERY_LOG);
}

/**
 * @return mysqli the connection, opened on the first call
 */
function db_link() {
	static $link = null;
	if ($link === null) {
		// Since PHP 8.1 mysqli throws on every error by default; this code checks return values
		mysqli_report(MYSQLI_REPORT_OFF);

		$link = mysqli_connect('p:' . DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE)
				or die("COULD NOT CONNECT: " . mysqli_connect_error());

		if (MYSQL_FIRST_QUERY) {
			db_qw(MYSQL_FIRST_QUERY);
		}
	}
	return $link;
}

/**
 * Runs a query with "?" placeholders, e.g. db_qw('SELECT * FROM t WHERE id=?', $id).
 * Without arguments returns the number of queries run so far.
 * @return mysqli_result|bool
 */
function db_qw() {
	$args = func_get_args();

	static $counter = 0;
	if (empty($args)) return $counter;
	$counter++;

	$query = call_user_func_array("db_make_qw", $args);

	if (MYSQL_DEBUG_MODE) {
		global $MYSQL_LOGGER;
		@$MYSQL_LOGGER->sql($query);
	}

	$result = mysqli_query(db_link(), $query);
	if ($result === false) {
		// Callers only see false, so log why
		error_log("db_qw: " . mysqli_error(db_link()) . " in: " . substr($query, 0, 500));
	}
	return $result;
}

function db_make_qw() {
	$args = func_get_args();
	$tmpl = & $args[0];
	$tmpl = str_replace("%", "%%", $tmpl);
	$tmpl = str_replace("?", "%s", $tmpl);
	foreach ($args as $i => $v) {
		if (!$i)
			continue;
		if (is_int($v))
			continue;
		$args[$i] = "'" . db_escape($v) . "'";
	}
	for ($i = $c = count($args) - 1; $i < $c + 20; $i++)
		$args[$i + 1] = "UNKNOWN_PLACEHOLDER_$i";
	return call_user_func_array("sprintf", $args);
}

function db_query($query) {
	return mysqli_query(db_link(), $query);
}

function db_fetch_assoc($result) {
	if (!$result) return false;
	$row = mysqli_fetch_assoc($result);
	return $row === null ? false : $row;
}

function db_num_rows($result) {
	return $result ? mysqli_num_rows($result) : false;
}

/**
 * Replaces mysql_result(): one value by row number and column number or name.
 */
function db_result($result, $row = 0, $field = 0) {
	if (!$result || $row >= mysqli_num_rows($result) || !mysqli_data_seek($result, $row)) return false;
	$data = mysqli_fetch_array($result, MYSQLI_BOTH);
	return ($data && array_key_exists($field, $data)) ? $data[$field] : false;
}

function db_insert_id() {
	return mysqli_insert_id(db_link());
}

function db_affected_rows() {
	return mysqli_affected_rows(db_link());
}

function db_escape($string) {
	return mysqli_real_escape_string(db_link(), (string) $string);
}

function db_error() {
	return mysqli_error(db_link());
}

db_link();
?>
