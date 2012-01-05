<?php

require_once dirname(__FILE__) . '/../includes/config.php';
require_once dirname(__FILE__) . '/../../' . MAINSITE . '/classes/cupms/League.php';

/**
 * Echos JSON with league id and name
 * @param League $league 
 */
function league_response_new(League $league) {
	if ($league == null) {
		echo json_encode(array(
			"response" => "",
			"status" => "ok"
		));
		return;
	}

	echo json(array(
		"id" => $league->getId(),
		"name" => $league->getName(),
		"status" => "ok"
	));
}

function league_response_is_such_name($name) {
	echo json_encode(League::isSuchName($name));
}

function league_response_formula($formula) {
	return json(array(
		'status' => 'ok',
		'formula' => $formula->getName()
	));
}
?>
