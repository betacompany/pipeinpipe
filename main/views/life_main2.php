<?php
/**
 * User: ortemij
 * Date: 02.04.12
 * Time: 9:55
 */

$items = Item::getAll(100, true);

?>

<? include dirname(__FILE__) . "/life_timeline.php"; ?>

