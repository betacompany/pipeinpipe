<div class="body_container">
<?php
/**
 * @author Innokenty Shuvalov
 */

require_once dirname(__FILE__).'/../classes/cupms/ResultTable.php';
require_once dirname(__FILE__).'/../classes/cupms/Competition.php';
require_once dirname(__FILE__).'/sport_competition_functions.php';

try {
	assertIsset($_REQUEST['comp_id'], 'comp_id');
	$compId = $_REQUEST['comp_id'];
	$competition = Competition::getById($compId);
?>
	<div id="competition">
<?
    sport_show_competition_header($competition);

	$mainCup = $competition->getMainCup();
	if ($mainCup) {
        sport_competition_show_cup($mainCup);
	}
?>
	</div>
<?
} catch (Exception $e) {
	echo $e->getMessage();
}
?>
</div>
