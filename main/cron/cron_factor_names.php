<?php

require_once dirname(__FILE__) . '/../includes/common.php';

if (param('password') != 'EoPpLpnxcvjnfdNJJKNugngyfiughvTT') {
	echo 'ACCESS DENIED!';
	exit(0);
}

require_once dirname(__FILE__) . '/../classes/utils/Logger.php';
require_once dirname(__FILE__) . '/../classes/utils/Lock.php';

require_once dirname(__FILE__) . '/../classes/cupms/Player.php';

assertParam('factor_file');

$logger = new Logger('../../logs/factors.log');
$logger->info("Pipemen names factor started");

$lock = new Lock("factor_names", $logger);
if ($lock->isLocked()) {
	$logger->warn("Locked!");
} else {
	$lock->lock();

	$fp = fopen(param('factor_file'), "w");

	$players = Player::getAll();
	foreach ($players as $player) {
		fwrite($fp, $player->getName() . "\n");
		fwrite($fp, $player->getSurname() . "\n");
		fwrite($fp, $player->getFullName() . "\n");
	}

	fflush($fp);
	fclose($fp);

	$lock->release();
}

$logger->info("Pipemen names factor finished");

?>