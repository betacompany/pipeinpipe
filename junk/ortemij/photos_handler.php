<?php

require_once '../../main/classes/media/Photo.php';

$data = explode("\n", $_REQUEST['urls']);
$urls = array();
foreach ($data as $row) {
	list($size, $url) = explode('=', $row, 2);
	$urls[$size] = $url;
}

Photo::create(14, 1, "Заголовок", $urls);

Header('Location: photo.php');
exit(0);

?>
