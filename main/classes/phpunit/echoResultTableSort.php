<?php

require_once dirname(__FILE__).'/../cupms/Cup.php';
require_once dirname(__FILE__).'/../cupms/ResultTable.php';


$time_begin = microtime(true);

$cup = new Cup(1);
$result = ResultTable::getForCupLite($cup);
ResultTable::sort($result);

$time_end = microtime(true);

echo '<pre>';
print_r($result);
echo "\nTime: ".($time_end - $time_begin).' sec';
echo '</pre>';

?>
