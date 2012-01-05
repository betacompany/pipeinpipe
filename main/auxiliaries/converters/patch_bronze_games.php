<?php
/**
 * @autor Innokenty Shuvalov
 */

require_once dirname(__FILE__) . "/../../classes/cupms/Game.php";
require_once dirname(__FILE__) . "/../../classes/db/MySQLResultIterator.php";

$iterator = new MySQLResultIterator(mysql_qw("SELECT `id` FROM `p_game` WHERE `stage`=3"));
echo '<pre>';
echo "adding semi-finals as previous matches for bronze games\n";
echo "games selected, " . count($iterator->getResults()) . " entries found.\n\n";
while ($iterator->valid()) {
	$current = $iterator->current();
	$iterator->next();

	$game = new Game($current["id"]);
	if (!($game->getCup() instanceof CupPlayoff)) continue;
	
	echo "converting game with id = " . $game->getId() . "\n";
	echo "cup_id = " . $game->getCup()->getId() . "\n";
	
	$final = $game->getCup()->getFinalGame();
	echo "final game in this cup. id = " . $final->getId() . "\n";
	echo "prev game id 1 = " . $final->getPrevGameId1() . "\n";
	echo "prev game id 2 = " . $final->getPrevGameId2() . "\n";
	echo "\n";

	$game->setPrevGameId1($final->getPrevGameId1());
	$game->setPrevGameId2($final->getPrevGameId2());

	$game->update();
}
echo "done!";
echo "</pre>"
?>
