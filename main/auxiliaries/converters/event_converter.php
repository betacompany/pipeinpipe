<?php
/**
 * @author Artyom Grigoriev
 */

require_once '../../includes/mysql.php';
require_once 'converter_library.php';

require_once '../../classes/life/Event.php';
require_once '../../classes/blog/BlogPost.php';

echo '<pre>';

mysql_qw('DELETE FROM `p_content_item` WHERE `type`=\'event\'');
echo "Events deleted from DB\n";
flush();

mysql_qw('DELETE FROM `p_content_item` WHERE `type`=\'blog_post\' AND `group_id`=0');
echo "Blogs with group_id=0 deleted from DB\n";
flush();

$blogId = mysql_result(
	mysql_qw('SELECT `id` FROM `pv_content_group` WHERE `type`=\'blog\' ORDER BY `id` ASC'), 0, 0
);

$req = mysql_qw('SELECT * FROM `pipe_calendar` ORDER BY `id` ASC');
while ($ev = mysql_fetch_assoc($req)) {
	$time = strtotime($ev['date']);
	if ($ev['type'] == 2 || $ev['type'] == 3) {
		$event = Event::create(date("Y-m-d", $time), '12:00', $ev['short'], $ev['text'], $ev['authorid']);
		echo 'Event ' . $event->getId() . " created\n";
		flush();
	} elseif ($ev['type'] == 1) {
		$post = BlogPost::create($blogId, $ev['authorid'], $ev['short'], $ev['text'], $ev['text'], $time);
		echo 'Post ' . $post->getId() . " created\n";
		flush();
	} else {
		echo 'Unknown type in ' . $ev['id'] . "\n";
	}
}

echo '</pre>';

?>
