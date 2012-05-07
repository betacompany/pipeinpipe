<?php

require_once dirname(__FILE__) . '/../classes/content/Group.php';

global $auth;

$albums = array ();
switch (param('part')) {
case 'photo':
	$albums = Group::getRootsByType(Group::PHOTO_ALBUM, true);
	break;
case 'video':
	$albums = Group::getRootsByType(Group::VIDEO_ALBUM, true);
	break;
}
?>

<div id="media_container" class="body_container">
	<h1><?=(param('part') == 'photo' ? 'Фотогалерея' : 'Видеогалерея')?></h1>

<?

foreach ($albums as $album) {
	$lastItems = $album->getItems(0, 10, true);
?>
	<div class="media_album <?=$album->getType();?>" id="album_<?=$album->getId();?>">
		<div class="title">
			<a href="/media/<?=param('part')?>/album<?=$album->getId()?>"><?=$album->getTitle()?></a>
		</div>
		<div class="description">
			<?=$album->getContentParsed()?>
		</div>
		<div class="preview">
<?
	foreach ($lastItems as $item) {
		if ($item instanceof Photo) {
?>

			<a href="/media/<?=param('part')?>/album<?=$album->getId()?>/<?=$item->getId()?>" title="<?=$item->getTitle()?>">
				<img class="<?=$item->getType()?>" src="<?=$item->getPreviewUrl()?>" alt="<?=$item->getTitle()?>" />
			</a>
<?
		} elseif ($item instanceof Video) {
?>

			<a href="/media/<?=param('part')?>/album<?=$album->getId()?>/<?=$item->getId()?>" title="<?=$item->getTitle()?>">
				<div class="<?=$item->getType()?>" style="background-image: url('<?=$item->getPreviewUrl()?>');">
					<div></div>
				</div>
			</a>
<?
		} else {
			global $LOG;
			$LOG->warn('Album ' + $album->getId() + ' contains not only photos or videos!');
		}
	}
?>

		</div>
	</div>
<?
}
?>
	
</div>

