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

foreach ($album2league as $albumId => $leagueId) {
	$album = Group::getById($albumId);
	$league = false;
	if ($leagueId < FAKE_ID) {
		$league = League::getById($leagueId);
	}
	media_show_album_cover($album, $league);
}

function media_show_album_cover(Group $group, $league = false) {
	$count = $group->countItems();
	$n = max(floor(sqrt($count)), 8);
	$items = $group->getItems(0, $n * $n, true, Item::CREATION);
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
				<tbody>
<?
	for ($i = 0; $i < $n; $i++)	{
?>
		
					<tr>
<?
		for ($j = 0; $j < $n; $j++) {
			$k = $i * $n + $j;
			$photo = $items[ $k ];
			if (!($photo instanceof Photo)) {
				global $LOG;
				@$LOG->error("Not photo item (id={$photo->getId()}) in photo_album (id={$group->getId()})");
			}

			echo '<td><div><img src="'.$photo->getUrl(Photo::SIZE_MINI).'"/></div></td>';
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