<?php

require_once dirname(__FILE__) . '/forum_view.php';

try {
	$part = new ForumPart($_REQUEST['part_id']);
	$forum = $part->getForum();
?>

	<div id="forum_part">
		<div class="title">
			<a href="/forum"><?=$forum->getTitle()?></a> &rsaquo;
			<?=$part->getTitle()?>
		</div>
		<div class="body" id="part_<?=$part->getId()?>">
			<? global $auth; forum_show_topics($auth->uid(), $part); ?>

		</div>
	</div>
<?
} catch (Exception $e) {
	// FIXME
	echo $e->getTraceAsString();
}
?>