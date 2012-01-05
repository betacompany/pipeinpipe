<?php

require_once '../../includes/mysql.php';
require_once '../../includes/config-local.php';

require_once 'converter_library.php';

require_once '../../classes/media/Video.php';
require_once '../../classes/content/Comment.php';
require_once '../../classes/content/Group.php';
require_once '../../classes/content/Action.php';
require_once '../../classes/user/User.php';

echo "<pre>";

mysql_qw('DELETE FROM `p_content_group` WHERE `type`=\'video_album\'');
echo "Albums deleted from DB\n";
flush();

mysql_qw('DELETE FROM `p_content_comment` WHERE `item_id` IN (SELECT `id` FROM `p_content_item` WHERE `type`=\'video\')');
echo "Comments for videos deleted from DB\n";
flush();

mysql_qw('DELETE FROM `p_content_action` WHERE `target_type`=\'item\' AND `target_id` IN (SELECT `id` FROM `p_content_item` WHERE `type`=\'video\')');
echo "Actions for videos deleted from DB\n";
flush();

mysql_qw('DELETE FROM `p_content_item` WHERE `type`=\'video\'');
echo "Videos deleted from DB\n";
flush();

$aii = mysql_result(mysql_qw('SELECT MAX(`id`) + 1 FROM `p_content_item`'), 0, 0);
mysql_qw('ALTER TABLE  `p_content_item` AUTO_INCREMENT=' . $aii);
echo "Auto increment for `p_content_item` set to value of $aii\n";
flush();

$aig = mysql_result(mysql_qw('SELECT MAX(`id`) + 1 FROM `p_content_group`'), 0, 0);
mysql_qw('ALTER TABLE  `p_content_group` AUTO_INCREMENT=' . $aig);
echo "Auto increment for `p_content_group` set to value of $aig\n\n";
flush();

$albums = array();

$req = mysql_qw('SELECT * FROM `pipe_videos` ORDER BY `id` ASC');
while ($v = mysql_fetch_assoc($req)) {
	$v['part'] = empty($v['part']) ? 'Сопутствующие видео' : $v['part'];
	$hash = md5($v['part']);
	$albumid = 0;
	if (isset($albums[$hash])) {
		$albumid = $albums[$hash];
	} else {
		$album = Group::create(Group::VIDEO_ALBUM, 0, $v['part'], '', '');
		$albumid = $album->getId();
		echo "Group id=$albumid created\n";
		$albums[$hash] = $albumid;
	}

	$video = Video::create($albumid, $v['uid'], $v['name'], $v['code']);
	echo "Video " . $v['id'] . " has now id=" . $video->getId() . "\n";
	flush();

	$reqc = mysql_qw('SELECT * FROM `pipe_comments` WHERE `type`=6 AND `cid`=?', $v['id']);
	while ($com = mysql_fetch_assoc($reqc)) {
		$comment = $video->addComment(Comment::BASIC_COMMENT, $com['authorid'], 0, $com['text'], '');
		echo "\tcomment ".$com['id']." added with id=".$comment->getId() . "\n";
		flush();
	}

	$reqe = mysql_qw('SELECT * FROM `pipe_ratings` WHERE `type`=\'video\' AND `cid`=?', $v['id']);
	while ($ev = mysql_fetch_assoc($reqe)) {
		$e = $video->act(User::getById($ev['uid']), Action::EVALUATION, $ev['note']);
		echo "\tEvaluation " . $ev['id'] . " added with id=" . (!$e ? 'error' : $e->getId()) . "\n";
	}

	if (file_exists('../../content/videos/' . $v['id'] . '.jpg')) {
		rename('../../content/videos/' . $v['id'] . '.jpg', '../../content/videos/' . $video->getId() . '.jpg');
	}
}

echo "</pre>"

?>
