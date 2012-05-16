<?php

require_once dirname(__FILE__) . '/../includes/common.php';

if (param('password') != 'TYtyBhBHYUbjnjjhJYYEISAWEPpppllcF') {
	echo 'ACCESS DENIED!';
	exit(0);
}

require_once dirname(__FILE__) . '/../classes/utils/Logger.php';
require_once dirname(__FILE__) . '/../classes/utils/Lock.php';

require_once dirname(__FILE__) . '/../classes/cupms/Competition.php';

assertParam('factor_file');

$logger = new Logger('../../logs/factors.log');
$logger->info("Competition dates factor started");

$lock = new Lock("factor_dates", $logger);
if ($lock->isLocked()) {
	$logger->warn("Locked!");
} else {
	$lock->lock();

	$fp = fopen(param('factor_file'), "w");

	$competitions = Competition::getAll();
	foreach ($competitions as $competition) {
		fwrite($fp, $competition->getDate() . "\n");
	}

	fflush($fp);
	fclose($fp);

	$lock->release();
}

$logger->info("Competition dates factor finished");

?>