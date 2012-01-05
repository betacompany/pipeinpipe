<?php

/**
 * @author Artyom Grigoriev
 * 
 */

require_once '../../includes/mysql.php';
require_once 'converter_library.php';

function convert_type($old) {
	$type = '';
	switch ($old) {
	case 0:
	case 2:
		$type = 'one-lap';
		break;
	case 1:
	case 3:
		$type = 'two-laps';
		break;
	case 4:
		$type = 'playoff';
		break;
	default:
		$type = 'undefined';
	}

	return $type;
}

function convert_status($old) {
	$status = '';
	switch ($old) {
	case -1:
		$status = 'before';
		break;
	case 0:
	case 1:
	case 2:
		$status = 'running';
		break;
	case 3:
		$status = 'finished';
		break;
	default:
		$status = 'before';
	}

	return $status;
}

echo "<pre>Convertion started\n";
flush();

mysql_qw('TRUNCATE TABLE `p_competition`');
echo "`p_competition` truncated.\n";
flush();

mysql_qw('TRUNCATE TABLE `p_cup`');
echo "`p_cup` truncated.\n";
flush();

$req = mysql_qw('SELECT * FROM `pipe_cups` WHERE `child_for`=0 ORDER BY `id` ASC');
while ($comp = mysql_fetch_assoc($req)) {
	$date = date('Y-m-d', strtotime($comp['date']));

	mysql_qw('
		INSERT INTO `p_competition` SET
		`league_id`=?, `tournament_id`=?, `name`=?, `season`=?, `date`=?, `coef`=?, `description`=?
	',
		1, 0, $comp['name'], '', $date, $comp['wprK'], $comp['description']
	);
	
	$id = mysql_insert_id();
	echo "competition with id=$id inserted\n";
	flush();

	$type = convert_type($comp['type']);
	$status = convert_status($comp['status']);

	mysql_qw('
		INSERT INTO `p_cup` SET
		`id`=?, `competition_id`=?, `parent_cup_id`=?, `name`=?, `type`=?, `status`=?
	',
		$comp['id'], $id, 0, '', $type, $status
	);

	echo "\tcup with id=".$comp['id']." inserted\n";
	flush();

	$req_ch = mysql_qw('SELECT * FROM `pipe_cups` WHERE `child_for`=? ORDER BY `id` ASC', $comp['id']);
	while ($cup = mysql_fetch_assoc($req_ch)) {
		mysql_qw('
			INSERT INTO `p_cup` SET
			`id`=?, `competition_id`=?, `parent_cup_id`=?, `name`=?, `type`=?, `status`=?
		',
			$cup['id'], $id, $comp['id'], $cup['name'], convert_type($cup['type']), convert_status($cup['status'])
		);

		echo "\t\tcup with id=".$cup['id']." inserted\n";
		flush();
	}
}

echo "Convertion finished in ";
echo save_results() . "ms</pre>";

?>
