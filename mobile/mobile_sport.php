<?php
/**
 * @author Artyom Grigoriev
 */

switch (param('part')) {

case 'sport':

	require_once dirname(__FILE__) . '/../main/classes/cupms/RatingTable.php';
	require_once dirname(__FILE__) . '/../main/classes/cupms/League.php';

	$leagues = League::getTopLeagues($user);

	begin_block('Все лиги пайпа');
	foreach ($leagues as $i => $league) {
		mobile_show_league_row($i + 1, $league);
	}
	end_block();

	$ratingTable = RatingTable::getInstance(1);
	$data = $ratingTable->getData();

	begin_block('WPR Top 5');
	for ($i = 0; $i < 5; $i++) {
		$row = $data[$i];
		mobile_show_rating_row($i + 1, $row['name'], $row['surname'], $row['image'], $row['points']);
	}
	echo '<tr class="b0"><td colspan="2"></td><td style="text-align: left;" colspan="2"><a href="/sport_rating/1">Смотреть весь рейтинг</a></td></tr>';
	end_block();

	$PATH = '/sport';

	break;

case 'sport_league':

	require_once dirname(__FILE__) . '/../main/classes/cupms/League.php';

	assertParam('id');
	$league = League::getById(intparam('id'));
	if ($league instanceof League) {
		begin_block($league->getName());
		mobile_show_league_info($league);
		end_block();

		begin_block('<a name="competitions"></a>Турниры');
		$competitions = $league->getCompetitions(true);
		foreach ($competitions as $i => $competition) {
			if ($competition instanceof Competition) {
				mobile_show_competition($competition);
			}
			if (!issetParam('unlimited')) {
				if ($i >= 2) break;
			}
		}
		if (count($competitions) > 3) {
			echo '<tr class="b0"><td></td><td style="text-align: left;"><a href="/sport_league/'.
				 $league->getId().'/unlimited#competitions">Смотреть все турниры</a></td></tr>';
		}

		end_block();
	}

	$PATH = '/sport/league/'.$league->getId();

	break;

case 'sport_rating':

	assertParam('id');

	$league = League::getById(intparam('id'));
	$ratingTable = RatingTable::getInstance(intparam('id'));
	$data = $ratingTable->getData();

	if ($league instanceof League) {
		begin_block('<a href="/sport_league/'.$league->getId().'">' .$league->getName() . '&nbsp;&raquo;</a> Рейтинг');
		end_block();

		$from = issetParam('page') ? (intparam('page') - 1) * ITEMS_PER_PAGE : 0;
		mobile_show_pager(
			"/sport_rating/{$league->getId()}/page%d",
			floor($from / ITEMS_PER_PAGE) + 1,
			ceil(count($data) / ITEMS_PER_PAGE)
		);

		begin_block(false);
		$count = count($data);
		for ($i = $from; $i < $from + ITEMS_PER_PAGE && $i < $count; ++$i) {
			$row = $data[$i];
			mobile_show_rating_row($i + 1, $row['name'], $row['surname'], $row['image'], $row['points']);
		}
		end_block();

		mobile_show_pager(
			"/sport_rating/{$league->getId()}/page%d",
			floor($from / ITEMS_PER_PAGE) + 1,
			ceil(count($data) / ITEMS_PER_PAGE)
		);
		echo "<div class=\"pg\"></div>";
	}

	$PATH = '/sport/rating#league='.$league->getId();

	break;

case 'sport_competition':

	require_once dirname(__FILE__) . '/../main/classes/cupms/Competition.php';

	assertParam('id');

	$competition = Competition::getById(intparam('id'));
	$league = $competition->getLeague();

	begin_block('<a href="/sport_league/'.$league->getId().'">' .
				$league->getName() . '&nbsp;&raquo;</a> '.
				$competition->getName());
	mobile_show_competition_info($competition);
	end_block();

	$cups = $competition->getCupsList();
	begin_block('Группы и этапы');
	foreach ($cups as $cup) {
		mobile_show_cup_row($cup);
	}
	end_block();

	$PATH = '/sport/league/' . $competition->getLeagueId() . '/competition/' . $competition->getId();

	break;

case 'sport_cup':

	require_once dirname(__FILE__) . '/../main/classes/cupms/CupFactory.php';

	assertIsset('id');

	$cup = CupFactory::getCupById(intparam('id'));
	begin_block(
		'<a href="/sport_competition/'.$cup->getCompetitionId().'">'.
		$cup->getCompetition()->getName().'&nbsp;&raquo;</a> '.
		$cup->getName()
	);
	end_block();
	if ($cup instanceof CupPlayoff) {
		if (issetParam('stage')) {
			begin_block(false);
			mobile_show_cup_playoff_stage($cup, intparam('stage'));
			end_block();
		}

		begin_block(false);
		mobile_show_cup_playoff_stages($cup, intparam('stage'));
		end_block();
	} elseif ($cup instanceof CupOneLap) {
		mobile_show_cup_one_lap($cup);
	}

	break;
}

?>
