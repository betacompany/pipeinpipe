<?php

require_once dirname(__FILE__) . '/../../classes/cupms/RatingTable.php';
require_once dirname(__FILE__).'/../../includes/mysql.php';
require_once dirname(__FILE__).'/../../includes/common.php';

function next_date($date) {
	return date("Y-m-d", strtotime($date . " +1 day"));
}

define ("END_DATE", date("Y-m-d"));
define ("SUM_TIME", ini_get("max_execution_time"));

echo "END_DATE=".END_DATE." SUM_TIME=".SUM_TIME."<br/>";

$cur_date = issetParam('start_date') ? param('start_date') : "2009-09-28";
$avg_time = 0;
$sum_time = 0;
$count = 0;

echo "Starting<br/>";

while ($cur_date <= END_DATE) {
	$start_time = microtime(true);

	$req = mysql_qw('SELECT COUNT(DISTINCT(`league_id`)) FROM `p_rating` WHERE `date`=?', $cur_date);
	if (!$req || mysql_result($req, 0, 0) == 0) {
		foreach (League::getAllIds() as $league_id) {
			RatingTable::getInstanceByDate($league_id, $cur_date);
			echo "$sum_time: league_id=$league_id, date=$cur_date added<br/>";
			flush();
		}
	}

	$time = microtime(true) - $start_time;
	echo "time=$time, date=$cur_date<br/>";
	flush();

	$sum_time += $time;
	$avg_time = $avg_time * $count + $time;
	$count++;
	$avg_time = $avg_time / $count;

	if (SUM_TIME - $sum_time < 3 * $avg_time) {
		break;
	}

	$cur_date = next_date($cur_date);
}

echo "Finished on $cur_date<br/>";
echo "<a href=\"?start_date=$cur_date\">Continue...</a>"

?>