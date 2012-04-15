<?php

require_once dirname(__FILE__) . '/../classes/cupms/Player.php';
require_once dirname(__FILE__) . '/../classes/stats/StatsCounter.php';
require_once dirname(__FILE__) . '/../classes/cupms/Competition.php';

require_once dirname(__FILE__) . '/../classes/utils/ResponseCache.php';

function show_club69_item(Player $player, $victories, $rank) {
	$placeSize = '';
	if ($rank > 9 && $rank < 100) {
		$placeSize = ' small';
	} elseif ($rank > 99) {
		$placeSize = ' supersmall';
	}
?>

<li>
	<div class="place<?=$placeSize?>"><?=$rank?></div>
	<div class="box">
		<a href="<?=$player->getURL()?>" target="blank">
			<img alt="<?=$player->getFullName()?>" src="<?=$player->getImageURL(Player::IMG_SMALL)?>"/>
			<div class="content">
				<div><?=$player->getSurname()?></div>
				<div><?=$player->getName()?></div>
			</div>
		</a>
		<div class="points"><?=$victories?></div>
		<div style="clear: both"></div>
	</div>
</li>
<?
}

function get_record_signature($label) {
	switch ($label) {
		case 'total': return "Всего матчей";
		case 'max_ave': return "Среднее очков за турнир";
		case 'max_comp_perc': return "Процент выигранных турниров";
		case 'max_comps': return "Всего турниров";
		case 'max_comp_won': return "Выиграно турниров";
		case 'max_days_on_top': return "Дни на вершине рейтинга";
		case 'max_points': return "WPR-очков за турнир";
		case 'max_win': return "Процент побед";
		case 'max_loss': return "Процент поражений";
		case 'play-off': return "Игр в плей-офф";
		case 'whitewash-win': return "Выиграно в сухую";
		case 'whitewash-loss': return "Проиграно в сухую";
	}
}

function is_percentage_record($label) {
	if ($label == "max_comp_perc" ||
			$label == "max_win" ||
			$label == "max_loss") {
		return true;
	}
	return false;
}

function show_record_match($recordMatch, $label, $name = null) {

	$i = 0;
	foreach ($recordMatch as $game) {
		$firstPlayer = $game->getPlayer1();
		$secondPlayer = $game->getPlayer2();
		$cupId = $game->getCupId();
		$competition = Competition::getByCupId($cupId);
		$competitionName = $competition->getName();
		$competitionURL = "/sport/league/" . $competition->getLeagueId() . "/competition/" . $competition->getId();
?>
		<div class="<?=($i == 0) ? "top_record_match" : "record_match"?>" id="record_match_<?=$name . '_' . $i?>">
			<div class="picture">
				<img src="<?=$firstPlayer->getImageURL()?>" alt="<?=$firstPlayer->getFullName()?>"/>
				<img src="<?=$secondPlayer->getImageURL()?>" alt="<?=$secondPlayer->getFullName()?>"/>
			</div>
			<div class="description">
				<div class="header"><?=$label?></div>
				<span><?=$game->getScore1() . ':' . $game->getScore2()?></span> зафиксирован в матче
				&laquo;<a href="<?=$firstPlayer->getURL()?>" target="_blank"><?=$firstPlayer->getFullName()?> </a> vs
				<a href="<?=$secondPlayer->getURL()?>" target="_blank"><?=$secondPlayer->getFullName()?></a>&raquo; на
				турнире 
				&laquo;<a href="<?=$competitionURL?>" target="_blank"><?=$competitionName?></a>&raquo;.
			</div>
		</div>
<?
		$i++;
	}
?>
	<script type="text/javascript">
		recordMatchesAmount['<?=$name?>'] = <?=count($recordMatch)?>;
		currentRecordMatch['<?=$name?>'] = 0;
	</script>
<?
}

function show_personal_match($totalGames) {

	$i = 0;
	foreach ($totalGames as $game) {
		$firstPlayer = Player::getById($game['pmid1']);
		$secondPlayer = Player::getById($game['pmid2']);
?>
		<div class="<?=($i == 0) ? "top_record_match" : "record_match"?>" id="personal_record_match_<?=$i?>">
			<div class="picture">
				<img src="<?=$firstPlayer->getImageURL()?>" alt="<?=$firstPlayer->getFullName()?>"/>
				<img src="<?=$secondPlayer->getImageURL()?>" alt="<?=$secondPlayer->getFullName()?>"/>
			</div>
			<div class="description">
				<span><?=$game['gameNum']?> матчей</span> &mdash; столько раз играли между собой
				<a href="<?=$firstPlayer->getURL()?>" target="_blank"><?=$firstPlayer->getFullName()?></a> и
				<a href="<?=$secondPlayer->getURL()?>" target="_blank"><?=$secondPlayer->getFullName()?></a>.
			</div>
		</div>
<?
		$i++;
	}
?>
	<script type="text/javascript">
		personalMatchesAmount = <?=count($totalGames)?>;
		currentPersonalMatch = 0;
	</script>
<?
}

function show_personal_win($winCounter) {

	$i = 0;
	foreach ($winCounter as $game) {
		$firstPlayer = Player::getById($game['pmid1']);
		$secondPlayer = Player::getById($game['pmid2']);
?>
		<div class="<?=($i == 0) ? "top_record_match" : "record_match"?>" id="personal_win_<?=$i?>">
			<div class="picture">
				<img src="<?=$firstPlayer->getImageURL()?>" alt="<?=$firstPlayer->getFullName()?>"/>
				<img src="<?=$secondPlayer->getImageURL()?>" alt="<?=$secondPlayer->getFullName()?>"/>
			</div>
			<div class="description">
				<span><?=$game['gameNum']?> матчей</span> &mdash; столько раз
				<a href="<?=$firstPlayer->getURL()?>" target="_blank"><?=$firstPlayer->getFullName()?></a> оказывался сильнее, чем
				<a href="<?=$secondPlayer->getURL()?>" target="_blank"><?=$secondPlayer->getFullName()?></a>.
			</div>
		</div>
<?
		$i++;
	}
?>
	<script type="text/javascript">
		personalWinAmount = <?=count($winCounter)?>;
		currentPersonalWin = 0;
	</script>
<?
}

function show_sport_record($records, $label) {

	$rec = $records[$label];
	$i = 0;
?>
				<div class="record" id="record_<?=$label?>">
<?
	foreach ($rec as $key => $value) {
		$p = Player::getById($key);
		$url = $p->getImageURL();
?>				
					<div class="<?=($i==0) ? "top_record_owner" : "record_owner"?>" id="record_<?=$label . "_" . $i?>">
						<img src="<?=$url?>" alt="<?=$p->getFullName()?>" />
						<div class="name" id="name_<?=$label . "_" . $i?>">
							<a href="<?=$p->getURL()?>" target="_blank"><?=$p->getFullName()?></a>
						</div>
						<div class="record_signature">
							<div class="record_name">
								<?=get_Record_signature($label)?>
							</div>
							<div class="record_value">
								<?=$value . ((is_percentage_record($label)) ? "%" : "")?>
							</div>
						</div>
					</div>
<?
		$i++;
	}
?>
				</div>
				<script type="text/javascript">
					amount['<?=$label?>'] = <?=$i?>;
					current['<?=$label?>'] = 0;
				</script>
<?
}

$cache = new ResponseCache('sport/stats', array('date' => date('Y-m-d')));
if ($cache->getAge() <= 3600) {
	echo $cache->get();
} else {
	$cache->start();

	$stats = StatsCounter::getInstance();
	$records = $stats->getRecordPipeMans();

	$recordMatches = $stats->getRecordMatches();
	$personalGames = $stats->getMaxPersonalGames();
	$personalWins = $stats->getMaxPersonalWins();
?>

<div id="stats_container" class="body_container">
	<script type="text/javascript">
		var records = ['total', 'play-off', 'max_win', 'max_comp_won'];
		var amount = {};
		var current = {};

		var recordMatchesAmount = {};
		var currentRecordMatch = {};

		var personalMatchesAmount = 0;
		var currentPersonalMatch = 0;

		var personalWinAmount = 0;
		var currentPersonalWin = 0;

		$(document).ready(function(event) {
			debug(current);
			for (var i = 0; i < records.length; i++) {
				if (amount[records[i]] > 1) {
					recordsSlideShow(records[i]);
				}
			}

			if (recordMatchesAmount['regular'] > 1) {
				recordMatchesSlideShow('regular');
			}

			if (recordMatchesAmount['play-off'] > 1) {
				recordMatchesSlideShow('play-off');
			}

			if (personalMatchesAmount > 1) {
				personalMatchesSlideShow();
			}

			if (personalWinAmount > 1) {
				personalWinSlideShow();
			}

			$('.top_record_owner, .record_owner').mouseenter(function() {
				var id = $(this).children(".name").attr('id');
				$('#' + id).fadeIn(300, function(){});
			});

			$('.top_record_owner, .record_owner').mouseleave(function() {
				var id = $(this).children(".name").attr('id');
				$('#' + id).fadeOut(300, function(){});
			});
		});

		function recordsSlideShow(recordName) {

			if (current[recordName] == amount[recordName] - 1) {
				current[recordName] = 0;
			} else {
				current[recordName]++;
			}

			$('#record_' + recordName + ' .top_record_owner').fadeOut(2000, function () {
				$(this).removeClass('top_record_owner').addClass('record_owner');
			});
			$('#record_' + recordName + '_' + current[recordName]).fadeIn(2000, function() {
				$(this).removeClass('record_owner').addClass('top_record_owner');
			});
			setTimeout('recordsSlideShow("' + recordName + '")', 5000);
		}

		function recordMatchesSlideShow(recordMatchName) {

			var prev = currentRecordMatch[recordMatchName];
			if (currentRecordMatch[recordMatchName] == recordMatchesAmount[recordMatchName] - 1) {
				currentRecordMatch[recordMatchName] = 0;
			} else {
				++currentRecordMatch[recordMatchName];
			}

			$('#record_match_' + recordMatchName + '_' + prev).fadeOut(2000, function() {
				$(this).removeClass('top_record_match').addClass('record_match');
			});
			$('#record_match_' + recordMatchName + '_' + currentRecordMatch[recordMatchName]).fadeIn(2000, function() {
				$(this).removeClass('record_match').addClass('top_record_match');
			});
			setTimeout('recordMatchesSlideShow("' + recordMatchName + '")', 8000);
		}

		function personalMatchesSlideShow() {

			var prev = currentPersonalMatch;
			if (currentPersonalMatch == personalMatchesAmount - 1) {
				currentPersonalMatch = 0;
			} else {
				++currentPersonalMatch;
			}

			$('#personal_record_match_' + prev).fadeOut(2000, function() {
				$(this).removeClass('top_record_match').addClass('record_match');
			});
			$('#personal_record_match_' + currentPersonalMatch).fadeIn(2000, function() {
				$(this).removeClass('record_match').addClass('top_record_match');
			});
			setTimeout('personalMatchesSlideShow()', 8000);
		}

		function personalWinSlideShow() {

			var prev = currentPersonalWin;
			if (currentPersonalWin == personalWinAmount - 1) {
				currentPersonalWin = 0;
			} else {
				++currentPersonalWin;
			}

			$('#personal_win_' + prev).fadeOut(2000, function() {
				$(this).removeClass('top_record_match').addClass('record_match');
			});
			$('#personal_win_' + currentPersonalMatch).fadeIn(2000, function() {
				$(this).removeClass('record_match').addClass('top_record_match');
			});
			setTimeout('personalWinSlideShow()', 8000);
		}

	</script>
	<div id="result_charts">
		<h2 class="other">Статистика исходов матчей</h2>
		<div class="chart">
			<img src="<?=$stats->getPieChart(true)?>" alt=""/>
		</div>
		<div class="chart">
			<img src="<?=$stats->getBarChart(true)?>" alt=""/>
		</div>
		<div style="clear:both"></div>
	</div>
	<div id="record_matches_container">
		<h2 class="other">Рекордные матчи</h2>
	<!--Record score in regular part-->
		<div class="record_match_wrapper">
<?
show_record_match($recordMatches['regular'], "Максимальный счёт в регулярной части", "regular");
?>
		</div>
		<div class="record_match_wrapper">
<?
show_record_match($recordMatches['play-off'], "Максимальный счёт в плей-офф", "play-off");
?>
		</div>
		<h2 class="other">Рекорды личных встреч</h2>
		<div class="record_match_wrapper">
<?
show_personal_match($personalGames);
?>
		</div>
		<div class="record_match_wrapper">
<?
show_personal_win($personalWins);
?>
		</div>
		<div style="clear:both"></div>
	</div>
	
	<div id="records_container">
		<div id="record_pipemen">
			<h2 class="other">Рекордспаймены</h2>
			<div id="record_pipemen_wrapper">
<?
$recordNames = $stats->getRecordNames();
$club69 = $stats->getClub69();

foreach ($recordNames as $name) {
	show_sport_record($records, $name);
}

?>

			</div>
		</div>
		<div id="club_69">
			<h2 class="other">Клуб 69-ти побед</h2>
			<ul id="rating">
<?
$i = 1;
$candidatesStarted = false;
foreach ($club69 as $pmid => $victories) {
	if ($victories < 69 && !$candidatesStarted) {
		$candidatesStarted = true;
?>
				<h4 class="other">Претенденты</h4>
<?
	}
	show_club69_item(Player::getById($pmid), $victories, $i++);
}
?>
			</ul>
		</div>
		<div style="clear:both"></div>
	</div>
	<div id="record_competitions">
		<h2 class="other">Самые масштабные турниры</h2>
		<br/>
<?
$compsWithMaxMatches = $stats->getCompsWithMaxMatches();
$competitions = Competition::getByIds(array_keys($compsWithMaxMatches));
foreach ($compsWithMaxMatches as $compId => $matchesNum) {
	$comp = Competition::getByIdFromArray($compId, $competitions);
?>
		<a href="<?=$comp->getURL()?>" target="_blank"><?=$comp->getName()?></a> &mdash;
		самое большое количество матчей: <?=$matchesNum?>.
		<br/>
<?
}
$compsWithMaxPman = $stats->getCompsWithMaxPman();
$competitions = Competition::getByIds(array_keys($compsWithMaxPman));
foreach ($compsWithMaxPman as $compId => $pmanNum) {
	$comp = Competition::getByIdFromArray($compId, $competitions);
?>
		<a href="<?=$comp->getURL()?>" target="_blank"><?=$comp->getName()?></a> &mdash;
		самое большое количество участников: <?=$pmanNum?>.
<?
}
?>
	</div>
</div>

<?
	$cache->store();
}
?>
