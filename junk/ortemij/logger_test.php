<?php

require_once '../../main/classes/utils/Logger.php';

$log = new Logger();

try {
	throw new InvalidArgumentException('EXCEPTION_MESSAGE');
} catch (Exception $e) {
	$log->exception($e);
}

?>
