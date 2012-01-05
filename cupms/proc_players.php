<?php

/**
 * @author Innokenty Shuvalov
 */

require_once 'includes/error.php';
require_once 'includes/config.php';
require_once 'views/players_view.php';

require_once '../'.MAINSITE.'/classes/cupms/Player.php';

require_once '../'.MAINSITE.'/classes/user/Auth.php';
require_once '../'.MAINSITE.'/classes/user/User.php';

require_once '../'.MAINSITE.'/includes/security.php';
require_once '../'.MAINSITE.'/includes/common.php';

Header('Content-type: text/html; charset="UTF-8"', true);

if (!isset($_REQUEST['method'])) {
	json_echo_error_code(ERROR_NOMETHOD);
	exit(0);
}

$auth = new Auth();
$user = $auth->getCurrentUser();

if ($user === null) {
	json_echo_error_code (ERROR_NOACCESS);
	exit(0);
}

try {
	switch ($_REQUEST['method']) {
		case 'main_page':
			if ($user->hasPermission('player', 'view_editing_page')) {
				players_main_page();
			} else {
				json_echo_error_code(ERROR_NOACCESS);
			}

			exit(0);

		case 'create' :
			if ($user->hasPermission('player', 'add')) {
				assertIsset($_REQUEST['name']);
				assertIsset($_REQUEST['surname']);
				assertIsset($_REQUEST['gender']);
				assertIsset($_REQUEST['email']);
				assertIsset($_REQUEST['country']);
				assertIsset($_REQUEST['city']);
				assertIsset($_REQUEST['description']);
				assertIsset($_REQUEST['user_id']);

				$uid = intval($_REQUEST['user_id']);
				$name = string_convert($_REQUEST['name']);
				$surname = string_convert($_REQUEST['surname']);
				$gender = string_convert($_REQUEST['gender']);
				$email = string_convert($_REQUEST['email']);
				$country = string_convert($_REQUEST['country']);
				$city = string_convert($_REQUEST['city']);
				$description = string_convert($_REQUEST['description']);

				$player = Player::create($name, $surname, $gender, $country, $city, $email, $description);
				if ($player === null) {
					json_echo_error_code(ERROR_DB);
					exit(0);
				}

				if ($uid > 0 && null !== ($userToAssign = User::getById($uid))) {
					$userToAssign->put(User::KEY_PMID, $player->getId());
				} else {
					json_echo_error_code(ERROR_DB, "New User created but with an error while assigning a user to this pipeman.");
					exit(0);
				}
				
				response_success();
			} else {
				json_echo_error_code(ERROR_NOACCESS);
			}
			
			exit(0);

		case 'save_changes' :
			if ($user->hasPermission('player', 'edit')) {
				assertIsset($_REQUEST['pmid']);
				assertIsset($_REQUEST['name']);
				assertIsset($_REQUEST['surname']);
				assertIsset($_REQUEST['gender']);
				assertIsset($_REQUEST['email']);
				assertIsset($_REQUEST['country']);
				assertIsset($_REQUEST['city']);
				assertIsset($_REQUEST['description']);
				assertIsset($_REQUEST['user_id']);

				$pmid = intval($_REQUEST['pmid']);
				if (null === ($player = Player::getById($pmid))) {
					json_echo_error_code(ERROR_NOPLAYER);
					exit(0);
				}

				$uid = intval($_REQUEST['user_id']);
				if ($uid < 0) {
					json_echo_error_code(ERROR_WRONGPARAMS);
					exit(0);
				}

				$name = string_convert($_REQUEST['name']);
				$surname = string_convert($_REQUEST['surname']);
				$gender = string_convert($_REQUEST['gender']);
				$email = string_convert($_REQUEST['email']);
				$country = string_convert($_REQUEST['country']);
				$city = string_convert($_REQUEST['city']);
				$description = string_convert($_REQUEST['description']);

				$player->setName($name);
				$player->setSurname($surname);
				$player->setGender($gender);
				$player->setEmail($email);
				$player->setCity($city);
				$player->setCountry($country);
				$player->setDescription($description);
				$player->updateInfo();

				$assignedUser = $player->getUser();
				if (($assignedUser !== null && $uid != $assignedUser->getId()) || ($assignedUser === null && $uid != 0)) {
					if ($assignedUser !== null)
						$assignedUser->deleteByKey(User::KEY_PMID);

					if (null !== $userToAssign = User::getById($uid)) {
						$userToAssign->put(User::KEY_PMID, $pmid);
					}
				}

				response_success(array(
					'content' => User::getAllToHTML()
				));
			} else {
				json_echo_error_code(ERROR_NOACCESS);
			}

			exit(0);

		case 'get_by_id' :
			assertIsset($_REQUEST['pmid']);
			if ($user->hasPermission('player', 'get_data')) {
				echo $player = Player::getById($_REQUEST['pmid'])->toJSON();
			} else {
				json_echo_error_code(ERROR_NOACCESS);
			}

			exit(0);
	}
} catch (Exception $e) {
	json_echo_error_code(ERROR, $e->getMessage());
    exit(0);
}
?>
