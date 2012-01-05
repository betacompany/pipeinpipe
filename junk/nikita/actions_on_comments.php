<?php

require_once dirname(__FILE__) . '/../../main/classes/stats/ForumStatsCounter.php';

$statsCounter = new ForumStatsCounter();
echo "<pre>";
print_r(ForumStatsCounter::getPairActions('roman', 0, 20));
echo "</pre>";
?>
