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
<p>Для этого выберите любой удобный для Вас способ:</p>
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
?>
        <table id="video_uploader">
            <form method="post" action="/procs/proc_media_uploader.php">
            <input name="method" type="hidden" value="youtube">
            <tbody>
                <tr>
                    <td><label for="video_title">Название:</label></td>
                    <td><input id="video_title" name="video_title" type="text"></td>
                </tr>
                <tr>
                    <td><label for="video_link">Вставьте ссылку на видео:</label></td>
                    <td><input id="video_link" name="video_link" type="text" onchange=""></td>
                </tr>
                <tr>
                    <td><label for="group_id">Выберите альбом</label></td>
                    <td>
                        <select id="group_id" name="group_id">
<?
        $groups = Group::getAllByType(Group::VIDEO_ALBUM);
        foreach ($groups as $group) {
?>
                            <option value="<?=$group->getId()?>"><?=$group->getTitle()?></option>
<?
        }
?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td><div id="video_upload_btn" class="button disabled">Загрузить</div></td>
                </tr>
            </tbody>
            </form>
        </table>
        <script type="text/javascript">
            $(document).ready(function() {
                media.youtube.init();
            });
        </script>
<?
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
