<?php
/**
 * User: ortemij
 * Date: 02.04.12
 * Time: 9:55
 */

require_once dirname(__FILE__) . '/../includes/import.php';

import('content/Feed');

$items = Feed::getBefore(2000);

?>

<? include dirname(__FILE__) . "/life_timeline.php"; ?>

