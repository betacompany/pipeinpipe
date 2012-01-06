<?php

/**
 * User: ortemij
 * Date: 06.01.12
 * Time: 15:15
 */
class OpenCVFaceDetector {

	private static $last_error_message = false;

	const PATH_TO_CLASSIFIER = "haarcascade_frontalface_alt.xml";

	public static function detectFaces($pathToImage) {
		$result = self::execute($pathToImage);
		if (!$result) {
			return false;
		}
		if ($result['count'] == 0) {
			return array();
		}
		return $result['faces'];
	}

	public static function getLastError() {
		return self::$last_error_message;
	}

	private static function execute($pathToImage) {
		$binary = dirname(__FILE__) . "/opencv-facedetect";
		if (!file_exists($binary)) {
			return false;
		}
		$binary .= " " . dirname(__FILE__) . "/" . self::PATH_TO_CLASSIFIER;
		if (!file_exists($pathToImage)) {
			return false;
		}

		$output = array();
		exec($binary . " " . $pathToImage, $output);
		global $LOG;
		@$LOG->info("Result: " . var_export($output, true));

		if (count($output) == 0) {
			return false;
		}

		$result = json_decode($output[0], true);
		if (!isset($result['status']) || $result['status'] == 'failed') {
			self::$last_error_message = $result['message'];
			return false;
		}

		if ($result['status'] != 'ok') {
			self::$last_error_message = "Unknown status";
			return false;
		}

		self::$last_error_message = false;
		return $result;
	}
}
