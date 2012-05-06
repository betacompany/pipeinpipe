<?php

require_once dirname(__FILE__) . '/../includes/import.php';
require_once dirname(__FILE__) . '/../views/tag_cloud.php';

define("FAKE_ID", 1000000);

import('content/Group');
import('content/Connection');
import('cupms/League');

$albums = array();
switch (param('part')) {
case 'photo':
	$albums = Group::getRootsByType(Group::PHOTO_ALBUM, true);
	break;
}

$album2league = array();
foreach ($albums as $album) {
	$album2league[ $album->getId() ] = FAKE_ID;
}

$leagues = League::getAll();
foreach ($leagues as $league) {
	$leagueGroups = Connection::getTypifiedContentGroupsFor($league, Group::PHOTO_ALBUM);
	foreach ($leagueGroups as $group) {
		$album2league[ $group->getId() ] = $league->getId();
	}
}

asort($album2league);

$tags = Tag::getAllByType(Item::PHOTO);

tag_cloud_show($tags);

$view = array();
foreach ($album2league as $albumId => $leagueId) {
	$album = Group::getById($albumId);
	$league = false;
	if ($leagueId < FAKE_ID) {
		$league = League::getById($leagueId);
	}
	
	$view[] = array($album, $league);
}

$c = count($view);
?>

<div id="media_container">
	<table class="wrap">
		<tbody>
			<tr>
				<td style="vertical-align: top;">
<?
for ($i = 0; $i < $c; $i += 3) {
	media_show_album_cover($view[$i][0], $view[$i][1]);
}
?>
				</td>
				<td style="vertical-align: top;">
<?
for ($i = 1; $i < $c; $i += 3) {
	media_show_album_cover($view[$i][0], $view[$i][1]);
}
?>
				</td>
				<td style="vertical-align: top;">
<?
for ($i = 2; $i < $c; $i += 3) {
	media_show_album_cover($view[$i][0], $view[$i][1]);
}
?>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<script type="text/javascript">
	$$(function () {
		$('.cover td div img').hover(
			function () {
				var t = $(this),
					w = t.innerWidth(),
					h = t.innerHeight();
				t.animate({
					marginLeft: (2 + Math.random()) * (75 - w) / 3,
					marginTop: (2 + Math.random()) * (75 - h) / 3
				}, "slow");
			},
			function () {
				$(this).animate({
					marginLeft: -5,
					marginTop: -5
				}, "slow");
			}
		);
	});
</script>
<?

function media_show_album_cover(Group $group, $league = false) {
	$count = $group->countItems();
	$m = 4;
	$n = max(1, min(floor($count / $m), $m));
	$items = Item::getByRating(Item::PHOTO, $n * $m, $group->getId());
	$additionClass = "";
	if ($n == 1) {
		$additionClass = " n1";
	} elseif ($n > 4) {
		$additionClass = " nn";
	}
?>

	<a href="/media/photo/album<?=$group->getId()?>">
		<div class="cover<?=$additionClass?>">
			<table>
				<thead>
					<th colspan="<?=$m?>">
						<div>
							<?if ($league):?>
							<a href="/sport/league/<?=$league->getId()?>" title="<?=$league->getName()?>"><img src="<?=$league->getImageUrl(League::IMAGE_SMALL)?>"/></a>
							<?endif;?>
							<?=$group->getTitle()?>
						</div>
					</th>
				</thead>
				<tbody>
<?
	for ($i = 0; $i < $n; $i++)	{
?>
		
					<tr>
<?
		for ($j = 0; $j < $m; $j++) {
			$k = $i * $n + $j;
			if ($k < $count) {
				$photo = $items[ $k ];
				if (!($photo instanceof Photo)) {
					global $LOG;
					@$LOG->error("Not photo item (id={$photo->getId()}) in photo_album (id={$group->getId()})");
					continue;
				}
				echo '<td><div><img src="'.$photo->getUrl(Photo::SIZE_MINI).'"/></div></td>';
			} else {
				echo '<td><div></div></td>';
			}
		}
?>
					
					</tr>
<?
	}
?>
				</tbody>
			</table>
		</div>
	</a>
<?
}

?>
