<?php

require_once dirname(__FILE__) . '/../classes/cupms/RatingFormula.php';
require_once dirname(__FILE__) . '/../classes/cupms/League.php';

require_once dirname(__FILE__) . '/../includes/assertion.php';
require_once dirname(__FILE__) . '/../includes/error.php';

require_once dirname(__FILE__) . '/../classes/exceptions/cupms_exception_set.php';

require_once dirname(__FILE__) . '/../views/blocks.php';

if (!isset($_REQUEST['method'])) {
	echo json_encode(array(
		'status' => 'failed',
		'reason' => 'method not specified'
	));
}

try {
	switch ($_REQUEST['method']) {
	case 'evaluate_coef':
		assertIsset($_REQUEST['league_id']);
		assertIsset($_REQUEST['pmids']);
		assertIsset($_REQUEST['date']);

		$pmids = explode(',', $_REQUEST['pmids']);
		$leagueId = intval($_REQUEST['league_id']);
		$date = $_REQUEST['date'];

		assertLeague($leagueId);
		assertDate($date);

		try {
			$league = League::getById($leagueId);
			$formula = $league->getFormula();
			$ratingTable = RatingTable::getInstanceByDate($leagueId, $date);
			echo $formula->toJSON($ratingTable, $pmids);
		} catch (InvalidIdException $e){
			echo_json_exception($e);
			exit(0);
		}

		break;

	case "get_pmids":
		assertIsset($_REQUEST['league_id']);
		assertIsset($_REQUEST['date']);

		$leagueId = intval($_REQUEST['league_id']);
		//$date = isset($_REQUEST['date']) ? $_REQUEST['date'] : date('Y-m-d');
		$date = $_REQUEST['date'];
		assertDate($date);

		try {
			$ratingTable = RatingTable::getInstanceByDate($leagueId, $date);
			$pmids = $ratingTable->getPmids();
			echo json_encode($pmids);
		} catch (Exception $e) {
			echo_json_exception($e);
			exit(0);
		}

		break;

	case "get_rating":
		assertIsset($_REQUEST['league_id']);
		assertIsset($_REQUEST['date']);

		$leagueId = intval($_REQUEST['league_id']);
		//$date = isset($_REQUEST['date']) ? $_REQUEST['date'] : date('Y-m-d');
		$date = $_REQUEST['date'];
		assertDate($date);

		try {
			$ratingTable = RatingTable::getInstanceByDate($leagueId, $date);

			echo '[';

			$data = $ratingTable->getData();

			foreach ($data as $index => $row) {
				try {
					$pm = Player::getById($row['pmid']);
					if ($index > 0) echo ',';
					echo '{';
						echo '"pmid":"' . $pm->getId() . '",';
						echo '"url":"' . $pm->getURL() . '",';
						echo '"name":"' . $pm->getName() . '",';
						echo '"surname":"' . $pm->getSurName() . '",';
						echo '"points":"' . $row['points'] . '",';
						echo '"photo":"' . $pm->getImageURL(Player::IMG_SMALL) . '"';
					echo '}';					
				} catch (InvalidIdException $e) {
					// TODO use error log file
				}
			}

			echo ']';
		} catch (Exception $e) {

			exit(0);
		}

		break;

	case "get_rating_csv":
		Header('Content-type: text/csv');

		assertIsset($_REQUEST['league_id']);
		assertIsset($_REQUEST['date']);

		$leagueId = intval($_REQUEST['league_id']);
		$date = $_REQUEST['date'];
		assertDate($date);

		try {
			$ratingTable = RatingTable::getInstanceByDate($leagueId, $date);

			$data = $ratingTable->getData();

			foreach ($data as $index => $row) {
				try {
					$pm = Player::getById($row['pmid']);
					$i = $index + 1;
					$points = round($row['points'], 2);
					echo "{$i},\"{$pm->getSurname()} {$pm->getName()}\",{$points}\n";
				} catch (InvalidIdException $e) {
					// TODO use error log file
				}
			}
		} catch (Exception $e) {

			exit(0);
		}

		break;

	case "get_movement":
		assertIsset($_REQUEST['pmid']);
		assertIsset($_REQUEST['league_id']);

		$leagueId = intval($_REQUEST['league_id']);
		assertLeague($leagueId);

		try {
			$pm = Player::getById($_REQUEST['pmid']);
			echo json_encode($pm->getRatingMovement($leagueId));
		} catch (InvalidIdException $e) {
			echo_json_exception($e);
			exit(0);
		}

		break;

	case 'get_interesting':
		assertIsset($_REQUEST['pmid']);

		show_sport_rating_popup($_REQUEST['pmid']);

		break;
	}
} catch (Exception $e) {
	echo_json_exception($e);
	exit(0);
}

?>
