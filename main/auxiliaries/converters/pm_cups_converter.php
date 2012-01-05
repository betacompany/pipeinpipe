<?php

/**
 * @author Artyom Grigoriev
 * Converts results of cups into new table
 */

require_once '../../includes/mysql.php';
require_once 'converter_library.php';

mysql_qw('TRUNCATE TABLE `p_man_cup_result`');
echo "`p_man_cup_result` truncated.\n";

$req = mysql_qw('
	SELECT * FROM (
		`pipe_men_cups` LEFT JOIN 
		(SELECT `id`, `date` FROM `pipe_cups`) `result`
		ON `pipe_men_cups`.`cupid`=`result`.`id`
	) WHERE 1=1
');

echo "<pre>Convertion started\n";
flush();

while ($r = mysql_fetch_assoc($req)) {
	$d = 0; $m = 0; $y = 0;
	list($d, $m, $y) = explode('-', $r['date'], 3);
	$d = (strlen($d) == 1) ? '0'.$d : $d;
	$m = (strlen($m) == 1) ? '0'.$m : $m;
	$date = "$y-$m-$d";

	mysql_qw('INSERT INTO `p_man_cup_result` SET `pmid`=?, `cup_id`=?, `date`=?, `points`=?, `place`=?',
				$r['uid'], $r['cupid'], $date, $r['wpr'], $r['place']
			);

	echo 'results: cupid=' . $r['cupid'] . ', date=' . $date . ', pmid=' . $r['uid'] . "\n";
	flush();
}

echo 'Convertion finished in ';
echo save_results() . 'ms</pre>';

?>
