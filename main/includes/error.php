<?php

require_once dirname(__FILE__) . '/config-local.php';

function echo_json_exception(Exception $e) {
	echo json_encode(
		ERROR_DEBUG_MODE ?
			array (
				'status' => 'failed',
				'reason' => $e->getMessage(),
				'exception' => array (
					'trace' => $e->getTrace(),
					'file' => $e->getFile(),
					'line' => $e->getLine()
				)
			) :
			array (
				'status' => 'failed',
				'reason' => 'exception'
			)
	);
}

?>
