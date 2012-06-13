<?php

require_once 'includes/config.php';
require_once 'views/side_menu_view.php';

require_once '../main/classes/user/Auth.php';
require_once '../main/classes/user/User.php';

try {
	$auth = new Auth();
	$user = $auth->getCurrentUser();

	Header('Content-type: text/html; charset="UTF-8"', true);

	if (!isset($_REQUEST['method'])) {
		echo_error_code(ERROR_NOMETHOD);
		exit(0);
	}

	switch ($_REQUEST['method']) {

	case 'side_menu':
		getSideMenu($user);
		exit(0);
		
	case 'load_admins' :
		loadTotalAdmins($user);
		exit(0);

	case 'make_admin':
		if ($user->hasPermission('total_admin', 'add')) {
			assertIsset($_REQUEST['uid']);
			$admin = User::getById($_REQUEST['uid']);
			if ($admin->makeTA()) {
				response_success(array(
					'name' => $admin->getFullName()
				));
				exit(0);
			} else {
				json_echo_error_code(ERROR_DB);
				exit(0);
			}
		} else {
			json_echo_error_code(ERROR_NOACCESS);
			exit(0);
		}
	}

} catch (Exception $e) {
	echo_error(ERROR, $e->getMessage(), $_REQUEST);
	exit(0);
}

?>
