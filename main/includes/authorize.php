<?php

require_once dirname(__FILE__) . '/../classes/user/User.php';
require_once dirname(__FILE__) . '/../classes/user/Auth.php';

require_once dirname(__FILE__) . '/config-local.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

$use_mobile = $auth->sessionGet(Auth::KEY_USE_MOBILE_SESSION);

if ($_SERVER['HTTP_HOST'] != MOBILE_SITE_URL && $auth->isMobile()) {

	$mobile_paths = array(
		'/sport.php' => array(
			'league' => array('league_id', '/sport_league/'),
			'competition' => array('comp_id', '/sport_competition/'),
			'rating' => array('', '/sport_rating/1'),

			'default' => '/sport'
		),

		'/forum.php' => array(
			'part' => array('part_id', '/forum_part/'),
			'topic' => array('topic_id', '/forum_topic/'),

			'default' => '/forum'
		)
	);

	$path = "/";

	$parts = $mobile_paths[$_SERVER['SCRIPT_NAME']];
	if ($parts) {
		$path = $parts['default'];

		$a = $parts[param('part')];
		if (!$a) {
			$a = $parts[param('forum_action')];
		}
		if ($a) {
			$id = intParam($a[0]);
			if ($a[0] === '') {
				$path = $a[1];
			} elseif ($id > 0) {
				$path = $a[1] . $id;
			}
		}
	}

	$mobile_url = 'http://' . MOBILE_SITE_URL . $path;
	if (isset($use_mobile)) {
		if ($use_mobile == '1') {
			Header('Location: ' . $mobile_url);
			exit(0);
		}
	} else {
		include dirname(__FILE__) . '/../views/mobile_redirect.php';
		exit(0);
	}

}

?>
