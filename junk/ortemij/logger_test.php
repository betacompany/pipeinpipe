<?php

require_once '../../main/classes/utils/Logger.php';

$log = new Logger();

try {
	throw new InvalidArgumentException('EXCEPTION_MESSAGE');
} catch (Exception $e) {
	$log->exception($e);
}

$log->info("Hello, world!");
$log->error("Hello, world!");
$log->warn("Hello, world!");
$log->pizdets("Hello, world!");
$log->info("Hello, world!");

?>
