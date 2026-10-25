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

/**
 * Replaces what the page has output so far with the 404 page, for a deleted
 * or never existing album, post, user etc. The page must have called
 * ob_start() before its first output.
 */
function show_not_found() {
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	http_response_code(404);
	readfile(dirname(__FILE__) . '/../error_docs/404.html');
	exit;
}

?>
