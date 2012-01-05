<?php

require_once 'classes/user/Auth.php';
require_once 'classes/user/User.php';

require_once 'includes/common.php';
require_once 'includes/log.php';

try {
	include 'includes/authorize.php';
	include 'views/header.php';
?>

<div id="media_container" class="body_container">
<?
	if (!issetParam('part')) {
		include 'views/media_main.php';
	} else {
		switch (param('part')) {
		case 'photo':
		case 'video':
			if (!issetParam('group_id')) {
				include 'views/media_albums.php';
			} elseif (!issetParam('item_id')) {
				include 'views/media_album.php';
			} else {
				include 'views/media_item.php';
			}
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