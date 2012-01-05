<html>
	<head>
		<title>photos</title>
	</head>
	<body>

<?php

require_once '../../main/classes/media/Photo.php';

echo '<pre>';

$photos = Photo::getByGroupId(22);

foreach ($photos as $photo) {
	$urls = $photo->getUrls();
	echo $photo->getId() . "\n";
	foreach ($urls as $size => $url) {
		echo "\t$size => $url\n";
	}
	echo "\t HQ:" . $photo->getNearestAvailableSize(Photo::SIZE_HQ) . "\n";
	echo "\t N:" . $photo->getNearestAvailableSize(Photo::SIZE_MIDDLE) . "\n";
	echo "\t MINI:" . $photo->getNearestAvailableSize(Photo::SIZE_MINI) . "\n";
	echo "\t MICRO:" . $photo->getNearestAvailableSize(Photo::SIZE_MICRO) . "\n";


}

echo '</pre>';

?>

<form action="photos_handler.php" method="post">
	<textarea name="urls" cols="100" rows="5"></textarea><br/>
	<input type="submit" />
</form>

	</body>
</html>