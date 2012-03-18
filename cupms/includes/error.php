<?php

require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/../../main/classes/utils/Logger.php';

$LOG = new Logger(Logger::CUPMS_LOG);

/**
 * echo error response
 * @param int $errorCode
 * @param string $errorMsg
 * @param array $params
 */
function echo_error($errorCode, $errorMsg, $params = array()) {
	echo '<error>';
	echo '<error_code>'.$errorCode.'</error_code>';
	echo '<error_msg>'.$errorMsg.'</error_msg>';
	echo '<request>';
	foreach ($params as $key => $value) {
		echo '<parameter>';
		echo '<key>'.$key.'</key>';
		echo '<value>'.$value.'</value>';
		echo '</parameter>';
	}
	echo '</request>';
	echo '</error>';
}



/**
 * echo error response
 * @param int $errorCode
 * @param string $errorMsg
 * @param array $params
 */
function json_echo_error($errorCode, $errorMsg, $params = array()) {
	echo json_encode(array(
		"status" => "failed",
		"code" => $errorCode,
		"msg" => $errorMsg,
		"request" => $params
	));
}

// Global Errors: 0 - 99
define('ERROR', 0);
define('ERROR_NOMETHOD', 1);
define('ERROR_NOACCESS', 2);
define('ERROR_DB', 3);
define('ERROR_WRONGPARAMS', 4);

// Competition Errors: 100 - 199
define('ERROR_NOCOMPETITION', 100);
define('ERROR_NOCUP', 101);
define('ERROR_IRREGULARCUP', 102);
define('ERROR_INCORRECT_STATUS', 103);
define('ERROR_NO_MAIN_CUP', 104);
define('ERROR_NULL_DATE', 105);
define('ERROR_INVALID_DATE', 106);
define('ERROR_REGULARCUP', 107);

// League Errors: 200 - 299
define('ERROR_NOLEAGUE', 200);
define('ERROR_LEAGUE_CREATION', 201);
define('ERROR_LEAGUE_NAME_EXISTS', 202);

// Game Errors: 300 - 399
define('ERROR_NOGAME', 300);

//Player Errors: 400 - 499
define('ERROR_NOPLAYER', 400);

//ResultTable Errors: 500 -5 99
define('ERROR_RESULT_TABLE', 500);

function error_message($code) {
	$errorList = array(
		ERROR => 'Unknown error',
		ERROR_NOMETHOD => 'Method not specified',
		ERROR_NOACCESS => 'Access denied',
		ERROR_DB => 'Error while db insertion',
		ERROR_WRONGPARAMS => 'Wrong parameters passed to handler',
		ERROR_NOCOMPETITION => 'There is no such competition',
		ERROR_NOCUP => 'The cup is not set',
		ERROR_NO_MAIN_CUP => 'There is no main cup',
		ERROR_IRREGULARCUP => 'This cup is play-off',
		ERROR_REGULARCUP => 'This cup is not a play-off cup',
		ERROR_NOLEAGUE => 'There is no such league',
		ERROR_LEAGUE_CREATION => 'Error while creating new league',
		ERROR_LEAGUE_NAME_EXISTS => 'There is a league with such name already',
		ERROR_NOGAME => 'There is no such game',
		ERROR_NOPLAYER => 'There is no player with such id',
		ERROR_NULL_DATE => 'The date of the given competition is not set',
		ERROR_INVALID_DATE => 'Fornat of the given date is invalid. Usage: YYYY-MM-DD',
		ERROR_RESULT_TABLE => 'There was a problem with recalculating the results!'
	);
	
	return $errorList[$code];
}

function echo_error_code($code, $msg = "") {
	echo_error($code, error_message($code) . (empty($msg) ? "" : ". " . $msg), $_REQUEST);
}


function json_echo_error_code($code, $msg = "") {
	json_echo_error($code, error_message($code) . (empty($msg) ? "" : ". " . $msg), $_REQUEST);
}

?>
