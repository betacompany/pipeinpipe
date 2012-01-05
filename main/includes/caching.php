<?php

function cache_loading_enabled() {
	return !isset($_REQUEST['cache_load']) || $_REQUEST['cache_load'] == 'enabled';
}

function cache_storing_enabled() {
	return !isset($_REQUEST['cache_store']) || $_REQUEST['cache_store'] == 'enabled';
}

function cache_query() {
	return array_diff_key(
				$_REQUEST,
				array(
					'handler' => '',
					'method' => '',
					'cache_load' => '',
					'cache_store' => '',
					'PHPSESSID' => ''
				)
			);
}

function cache_filepath($key) {
	return dirname(__FILE__) . '/../temp/cache/' . $key . '/' .md5(cache_query());
}

function cache_dirpath($key) {
	return dirname(__FILE__) . '/../temp/cache/' . $key . '/';
}

function cache_exists($key) {
	return file_exists(cache_filepath($key));
}

function cache_get($key) {
	if (!cache_loading_enabled()) return false;
	return file_get_contents(cache_filepath($key));
}

function cache_start($key) {
	ob_start();
}

function cache_store($key) {
	if (!cache_storing_enabled()) return false;
	$contents = ob_get_contents();
	if (!cache_dirpath($key)) mkdir (cache_dirpath($key), 0777, true);
	file_put_contents(cache_filepath($key), $contents);
	ob_flush();
}

?>
