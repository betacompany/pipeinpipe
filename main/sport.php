<?php

require_once 'classes/user/Auth.php';
require_once 'classes/user/User.php';

require_once 'includes/log.php';

try {
	include 'includes/authorize.php';
	include 'views/header.php';
	require_once 'includes/date.php';

	if (!isset($_REQUEST['part'])) {
		include 'views/sport_main.php';
	} else switch ($_REQUEST['part']) {
		case 'rules':
			include 'static/sport_rules.xhtml';
			break;
		case 'league':
			if (!isset($_REQUEST['league_id']))
				include 'views/sport_leagues.php';
			else
				include 'views/sport_league.php';
			break;
		case 'rating':
			include 'views/sport_rating.php';
			break;
		case 'statistics':
			include 'views/sport_stats2.php';
			break;
		case 'competition':
			if (!isset($_REQUEST['comp_id']))
				include 'views/sport_competitions.php';
			else
				include 'views/sport_competition.php';
			break;
		case 'pipemen':
			include 'views/sport_pipemen2.php';
			break;
	}

	include 'views/footer.php';
} catch (Exception $e) {
	global $LOG;
	$LOG->exception($e);
}

?>