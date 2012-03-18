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

require_once '../main/classes/cupms/Competition.php';
require_once '../main/classes/cupms/Cup.php';
require_once '../main/classes/cupms/Game.php';

require_once '../main/classes/user/Auth.php';
require_once '../main/classes/user/User.php';

require_once '../main/classes/exceptions/cupms_exception_set.php';

require_once '../main/includes/security.php';
require_once '../main/includes/common.php';

try {

	$auth = new Auth();
	$user = $auth->getCurrentUser();

	Header('Content-type: text/html; charset="UTF-8"', true);

	if (!isset($_REQUEST['method'])) {
		echo_error_code(ERROR_NOMETHOD);
		exit(0);
	}

	switch ($_REQUEST['method']) {

	case 'main_page':
	case 'load_properties':
	case 'load_structure':
	case 'load_players':
	case 'load_zherebjator':
	case 'load_games':
	case 'load_games_cup':
	case 'load_admins':
	case 'load_players_cup':
	case 'load_delete_confirmation':
	case 'load_monitoring':
	case 'set_name':
	case 'get_tournaments':
	case 'set_tournament':
	case 'add_tournament':
	case 'set_date':
	case 'set_description':
	case 'delete_competition' :
	case 'delete_admin':
	case 'make_admin':
	case 'evaluate_coef':
	case 'set_coef':

		assertIsset($_REQUEST['comp_id']);

		try {
			$competition = Competition::getById($_REQUEST['comp_id']);
			switch ($_REQUEST['method']) {
			case 'main_page':
				competition_main_page($competition, $user);
				exit(0);

			case 'load_properties':
				competition_properties($competition, $user);
				exit(0);

			case 'load_structure':
				competition_structure($competition, $user);
				exit(0);

			case 'load_players':
				competition_players($competition, $user);
				exit(0);

			case 'load_zherebjator':
				competition_zherebjator($competition, $user);
				exit(0);

			case 'load_games':
				competition_games($competition, $user);
				exit(0);

			case 'load_admins':
				competition_admins($competition, $user);
				exit(0);

			case 'load_games_cup':
				assertIsset($_REQUEST['cup_id']);

				$cup = CupFactory::getCupById($_REQUEST['cup_id']);
				if ($cup->getCompetitionId() != $competition->getId()) {
					echo_error_code(ERROR_NOCUP);
					exit(0);
				}

				competition_games($competition, $user, $cup);
				exit(0);

			case 'load_players_cup':
				assertIsset($_REQUEST['cup_id']);

				$cup = CupFactory::getCupById($_REQUEST['cup_id']);
				if ($cup->getCompetitionId() != $competition->getId()) {
					echo_error_code(ERROR_WRONGPARAMS);
					exit(0);
				}

				competition_players($competition, $user, $cup);
				exit(0);

			case 'load_delete_confirmation':
				competition_delete_confirmation ($competition->getId());
				exit(0);

			case 'load_monitoring':
				competition_monitoring($competition);
				exit(0);

			case 'set_name':
			case 'set_description':
			case 'set_date':
			case 'set_tournament':
			case 'evaluate_coef':
			case 'set_coef':
				if ($user->hasPermission($competition, 'edit')) {

					switch ($_REQUEST['method']) {
					/**
					 * @param comp_id
					 * @param name
					 */
					case 'set_name':
						assertIsset($_REQUEST['name']);
						$name = $_REQUEST['name'];

						if (!$competition->setName($name)) {
							echo_error_code(ERROR_DB);
							exit(0);
						}
						echo $competition->getName();
						exit(0);

					/**
					 * @param comp_id
					 * @param tour_id
					 */
					case 'set_tournament':
						assertIsset($_REQUEST['tour_id']);

						if (!$competition->setTournamentId($_REQUEST['tour_id'], true)) {
							echo_error_code(ERROR_DB);
							exit(0);
						}
						competition_response_tournament($competition);
						exit(0);

					/*
					 * @param comp_id Competition to edit.
					 * @param date String in format YYYY-MM-DD.
					 */
					case 'set_date':
						assertIsset($_REQUEST['date']);
						$date = $_REQUEST['date'];
						if (!$competition->setDate($date, true)) {
							json_echo_error_code(ERROR_DB);
							exit(0);
						}
						response_success(array ('date' => $competition->getDate()));
						exit(0);


					/*
					 * @param comp_id
					 * @param coef
					 */
					case 'set_coef':
					case 'evaluate_coef' :
						if ($_REQUEST['method'] == 'set_coef') {
							assertIsset($_REQUEST['coef']);
							$newCoef = $_REQUEST['coef'];
						} else {
							$formula = $competition->getLeague()->getFormula();
							$table = RatingTable::getInstance($competition->getLeagueId());
							$pmIds = $competition->getPlayerIds();
							$newCoef = $formula->evaluate($table, $pmIds);
						}

						if (!$competition->setCoef($newCoef))
							json_echo_error_code(ERROR_DB);
						else
							response_success(array('coef' => $competition->getCoef()));
						exit(0);

					/*
					 * @param comp_id
					 * @param description
					 */
					case 'set_description':
						assertIsset($_REQUEST['description']);
						$description = $_REQUEST['description'];
						$description = string_process($description, SECURITY_DESCRIPTION);

						if (!$competition->setDescription($description, true))
							echo_error_code(ERROR_DB);
						else
							echo $competition->getDescription();
						exit(0);
					}

				} else {
					json_echo_error_code(ERROR_NOACCESS);
					exit(0);
				}

			/**
			 * @param comp_id
			 * @param tour_name
			 */
			case 'add_tournament':
				if ($user->hasPermission('tournament', 'add')) {
					assertIsset($_REQUEST['tour_name']);
					$name = $_REQUEST['tour_name'];

					$tour = Tournament::create($name);
					if ($tour == null || !$competition->setTournamentId($tour->getId(), true))
						echo_error_code(ERROR_DB);
					else
						competition_response_tournament($competition);
					exit(0);
				} else {
					json_echo_error_code(ERROR_NOACCESS);
					exit(0);
				}

			case 'delete_competition' :
				if ($user->hasPermission($competition, 'remove')) {
					try {
						if ($competition->remove())
							response_success(array('league_id' => $competition->getLeagueId()));
						else
							json_echo_error_code(ERROR_DB);
					} catch (InvalidStatusException $e) {
						json_echo_error_code(ERROR_INCORRECT_STATUS);
					}
				} else {
					json_echo_error_code(ERROR_NOACCESS);
				}
				exit(0);

			case 'delete_admin':
			case 'make_admin':
				if ( ($_REQUEST[method] == 'delete_admin' && $user->hasPermission($competition, 'delete_admin')) ||
					 ($_REQUEST[method] == 'make_admin' && $user->hasPermission($competition, 'add_admin')) ) {
					assertIsset($_REQUEST['uid']);
					$admin = User::getById($_REQUEST['uid']);
					if ( ($_REQUEST[method] == 'delete_admin' && $admin->deleteCA($competition->getId())) ||
						 ($_REQUEST[method] == 'make_admin' && $admin->makeCA($competition->getId())) )
						response_success(array(
							'name' => $admin->getFullName()
						));
					else
						json_echo_error_code(ERROR_DB);
					exit(0);
				} else {
					json_echo_error_code(ERROR_NOACCESS);
					exit(0);
				}
			}
		} catch (InvalidArgumentException $e) {
			echo_error_code(ERROR_NOCOMPETITION);
			exit(0);
		}

		exit(0);

	case 'add_competition_page':
		assertIsset($_REQUEST['league_id']);
		if ($user->hasPermission('competition', 'add')) {
			competition_add_page($_REQUEST['league_id']);
			exit(0);
		} else {
			echo_error_code(ERROR_NOACCESS);
			exit(0);
		}

	case 'is_such_competition':
		$name = string_convert($_REQUEST['name']);
		response_boolean(Competition::isSuchName($name));
		exit(0);

	case 'get_tournaments':
		echo Tournament::getAll();
		exit(0);

	/*
	 * @param comp_id
	 */
	case 'start':
	case 'stop':
	case 'restart':
	case 'start_registering':
		assertIsset($_REQUEST['comp_id'], 'comp_id');

		try {
			$competitionId = intval($_REQUEST['comp_id']);
			$competition = Competition::getById($competitionId);

			if ($user->hasPermission($competition, $_REQUEST['method'])) {
				try {
					switch ($_REQUEST['method']) {
					case 'start': $competition->start(); break;
					case 'start_registering': $competition->startRegistering(); break;
					case 'restart': $competition->restart(); break;
					case 'stop':
						assertIsset($_REQUEST['use_current_date'], 'use_current_date');
						if ($_REQUEST['use_current_date']) {
							assertIsset($_REQUEST['date'], 'date');
							$competition->finish($_REQUEST['date']);
						} else
							$competition->finish();

						break;
					}
					
					response_success();
					exit(0);
				} catch (InvalidStatusException $e) {
					json_echo_error_code(ERROR_INCORRECT_STATUS);
					exit(0);
				} catch (NullCupException $e) {
					json_echo_error_code(ERROR_NO_MAIN_CUP,
							"You have to create the main cup in this competition before ".$_REQUEST['method'].'ing it!');
				} catch (NullDateException $e) {
					json_echo_error_code(ERROR_NULL_DATE);
				} catch (InvalidDateException $e) {
					json_echo_error_code(ERROR_INVALID_DATE);
				}
			} else {
				json_echo_error_code(ERROR_NOACCESS);
				exit(0);
			}
		} catch (InvalidIdException $e) {
			json_echo_error_code(ERROR_NOCOMPETITION);
			exit(0);
		}
		
		exit(0);
	}
} catch (Exception $e) {
	echo_error(ERROR, $e->getMessage(), $_REQUEST);
	exit(0);
}
?>
