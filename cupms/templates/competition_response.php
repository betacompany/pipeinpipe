<?php

require_once dirname(__FILE__) . '/../includes/config.php';
require_once dirname(__FILE__) . '/../../main/classes/cupms/Competition.php';

function competition_response_id(Competition $competition) {
	if ($competition == null) {
		json_echo_error_code(ERROR_NOCOMPETITION);
		return;
	}

	echo json_encode(array(
		'status' => 'ok',
		'competition_id' => $competition->getId()
	));
}

function competition_response_tournament(Competition $competition) {
	if ($competition == null) {
		json_echo_error_code(ERROR_NOCOMPETITION);
		return;
	}
	echo json(array(
		'status' => 'ok',
		'comp_id' => $competition->getId(),
		'tour_id' => $competition->getTournamentId(),
		'tour_name' => $competition->getTournament()->getName()
	));
}

?>
