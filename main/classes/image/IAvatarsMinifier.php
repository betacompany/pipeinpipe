<?php
/**
 * User: ortemij
 * Date: 05.01.12
 * Time: 19:05
 */
interface IAvatarsMinifier {
	/**
	 * @param $pathToSourceImage
	 * @param $pathToDestinationImage
	 * @param $destinationWidth
	 * @param $destinationHeight
	 * @static
	 * @abstract
	 */
	function minify($pathToSourceImage, $pathToDestinationImage, $destinationWidth, $destinationHeight);

	/**
	 * @static
	 * @abstract
	 * @return IAvatarsMinifier
	 */
	static function getInstance();
}
