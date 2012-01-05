<?php
/**
 * @author Artyom Grigoriev
 */
require_once dirname(__FILE__).'/../main/includes/assertion.php';
require_once dirname(__FILE__).'/../main/includes/common.php';

try {
	$SUPPORTED = array('proc_main');
	assertParam('proxy_proc');
	assertTrue('Unsupported proxy', array_contains($SUPPORTED, param('proxy_proc')));

	require_once dirname(__FILE__) . '/../main/procs/'.param('proxy_proc').'.php';
	
} catch (Exception $e) {
	echo $e->getMessage();
}
?>
