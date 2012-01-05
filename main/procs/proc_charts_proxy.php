<?php

require_once dirname(__FILE__) . '/../classes/utils/Cache.php';

if (!isset($_REQUEST['hash'])) {
	Header('Status: 404');
	exit(0);
}

$cache = new Cache('charts');
$data = $cache->getByHash($_REQUEST['hash']);
$post_data = unserialize($data);
if (!empty($_REQUEST['chs'])) {
	$post_data['chs'] = $_REQUEST['chs'];
}

$url = 'http://chart.apis.google.com/chart';
$context = stream_context_create(
	array(
		'http' => array(
			'method' => 'POST',
			'content' => http_build_query($post_data)
		)
	)
);

Header('Content-type: image/png');
fpassthru(fopen($url, 'r', false, $context));

// TODO remove but be sure of security
// (как бы злоумышленник в качестве хэша не передал путь к файлу...)
//$cache->removeByHash($_REQUEST['hash']);

?>
