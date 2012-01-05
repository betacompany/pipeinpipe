<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../../main/classes/stats/StatsCounter.php';
require_once dirname(__FILE__) . '/../../main/classes/charts/PieChart.php';


$statsCounter = StatsCounter::getInstance();
//$gameCounter = $statsCounter->getGameCounter();
//
//$pie = new PieChart();
//$labels = array('5:0', '5:1', '5:2', '5:3', '6:4', 'balance');
//$data = array();
//for ($i = 0; $i < 6; $i++) {
//	$data[$i] = $gameCounter[$labels[$i]];
//}
//$pie->set($data, $labels);
//$colors = array();
//$colors[] = '0000FF';
//$colors[] = '8000FF';
//$colors[] = 'FF00FF';
//$colors[] = 'FF0080';
//$colors[] = '0080FF';
//$colors[] = '00FFFF';
echo $statsCounter->getPieChart(true);
?>
<br/>
<img src="<?=$statsCounter->getPieChart(true);?>"/>
