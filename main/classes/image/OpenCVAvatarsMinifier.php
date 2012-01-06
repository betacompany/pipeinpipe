<?php

require_once dirname(__FILE__) . '/IAvatarsMinifier.php';
require_once dirname(__FILE__) . '/DummyAvatarsMinifier.php';

require_once dirname(__FILE__) . '/OpenCVFaceDetector.php';

/**
 * User: ortemij
 * Date: 06.01.12
 * Time: 15:14
 */
class OpenCVAvatarsMinifier implements IAvatarsMinifier {

	private static $instance = false;

	/**
	 * @param $pathToSourceImage
	 * @param $pathToDestinationImage
	 * @param $destinationWidth
	 * @param $destinationHeight
	 * @static
	 */
	public function minify($pathToSourceImage, $pathToDestinationImage, $destinationWidth, $destinationHeight) {

		$faces = OpenCVFaceDetector::detectFaces($pathToSourceImage);

		$dummyAvatarsMinifier = DummyAvatarsMinifier::getInstance();

		global $LOG;

		if (!$faces || count($faces) == 0) {
			@$LOG->info("No faces detected on $pathToSourceImage, dummy minifier called");
			$dummyAvatarsMinifier->minify($pathToSourceImage, $pathToDestinationImage, $destinationWidth, $destinationHeight);
			return;
		}

		$ratio = $destinationWidth / $destinationHeight;
		$best_face = $faces[0];
		$best_measure = self::measure($best_face, $ratio);
		@$LOG->info("Faces: " . var_export($faces, true));
		foreach ($faces as $face) {
			// Enlarging rectangle
			$face = self::resizeRect($face, $face['w'] / 2, $face['h'] / 2);
			$measure = self::measure($face, $ratio);
			if ($measure > $best_measure) {
				$best_face = $face;
				$best_measure = $measure;
			}
			@$LOG->info("Face: " . var_export($face, true) . "; measure=" . $measure);
		}

		$srcImage = imagecreatefromjpeg($pathToSourceImage);
		$dstImage = imagecreatetruecolor($destinationWidth, $destinationHeight);
		$srcWidth = imagesx($srcImage);
		$srcHeight = imagesy($srcImage);

		$adjusted = self::adjustSize($best_face, $srcWidth, $srcHeight, $destinationWidth, $destinationHeight);
		@$LOG->info("Adjusted: " . var_export($adjusted, true));

		imagecopyresampled(
			$dstImage,
			$srcImage,
			0, 0,
			$adjusted['x'], $adjusted['y'],
			$destinationWidth, $destinationHeight,
			$adjusted['w'], $adjusted['h']
		);

		@$LOG->info("Path: $pathToDestinationImage");
		imagejpeg($dstImage, $pathToDestinationImage);

		imagedestroy($srcImage);
		imagedestroy($dstImage);
	}

	/**
	 * @static
	 * @return IAvatarsMinifier
	 */
	public static function getInstance() {
		if (!self::$instance) {
			return self::$instance = new OpenCVAvatarsMinifier();
		}
		return self::$instance;
	}

	private static function measure($rect, $ratio) {
		$d = $ratio - $rect['w'] / $rect['h'];
		return $rect['w'] * $rect['h'] / (1 + $d * $d);
	}

	private static function adjustSize($rect, $srcWidht, $srcHeight, $needWidth, $needHeight) {

		$ratio = $needWidth / $needHeight;
		if ($needWidth > $srcWidht) {
			$needWidth = $srcWidht;
			$needHeight = round($needWidth / $ratio);
		}
		if ($needHeight > $srcHeight) {
			$needHeight = $srcHeight;
			$needWidth = round($needHeight * $ratio);
		}

		if ($rect['w'] > $rect['h']) {
			$rh = round($rect['w'] / $ratio);
			$dh = $rh - $rect['h'];
			$rect = self::resizeRect($rect, 0, $dh);
		} else {
			$rw = round($rect['h'] * $ratio);
			$dw = $rw - $rect['w'];
			$rect = self::resizeRect($rect, $dw, 0);
		}

		if ($rect['w'] > $needWidth && $rect['h'] > $needHeight) {
			return $rect;
		}

		// todo

		return $rect;
	}

	private static function resizeRect($rect, $dw, $dh) {
		return array (
			'x' => round($rect['x'] - $dw / 2),
			'y' => round($rect['y'] - $dh / 2),
			'w' => $rect['w'] + $dw,
			'h' => $rect['h'] + $dh
		);
	}

	private static function moveRect($rect, $dx, $dy) {
		return array (
			'x' => $rect['x'] + $dx,
			'y' => $rect['y'] + $dy,
			'w' => $rect['w'],
			'h' => $rect['h']
		);
	}
}
