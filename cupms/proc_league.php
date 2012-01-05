<?php

/**
 * @author Artyom Grigoriev
 */
require_once 'includes/error.php';
require_once 'includes/config.php';

require_once 'views/league_view.php';

require_once 'templates/league_response.php';
require_once 'templates/competition_response.php';

require_once '../' . MAINSITE . '/classes/cupms/League.php';

require_once '../' . MAINSITE . '/classes/user/Auth.php';
require_once '../' . MAINSITE . '/classes/user/User.php';

require_once '../' . MAINSITE . '/includes/security.php';
require_once '../' . MAINSITE . '/includes/common.php';

try {

	$auth = new Auth();
	$user = $auth->getCurrentUser();

	Header('Content-type: text/html; charset="UTF-8"', true);

	if (!isset($_REQUEST['method'])) {
		json_echo_error_code(ERROR_NOMETHOD);
		exit(0);
	}

	switch ($_REQUEST['method']) {

	case 'main_page':
	case 'load_admins':
	case 'load_properties':
	case 'delete_admin':
	case 'make_admin':
	case 'set_name':
	case 'set_description':
	case 'add_competition':
	case 'set_formula':

		try {
			assertIsset($_REQUEST['league_id']);
			$league = League::getById($_REQUEST['league_id']);

			switch ($_REQUEST['method']) {
			case 'main_page':
				league_main_page($league);
				exit(0);

			case 'load_admins':
				league_admins($league, $user);
				exit(0);

			case 'load_properties':
				league_properties($league, $user);
				exit(0);


			case 'delete_admin':
			case 'make_admin':

				if ( ($_REQUEST[method] == 'delete_admin' && $user->hasPermission($league, 'delete_admin')) ||
					 ($_REQUEST[method] == 'make_admin' && $user->hasPermission($league, 'add_admin')) ) {
					assertIsset($_REQUEST['uid']);
					$admin = User::getById($_REQUEST['uid']);
					if ( ($_REQUEST[method] == 'delete_admin' && $admin->deleteLA($league->getId())) ||
						 ($_REQUEST[method] == 'make_admin' && $admin->makeLA($league->getId())) ) {
						response_success(array(
							'name' => $admin->getFullName()
						));
						exit(0);
					}
					json_echo_error_code(ERROR_DB);
				} else {
					json_echo_error_code(ERROR_NOACCESS);
				}
				exit(0);

			/*
			 * set name for competition
			 *
			 * @param league_id
			 * @param name
			 */
			case 'set_name':
				assertIsset($_REQUEST['name']);
				$name = $_REQUEST['name'];

				if ($user->hasPermission($league, 'edit')) {
					if (!$league->setName($name)) {
						echo_error_code(ERROR_DB);
						exit(0);
					}
					echo $league->getName();
				} else {
					echo_error_code(ERROR_NOACCESS);
				};
				exit(0);

			/*
			 * set description for league
			 *
			 * @param league_id League to edit.
			 * @param description new description.
			 */
			case 'set_description':
				assertIsset($_REQUEST['description']);

				$description = $_REQUEST['description'];
				$description = string_process($description, SECURITY_DESCRIPTION);

				if ($user->hasPermission($league, 'edit')) {
					if (!$league->setDescription($description, true)) {
						echo_error_code(ERROR_DB);
						exit(0);
					}
					echo $league->getDescription();
				} else {
					echo_error_code(ERROR_NOACCESS);
				}
			exit(0);

			/**
			 * @param league_id
			 * @param name
			 * @param description
			 */
			case 'add_competition':
				assertIsset($_REQUEST['name']);
				assertIsset($_REQUEST['description']);

				$name = string_convert($_REQUEST['name']);
				$description = string_convert($_REQUEST['description']);

				try {
					if ($user->hasPermission($league, 'add_competition')) {
						$competition = Competition::create($league->getId(), 0, $name, $description);
						competition_response_id($competition);
					} else {
						json_echo_error_code(ERROR_NOACCESS);
					}
				} catch (InvalidArgumentException $e) {
					json_echo_error_code(ERROR_DB);
				}
				exit(0);

			case 'set_formula':
				assertIsset($_REQUEST['formula'], 'formula');
				$formula = string_convert($_REQUEST['formula']);
				if(!$league->setFormula($formula))
					json_echo_error_code (ERROR_DB);
				echo league_response_formula($league->getFormula());
				exit(0);
			}
		} catch (Exception $e) {
			json_echo_error_code(ERROR, $e->getMessage());
		}
		exit(0);


	case 'add_league_page':
		if ($user->hasPermission('league', 'add')) {
			league_add_page();
			exit(0);
		}
		echo_error_code(ERROR_NOACCESS);
		exit(0);

	/*
	 * @param name
	 * @param description
	 * @param formula
	 */
	case 'add_league':
		assertIsset($_REQUEST['name']);
		assertIsset($_REQUEST['description']);
		assertIsset($_REQUEST['formula']);

		assertNotEmpty($_REQUEST['name']);
		assertNotEmpty($_REQUEST['description']);
		assertNotEmpty($_REQUEST['formula']);

		$name = string_convert($_REQUEST['name']);
		$description = string_convert($_REQUEST['description']);
		$formula = string_convert($_REQUEST['formula']);

		$name = string_process($name);
		$description = string_process($description, 'description');
		$formula = string_process($formula, 'formula');

		if (League::isSuchName($name)) {
			echo_error_code(ERROR_LEAGUE_NAME_EXISTS);
			exit(0);
		}

		try {
			$league = League::create($name, $description, $formula);
			league_response_new($league);
		} catch (InvalidArgumentException $e) {
			json_echo_error_code(ERROR_LEAGUE_CREATION);
		} catch (Exception $e) {
			json_echo_error_code(ERROR, $e->getMessage());
		}
		exit(0);

	case 'is_such_league':
		$name = string_convert($_REQUEST['name']);
		league_response_is_such_name($name);
		exit(0);

	default:
		json_echo_error_code(ERROR_NOMETHOD);
		exit(0);
	}
} catch (Exception $e) {
	json_echo_error(ERROR, $e->getMessage());
}
?>
