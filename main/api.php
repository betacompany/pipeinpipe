<?php

require_once 'classes/utils/ResponseCache.php';
require_once 'classes/user/Auth.php';

define('EXECUTION_TIME', round(microtime(true) * 1000));
define('API_DEBUG_MODE', true);

$auth = new Auth();
$last_execution_time = $auth->sessionGet('LAST_EXECUTION_TIME');
$last_execution_query = $auth->sessionGet('LAST_EXECUTION_QUERY');

$auth->sessionPut('LAST_EXECUTION_TIME', EXECUTION_TIME);
$auth->sessionPut('LAST_EXECUTION_QUERY', $_SERVER['QUERY_STRING']);

if (
	isset($last_execution_time) &&
	isset($last_execution_query) &&
	(EXECUTION_TIME - $last_execution_time < 500) &&
	($last_execution_query == $_SERVER['QUERY_STRING'])
) {
	echo json (array (
		'status' => 'failed',
		'retry' => 'true',
		'reason' => 'too many equal queries per second'
	));
	exit(0);
}

if (
	isset($last_execution_time) &&
	isset($last_execution_query) &&
	(EXECUTION_TIME - $last_execution_time < 100)
) {
	echo json (array (
		'status' => 'failed',
		'reason' => 'too many queries per second'
	));
	exit(0);
}

if (
	isset($last_execution_time) &&
	isset($last_execution_query) &&
	(EXECUTION_TIME - $last_execution_time < 200)
) {
	echo json (array (
		'status' => 'failed',
		'retry' => 'true',
		'reason' => 'too many queries per second'
	));
	exit(0);
}

if (!isset($_REQUEST['handler'])) {
	echo json_encode(array(
		'status' => 'failed',
		'reason' => 'handler not specified'
	));

	exit(0);
}

$cache_store_policy = array();
if (!API_DEBUG_MODE) {
	$cache_store_policy['api/sport_rating/get_rating'] = 600;
	$cache_store_policy['api/sport_rating/get_movement'] = 600;
	$cache_store_policy['api/sport_rating/get_pmids'] = 600;
	$cache_store_policy['api/sport_rating/evaluate_coef'] = 600;
	$cache_store_policy['api/sport_player_comparator/compare'] = 600;
	$cache_store_policy['api/charts/rating_all'] = 3600 * 24;
	$cache_store_policy['api/photobg/get'] = 3600 * 24;
	if (!$auth->isAuth()) {
		$cache_store_policy['api/life/load_before'] = 60;
	}
} else {
	$cache_store_policy['api/sport_rating/get_rating'] = 0;
	$cache_store_policy['api/sport_rating/get_movement'] = 0;
	$cache_store_policy['api/sport_rating/get_pmids'] = 0;
	$cache_store_policy['api/sport_rating/evaluate_coef'] = 0;
	$cache_store_policy['api/sport_player_comparator/compare'] = 0;
	$cache_store_policy['api/charts/rating_all'] = 0;
	$cache_store_policy['api/error_library/load_library'] = 0;
	$cache_store_policy['api/photobg/get'] = 0;
	$cache_store_policy['api/life/load_before'] = 0;
}

$cache_key = 'api/' . $_REQUEST['handler'] . '/' . $_REQUEST['method'];
$cache = ResponseCache::getInstance($cache_key);
$cache->echoByPolitics($cache_store_policy);

$cache->start();

// Please, note that all catched exceptions are handled with exit(0)!
// Therefore exceptions won't be cached
// For example see ./procs/proc_sport_rating.php

switch ($_REQUEST['handler']) {
case 'sport_rating':
	include 'procs/proc_sport_rating.php';
	break;
case 'sport_player_comparator':
	include 'procs/proc_sport_player_comparator.php';
	break;
case 'charts':
	include 'procs/proc_charts.php';
	break;
case 'error_library':
	include 'procs/proc_error_library.php';
	break;
case 'photobg':
	include 'procs/proc_photobg.php';
	break;
case 'life':
	include 'procs/proc_life.php';
	break;
default:
	echo json(array (
		'status' => 'failed',
		'reason' => 'unknown handler'
	));
}

$cache->store();

?>
