<?php

require_once '../../includes/mysql.php';
require_once 'converter_library.php';

echo "<pre>";

mysql_qw(
	'INSERT INTO `p_cup` SET `competition_id`=1, `parent_cup_id`=0, `type`=?, `status`=?',
	"playoff", "finished"
);
echo "Playoff inserted\n";

$id = mysql_insert_id();

mysql_qw('UPDATE `p_cup` SET `parent_cup_id`=? WHERE `id`=1', $id);
echo "Parent cup updated\n";

mysql_qw('UPDATE `p_game` SET `cup_id`=? WHERE `cup_id`=1 and `stage`>0', $id);
echo "Games updated. Rows affected: " . mysql_affected_rows() . "\n";

echo 'Patch finished in ' . save_results() . 'ms';

echo "</pre>";

?>
