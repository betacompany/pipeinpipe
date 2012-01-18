<?php

require_once dirname(__FILE__) . '/../classes/user/User.php';
require_once dirname(__FILE__) . '/../classes/user/Auth.php';

require_once dirname(__FILE__) . '/config-local.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

if ($auth->isMobile()) {
	Header('Location: ' . MOBILE_SITE_URL);
	exit(0);
}

?>
