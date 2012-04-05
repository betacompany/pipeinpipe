<?php

require_once dirname(__FILE__) . '/config-local.php';
require_once dirname(__FILE__) . '/../classes/utils/Logger.php';

if (MYSQL_DEBUG_MODE) {
	$MYSQL_LOGGER = new Logger(Logger::QUERY_LOG);
}

function mysql_qw() {
	$args = func_get_args();

	static $counter = 0;
	if (empty($args)) return $counter;
	$counter++;

	$conn = null;
	if (is_resource($args[0]))
		$conn = array_shift($args);
	$query = call_user_func_array("mysql_make_qw", $args);

	if (MYSQL_DEBUG_MODE) {
		global $MYSQL_LOGGER;
		$MYSQL_LOGGER->sql($query);
	}

	return ($conn !== null) ? mysql_query($query, $conn) : mysql_query($query);
}

function mysql_make_qw() {
	$args = func_get_args();
	$tmpl = & $args[0];
	$tmpl = str_replace("%", "%%", $tmpl);
	$tmpl = str_replace("?", "%s", $tmpl);
	foreach ($args as $i => $v) {
		if (!$i)
			continue;
		if (is_int($v))
			continue;
		$args[$i] = "'" . mysql_real_escape_string($v) . "'";
	}
	for ($i = $c = count($args) - 1; $i < $c + 20; $i++)
		$args[$i + 1] = "UNKNOWN_PLACEHOLDER_$i";
	return call_user_func_array("sprintf", $args);
}

$user = DB_USERNAME;
$pass = DB_PASSWORD;
$db = DB_DATABASE;

mysql_pconnect("localhost", $user, $pass)
		or die("COULD NOT CONNECT: " . mysql_error());

mysql_select_db($db)
		or die("COULD NOT SELECT DATABASE: " . mysql_error());

if (MYSQL_FIRST_QUERY) {
	mysql_qw(MYSQL_FIRST_QUERY);
}
?>
