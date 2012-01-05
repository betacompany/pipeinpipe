<?php

/**
 * @author Artyom Grigoriev
 */
require_once dirname(__FILE__) . '/includes/error.php';
require_once dirname(__FILE__) . '/includes/config.php';
require_once dirname(__FILE__) . '/views/competition_view.php';
require_once dirname(__FILE__) . '/templates/cup_response.php';

require_once '../'.MAINSITE.'/classes/cupms/Competition.php';
require_once '../'.MAINSITE.'/classes/cupms/CupFactory.php';
require_once '../'.MAINSITE.'/classes/cupms/ResultTable.php';

require_once '../'.MAINSITE.'/classes/user/Auth.php';
require_once '../'.MAINSITE.'/classes/user/User.php';

require_once '../' . MAINSITE . '/includes/security.php';
require_once '../' . MAINSITE . '/includes/common.php';

try {
	$auth = new Auth();
	$user = $auth->getCurrentUser();

	Header('Content-type: text/html; charset="UTF-8"', true);

	if (!isset($_REQUEST['method'])) {
		echo_error_code(ERROR_NOMETHOD);
		exit(0);
	}

	switch ($_REQUEST['method']) {
		//theese cup need the cup_id parameter to be set
		case 'set_name_and_mult':
		case 'remove':
		case 'add_player':
		case 'remove_player':
		case 'recalc_result_table':
		case 'create_stages':
		case 'get_cup_data':

			assertIsset($_REQUEST['cup_id']);
			assertCup($_REQUEST['cup_id']);
			$cup = CupFactory::getCupById($_REQUEST['cup_id']);

			// theese methods do not require permission
			switch ($_REQUEST['method']) {
				case 'recalc_result_table':
					if (!ResultTable::recalculateForCup($cup)) {
						json_echo_error_code (ERROR_RESULT_TABLE);
						exit(0);
					}
					cup_response_short($cup);
					exit(0);
			}

			//cheking whether the current user is allowed to make changes
			if ($user->hasPermission($cup->getCompetition(), 'edit')) {
				switch ($_REQUEST['method']) {
					
				case 'set_name_and_mult':
					if (isset($_REQUEST['name'])) {
						$name = $_REQUEST['name'];

						if (!$cup->setName($name)) {
							json_echo_error_code(ERROR_DB);
							exit(0);
						}
					}
					if (isset($_REQUEST['cup_mult'])) {
						if (!$cup->setMultiplier($_REQUEST['cup_mult'])) {
							json_echo_error_code(ERROR_DB);
							exit(0);
						}
					}
					if (isset($_REQUEST['sub_cups_mult'])) {
						if (!$cup->setMultiplierForChildren($_REQUEST['sub_cups_mult'])) {
							json_echo_error_code(ERROR_DB);
							exit(0);
						}
					}
					response_success();
					exit(0);

				case 'remove':
					if (!$cup->remove()) {
						json_echo_error_code(ERROR_DB);
						exit(0);
					}
					cup_response_short($cup);
					exit(0);

				/*
				 * @param cup_id
				 * @param pmid
				 */
				case 'add_player':
					assertIsset($_REQUEST['pmid']);
					assertPipeman($_REQUEST['pmid']);

					if (!$cup->addPlayer($_REQUEST['pmid'])) {
						json_echo_error_code(ERROR_DB);
						exit(0);
					}
					cup_response_player_short($_REQUEST['pmid']);
					exit(0);

				/*
				 * @param cup_id
				 * @param pmid
				 */
				case 'remove_player':
					assertIsset($_REQUEST['pmid']);
					
					if (!$cup->removePlayer($_REQUEST['pmid'])) {
						json_echo_error_code(ERROR_DB);
						exit(0);
					}
					cup_response_player_short($_REQUEST['pmid']);
					exit(0);

				case 'create_stages':
					if (! ($cup instanceof CupPlayoff)) {
						json_echo_error_code(ERROR_REGULARCUP);
						exit(0);
					}

					assertIsset($_REQUEST['max_stage']);

					$maxStage = $_REQUEST['max_stage'];
					$cupId = $cup->getId();
					$final = $cup->getFinalGame();
					if ($final === null)
						$final = Game::create($cupId, 1);
					$games = array($final);
					$currentStage = 1;

					while ($currentStage < $maxStage) {
						$nextStageGames = array();
						foreach ($games as $game) {
							foreach ($game->getPrevGames() as $which => $prevGame) {
								if ($prevGame === null) {
									$prevGame = Game::create($cupId, 2 * $currentStage);
									$game->setPrevGame($which, $prevGame);
								}
								$nextStageGames[] = $prevGame;
							}
							$game->update();
						}
						$games = $nextStageGames;
						$currentStage *= 2;
					}

					exit(0);
				}
			} else {
				json_echo_error_code(ERROR_NOACCESS);
				exit(0);
			}
			exit(0);

		//theese methods don't need to know the cup_id

		/*
		 * @param comp_id
		 * @param parent_cup_id
		 * @param name
		 * @param type
		 */
		case 'create':
			assertIsset($_REQUEST['comp_id']);
			assertIsset($_REQUEST['parent_cup_id']);
			assertIsset($_REQUEST['name']);
			assertIsset($_REQUEST['type']);

			try {
				$competition = Competition::getById($_REQUEST['comp_id']);
				if ($user->hasPermission($competition, 'edit')) {
					$name = string_convert(string_process($_REQUEST['name']));

					if ($_REQUEST['parent_cup_id'] != 0) {
						assertCup($_REQUEST['parent_cup_id']);
					} else {
						assertTrue('Main cup already exists', $competition->getMainCupId() == 0);
					}

					$cup = CupFactory::create($_REQUEST['comp_id'], $_REQUEST['parent_cup_id'], $name, $_REQUEST['type']);
					cup_response_short($cup);
				} else {
					echo_error_code(ERROR_NOACCESS);
				}
			} catch (InvalidArgumentException $e) {
				echo_error_code(ERROR_NOCUP);
			}
			exit(0);

		case 'is_correct_type':
			assertIsset($_REQUEST['type']);
			$type = string_convert(string_process($_REQUEST['type']));
			response_boolean(Cup::isCorrectType($type));
			exit(0);

		/**
		 * checks whether the given competition already has a cup with such name
		 */
		case 'is_correct_name':
			assertIsset($_REQUEST['name']);
			assertIsset($_REQUEST['comp_id']);

			$name = string_convert(string_process($_REQUEST['name']));
			response_boolean(Cup::isCorrectName($_REQUEST['comp_id'], $name));
			exit(0);

		/*
		 * if game_id is set, the method updates all data
		 * considering which parameters are set too.
		 *
		 * if game_id is not set the method creates a new game
		 * with given parameters.
		 *
		 * @param game_id
		 * @param cup_id
		 * @param stage
		 * @param tour
		 * @param pmid1
		 * @param pmid2
		 * @param score1
		 * @param score2
		 * @param time
		 * @param tech
		 * @param parent_game_id
		 * @param is_left
		 */
		case 'edit_game':
			if (isset($_REQUEST['game_id'])) {
				$game = new Game($_REQUEST['game_id']);

				if (isset($_REQUEST['stage'])) $game->setStage($_REQUEST['stage']);
				if (isset($_REQUEST['tour'])) $game->setTour($_REQUEST['tour']);
				if (isset($_REQUEST['pmid1'])) $game->setPmid1($_REQUEST['pmid1']);
				if (isset($_REQUEST['pmid2'])) $game->setPmid2($_REQUEST['pmid2']);
				if (isset($_REQUEST['score1'])) $game->setScore1($_REQUEST['score1']);
				if (isset($_REQUEST['score2'])) $game->setScore2($_REQUEST['score2']);
				if (isset($_REQUEST['time'])) $game->setTime($_REQUEST['time']);
				if (isset($_REQUEST['type'])) $game->setType($_REQUEST['type']);

				if (!$game->update()) {
					json_echo_error_code(ERROR_DB);
					exit(0);
				}
			} else {
				assertIsset($_REQUEST['cup_id']);

				$stage = isset($_REQUEST['stage']) ? $_REQUEST['stage'] : 0;
				$pmid1 = isset($_REQUEST['pmid1']) ? $_REQUEST['pmid1'] : null;
				$pmid2 = isset($_REQUEST['pmid2']) ? $_REQUEST['pmid2'] : null;
				$score1 = isset($_REQUEST['score1']) ? $_REQUEST['score1'] : null;
				$score2 = isset($_REQUEST['score2']) ? $_REQUEST['score2'] : null;
				$tour = isset($_REQUEST['tour']) ? $_REQUEST['tour'] : null;
				$time = isset($_REQUEST['time']) ? $_REQUEST['time'] : null;
				$isTech = isset($_REQUEST['tech']) ? $_REQUEST['tech'] : null;

				$game = Game::create($_REQUEST['cup_id'], $stage, $tour, $pmid1, $pmid2, $score1, $score2, $time, $isTech);

				if (isset($_REQUEST['parent_game_id'])) {
					assertIsset($_REQUEST['is_left']);
					$parentGame = new Game($_REQUEST['parent_game_id']);

					if (isset($_REQUEST['stage']) && $_REQUEST['stage'] != ($parentGame->getStage() * 2)) {
						json_echo_error_code(ERROR_WRONGPARAMS, 'Stage is incorrect');
						exit(0);
					}

					if ($_REQUEST['is_left'] == 'true') {
						$parentGame->setPrevGameId1($game->getId());
					} else {
						$parentGame->setPrevGameId2($game->getId());
					}

					if (!$parentGame->update()) {
						json_echo_error_code(ERROR_DB);
						exit(0);
					}
				}
			}

			cup_response_game($game);
			exit(0);

		case 'get_game_data':
			assertIsset($_REQUEST['game_id']);
			cup_response_game(new Game($_REQUEST['game_id']));
			exit(0);
		}
} catch (Exception $e) {
	json_echo_error_code(ERROR, $e->getMessage());
	exit(0);
}

?>
