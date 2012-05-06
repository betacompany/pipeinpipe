<?php

require_once 'classes/user/Auth.php';
require_once 'classes/user/User.php';

require_once 'includes/common.php';
require_once 'includes/log.php';

try {
	include 'includes/authorize.php';
	include 'views/header.php';

	if (!issetParam('part')) {
		include 'views/media_main.php';
	} else {
		switch (param('part')) {
		case 'photo':
		case 'video':
			if (!issetParam('group_id')) {
				$part = param('part');
				if ($part == 'photo') {
					include 'views/media_photo_albums.php';
				} elseif ($part == 'video') {
					//include 'views/media_video_albums.php';
					include 'views/media_albums.php';
				}
			} elseif (!issetParam('item_id')) {
				$part = param('part');
				if ($part == 'photo') {
					$album = Group::getById(intparam('group_id'));
					$photos = $album->getItems();
					include 'views/media_photo_viewer.php';
				} else {
					include 'views/media_album.php';
				}
			} else {
				$part = param('part');
				if ($part == 'photo') {
					$album = Group::getById(intparam('group_id'));
					$photos = $album->getItems();
					$itemId = intparam('item_id');
					include 'views/media_photo_viewer.php';
				} else {
					include 'views/media_item.php';
				}
			}
			break;
		case 'upload':
			include 'views/media_upload.php';
			break;
		case 'download':
			include 'static/media_download.xhtml';
			break;
		}


	}
?>

</div>
<?
	include 'views/footer.php';
} catch (Exception $e) {
	global $LOG;
	$LOG->exception($e);
}

?>