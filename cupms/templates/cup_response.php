<?php

require_once dirname(__FILE__) . '/../includes/config.php';
require_once dirname(__FILE__) . '/../../main/classes/cupms/Cup.php';
require_once dirname(__FILE__) . '/../../main/classes/cupms/Game.php';
require_once dirname(__FILE__) . '/../../main/includes/assertion.php';


function cup_response_short(Cup $cup) {
	if ($cup == null) {
		json_echo_error_code(ERROR_NOCUP);
	}

	echo json(array(
		'status' => 'ok',
		'cup_id' => $cup->getId(),
		'cup_name' => $cup->getName(),
		'parent_cup_id' => $cup->getParentCupId(),
		'mult' => $cup->getMultiplier()
	));
}


function cup_response_player_short($pmid) {
	$player = Player::getById($pmid);

	echo json(array(
		'status' => 'ok',
		'name' => $player->getFullName(),
		'pmid' => $pmid
	));
}

function cup_response_players($players) {
	echo '[';
	foreach ($players as $i => $player) {
		echo '{';
		echo '"id": "' . $player->getId() . '", ';
		echo '"value" :"' . $player->getShortName() . '"';
		echo '}';
		if ($i < count($players) - 1) echo ',';
	}
	echo ']';
}

function cup_response_game(Game $game) {
	echo $game->toJSON();
}
?>
