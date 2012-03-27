<?php
/**
 * @author Innokenty Shuvalov
 */
require_once dirname(__FILE__) . '/../includes/common.php';
require_once dirname(__FILE__) . '/../classes/stats/StatsCounter.php';

$timelineSettings = array(
	'number_of_leagues_to_show_on_timeline' => 100000,
	'min_word_length_to_hyphenate' => 8,//charachters (1 - hyphenate everytime)
	'max_length_collapsed' => 27,//charchters
	'max_lines_collapsed' => 2,
	'max_lines_collapsed_secondary' => 2,
	'max_length_expanded' => 30,//charchters
	'max_lines_expanded' => 4
);

$bestPipemenSettings = array(
	'number_of_players_to_show' => 5
);

$bestLeagueSettings = array(
	'number_of_leagues_to_show_in_top_leagues' => $bestPipemenSettings['number_of_players_to_show'],
	'max_league_name_first_line_length' => 22,//characters
	'max_league_name_second_line_length' => 18,//characters
	'min_league_name_word_length_to_hyphenate' => 9//characters
);

try {
	$auth = new Auth();
	$user = $auth->getCurrentUser();
	$leagues = League::getTopLeagues($user);
?>
<div id="sport_timeline_container"></div>
<?
	sport_show_timeline($leagues, $timelineSettings);
?>
<div id="sport_main" class="body_container">
<?
	$pipemenCount = Player::countAll();
	$pipemenLeagueCount = array();
	foreach ($leagues as $leagueCount => $league) {
		$currentCount = count($league->getPlayers());
		$pipemenLeagueCount[$league->getId()] = $currentCount;
	}
	$leagueCount++;

	$stats = StatsCounter::getInstance();
?>
	<table class="sport_stats">
		<tbody>
			<tr>
				<td>
<?
	sport_show_best_pipemen($pipemenCount, $bestPipemenSettings);
?>

				</td>
				<td>
<?
	sport_show_best_leagues($leagues, $pipemenLeagueCount, $bestLeagueSettings);
?>

				</td>
			</tr>
		</tbody>
	</table>
	<div id="sport_stats_wrapper">
		<div id="sport_stats">
<?
	sport_show_stats_block('total_matches', 'Сыграно матчей', Game::countAll());
	sport_show_stats_block('total_competitions', 'Сыграно турниров', Competition::countAll());

	$days_count = datetoint("2007-10-23", date('Y-m-d'));
	sport_show_stats_block('age', 'Всего пайпу', $days_count, '', lang_sclon($days_count, 'день', 'дня', 'дней'));

	sport_show_stats_block('pipemen_count', 'Всего пайпменов', $pipemenCount, '/sport/pipemen');

	$compsWithMaxMatches = $stats->getCompsWithMaxMatches();
	$competitions = Competition::getByIds(array_keys($compsWithMaxMatches));
//	Competition::sortByDate($competitions);
	$comp = $competitions[0];
	sport_show_stats_block('max_matches', 'Максимальное число матчей в турнире', $compsWithMaxMatches[$comp->getId()], $comp->getURL());

	$compsWithMaxPman = $stats->getCompsWithMaxPman();
	$competitions = Competition::getByIds(array_keys($compsWithMaxPman));
//	Competition::sortByDate($competitions);
	$comp = $competitions[0];
	$count = sport_show_stats_block('max_pman', 'Максимальное число игроков в турнире', $compsWithMaxPman[$comp->getId()], $comp->getURL());
?>
		</div>
	</div>
	<script type="text/javascript">
		$(function () {
			$('.sport_stats_block').hover(
				function () {
					$(this).find('.value').slideDown();
					$(this).find('img').animate({'opacity': 1});
				},
				function () {
					$(this).find('.value').slideUp();
					$(this).find('img').animate({'opacity': .8});
				}
			);
			$('.sport_stats_block img').css('opacity', .8);
			var blockWidth = 100,
				margin = 10,
				count = <?=$count?>,
				w = count * (blockWidth + margin);

			var drag = false,
				check = function () {
					if (drag) return;
					var wrapperWidth = $('#sport_stats_wrapper').innerWidth();
					if (w > wrapperWidth) {
						$('#sport_stats')
							.width(w)
							.draggable({
								axis: 'x',
								cursor: 'e-resize',
								drag: function(e, ui) {}
							});

						drag = true;
					} else {
						$('#sport_stats').width(wrapperWidth);
						$('.sport_stats_block').css({
							'margin': '0 ' + ((wrapperWidth - w) / count / 2) + 'px'
						});
					}
				};

			check();
			$(window).resize(check);
		});
	</script>
<?

} catch (Exception $ex) {
	//TODO use exception block
	echo $ex->getMessage();
}
?>
</div>
<?

function sport_show_stats_block($icon, $prefix, $number, $link = '', $postfix = '') {
	static $count = 0;
	$count++;
?>
<div class="sport_stats_block">
<?
	if ($link !== '') {
?>
	<a href="<?=$link?>">
<?
	}
?>
		<img src="/images/sport/<?=$icon?>.png" />
		<div class="value">
			<div class="prefix"><?=$prefix?></div>
			<div class="number"><?=$number?></div>
<?
		if ($postfix !== '') {
?>
			<div class="postfix"><?=$postfix?></div>
<?
		}
?>
		</div>
<?
	if ($link !== '') {
?>
	</a>
<?
	}
?>
</div>
<?
	return $count;
}

function sport_show_timeline($leagues, $settings) {
	$competitions = array();
	foreach (array_slice($leagues, 0, $settings['number_of_leagues_to_show_on_timeline']) as $league)
		foreach($league->getCompetitions() as $comp)
			switch ($comp->getStatus()) {
				case Competition::STATUS_FINISHED:
				case Competition::STATUS_REGISTERING:
				case Competition::STATUS_RUNNING:
					$competitions[] = $comp;
					break;
			}
	Competition::sortByDate($competitions);

	$competitionsToJson = array();
	foreach($competitions as $comp) {
		$primary = $comp->getLeagueId() == League::MAIN_LEAGUE_ID;
		$name = $comp->getName();//$comp->getDate() . " " . $comp->getDateToInt();

		$splittedNameCollapsed = string_split_into_lines($name, $settings['max_length_collapsed'], $settings['min_word_length_to_hyphenate']);
		$nameCollapsed = array_slice($splittedNameCollapsed, 0, $primary ? $settings['max_lines_collapsed'] : $settings['max_lines_collapsed_secondary']);
		if (count($splittedNameCollapsed) != count($nameCollapsed))
			$nameCollapsed[count($nameCollapsed) - 1] .= '...';

		$splittedNameExpanded = string_split_into_lines($name, $settings['max_length_expanded'], $settings['min_word_length_to_hyphenate']);
		$nameExpanded = array_slice($splittedNameExpanded, 0, $settings['max_lines_expanded']);
		if (count($splittedNameExpanded) != count($nameExpanded))
			$nameExpanded[count($nameExpanded) - 1] .= '...';

		$competitionsToJson[] = array(
			'date' => $comp->getDateToInt(),
			'id' => $comp->getId(),
			'url' => $comp->getURL(),
			'name' => $name,
			'collapsed_name' => $nameCollapsed,
			'expanded_name' => $nameExpanded,
			'image' => $comp->getImageURL(Competition::IMAGE_SMALL),
			'status' => $comp->isRegistering() || $comp->isRunning(),
			'status_image' => $comp->getStatusImageURL(),
			'primary' => $primary
		);
	}
	$startDate = '2007-10-23';
?>
<script type="text/javascript">
	$$(function () {
		sportTimeline.startDate = '<?=$startDate?>';
		sportTimeline.dateInterval = <?=(datetoint($startDate, date('Y-m-d')) / 30 + 1)?>;
		sportTimeline.show(<?=json($competitionsToJson)?>);
	});
</script>
<?
}

function sport_show_best_pipemen($pipemenCount, $settings) {
?>
<div id="sport_best_pipemen" class="slide_block">
	<div class="title opened">
		<div class="left">
			<a href="/sport/rating#league=<?=League::MAIN_LEAGUE_ID?>" class="content">
				<div>Лучшие Пайпмены</div>
			</a>
		</div>
		<div class="right">
			<div class="info">
				<div>всего: <?=$pipemenCount?></div>
			</div>
		</div>
		<div style="clear: both"></div>
	</div>
	<div class="body">
		<ul class="std_rating" style="position: static;">
<?
	$data = RatingTable::getInstance(League::MAIN_LEAGUE_ID)->getData();
	foreach (array_slice($data, 0, $settings['number_of_players_to_show']) as $place => $playerInfo) {
?>
			<li>
				<div class="place"><?=($place + 1)?></div>
				<div class="box">
					<a href="<?=$playerInfo['url']?>">
						<img alt="<?=$playerInfo['surname']?>" src="<?=$playerInfo['image']?>"/>
						<div class="content">
								<div><?=$playerInfo['surname']?></div>
								<div><?=$playerInfo['name']?></div>
						</div>
					</a>
					<div class="points"><?printf("%.1f", $playerInfo['points'])?></div>
					<div style="clear: both"></div>
				</div>
			</li>
<?
	}
?>
		</ul>
	</div>
</div>
<?
}

function sport_show_best_leagues($leagues, $pipemenLeagueCount, $settings) {
?>
<div id="sport_best_leagues" class="slide_block">
	<div class="title opened">
		<div class="left">
            <a class="content" href="/sport/league">
                <div>Лиги пайпа</div>
            </a>
		</div>
		<div class="right">
			<div class="info">
				<div>всего: <?=count($leagues)?></div>
			</div>
		</div>
		<div style="clear: both"></div>
	</div>
	<div class="body">
		<ul class="std_rating">
<?
	foreach (array_slice($leagues, 0, $settings['number_of_leagues_to_show_in_top_leagues']) as $league) {
?>
			<li>
				<div class="box">
					<a href="/sport/league/<?=$league->getId()?>">
						<img alt="<?=$league->getName()?>" src="<?=$league->getImageURL(League::IMAGE_SMALL)?>"/>
						<div class="content">
<?
		$nameLines = string_split_into_lines($league->getName(), $settings['max_league_name_first_line_length'], $settings['min_league_name_word_length_to_hyphenate']);
?>
							<div><?=$nameLines[0]?></div>
							<div><?=string_short($nameLines[1], $settings['max_league_name_second_line_length'] * 0.9, $settings['max_league_name_second_line_length'])?></div>
						</div>
					</a>
					<div class="points"><?=lang_number_sclon($pipemenLeagueCount[$league->getId()], "пайпмен", "пайпмена", "пайпменов")?></div>
					<div style="clear: both"></div>
				</div>
			</li>
<?
	}
?>
		</ul>
	</div>
</div>
<?
}

function sport_news() {
?>
<div id="sport_news" class="slide_block">
	<div class="title opened">
		<div class="left">
			<div class="content">
				<a href="/life">Лента Новостей</a>
			</div>
		</div>
		<div style="clear: both"></div>
	</div>
	<div class="body hidden" style="display: block">

	</div>
</div>
<?
}
?>
