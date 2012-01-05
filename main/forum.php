<?php

require_once 'classes/user/Auth.php';
require_once 'classes/user/User.php';

require_once 'classes/forum/Forum.php';
require_once 'classes/forum/ForumException.php';

require_once 'includes/log.php';

try {
	include 'includes/authorize.php';
	include 'views/header.php';
?>

<div id="forum_container">
<?
	if (!isset($_REQUEST['part_id'])) {
		include 'views/forum_main.php';
	} elseif (!isset($_REQUEST['topic_id'])) {
		include 'views/forum_part.php';
	} else {
		include 'views/forum_topic.php';
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