<?php

require_once dirname(__FILE__).'/../cupms/Game.php';
require_once dirname(__FILE__).'/../cupms/Player.php';

$player = new Player(3);

echo "<pre>=== Kex's class test ===\n\n";

$kex_start = microtime(true);

$player->getDefeats(true);
$player->getVictories(true);
print_r($player);

$kex_finish = microtime(true);
echo "\nTime: ".($kex_finish - $kex_start).' s'."\n\n=== My class test ===\n\n";

$my_start = microtime(true);

echo "Games: ".Game::countGamesFor($player->getId())."\n";
echo "Lose5: ".Game::countLose5For($player->getId())."\n";
echo "Lose6: ".Game::countLose6For($player->getId())."\n";
echo "Loseb: ".Game::countLosebFor($player->getId())."\n";
echo "Win5: ".Game::countWin5For($player->getId())."\n";
echo "Win6: ".Game::countWin6For($player->getId())."\n";
echo "Winb: ".Game::countWinbFor($player->getId())."\n";

$my_finish = microtime(true);
echo "\nTime: ".($my_finish - $my_start).' s';

echo '</pre>';

?>
