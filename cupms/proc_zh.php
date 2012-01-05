<?php

/**
 * @author Artyom Grigoriev
 */
require_once 'includes/error.php';
require_once 'includes/config.php';

require_once 'views/competition_view.php';
require_once 'views/cup_games_view.php';

require_once 'templates/cup_response.php';
require_once 'templates/competition_response.php';

require_once '../' . MAINSITE . '/classes/cupms/Competition.php';
require_once '../' . MAINSITE . '/classes/cupms/Cup.php';
require_once '../' . MAINSITE . '/classes/cupms/Game.php';
require_once '../' . MAINSITE . '/classes/cupms/RatingTable.php';

require_once '../' . MAINSITE . '/classes/user/Auth.php';
require_once '../' . MAINSITE . '/classes/user/User.php';

require_once '../' . MAINSITE . '/classes/exceptions/cupms_exception_set.php';

require_once '../' . MAINSITE . '/includes/security.php';
require_once '../' . MAINSITE . '/includes/common.php';

try {

	$auth = new Auth();
	$user = $auth->getCurrentUser();

	Header('Content-type: text/html; charset="windows-1251"', true);

	if (!isset($_REQUEST['method'])) {
		echo_error_code(ERROR_NOMETHOD);
		exit(0);
	}

	assertParam('comp_id');
	$competition = Competition::getById(intparam('comp_id'));
	assertTrue('Permission denied', $user->hasPermission($competition, 'edit') && $competition instanceof Competition);

	switch ($_REQUEST['method']) {
	case 'get_registered':
		echo json(array(
			'status' => 'ok',
			'response' => $competition->getRegisteredArray()
		));
		break;
	
	case 'register':
		$competition->register(0, param('pmid'));
		echo json(array('status' => 'ok'));
		break;

	case 'register_new':
		assertParam('name');
		assertParam('surname');

		$player = Player::create(textparam('name'), textparam('surname'));
		if ($player instanceof Player) {
			$competition->register(0, $player->getId());
			echo json(array('status' => 'ok'));
		}
		break;
		
	case 'remove':
		assertParam('usid');
		assertParam('pmid');

		if (CompetitionDBClient::deleteRawRegistration($competition->getId(), intparam('pmid'), intparam('usid'))) {
			echo json(array('status' => 'ok'));
		}

		break;

	case 'view_baskets':
		assertParam('lower_bound');
		assertParam('upper_bound');

		$rating = RatingTable::getInstanceByDate($competition->getLeagueId(), $competition->getDate());
		$players = array();
		$array = $competition->getRegisteredArray();
		foreach ($array as $item) {
			if ($item['player']['id']) {
				$players[ $item['player']['id'] ] = Player::getById($item['player']['id']);
			}
		}

		$length = count (array_keys($array));
		$result = array();
		foreach ($rating as $row) {
			if ($players[ $row['pmid'] ]) {
				$result[] = $players[ $row['pmid'] ];
				unset ($players[ $row['pmid'] ]);
			}
		}

		print_r($result);

		break;
	}
} catch (Exception $e) {
	echo_error(ERROR, $e->getMessage(), $_REQUEST);
	exit(0);
}
?>
