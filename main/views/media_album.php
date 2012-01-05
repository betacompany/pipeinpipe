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
	$group = Group::getById(intparam('group_id'));

	if ($user != null && $group->getType() == Group::PHOTO_ALBUM) {
		$group->viewedBy($user);
		// videos are viewed separately
	}

?>

	<div class="media_album_preview">
		<h1><?=$group->getTitle()?></h1>
		<div class="description">
			<?=$group->getContentParsed()?>

		</div>
		<div class="preview">
			<? media_show_preview_items($uid, $group, 0, 20); ?>

		</div>
		<? media_script_album(); ?>

	</div>

<?
	media_slideshow_block();

} catch (Exception $e) {
	global $LOG;
	$LOG->exception($e);
}

?>
