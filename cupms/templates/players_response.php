<?php

require_once dirname(__FILE__) . '/../includes/config.php';
require_once dirname(__FILE__) . '/../../main/classes/cupms/Player.php';
require_once dirname(__FILE__) . '/../../main/includes/assertion.php';

function players_response_player($pmid) {
	$player = Player::getById($pmid);
	echo $player->toJSON();
}

?>
