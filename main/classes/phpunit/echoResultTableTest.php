<?php

require_once dirname(__FILE__).'/../cupms/ResultTable.php';

$time_begin = microtime(true);
$results = ResultTable::generateAndStore(1);
//$results = ResultTable::getForCupLite(new Cup(1));
//ResultTable::sort($results);
$time_end = microtime(true);

echo '<pre>';
print_r($results);
echo "\nTime: ".($time_end - $time_begin).' sec';
echo '</pre>';

?>
