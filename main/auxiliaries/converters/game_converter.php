<?php

require_once '../../includes/mysql.php';
require_once 'converter_library.php';

function convert_stage($old) {
	switch ($old) {
	case 'F':
		return 1;

	case 'SF1':
	case 'SF2':
		return 2;

	case 'BF':
		return 3;

	case 'QF1':
	case 'QF2':
	case 'QF3':
	case 'QF4':
		return 4;

	case '1/8':
		return 8;

	case '1/16':
		return 16;

	default:
		return 0;
	}
}

function convert_prev($old) {
	return $old < 0 ? 0 : $old;
}

function convert_date($old) {
	return date('Y-m-d H:i:s', strtotime($old));
}

function convert_is_tech($old) {
	switch ($old) {
	case 'Фаталити №1':
	case 'Фаталити №2':
	case 'Фаталити №3':
		return 'f';

	case 'техническое поражение':
	case 'Техническое поражение':
	case 'техническая ничья':
		return '1';

	default:
		return '0';
	}
}

echo "<pre>Convertion started\n";
flush();

mysql_qw('TRUNCATE TABLE `p_game`');
echo "`p_game` truncated\n";
flush();

$req = mysql_qw('SELECT * FROM `pipe_games` WHERE 1=1 ORDER BY `id`');
while ($g = mysql_fetch_assoc($req)) {
	mysql_qw('INSERT INTO `p_game` SET	`id`=?,
										`cup_id`=?,
										`stage`=?,
										`tour`=?,
										`pmid1`=?,
										`pmid2`=?,
										`score1`=?,
										`score2`=?,
										`prev_game_id1`=?,
										`prev_game_id2`=?,
										`time`=?,
										`is_tech`=?',
			$g['id'], $g['cupid'], convert_stage($g['comment']), $g['tour'],
			$g['player1'], $g['player2'], $g['score1'], $g['score2'],
			convert_prev($g['prev1']), convert_prev($g['prev2']), convert_date($g['date']),
			convert_is_tech($g['text']));

	echo "game id=".$g['id']." handled\n";
	flush();
}

echo "Convertion finished in ";
echo save_results() . "ms</pre>";

?>
