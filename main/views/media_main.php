<?php

require_once dirname(__FILE__) . '/../classes/content/Group.php';
require_once dirname(__FILE__) . '/../classes/content/Item.php';
require_once dirname(__FILE__) . '/../classes/media/Photo.php';
require_once dirname(__FILE__) . '/../classes/media/Video.php';

require_once dirname(__FILE__) . '/media_view.php';

global $auth;
$uid = $auth->uid();

try {

	$best_photos = Photo::getAllByRating(10);
	$best_videos = Video::getAllByRating(10);

?>


<div id="media_container" class="body_container">

<div class="media_album_preview">
	<h1>Лучшие <a href="/media/photo">фотографии</a></h1>
	<div class="preview">
<?
	media_show_preview_items_array($uid, $best_photos);
?>

	</div>
<?
	media_script_album();
?>

</div>

<div class="media_album_preview">
	<h1>Лучшие <a href="/media/video">видеозаписи</a></h1>
	<div class="preview" style="padding: 20px;">
<?
	media_show_preview_items_array($uid, $best_videos);
?>

	</div>
</div>

<?
	media_slideshow_block();
?>

</div>

<script type="text/javascript">
	$$(function () {
		media.onClose = function () {
			media.clearSlideShow();
		};
	})
</script>

<?
} catch (Exception $e) {
	global $LOG;
	@$LOG->exception($e);
}

?>
