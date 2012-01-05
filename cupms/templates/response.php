<?php
/**
 * @author Innokenty Shuvalov
 */

/**
 * sends boolean in JSON
 * @param <boolean> $bool
 */
function response_boolean($bool) {
	echo json_encode(array(
		"status" => "ok",
		"result" => $bool
	));
}
	
function response_success(array $data = null) {
	if($data == null) {
		$response = array('status' => 'ok');
	} else {
		$response = array_merge(array('status' => 'ok'), $data);
	}
	echo json_encode($response);
}
?>