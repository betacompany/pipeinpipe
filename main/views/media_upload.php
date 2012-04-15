<?php

global $auth;
if (!$auth->isAuth()) {
	exit(0);
}

$subpart = param('subpart');
$subparts = array(
	'video_youtube' => 'Видео YouTube',
	'video_vkontakte' => 'Видео VK.com',
	'photo_files' => 'Фото из файлов',
	'photo_vkontakte' => 'Фото VK.com'
);
$icons = array(
	'video_youtube' => 'youtube.png',
	'video_vkontakte' => 'vk16.png',
	'photo_files' => '',
	'photo_vkontakte' => 'vk16.png'
);

?>

<?if (!$subpart):?>
<h1 class="other">Загружайте фото и видео с пайпом на наш сайт!</h1>
<p>
	Для этого выберите в меню ниже любой удобный для Вас способ:
</p>
<? endif; ?>

<div class="slide_block">
	<div class="tabs title" style="font-size: 1em;">
		<?foreach ($subparts as $href => $title):?>
	    <a href="/media/upload/<?=$href?>">
			<div class="tab<?=($subpart == $href ? " selected" : "")?>">
				<?if ($icons[$href]) {?><img style="width: 16px; margin-bottom: -3px;" src="/images/social/<?=$icons[$href]?>" alt=""/><? } ?>
				<?=$title?>
			</div>
		</a>
		<?endforeach;?>
		<div class="clear"></div>
	</div>
<?
if ($subpart) {
?>

	<div class="body">
<?
	switch ($subpart) {
	case 'video_youtube':
		echo 'YouTube';
		break;
	case 'video_vkontakte':
		echo 'Video VK';
		break;
	case 'photo_files':
		echo 'Photo Files';
		break;
	case 'photo_vkontakte':
		echo 'Photo VK';
		break;
	}
?>

	</div>
<?
}
?>

</div>