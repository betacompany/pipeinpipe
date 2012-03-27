<?php

require_once 'classes/user/Auth.php';
require_once 'classes/user/User.php';

require_once 'includes/log.php';

try {
	include 'includes/authorize.php';
	include 'views/header.php';

	switch ($_REQUEST['part']) {
	case 'about':
		include 'views/page_about.php';
		break;
	case 'search':
		include 'views/page_search.php';
		break;
	}

	include 'views/footer.php';
} catch (Exception $e) {
	global $LOG;
	@$LOG->exception($e);
}

?>
