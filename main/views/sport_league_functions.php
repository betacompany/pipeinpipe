<?php
/**
 * @author Innokenty Shuvalov
 * @author Artem Grigoriev
 */
require_once dirname(__FILE__).'/../classes/cupms/RatingTable.php';
require_once dirname(__FILE__).'/../classes/cupms/Competition.php';
require_once dirname(__FILE__).'/../classes/cupms/League.php';

require_once dirname(__FILE__).'/blocks.php';
require_once dirname(__FILE__).'/life_view.php';

function league_show_competition_preview_full(Competition $competition, $pmCount) {
?>
<div class="competition_preview_full round_border">
	<a href="<?=$competition->getURL()?>">
		<div>
			<img alt="<?=$competition->getName()?>" src="<?=$competition->getImageURL(Competition::IMAGE_SMALL)?>"/>
<?
	if ($competition->isRunning() || $competition->isRegistering()) {
?>
			<img class="competition_status" alt="<?=$competition->getStatus()?>" src="<?=$competition->getStatusImageURL()?>"/>
<?
	}
?>
			<div>
				<div><?=$competition->getName()?></div>
				<div><?=string_short($competition->getDescription(), 90, 130)?></div>
			</div>
			<div class="small">
<?
	if($competition->isFinished()) {
?>
				<div>Дата завершения: <?=$competition->getDate()?></div>
<?
	} else if ($competition->isRunning()) {
?>
				<div>Турнир проходит в настоящее время</div>
<?
	} else if ($competition->isRegistering()) {
?>
				<div>На турнир открыта онлайн-регистрация</div>
<?
	}
?>
				<div>Количество участников: <?=$pmCount?></div>
			</div>
		</div>
	</a>
</div>
<?
}

function league_show_competition_preview_short(Competition $competition) {
?>
<div class="competition_preview_short round_border">
	<a href="<?=$competition->getURL()?>">
		<div>
			<img class="competition_image"
                alt="<?=$competition->getName()?>"
                title="<?=$competition->getName()?>"
                src="<?=$competition->getImageURL(Competition::IMAGE_SMALL)?>"/>
<?
	if ($competition->isRunning() || $competition->isRegistering()) {
?>
			<img class="competition_status" alt="<?=$competition->getStatus()?>" src="<?=$competition->getStatusImageURL()?>"/>
<?
	}
?>
		</div>
	</a>
</div>
<?
}

function league_show_competitions(League $league, $page = 1) {
	$compPage = $league->getCompetitionsChronologically();
	$count = count($compPage);
	$limit = 5;
	$first = ($page - 1) * $limit + 1;
	$last = $first + $limit - 1;
	$leagueId = $league->getId();
?>
<div class="title opened">
	<div class="left">
		<div class="content">
			Турниры
		</div>
	</div>
	<div class="right">
		<div class="info">
			<div>всего турниров: <?=$count?> (<?=$first?>-<?=$last?>)</div>
<?
	show_paging_bar($count, $limit, $page, 'league.loadCompetitionsPage(%d, ' . $leagueId . ')');
?>
		</div>
		<div class="quick" onclick="javascript: slideBlock.togglePart('league_competitions')"></div>
	</div>
	<div style="clear: both"></div>
</div>
<div class="body hidden" style="display: block">
<?
	$compPage = array_slice($compPage, ($page - 1) * $limit, $limit);
	$pmCount = Competition::getPmCount();
	foreach ($compPage as $competition) {
		$compId = $competition->getId();
		league_show_competition_preview_full($competition, $pmCount[$compId]);
	}
?>
</div>
<?
}

function league_show_rating(League $league, $page = 1) {
    $NUM_PLAYERS_PER_PAGE = 30;

    $leagueId = $league->getId();
    $data = RatingTable::getInstance($leagueId)->getData();
    $count = count($data);
	$first = min(($page - 1) * $NUM_PLAYERS_PER_PAGE + 1, $count);
	$last = min($first + $NUM_PLAYERS_PER_PAGE - 1, $count);
?>
<div class="title opened">
	<div class="left">
		<div class="content">
			<a href="/sport/rating#league=<?=$league->getId()?>">Пайпмены</a>
		</div>
	</div>
	<div class="right">
		<div class="info">
			<div>всего: <?=$count?> (<?=$first?>-<?=$last?>)</div>
<?
	show_paging_bar($count, $NUM_PLAYERS_PER_PAGE, $page, 'league.loadPipemenPage(%d, ' . $leagueId . ')');
?>
		</div>
	</div>
	<div style="clear: both"></div>
</div>
<div class="body hidden" style="display: block">
<?
	$dataPage = array_slice($data, ($page - 1) * $NUM_PLAYERS_PER_PAGE, $NUM_PLAYERS_PER_PAGE);
	league_show_rating_list($dataPage, $first);
?>
</div>
<?
}

function league_show_rating_list($data, $place) {
?>
<ul id="rating">
<?
	foreach ($data as $playerInfo) {
?>
	<li>
		<div class="place<?=($place >= 10 ? ($place >= 100 ? ' supersmall' : ' small') : '')?>"><?=$place?></div>
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
		$place++;
	}
?>
</ul>
<?
}

function league_show_admins_list(League $league) {
?>
<div id="admins_list">
<?
	foreach ($league->getAdmins() as $user) {
		//$permission = $user->isTotalAdmin() ? 'главный администратор' : 'администратор лиги';
		$uid = $user->getId();
?>

	<div>
		<a href="/id<?=$uid?>" target="_blank">
			<img alt="user#<?=$uid?>" src="<?=$user->getImageURL(User::IMAGE_SQUARE)?>"/>
			<div>
				<div><?=$user->getSurname()?></div>
				<div><?=$user->getName()?></div>
			</div>
		</a>
		<!--<div class="small"><?//=$permission?></div>-->
	</div>
<?
	}
?>
</div>
<div style="clear: both"></div>
<?
}

require_once dirname(__FILE__).'/../classes/content/Connection.php';
require_once dirname(__FILE__).'/../classes/content/Group.php';

require_once dirname(__FILE__).'/../classes/blog/Blog.php';
require_once dirname(__FILE__).'/../classes/blog/BlogPost.php';

function league_show_news(League $league) {
	global $user;
	$posts = array_reverse(Connection::getTaggedTypifiedContentItemsFor($league, Item::BLOG_POST));
?>

				<div id="league_news" class="slide_block">
					<div class="title opened">
						<div class="left">
							<div class="content">
								Новости лиги
							</div>
						</div>
						<div class="right">
							<div class="info">
								<div><?=lang_number_sclon(count($posts), 'пост', 'поста', 'постов')?></div>
							</div>
							<div class="quick" onclick="javascript: slideBlock.togglePart('league_news')"></div>
						</div>
						<div style="clear: both"></div>
					</div>
					<div class="body hidden" style="display: block">
<?
	life_show_posts(array_slice($posts, 0, 5), $user, false, false);
?>

						<div style="clear: both"></div>
					</div>
				</div>

<?
}

function league_show_photos(League $league) {
	$photos = array_reverse(Connection::getTaggedTypifiedContentItemsFor($league, Item::PHOTO));
?>
				<div id="league_photos" class="slide_block">
					<div class="title opened">
						<div class="left">
							<div class="content">
								Фотографии
							</div>
						</div>
						<div class="right">
							<div class="info">
								<div><?=lang_number_sclon(count($photos), 'фотография', 'фотографии', 'фотографий')?></div>
							</div>
							<div class="quick" onclick="javascript: slideBlock.togglePart('league_photos')"></div>
						</div>
						<div style="clear: both"></div>
					</div>
					<div class="body hidden" style="display: block">
						<div class="photos" style="width: <?=count($photos) * 80?>px;">
<?
	foreach ($photos as $photo) {
?>

							<a href="/media/photo/album<?=$album->getId()?>/<?=$photo->getId()?>"><img src="<?=$photo->getPreviewUrl()?>" alt="<?=$photo->getTitle()?>" /></a>
<?
	}
?>

						</div>
						<script type="text/javascript">
							$$(function () {
								$('#league_photos .photos').draggable({
									axis: 'x',
									cursor: 'e-resize',
									drag: function(e, ui) {}
								});
								preventSelection(ge('league_photos'));
							});
						</script>
						<div style="clear: both"></div>
					</div>
				</div>
<?
}

function league_show_videos(League $league) {
	$videos = array_reverse(Connection::getTaggedTypifiedContentItemsFor($league, Item::VIDEO));
?>
				<div id="league_videos" class="slide_block">
					<div class="title opened">
						<div class="left">
							<div class="content">
								Видеозаписи
							</div>
						</div>
						<div class="right">
							<div class="info">
								<div><?=lang_number_sclon(count($videos), 'видеозапись', 'видеозаписи', 'видеозаписей')?></div>
							</div>
							<div class="quick" onclick="javascript: slideBlock.togglePart('league_videos')"></div>
						</div>
						<div style="clear: both"></div>
					</div>
					<div class="body hidden" style="display: block">
						<div class="videos" style="width: <?=count($videos) * 80?>px;">
<?
	foreach ($videos as $video) {
?>

							<a href="/media/video/album<?=$album->getId()?>/<?=$video->getId()?>" title="<?=$video->getTitle()?>">
								<div class="video" style="background-image: url('<?=$video->getPreviewUrl()?>');">
									<div></div>
								</div>
							</a>
<?
	}
?>

						</div>
						<script type="text/javascript">
							$$(function () {
								$('#league_videos .videos').draggable({
									axis: 'x',
									cursor: 'e-resize',
									drag: function(e, ui) {}
								});
								preventSelection(ge('league_videos'));
							});
						</script>
						<div style="clear: both"></div>
					</div>
				</div>
<?
}
?>
