<?php

require_once dirname(__FILE__) . '/../classes/cupms/RatingTable.php';
require_once dirname(__FILE__) . '/../classes/cupms/League.php';
require_once dirname(__FILE__) . '/../classes/utils/ResponseCache.php';

require_once dirname(__FILE__) . '/../includes/mysql.php';

define("START_TIME", round(microtime(true), 4));

function stdoutlog($msg) {
	$current = round(microtime(true), 4);
	echo sprintf("%3.4f", ($current - START_TIME)), ': ', $msg;
	flush();
}

echo "<pre>";

mysql_qw('DELETE FROM `p_rating` WHERE `date`>=?', date('Y-m-d', strtotime("-5 day")));
stdoutlog("Database cleaned\n");

for ($i = -5; $i <= 5; $i++) {
	$date = date('Y-m-d', strtotime('now'));

	if ($i < 0) {
		$date = date('Y-m-d', strtotime("$i day"));
	} elseif ($i == 0) {
		$date = date('Y-m-d', strtotime('now'));
	} else {
		$date = date('Y-m-d', strtotime("+$i day"));
	}

	$ratingCache = new ResponseCache(
		'api/sport_rating/get_rating',
		array (
			'date' => $date
		)
	);
	$ratingCache->remove(true);

	$chartsCache = new ResponseCache(
		'api/charts/rating_all',
		array (
			'date' => $date
		)
	);
	$chartsCache->remove(true);

	foreach (League::getAll() as $league) {
		$leagueId = $league->getId();
		RatingTable::getInstanceByDate($leagueId, $date);
			// TODO write to log
		stdoutlog("league_id=$leagueId date=$date\n");
		flush();
	}
}

stdoutlog("\nSUCCESSFULLY FINISHED");

echo "</pre>";

?>
