<?php

require_once dirname(__FILE__).'/../classes/stats/StatsCounter.php';
require_once dirname(__FILE__).'/../classes/cupms/Player.php';

echo '<pre>';
$statsCounter = StatsCounter::getInstance();

$gameCounter = $statsCounter->getGameCounter();
echo '<h1>Статистика исходов матчей</h1>';
foreach ($gameCounter as $key => $value) {
	echo $key  . ' ' . $value . "\n";
}

echo PHP_EOL . PHP_EOL;

$recordMatches = $statsCounter->getRecordMatches();
echo '<h1>Рекордные по счёту матчи</h1>';
foreach ($recordMatches['regular'] as $key => $value) {
	$player1 = Player::getById($value->getPmid1());
	$player2 = Player::getById($value->getPmid2());
	echo $value->getScore1() . ' : ' . $value->getScore2() . ' - самый крупный счёт в регулярной части турнира. Зафиксирован в матче "';
	echo $player1->getFullName() . ' vs ' . $player2->getFullName() . '"' . PHP_EOL;
}
foreach ($recordMatches['play-off'] as $key => $value) {
	$player1 = Player::getById($value->getPmid1());
	$player2 = Player::getById($value->getPmid2());
	echo $value->getScore1() . ' : ' . $value->getScore2() . ' - самый крупный счёт в плей-офф. Зафиксирован в матче "';
	echo $player1->getFullName() . ' vs ' . $player2->getFullName() . '"' . PHP_EOL;
}

echo PHP_EOL . PHP_EOL;

$personalGames = $statsCounter->getMaxPersonalGames();
echo '<h1>Рекорды личных встреч</h1>';
foreach ($personalGames as $key => $value) {

	$p1 = Player::getById($value['pmid1']);
	$p2 = Player::getById($value['pmid2']);
	if ($key == 0) {
		echo $value['gameNum'] . ' матчей - столько раз играли между собой ';
		echo $p1->getFullName() . ' и ' . $p2->getFullName() . PHP_EOL;
	} else {
		echo $p1->getFullName() . ' и ' . $p2->getFullName() . PHP_EOL;
	}
}

echo PHP_EOL;

$personalWins = $statsCounter->getMaxPersonalWins();
foreach ($personalWins as $key => $value) {

	$p1 = Player::getById($value['pmid1']);
	$p2 = Player::getById($value['pmid2']);
	if ($key == 0) {
		echo $value['gameNum'] . ' побед - столько раз ' . $p1->getFullName() . ' оказывался сильнее, чем ' . $p2->getFullName();
	} else {
		echo $p1->getFullName() . ' чем ' . $p2->getFullName() . PHP_EOL;
	}
}

echo PHP_EOL . PHP_EOL;

echo '<h1>Рекордспайпмены</h1>';
echo '<h3>Всего матчей</h3>';
$recordPipeMans = $statsCounter->getRecordPipeMans();
foreach ($recordPipeMans['total'] as $key => $value) {
	$p = Player::getById($key);
	echo $p->getFullName() . ' - ' . $value . PHP_EOL;
}

echo '<h3>Матчей в плей-офф</h3>';
foreach ($recordPipeMans['play-off'] as $key => $value) {
	$p = Player::getById($key);
	echo $p->getFullName() . ' - ' . $value . PHP_EOL;
}

echo '<h3>Максимальный процент побед</h3>';
foreach ($recordPipeMans['max_win'] as $key => $value) {
	$p = Player::getById($key);
	echo $p->getFullName() . ' - ' . $value . '%' . PHP_EOL;
}

echo '<h3>Максимальный процент поражений</h3>';
foreach ($recordPipeMans['max_loss'] as $key => $value) {
	$p = Player::getById($key);
	echo $p->getFullName() . ' - ' . $value . '%' . PHP_EOL;
}

echo '<h3>Побед в сухую</h3>';
foreach ($recordPipeMans['whitewash-win'] as $key => $value) {
	$p = Player::getById($key);
	echo $p->getFullName() . ' - ' . $value . PHP_EOL;
}

echo '<h3>Поражений в сухую</h3>';
foreach ($recordPipeMans['whitewash-loss'] as $key => $value) {
	$p = Player::getById($key);
	echo $p->getFullName() . ' - ' . $value . PHP_EOL;
}

echo '<h3>Среднее количество очков за регулярный чемпионат <span style="color: #f00;">NEW!!!</span></h3>';
foreach ($recordPipeMans['max_ave'] as $key => $value) {
	$p = Player::getById($key);
	echo $p->getFullName() . ' - ' . $value . PHP_EOL;
}

echo '<h3>Максимальное количество выигранных турниров</h3>';
foreach ($recordPipeMans['max_comp_won'] as $key => $value) {
	$p = Player::getById($key);
	echo $p->getFullName() . ' - ' . $value . PHP_EOL;
}

echo '<h3>Процент выигранных турниров <span style="color: #f00;">NEW!!!</span></h3>';
foreach ($recordPipeMans['max_comp_perc'] as $key => $value) {
	$p = Player::getById($key);
	echo $p->getFullName() . ' - ' . $value . PHP_EOL;
}

echo '<h3>Количество участий в турнирах</h3>';
foreach ($recordPipeMans['max_comps'] as $key => $value) {
	$p = Player::getById($key);
	echo $p->getFullName() . ' - ' . $value . PHP_EOL;
}

echo '<h3>Максимальное количество очков за турнир</h3>';
foreach ($recordPipeMans['max_points'] as $key => $value) {
	$p = Player::getById($key);
	echo $p->getFullName() . ' - ' . $value . PHP_EOL;
}

echo '<h3>Максимальное количество дней на первом месте <span style="color: #f00;">NEW!!!</span></h3>';
foreach ($recordPipeMans['max_days_on_top'] as $key => $value) {
	$p = Player::getById($key);
	echo $p->getFullName() . ' - ' . $value . PHP_EOL;
}

echo '</pre>';

?>
