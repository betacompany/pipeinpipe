<?php

require_once '../../includes/mysql.php';
require_once '../../includes/config-local.php';

require_once 'converter_library.php';

require_once '../../classes/media/Photo.php';
require_once '../../classes/content/Comment.php';
require_once '../../classes/content/Action.php';
require_once '../../classes/user/User.php';

$PF = dirname(__FILE__) . '/../../content/photos';

if (!isset($_REQUEST['lower-bound'])) {
	echo 'Lower bound is not set';
	exit(0);
}

$lower = $_REQUEST['lower-bound'];
$upper = isset($_REQUEST['upper-bound']) ? $_REQUEST['upper-bound'] : $lower;

echo "<pre>\n";

$req = mysql_qw('SELECT * FROM `pipe_albums` WHERE `id`>=? and `id`<=? ORDER BY `id`', $lower, $upper);

while ($album = mysql_fetch_assoc($req)) {
	mysql_qw('INSERT INTO `p_content_group` SET `type`=?, `title`=?, `content_source`=?, `content_parsed`=?',
			'photo_album', $album['name'], $album['desсription'], $album['desсription']);

	echo "\nALBUM: ";
	print_r($album);
	echo "\n";
	$albumId = mysql_insert_id();

	$reqp = mysql_qw('SELECT * FROM `pipe_photos` WHERE `albumid`=?', $album['id']);

	while ($photo = mysql_fetch_assoc($reqp)) {
		$pid = $photo['id'];
		$pfile_main = dirname(__FILE__) . '/../../../' . FOLDER_OLD_SITE . '/photos/' . $pid . '.jpg';
		if (file_exists($pfile_main)) {
			$p = Photo::create($albumId, 69, $photo['title'], array());
			$pid2 = $p->getId();
			echo "Photo $pid now is photo $pid2\n";
			flush();

			copy($pfile_main, "$PF/$pid2.jpg");
			echo "\tFile $pid2.jpg created";
			$im = imagecreatefromjpeg($pfile_main);
			$size = imagesx($im) . 'x' . imagesy($im);
			imagedestroy($im);
			$p->addUrl($size, "/content/photos/$pid2.jpg", false);
			echo "\tURL /content/photos/$pid2.jpg added\n";
			flush();

			$pfile_hq = dirname(__FILE__) . '/../../../' . FOLDER_OLD_SITE . '/photos/' . $pid . '_hq.jpg';
			if (file_exists($pfile_hq)) {
				copy($pfile_hq, "$PF/$pid2"."_hq.jpg");
				echo "\tFile $pid2"."_hq.jpg created";
				$im = imagecreatefromjpeg($pfile_hq);
				$size = imagesx($im) . 'x' . imagesy($im);
				imagedestroy($im);
				$p->addUrl($size, "/content/photos/$pid2"."_hq.jpg", false);
				echo "\tURL /content/photos/$pid2"."_hq.jpg added\n";
			} else {
				echo "\tHQ variant does not exists\n";
			}
			flush();

			$pfile_micro = "http://ortemij:7935649@pipeinpipe.info/picture/50_$pid.jpg";
			//if (file_exists($pfile_micro)) {
				copy($pfile_micro, "$PF/$pid2"."_micro.jpg");
				echo "\tFile $pid2"."_micro.jpg created";
				$im = imagecreatefromjpeg($pfile_micro);
				$size = imagesx($im) . 'x' . imagesy($im);
				imagedestroy($im);
				$p->addUrl($size, "/content/photos/$pid2"."_micro.jpg", false);
				echo "\tURL /content/photos/$pid2"."_micro.jpg added\n";
			//} else {
			//	echo "\tMicro variant does not exists\n";
			//}
			flush();

			$pfile_mini = "http://ortemij:7935649@pipeinpipe.info/picture/150_$pid.jpg";
			//if (file_exists($pfile_mini)) {
				copy($pfile_mini, "$PF/$pid2"."_mini.jpg");
				echo "\tFile $pid2"."_mini.jpg created";
				$im = imagecreatefromjpeg($pfile_mini);
				$size = imagesx($im) . 'x' . imagesy($im);
				imagedestroy($im);
				$p->addUrl($size, "/content/photos/$pid2"."_mini.jpg", false);
				echo "\tURL /content/photos/$pid2"."_mini.jpg added\n";
			//} else {
			//	echo "\tMini variant does not exists\n";
			//}
			flush();

			$p->update();

			$reqc = mysql_qw('SELECT * FROM `pipe_comments` WHERE `type`=3 AND `cid`=?', $pid);
			while ($com = mysql_fetch_assoc($reqc)) {
				$c = $p->addComment(Comment::BASIC_COMMENT, $com['authorid'], 0, $com['text'], '');
				echo "\tComment " . $com['id'] . " added with id=" . $c->getId() . "\n";
			}

			$reqe = mysql_qw('SELECT * FROM `pipe_ratings` WHERE `type`=\'photo\' AND `cid`=?', $pid);
			while ($ev = mysql_fetch_assoc($reqe)) {
				$e = $p->act(User::getById($ev['uid']), Action::EVALUATION, $ev['note']);
				echo "\tEvaluation " . $ev['id'] . " added with id=" . (!$e ? 'error' : $e->getId()) . "\n";
			}
		} else {
			echo "[WARN] $pfile_main does not exists\n";
		}

		flush();
	}
}

echo '</pre>';

?>
