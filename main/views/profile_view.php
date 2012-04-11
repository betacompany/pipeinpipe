<?php
/**
 * @author Artyom Grigoriev
 */

function profile_show_body($person, $player, $tabs) {
	if ($person instanceof User && $tabs['person'] === 'selected') {
		profile_show_person($person, $player, $tabs);
	} elseif ($player instanceof Player && $tabs['player'] === 'selected') {
		profile_show_player($person, $player, $tabs);
	} elseif ($person instanceof User && $tabs['feed'] == 'selected') {
		profile_show_feed($person, $player, $tabs);
	} elseif ($person instanceof User && $tabs['edit'] == 'selected') {
		profile_show_edit($person, $player, $tabs);
	}
}

function profile_tab_name($person, $player, $tab) {
	switch ($tab) {
	case 'person': return 'Пользователь';
	case 'player': return $player instanceof Player ? 'Пайпмен'.($player->isMale() ? '' : 'ка') : '';
	case 'edit': return 'Редактировать';
	case 'feed': return 'Лента';
	}
}

function profile_tab_href($person, $player, $tab) {
	switch ($tab) {
	case 'person': return $person instanceof User ? $person->getURL() : '';
	case 'player': return $player instanceof Player ? $player->getURL() : '';
	case 'edit': return '/profile/edit';
	case 'feed': return '/profile';
	}
}

function profile_show_tabs($person, $player, $tabs) {
?>

			<div class="tabs title">
<?
	foreach ($tabs as $tab => $value) {
		if ($value) {
?>

				<a href="<?=profile_tab_href($person, $player, $tab)?>" onclick="javascript: return profile.open('<?=$tab?>');">
					<div class="tab<?=($value === 'selected' ? ' selected' : '')?>"><?=profile_tab_name($person, $player, $tab)?></div>
				</a>
<?
		}
	}
?>
				<div style="clear: both;"></div>
			</div>
<?
}

function profile_show_contact($href, $icon, $value) {
?>

								<div class="contact">
									<a href="<?=$href?>"<?if(strpos($href, 'http') === 0) {?> target="_blank"<?}?>>
										<div class="icon">
											<img src="<?=$icon?>" />
										</div>
										<div class="value"><?=$value?></div>
									</a>
								</div>
<?
}

function profile_show_person(User $person, $player, $tabs) {
	$place = false;
	if ($person->getCountryName()) {
		if ($person->getCityName()) {
			$place = $person->getCountryName() . ',&nbsp;' . $person->getCityName();
		}
	}
?>

	<div id="person">
		<div class="left_column">
			<img class="photo" src="<?=$person->getImageURL(User::IMAGE_NORMAL)?>" alt="<?=$person->getFullName()?>" />
		</div>

		<div class="info_wrapper">
			<div class="info_container">
				<h1 class="other"><?=$person->getFullName()?></h1>
				<? if ($place) { ?><div class="place"><?=$place?></div><? } ?>

				<div class="slide_block">
					<? profile_show_tabs($person, $player, $tabs); ?>

					<div class="body">
						<div class="data_wrapper">
<?
	if ($person->get(User::KEY_BIRTHDAY)) {
?>

							<div class="birthday">
								<span>Дата рождения:</span> <?=date_local_ymd($person->get(User::KEY_BIRTHDAY))?>
							</div>
<?
	}
?>

						</div>
						<div class="contacts_wrapper">
							<div class="contacts">
<?
	$count = 0;
	foreach (User::getContactKeys() as $key) {
		$value = $person->get($key);
		if ($value != null) {
			$count++;
			switch ($key) {
			case User::KEY_ICQ:
				profile_show_contact(
					'http://icq.com/people/'.$value,
					'/images/social/icq.png',
					'ICQ#&nbsp;'.$value
				);
				break;
			case User::KEY_SKYPE:
				profile_show_contact(
					'skype:'.$value.'?userinfo',
					'/images/social/skype.png',
					'Skype:&nbsp;'.$value
				);
				break;
			case User::KEY_VKID:
				profile_show_contact(
					'http://vkontakte.ru/id'.$value,
					'/images/social/vkontakte.png',
					'В&nbsp;Контакте'
				);
				break;
			}
		}
	}
?>
								
							</div>
						</div>
						<script type="text/javascript">
							$(function () {
								$('.contact').hover(
									function () {
										$(this).find('.value').slideDown();
										$(this).find('.icon').animate({'opacity': 1});
									},
									function () {
										$(this).find('.value').slideUp();
										$(this).find('.icon').animate({'opacity': .7});
									}
								);
								$('.contact .icon').css('opacity', .7);
								var w = <?=$count * 70?>;
								$('.contacts').width(w);
								
								var drag = false,
									check = function () {
										if (drag) return;
										if (w > $('.contacts_wrapper').innerWidth()) {
											$('.contacts').draggable({
												axis: 'x',
												cursor: 'e-resize',
												drag: function(e, ui) {}
											});
											drag = true;
										}
									};

								check();
								$(window).resize(check);
							});
						</script>
					</div>
				</div>

<?
	$forumMsgs = Forum::countMessages($person->getId());
	$forumMsgsTotal = Forum::countMessages();
	$forumMsgsAvg = $forumMsgsTotal ? round(100 * $forumMsgs / $forumMsgsTotal, 1) : 0;

	$forumTopics = Forum::countTopics($person->getId());
	$forumTopicsTotal = Forum::countTopics();
	$forumTopicsAvg = $forumTopicsTotal ? round(100 * $forumTopics / $forumTopicsTotal, 1) : 0;

	$agreements = Action::countActive(Action::AGREE, $person->getId());
	$agreementsTotal = Action::countActive(Action::AGREE);
	$agreementsAvg = $agreementsTotal ? round(100 * $agreements / $agreementsTotal, 1) : 0;

	$romanments = Action::countActive(Action::ROMAN, $person->getId());
	$romanmentsTotal = Action::countActive(Action::ROMAN);
	$romanmentsAvg = $romanmentsTotal ? round(100 * $romanments / $romanmentsTotal, 1) : 0;

	$evaluations = Action::countActive(Action::EVALUATION, $person->getId());
	$evaluationsTotal = Action::countActive(Action::EVALUATION);
	$evaluationsAvg = $evaluationsTotal ? round(100 * $evaluations / $evaluationsTotal, 1) : 0;

	$comments = Comment::countBasicComments($person->getId());
	$commentsTotal = Comment::countBasicComments();
	$commentsAvg = $commentsTotal ? round(100 * $comments / $commentsTotal, 1) : 0;

	$photos = ItemDBClient::countByType(Item::PHOTO, $person->getId());
	$photosTotal = ItemDBClient::countByType(Item::PHOTO);
	$photosAvg = $photosTotal ? round(100 * $photos / $photosTotal, 1) : 0;

	$videos = ItemDBClient::countByType(Item::VIDEO, $person->getId());
	$videosTotal = ItemDBClient::countByType(Item::VIDEO);
	$videosAvg = $videosTotal ? round(100 * $videos / $videosTotal, 1) : 0;

	$posts = ItemDBClient::countByType(Item::BLOG_POST, $person->getId());
	$postsTotal = ItemDBClient::countByType(Item::BLOG_POST);
	$postsAvg = $postsTotal ? round(100 * $posts / $postsTotal, 1) : 0;

	$total = $forumMsgs + $forumTopics + $agreements + $romanments +
				$evaluations + $comments + $photos + $videos + $posts;

	$totalTotal = $forumMsgsTotal + $forumTopicsTotal + $agreementsTotal + $romanmentsTotal +
				$evaluationsTotal + $commentsTotal + $photosTotal + $videosTotal + $posts;
	$totalAvg = $totalTotal ? round(100 * $total / $totalTotal, 1) : 0;

?>
				
				<div class="slide_block">
					<div class="title">Статистика жизни на сайте</div>
					<div class="body">
						<div class="stats">
							<div>
								<div class="stat">
									<div class="title">Сообщений<br />на форуме</div>
									<div class="abs"><?=$forumMsgs?></div>
									<div class="rel"><?=$forumMsgsAvg?>%</div>
								</div>
							</div>

							<div>
								<div class="stat">
									<div class="title">Топиков<br />на форуме</div>
									<div class="abs"><?=$forumTopics?></div>
									<div class="rel"><?=$forumTopicsAvg?>%</div>
								</div>
							</div>

							<div>
								<div class="stat">
									<div class="title">Согласий<br />на сайте</div>
									<div class="abs"><?=$agreements?></div>
									<div class="rel"><?=$agreementsAvg?>%</div>
								</div>
							</div>

							<div>
								<div class="stat">
									<div class="title">Зароманиваний<br />на сайте</div>
									<div class="abs"><?=$romanments?></div>
									<div class="rel"><?=$romanmentsAvg?>%</div>
								</div>
							</div>

							<div>
								<div class="stat">
									<div class="title">Оценок<br />на сайте</div>
									<div class="abs"><?=$evaluations?></div>
									<div class="rel"><?=$evaluationsAvg?>%</div>
								</div>
							</div>

							<div>
								<div class="stat">
									<div class="title">Комментариев<br />на сайте</div>
									<div class="abs"><?=$comments?></div>
									<div class="rel"><?=$commentsAvg?>%</div>
								</div>
							</div>

							<div>
								<div class="stat">
									<div class="title">Постов<br />в блогах</div>
									<div class="abs"><?=$posts?></div>
									<div class="rel"><?=$postsAvg?>%</div>
								</div>
							</div>							

							<div>
								<div class="stat">
									<div class="title">Фотографий<br />на сайте</div>
									<div class="abs"><?=$photos?></div>
									<div class="rel"><?=$photosAvg?>%</div>
								</div>
							</div>

							<div>
								<div class="stat">
									<div class="title">Видеозаписей<br />на сайте</div>
									<div class="abs"><?=$videos?></div>
									<div class="rel"><?=$videosAvg?>%</div>
								</div>
							</div>

							<div>
								<div class="stat total">
									<div class="title">Итого<br />от сайта</div>
									<div class="abs"><?=$totalAvg?>%</div>
									<div class="rel"><?=$total?></div>
								</div>
							</div>
							
							<div style="clear: both;"></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div style="clear: both;"></div>
	</div>
<?
}

function profile_show_player($person, Player $player, $tabs) {
	$leaguesInfo = $player->getLeaguesInfo();
	$trophies = $player->getTrophiesInfo();
?>

	<div id="player">
		<div class="left_column">
			<img class="photo" src="<?=$player->getImageURL(Player::IMG_NORMAL)?>" alt="<?=$player->getFullName()?>" />
			<div class="leagues">
<?
	foreach ($leaguesInfo as $leagueInfo) {
		$league = League::getById($leagueInfo['league_id']);
?>

				<div class="league<?=(count($trophies[$league->getId()]) ? ' large' : '')?>">
					<div class="legend">
						<a href="/sport/league/<?=$league->getId()?>"><?=string_short($league->getName(), 15, 20)?></a>
					</div>
					<div class="place"><?=$leagueInfo['place']?></div>
					<div class="points">
						<a href="/sport/rating#league=<?=$league->getId()?>"><?=round($leagueInfo['points'], 2)?></a>
					</div>
					<div class="trophies">
<?
		$leagueTrophies = $trophies[$league->getId()];
		if (isset($leagueTrophies)) {
			foreach ($leagueTrophies as $competition) {
?>

						<a href="/sport/league/<?=$competition->getLeagueId()?>/competition/<?=$competition->getId()?>" target="_blank">
							<div class="trophy">
								<img src="<?=$competition->getImageURL(Competition::IMAGE_SMALL)?>" alt="<?=$competition->getName()?>" />
							</div>
						</a>
<?
			}
		}
?>

					</div>
				</div>
<?
	}
?>

			</div>
		</div>

		<div class="info_wrapper">
			<div class="info_container">
				<h1 class="other"><?=$player->getFullName()?></h1>
				<?if ($player->getCountry()) {?><div class="place"><?=$player->getCountry() . ',&nbsp;' . $player->getCity()?></div><?}?>

				<div class="slide_block">
					<? profile_show_tabs($person, $player, $tabs); ?>
			
					<div class="body"><?=str_replace("\n", '<br/>', $player->getDescription())?></div>
				</div>

				<div class="slide_block">
					<div class="title">
						<div class="left">
							<div class="content">Статистика участия в турнирах</div>
						</div>
						<div class="right"></div>
						<div style="clear: both;"></div>
					</div>
					<div class="body">
						<div class="competitions">
<?
	$compsInfo = $player->getCompetitionsInfo();
	foreach ($compsInfo as $compInfo) {
		$comp = $compInfo['competition'];
?>

							<a href="<?=$comp->getURL()?>">
								<div class="competition">
									<img src="<?=$comp->getImageURL(Competition::IMAGE_SMALL)?>" alt="<?=$comp->getName()?>" />
									<div class="title"><?=$comp->getName()?></div>
									<div class="place">
<?
		if ($compInfo['place'] > 0) {
?>

										<span class="pl"><?=$compInfo['place']?></span><span class="total">/<?=$compInfo['count']?></span>
<?
		} else {
?>

										<span class="total">не <?=($player->isMale() ? 'вышел' : 'вышла')?><br />из группы</span>
<?
		}
?>

									</div>
								</div>
							</a>
<?
	}
?>

						</div>
					</div>
				</div>

				<div class="slide_block">
					<div class="title">
						<div class="left">
							<div class="content">Статистика игр</div>
						</div>
						<div class="right"></div>
						<div style="clear: both;"></div>
					</div>
					<div class="body">
<?
	profile_show_game_stats($player);
?>

					</div>
				</div>


                <div id="rating_main_box" class="slide_block">
                    <div class="title">
                        <div class="left">
                            <div class="content">Рейтинг</div>
                            <script type="text/javascript">
                                var drawRatingGraphs = <?if(profile_get_charts_data($player) == null) echo "true"; else echo "false"?>;
                                if(drawRatingGraphs) $("#rating_main_box").hide();
                            </script>
                        </div>
                        <div class="right"></div>
                        <div style="clear: both;"></div>
                    </div>

					<div class="body">
						<div id="chart_vk_rating" style="margin: 20px;">
<?
	require_once dirname(__FILE__) . '/../classes/charts/VkontakteLineChart.php';
	$movement = $player->getRatingMovement();
        $line = new Line();
	    foreach ($movement as $d) {
            $line->addPoint(strtotime($d['date']), $d['points']);
        }
        $chart = new VkontakteLineChart("chart_vk_place_graph");
        $chart->addLine("Очков в WPR", "8fbc13", $line);
    echo $chart->toHTML(time());
?>
                            <div id="chart_place">

                                <script type="text/javascript" src="https://www.google.com/jsapi"></script>
                                <script type="text/javascript">
                                    google.load("visualization", "1", {packages:["corechart"]});
                                    google.setOnLoadCallback(drawChart);
                                    function drawChart() {
                                        var dataTable = new google.visualization.DataTable();
                                        dataTable.addColumn('date', 'Дата');
                                        dataTable.addColumn('number', 'Место в WPR');
                                        dataTable.addRows([
                                            <?
                                                $movement = profile_get_charts_data($player);
                                                    $date = array();
                                                    $isFirst = true;
                                                    foreach($movement as $d){
                                                        $date = explode('-', $d['date']);
                                                        if(!$isFirst) echo ",\n";
                                                        echo "[new Date(" . $date[0] . ', ' . $date[1] . ', ' . $date[2] . "), " . $d['place'] . "]";
                                                        $isFirst = false;
                                                    }
                                            ?>
                                        ]);

                                        var dataView = new google.visualization.DataView(dataTable);

                                        var chart = new google.visualization.AreaChart(document.getElementById('chart_place'));
                                        var options = {
                                            chartArea: {
                                                left: 58,
                                                top: 20,
                                                width: 666
                                            },
                                            colors: ['#2969FF'],
                                            hAxis: {
                                                baselineColor: '#2969FF',
                                                showTextEvery: 1,
                                                textStyle: {
                                                    fontSize: 10
                                                },
                                                format:'MMM y'
                                            },
                                            vAxis: {
                                                baselineColor: '#2969FF',
                                                direction: -1,
                                                maxValue: <?=count_max_places($player) * 1.5?>,
                                                minValue: 0.5
                                            },
                                            focusTarget: 'category',
                                            height: 400,
                                            legend: 'none'
                                        };
                                        chart.draw(dataView, options);
                                    }
                                </script>
                            </div>

						</div>
					</div>
				</div>
			</div>
		</div>

		<div style="clear: both;"></div>
	</div>

<?
}

function profile_get_charts_data(Player $player) {
	require_once dirname(__FILE__) . '/../classes/cupms/RatingTable.php';

	$points = array();
	$places = array();

	$leaguesInfo = $player->getLeaguesInfo();
	foreach ($leaguesInfo as $leagueInfo) {
		$today = date("Y-m-d");
		$start = "2007-10-23";

        $movement = RatingTable::getRatingMovementInterval($start, $today, 1, $player->getId());
		foreach ($movement as $step) {
            $points[] = round($step['points']);
            $places[] = $step['place'];
		}
	}

    return $movement;
}

function count_max_places(Player $player) {
    require_once dirname(__FILE__) . '/../classes/cupms/RatingTable.php';

    $places = array();
    $movement = $player->getRatingMovement();
    foreach ($movement as $step) {
        $places[] = $step['place'];
    }
    $res = 0;
    try{
        $res = max($places);
    } catch(Exception $e) {}

    return $res;
}

function profile_show_game_stats(Player $player) {
	profile_show_regularity_stats_table($player->getVictories(true), $player->getDefeats(true));
	profile_show_playoff_stats_table($player->getVictories(false), $player->getDefeats(false));
}

function profile_show_regularity_stats_table($victories, $defeats) {
?>
<table class="stats">
	<thead>
		<th></th>
		<th<?=$victories[Player::SCORE_FIVE] + $defeats[Player::SCORE_FIVE] == 0 ? ' class="zero"' : ''?>>со счётом 5</th>
		<th<?=$victories[Player::SCORE_SIX] + $defeats[Player::SCORE_SIX] == 0 ? ' class="zero"' : ''?>>со счётом 6</th>
		<th<?=$victories[Player::BALANCE] + $defeats[Player::BALANCE] == 0 ? ' class="zero"' : ''?>>по балансу</th>
		<th<?=$victories[Player::TECHNICAL] + $defeats[Player::TECHNICAL] == 0 ? ' class="zero"' : ''?>>технически</th>
		<th<?=$victories[Player::FATALITY] + $defeats[Player::FATALITY] == 0 ? ' class="zero"' : ''?>>по фаталити</th>
		<th<?=$victories[Player::TOTAL] + $defeats[Player::TOTAL] == 0 ? ' class="zero"' : ''?>>всего</th>
	</thead>
	<tbody>
		<tr>
			<td>Победы</td>
			<td<?=$victories[Player::SCORE_FIVE] == 0 ? ' class="zero"' : ''?>><?=$victories[Player::SCORE_FIVE]?></td>
			<td<?=$victories[Player::SCORE_SIX] == 0 ? ' class="zero"' : ''?>><?=$victories[Player::SCORE_SIX]?></td>
			<td<?=$victories[Player::BALANCE] == 0 ? ' class="zero"' : ''?>><?=$victories[Player::BALANCE]?></td>
			<td<?=$victories[Player::TECHNICAL] == 0 ? ' class="zero"' : ''?>><?=$victories[Player::TECHNICAL]?></td>
			<td<?=$victories[Player::FATALITY] == 0 ? ' class="zero"' : ''?>><?=$victories[Player::FATALITY]?></td>
			<td<?=$victories[Player::TOTAL] == 0 ? ' class="zero"' : ''?>><?=$victories[Player::TOTAL]?></td>
		</tr>
		<tr>
			<td>Поражения</td>
			<td<?=$defeats[Player::SCORE_FIVE] == 0 ? ' class="zero"' : ''?>><?=$defeats[Player::SCORE_FIVE]?></td>
			<td<?=$defeats[Player::SCORE_SIX] == 0 ? ' class="zero"' : ''?>><?=$defeats[Player::SCORE_SIX]?></td>
			<td<?=$defeats[Player::BALANCE] == 0 ? ' class="zero"' : ''?>><?=$defeats[Player::BALANCE]?></td>
			<td<?=$defeats[Player::TECHNICAL] == 0 ? ' class="zero"' : ''?>><?=$defeats[Player::TECHNICAL]?></td>
			<td<?=$defeats[Player::FATALITY] == 0 ? ' class="zero"' : ''?>><?=$defeats[Player::FATALITY]?></td>
			<td<?=$defeats[Player::TOTAL] == 0 ? ' class="zero"' : ''?>><?=$defeats[Player::TOTAL]?></td>
		</tr>
		<tr>
			<td>Всего</td>
			<td<?=$victories[Player::SCORE_FIVE] + $defeats[Player::SCORE_FIVE] == 0 ? ' class="zero"' : ''?>><?=$victories[Player::SCORE_FIVE] + $defeats[Player::SCORE_FIVE]?></td>
			<td<?=$victories[Player::SCORE_SIX] + $defeats[Player::SCORE_SIX] == 0 ? ' class="zero"' : ''?>><?=$victories[Player::SCORE_SIX] + $defeats[Player::SCORE_SIX]?></td>
			<td<?=$victories[Player::BALANCE] + $defeats[Player::BALANCE] == 0 ? ' class="zero"' : ''?>><?=$victories[Player::BALANCE] + $defeats[Player::BALANCE]?></td>
			<td<?=$victories[Player::TECHNICAL] + $defeats[Player::TECHNICAL] == 0 ? ' class="zero"' : ''?>><?=$victories[Player::TECHNICAL] + $defeats[Player::TECHNICAL]?></td>
			<td<?=$victories[Player::FATALITY] + $defeats[Player::FATALITY] == 0 ? ' class="zero"' : ''?>><?=$victories[Player::FATALITY] + $defeats[Player::FATALITY]?></td>
			<td<?=$victories[Player::TOTAL] + $defeats[Player::TOTAL] == 0 ? ' class="zero"' : ''?>><?=$victories[Player::TOTAL] + $defeats[Player::TOTAL]?></td>
		</tr>
	</tbody>
</table>
<?
}

function profile_show_playoff_stats_table($victories, $defeats) {
	if ($victories[Player::TOTAL] == 0 && $defeats[Player::TOTAL] == 0) return;

	$vStages = array_keys($victories);
	$dStages = array_keys($defeats);
	$maxStage = max(intval($vStages[0]), intval($dStages[0]));

	$total = array();
	foreach (array_merge($vStages, $dStages) as $key) {
		$total[$key] = $victories[$key] + $defeats[$key];
	}

?>

<table class="stats">
	<thead>
		<th></th>
<?
	profile_show_playoff_row($total, $maxStage, true);
?>

	</thead>
	<tbody>
		<tr>
			<td>Победы</td>
<?
	profile_show_playoff_row($victories, $maxStage);
?>

		</tr>
		<tr>
			<td>Поражения</td>
<?
	profile_show_playoff_row($defeats, $maxStage);
?>

		</tr>
		<tr>
			<td>Всего</td>
<?
	profile_show_playoff_row($total, $maxStage);
?>

		</tr>
	</tbody>
</table>
<?
}

function profile_show_playoff_row($data, $maxStage, $head = false) {

	for ($stage = $maxStage; $stage >= 2; $stage /= 2) {
?>

			<t<?=$head?'h':'d'?><?=$data[$stage]==0 ? ' class="zero"' : ''?>>
				<?=$head ? "1/".$stage : $data[$stage]?>
			</t<?=$head?'h':'d'?>>
<?
	}
?>
			<t<?=$head?'h':'d'?><?=$data["3"]==0 ? ' class="zero"' : ''?>>
				<?=$head ? "за 3 место" : $data["3"]?>
			</t<?=$head?'h':'d'?>>
			<t<?=$head?'h':'d'?><?=$data["1"]==0 ? ' class="zero"' : ''?>>
				<?=$head ? "финал" : $data["1"]?>
			</t<?=$head?'h':'d'?>>
			<t<?=$head?'h':'d'?><?=$data["total"]==0 ? ' class="zero"' : ''?>>
				<?=$head ? "всего" : $data['total']?>
			</t<?=$head?'h':'d'?>>
<?
}

function profile_show_feed(User $person, $player, $tabs) {
	$place = false;
	if ($person->getCountryName()) {
		if ($person->getCityName()) {
			$place = $person->getCountryName() . ',&nbsp;' . $person->getCityName();
		}
	}
?>

	<div id="feed">
		<div class="left_column">
			<img class="photo" src="<?=$person->getImageURL(User::IMAGE_NORMAL)?>" alt="<?=$person->getFullName()?>" />
		</div>

		<div class="info_wrapper">
			<div class="info_container">
				<h1 class="other"><?=$person->getFullName()?></h1>
				<? if ($place) { ?><div class="place"><?=$place?></div><? } ?>

				<div class="slide_block">
					<? profile_show_tabs($person, $player, $tabs); ?>

					<div class="body"></div>
				</div>

			</div>
		</div>

		<div style="clear: both;"></div>
	</div>
<?
}

function profile_show_edit(User $person, $player, $tabs) {
	$place = false;
	if ($person->getCountryName()) {
		if ($person->getCityName()) {
			$place = $person->getCountryName() . ',&nbsp;' . $person->getCityName();
		}
	}
?>

	<div id="edit">
		<div class="left_column">
			<div id="photo_container" onselectstart="javascript: return false;">
				<img class="photo" src="<?=$person->getImageURL(User::IMAGE_NORMAL)?>" alt="<?=$person->getFullName()?>" />
				<div id="photo_border"></div>				
			</div>
			<div id="photo_save" class="button">Сохранить</div>
		</div>

		<div class="info_wrapper">
			<div class="info_container">
				<h1 class="other"><?=$person->getFullName()?></h1>
				<? if ($place) { ?><div class="place"><?=$place?></div><? } ?>

				<div class="slide_block">
					<? profile_show_tabs($person, $player, $tabs); ?>

					<div class="body">
						<script type="text/javascript">
							var disabled = {}, values = {};
						</script>
						<h2 class="other">Информация</h2>
						<ul class="properties">
<?
	foreach (User::getEditableKeys() as $key) {
		$value = $person->get($key);
		profile_show_editing_bar($key, $value);
	}
?>

						</ul>
						<script type="text/javascript">
							$$(function () {
								$('.properties .button')
									.click(function () {
										var id = $(this).attr('id'),
											a = id.split('_'),
											key = a[1];
										if (disabled[key]) return;
										profile.save(key, $('#input_' + key).val());
									});
								$('.properties .input > input')
									.focusin(function () {
										var id = $(this).attr('id'),
											a = id.split('_'),
											key = a[1];
										$('#button_' + key).fadeIn();
									})
									.focusout(function () {
										var id = $(this).attr('id'),
											a = id.split('_'),
											key = a[1];
										if (values[key] == $('#input_' + key).val()) {
											$('#button_' + key).fadeOut();
										}
									});
							});
						</script>

						<h2 class="other">Изменение фотографии</h2>
						<div id="photo_editor">
							<form id="photo_form" action="/procs/proc_main.php" method="post" enctype="multipart/form-data">
								<input type="hidden" name="method" value="upload_photo" />
								<div style="float: left;">
									<input type="file" name="photo" accept="image/jpeg" onchange="javascript: $('#photo_editor .button').fadeIn();" />
								</div>
								<div class="button">Отправить</div>
								<div style="clear: both;"></div>
							</form>							
						</div>
						<script type="text/javascript">
							$('#photo_editor .button').click(function () {
								$('#photo_form').submit();
							});
						</script>

						<h2 class="other">Изменение миниатюр</h2>
						<small style="padding-left: 12px;"><a href="/procs/proc_main.php?method=unlink_miniatures">Сбросить миниатюры</a></small>
						<div id="photo_mini" onclick="javascript: profile.photo.init();">
							<img id="img_supersmall" src="<?=$person->getImageURL(User::IMAGE_SQUARE_SMALL)?>" />
							<img id="img_small" src="<?=$person->getImageURL(User::IMAGE_SQUARE)?>" />
						</div>
					</div>
				</div>

			</div>
		</div>

		<div style="clear: both;"></div>
	</div>
<?
}

function profile_show_editing_bar($key, $value) {
?>

							<li class="property">
<?
	$value = ($value == null) ? '' : $value;
	switch ($key) {
	case User::KEY_CITY:
		profile_show_editing_bar_simple($key, 'Город', $value);
		break;
	case User::KEY_COUNTRY:
		profile_show_editing_bar_simple($key, 'Страна', $value);
		break;
	case User::KEY_EMAIL:
		profile_show_editing_none($key, 'E-mail', $value);
		break;
	case User::KEY_ICQ:
		profile_show_editing_bar_simple($key, 'ICQ#', $value);
		break;
	case User::KEY_SKYPE:
		profile_show_editing_bar_simple($key, 'Skype', $value);
		break;
	case User::KEY_VKID:
		profile_show_editing_vkid($value);
		break;
	case User::KEY_BIRTHDAY:
		profile_show_date_edit($key, 'День рождения', $value);
		break;
	}
?>

								<div class="clear"></div>
							</li>
<?
}

function profile_show_editing_bar_simple($key, $name, $value) {
?>

								<div class="label"><?=$name?>:</div>
								<div class="input"><input id="input_<?=$key?>" type="text" name="<?=$key?>" value="<?=$value?>" /></div>
								<div id="button_<?=$key?>" class="button">Сохранить</div>
								<script type="text/javascript">
									disabled['<?=$key?>'] = false;
									values['<?=$key?>'] = '<?=$value?>';
								</script>
<?
}

function profile_show_editing_none($key, $name, $value) {
?>

								<div class="label"><?=$name?>:</div>
								<div class="input"><?=$value?></div>
<?
}

function profile_show_editing_vkid($value) {
?>

								<div class="label">В Контакте:</div>
								<div class="input">
									<?=$value?>
									
								</div>
<?
}

function profile_show_date_edit($key, $name, $value) {
?>

								<div class="label"><?=$name?>:</div>
								<div class="input" id="input_<?=$key?>"></div>
								<script type="text/javascript">
									$$(function () {
										var <?=$key?> = new DateSelector({
											<?if ($value != null) {?>date: '<?=$value?>',<?}?>

											onSelect: function (date) {
												profile.save('<?=$key?>', date);
											},

											hideOnSelect: true,
											maxDate: {d: <?=date('j')?>, m: <?=date('n')?>, y: <?=date('Y')?>}
										});
										debug(<?=$key?>);
										<?=$key?>.appendTo($('#input_<?=$key?>'));
									});
								</script>
<?
}
?>