<?php

require_once '../../includes/mysql.php';
require_once 'converter_library.php';

$btc_head = mysql_result(mysql_qw('SELECT `parent_cup_id` FROM `p_cup` WHERE `id`=1'), 0, 0);

echo $btc_head . ' ';

mysql_qw('DELETE FROM `p_man_cup_result` WHERE `cup_id` IN (1, '.$btc_head.') AND `pmid` IN (11, 3, 2, 8)');

$reqs = array (
	'INSERT INTO `p_man_cup_result` SET  `points`=605.55555555556, `pmid`=11, `cup_id`=1, `date`=\'2008-11-01\', `place`=4',
	'INSERT INTO `p_man_cup_result` SET  `points`=611.11111111111, `pmid`=3, `cup_id`=1, `date`=\'2008-11-01\', `place`=3',
	'INSERT INTO `p_man_cup_result` SET  `points`=744.44444444444, `pmid`=2, `cup_id`=1, `date`=\'2008-11-01\', `place`=2',
	'INSERT INTO `p_man_cup_result` SET  `points`=877.77777777778, `pmid`=8, `cup_id`=1, `date`=\'2008-11-01\', `place`=1',
	'INSERT INTO `p_man_cup_result` SET  `points`=50, `pmid`=11, `date`=\'2008-11-01\', `place`=4, `cup_id`='.$btc_head,
	'INSERT INTO `p_man_cup_result` SET  `points`=100, `pmid`=3, `date`=\'2008-11-01\', `place`=3, `cup_id`='.$btc_head,
	'INSERT INTO `p_man_cup_result` SET  `points`=200, `pmid`=2, `date`=\'2008-11-01\', `place`=1, `cup_id`='.$btc_head,
	'INSERT INTO `p_man_cup_result` SET  `points`=150, `pmid`=8, `date`=\'2008-11-01\', `place`=2, `cup_id`='.$btc_head
);

foreach ($reqs as $req) {
	mysql_qw($req);
}

echo 'DONE in ' . save_results() . 'ms';

?>
