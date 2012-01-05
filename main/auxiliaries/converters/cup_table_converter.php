<?php

require_once '../../includes/mysql.php';
require_once 'converter_library.php';

require_once '../../classes/cupms/ResultTable.php';
require_once '../../classes/cupms/CupFactory.php';

echo "<pre>Convertion started\n";
flush();

mysql_qw('TRUNCATE TABLE `p_man_cup_table`');
echo "`p_man_cup_table` truncated.\n";

foreach (CupFactory::getAllRegular() as $cup) {
	$table = ResultTable::generateAndStore($cup->getId());
	echo "Cup id=". $cup->getId() . " --------------------\n";
	print_r($table);
	echo "-----------------------------------\n";
	flush();
	
}

echo "Convertion finished in ";
echo save_results() . "ms</pre>";

?>
