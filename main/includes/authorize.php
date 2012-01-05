<?php

require_once dirname(__FILE__) . '/../classes/user/User.php';
require_once dirname(__FILE__) . '/../classes/user/Auth.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

?>
