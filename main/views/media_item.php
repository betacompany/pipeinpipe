<?php

require_once dirname(__FILE__) . '/blocks.php';
require_once dirname(__FILE__) . '/media_view.php';

require_once dirname(__FILE__) . '/../classes/media/Photo.php';
require_once dirname(__FILE__) . '/../classes/media/Video.php';

require_once dirname(__FILE__) . '/../classes/content/Group.php';
require_once dirname(__FILE__) . '/../classes/content/Item.php';
require_once dirname(__FILE__) . '/../classes/content/Comment.php';

global $auth;
global $user;
$uid = $auth->uid();

try {
?>

<div id="media_container" class="body_container">
<?
	$item = Item::getById(intparam('item_id'));
	
	if ($item instanceof Photo) {

		media_show_photo($uid, $item);
		media_slideshow_block();

	} elseif ($item instanceof Video) {

		media_show_video($uid, $item);

		if ($user != null) {
			$item->viewedBy($user);
		}

		// photos are viewed in groups
	} else {
		echo 'Item type is not photo or video';
	}
?>

</div>
<?

} catch (Exception $e) {
	global $LOG;
	$LOG->exception($e);
}

?>
