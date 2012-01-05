<?php

require_once dirname(__FILE__) . '/../classes/cupms/PlayerComparator.php';
require_once dirname(__FILE__) . '/../includes/error.php';

require_once dirname(__FILE__) . '/../views/sport_player_comparator2.php';

if (!isset($_REQUEST['method'])) {
	echo json_encode(array(
		'status' => 'failed',
		'reason' => 'method not specified'
	));
}

try {
	switch ($_REQUEST['method']) {
	case 'compare':
		assertIsset($_REQUEST['pmid1']);
		assertIsset($_REQUEST['pmid2']);

		$pmid1 = intval($_REQUEST['pmid1']);
		$pmid2 = intval($_REQUEST['pmid2']);

		try {
			$comparator = new PlayerComparator(Player::getById($pmid1), Player::getById($pmid2));
			echo $comparator->toHTML("show_player_comparator");
		} catch (InvalidIdException $e) {
			echo_json_exception($e);
			exit(0);
		}

		break;
	}
} catch (Exception $e) {
	echo_json_exception($e);
	exit(0);
}

?>
