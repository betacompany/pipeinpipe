<?php

require_once dirname(__FILE__) . '/../../includes/mysql.php';

define ('CONVERTER_START_TIME', microtime(true));

function save_results() {
	$ms = (microtime(true) - CONVERTER_START_TIME) * 1000;
	$ex = explode('/', $_SERVER['SCRIPT_NAME']);
	$script = $ex[count($ex) - 1];

	mysql_qw('UPDATE `p_converter` SET `time`=? WHERE `script`=?',
			$ms, $script);
	if (mysql_affected_rows () == 0) {
		mysql_qw('INSERT INTO `p_converter` SET `time`=? ,`script`=?',
			$ms, $script);
	}

	return $ms;
}

?>
