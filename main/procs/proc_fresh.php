<?php
/**
 * @author Artyom Grigoriev
 */

require_once dirname(__FILE__) . '/../classes/user/Auth.php';
require_once dirname(__FILE__) . '/../classes/user/User.php';

require_once dirname(__FILE__) . '/../classes/db/ContentViewDBClient.php';

require_once dirname(__FILE__) . '/../includes/assertion.php';
require_once dirname(__FILE__) . '/../includes/error.php';
require_once dirname(__FILE__) . '/../includes/common.php';

require_once dirname(__FILE__) . '/../views/life_view.php';

require_once dirname(__FILE__) . '/../includes/common.php';

define('START_TIME', microtime(true) * 1000);

try {
	$auth = new Auth();
	
	if (!$auth->isAuth()) {
		echo json(array (
			'status' => 'failed',
			'reason' => 'Access denied'
		));
		exit(0);
	}

	$uid  = $auth->uid();
    $ip   = $_SERVER['REMOTE_ADDR'];
    $file = dirname(__FILE__) . '/../temp/user_' . $uid;

    $array = file($file, FILE_IGNORE_NEW_LINES);
    if (!array_contains($array, $ip)) {
      file_put_contents($file, "$ip\n", FILE_APPEND);
    }

	$feed = life_get_day_feed(date("Y-m-d"));
	$life_count = 0;
	foreach ($feed as $items) {
		foreach ($items as $item) {
			if (
				$item instanceof Item &&
				array_contains(array(Item::BLOG_POST, Item::FORUM_TOPIC, Item::PHOTO, Item::VIDEO), $item->getType()) &&
				!$item->getLastViewFor($auth->uid())
			) {
				$life_count++;
			}
		}
	}

	echo json(array (
		'status' => 'ok',
		'forum' => ContentViewDBClient::countForumNewMessages($uid),
		'life' => $life_count,
		'process_time' => round(microtime(true) * 1000 - START_TIME)
	));

} catch (Exception $e) {
	echo_json_exception($e);
}

?>
