<?php

$PROPERTIES = array (
	
	'db' => array (
		'username' => 'root',
		'password' => '',
		'database' => 'ortemij_pipeinpipe'
	),
	
	// Cookies settings. Think twice before changing
	'cookies' => array (
		'domain' => '.pipev4.ru',
		'expire' => 3600 * 24 * 365,
		'secure' => false,
		'http'   => true 
	),
	
	// Sites' URLs
	'main_site_url'  => 'main.pipev4.ru',
	'cupms_site_url' => 'cupms.pipev4.ru',
	'junk_site_url'  => 'junk.pipev4.ru',
	'mobile_site_url' => 'mobile.pipev4.ru',
	
	// MySQL settings
	'mysql' => array (
		// If MYSQL_DEBUG_MODE=true then logging and counting of queries are enabled
		'debug_mode' => true,
		'first_query' => 'SET NAMES utf8'
	),
	
	// Trace exceptions in JSON answers
	'error_debug_mode' => true,
	
	// Log file maximum size = 1M
	'log_file_max_size' => 1024 * 1024,
	
	// Subroot folders' names
	'folder' => array (
		'main'     => 'main',
		'cupms'    => 'cupms',
		'junk'     => 'junk',
		'old_site' => 'pipeinpipe.info'
	),
	
	// Closure Compiler
	'closure_compiler_enabled' => true,

	// YUI Compiler
	'yui_compiler_enabled' => true,

	'version' => 1
);

?>
