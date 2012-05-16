<?php

require_once dirname(__FILE__) . '/../includes/common.php';

if (param('password') != 'GhBJUhfyHRgRGcvIMmGyU4558YhffV5tU') {
	echo 'ACCESS DENIED!';
	exit(0);
}

require_once dirname(__FILE__) . '/../classes/utils/Logger.php';
require_once dirname(__FILE__) . '/../classes/utils/Lock.php';

require_once dirname(__FILE__) . '/../classes/user/User.php';

assertParam('factor_file');

$logger = new Logger('../../logs/factors.log');
$logger->info("Usernames factor started");

$lock = new Lock("factor_usernames", $logger);
if ($lock->isLocked()) {
	$logger->warn("Locked!");
} else {
	$lock->lock();

	$fp = fopen(param('factor_file'), "w");

	$contact_keys = User::getContactKeys();
	$users = User::getAll();
	foreach ($users as $user) {
		foreach ($contact_keys as $key) {
			$data = $user->get($key);
			if ($data) {
				fwrite($fp, $data . "\n");
			}
		}
	}

	fflush($fp);
	fclose($fp);

	$lock->release();
}

$logger->info("Usernames factor finished");

?>