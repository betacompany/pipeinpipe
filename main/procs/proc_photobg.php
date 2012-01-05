<?php
/**
 * @author Artyom Grigoriev
 */

require_once dirname(__FILE__) . '/../includes/common.php';

$result = array ();
$all = glob(dirname(__FILE__) . '/../images/photobg/*.png');
foreach ($all as $imgfile) {
	$img = imagecreatefrompng($imgfile);

	$w = imagesx($img);
	$h = imagesy($img);
	$rightSide = false;
	$leftSide = false;

	for ($y = 0; $y < $h; $y++) {
		$color = imagecolorat($img, $w - 1, $y);
		$r = ($color >> 16) & 0xFF;
		$g = ($color >> 8) & 0xFF;
		$b = $color & 0xFF;
		if ($r != 255 || $g != 255 || $b != 255) {
			$rightSide = true;
		}

		$color = imagecolorat($img, 0, $y);
		$r = ($color >> 16) & 0xFF;
		$g = ($color >> 8) & 0xFF;
		$b = $color & 0xFF;
		if ($r != 255 || $g != 255 || $b != 255) {
			$leftSide = true;
		}
	}
	
	list ($prefix, $imgurl) = explode('..', $imgfile, 2);

	$result[] = array (
		'url' => $imgurl,
		'w' => $w,
		'h' => $h,
		'left_side' => $leftSide,
		'right_side' => $rightSide
	);

	imagedestroy($img);
}

if (empty ($result)) exit(0);

echo json(array (
	'status' => 'ok',
	'images' => $result
));

?>
