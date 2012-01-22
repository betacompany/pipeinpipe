<?php

require_once dirname(__FILE__) . '/IAvatarsMinifier.php';

/**
 * User: ortemij
 * Date: 05.01.12
 * Time: 19:08
 */
class DummyAvatarsMinifier implements IAvatarsMinifier {

	private static $instance = false;

	/**
	 * @param $pathToSourceImage
	 * @param $pathToDestinationImage
	 * @param $destinationWidth
	 * @param $destinationHeight
	 */
	public function minify($pathToSourceImage, $pathToDestinationImage, $destinationWidth, $destinationHeight) {
		$src_image = imagecreatefromjpeg($pathToSourceImage);
		$dst_image = imagecreatetruecolor($destinationWidth, $destinationHeight);

		$src_w = imagesx($src_image);
		$src_h = imagesy($src_image);

		if ($src_w > $src_h) {
			imagecopyresampled($dst_image, $src_image,
				0, 0, 0, 0, $destinationWidth, $destinationHeight,
				$destinationWidth * $src_h / $destinationHeight, $src_h);
		} else {
			imagecopyresampled($dst_image, $src_image,
				0, 0, 0, 0, $destinationWidth, $destinationHeight,
				$src_w, $destinationHeight * $src_w / $destinationWidth);
		}

		imagejpeg($dst_image, $pathToDestinationImage);

		imagedestroy($dst_image);
		imagedestroy($src_image);
	}

	private function __construct() {}

	/**
	 * @return IAvatarsMinifier
	 */
	public static function getInstance() {
		if (self::$instance) return self::$instance;
		return self::$instance = new DummyAvatarsMinifier();
	}
}
