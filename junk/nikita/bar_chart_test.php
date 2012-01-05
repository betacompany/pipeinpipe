<?php

require_once dirname(__FILE__) . '/../../main/classes/stats/StatsCounter.php';
require_once dirname(__FILE__) . '/../../main/classes/charts/BarChart.php';


$statsCounter = StatsCounter::getInstance();
$gameCounter = $statsCounter->getGameCounter();

$bar = new BarChart();
$labels = array('5:0', '5:1', '5:2', '5:3', '6:4', 'balance');
$data = array();
for ($i = 0; $i < 6; $i++) {
	$data[$i] = $gameCounter[$labels[$i]];
}
$labels[5] = 'Баланс';
$bar->set($data, $labels);
$colors = array();
$colors[] = '0000FF';
$colors[] = '8000FF';
$colors[] = 'FF00FF';
$colors[] = 'FF0080';
$colors[] = '0080FF';
$colors[] = '00FFFF';
echo $bar->url(400, 250, $colors, true);
?>
<br/>
<img src="<?=$bar->url(600, 300, $colors, true)?>"/>
