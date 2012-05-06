<?php
require_once dirname(__FILE__) . '/tag_creator.php';

global $auth;
if (!$auth->isAuth()) {
	exit(0);
}
$user = $auth->getCurrentUser();
$accessToken = $user->getAccessToken();

$subpart = param('subpart');
$subparts = array(
	'video_youtube' => 'Видео YouTube',
//	'video_vkontakte' => 'Видео VK.com',
//	'photo_files' => 'Фото из файлов',
	'photo_vkontakte' => 'Фото VK.com'
);
$icons = array(
	'video_youtube' => 'youtube.png',
//	'video_vkontakte' => 'vk16.png',
//	'photo_files' => '',
	'photo_vkontakte' => 'vk16.png'
);

?>

<div id="media_container" class="body_container">
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
                <td><input id="video_title" class="disabled" name="video_title" type="text" disabled="disabled"></td>
            </tr>
            <tr>
                <td><label for="video_link">Вставьте ссылку на видео:</label></td>
                <td><input id="video_link" name="video_link" type="text"></td>
            </tr>
            <tr>
                <td></td>
                <td><img id="video_preview"></td>
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
            <tr>
                <td><label for="video_tags">Добавьте теги:</label></td>
                <td>
                    <input id="video_tags" name="video_tags" type="hidden">
<?
        tag_creator_show();
?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <div id="video_upload_btn" class="button disabled" onclick="javascript: TagCreator.fillFormTagsInput('input#video_tags')">Загрузить</div>
                </td>
            </tr>
            </tbody>
        </form>
    </table>
    <script type="text/javascript">
        $(document).ready(function () {
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
		if (!$user->getVkid()) {
			echo "Добавьте привязку к ВКонтакте на странице <a href=\"/profile/edit\">редактирования Вашего профиля</a>";
			break;
		}
?>
    <div id="vk_photos_selector_container">
        <table id="vk_photos_selector" class="vk_photos">
            <thead>
            <tr>
                <th id="vk_available_photos_title"></th>
                <th id="vk_selected_photos_title" colspan="2"></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td id="vk_available_photos" class="vk_photos"></td>
                <td id="vk_selected_photos" class="vk_photos"></td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="vk_photos_controls">
        <div id="vk_photos_controls_back" class="button disabled">&larr; Назад</div>
        <div id="vk_photos_controls_all" class="button disabled">Выбрать все &rarr;</div>
        <div class="clear"></div>
    </div>

    <div id="vk_photos_options">
        <table class="vk_photos">
            <tbody>
            <tr id="vk_photos_group">
                <td>
                    <div>Выберите альбом</div>
                </td>
                <td>
                    <select id="vk_photos_group_id">
<?
                        $groups = Group::getAllByType(Group::PHOTO_ALBUM);
                        foreach ($groups as $group) {
?>
                        <option value="<?=$group->getId()?>"><?=$group->getTitle()?></option>
<?
                        }
?>
                    </select>
                </td>
            <tr>
            <tr id="vk_photos_tag_creator">
                <td>Добавьте теги</td>
                <td>
                    <?
                    tag_creator_show();
                    ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <div id="vk_photos_controls_upload" class="button disabled">Загрузить ▲</div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            var vkAuthPopupOptions = {
                url: 'http://oauth.vk.com/authorize?client_id=<?=Vkontakte::VK_APP_ID?>' +
                    '&scope=wall,friends,offline,notes,photos' +
                    '&redirect_uri=http%3A%2F%2F<?=MAIN_SITE_URL?>%2Fprocs%2Fproc_vk_access.php' +
                    '&response_type=code',
                windowName: 'VK Authorization',
                windowFeatures: 'width=800,height=500,location=yes,menubar=no,left=100,top=100'
            }
            media.vk.photos.init('<?=$accessToken?>', vkAuthPopupOptions);
        });
    </script>
<?
        break;
	}
?>

	</div>
<?
}
?>

</div>
</div>

