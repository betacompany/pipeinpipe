<?php
// Makes the avatar miniatures (<name>_sq.jpg, <name>_sq_small.jpg) missing from
// an instance's main/images/users. Pages would make them on the fly, but on
// the server PHP can't write there. Needs GD; run as the content's owner:
//
//   sudo -u ortemij php server/make-miniatures.php /var/www/pipeinpipe.info

require_once dirname(__FILE__) . '/../main/classes/image/DummyAvatarsMinifier.php';

if ($argc != 2 || !is_dir($argv[1] . '/main/images/users')) {
	fwrite(STDERR, "usage: php make-miniatures.php <pipeinpipe checkout>\n");
	exit(2);
}

// Face detection (OpenCVAvatarsMinifier) needs a binary the server doesn't
// have, so pages fall back to this one too
$minifier = DummyAvatarsMinifier::getInstance();
$made = 0;
foreach (glob($argv[1] . '/main/images/users/*.jpg') as $base) {
	if (preg_match('/_sq(_small)?\.jpg$/', $base)) continue;
	$square = substr($base, 0, -4) . '_sq.jpg';
	$small = substr($base, 0, -4) . '_sq_small.jpg';
	if (!file_exists($square)) {
		$minifier->minify($base, $square, 100, 100);
		echo "made $square\n";
		$made++;
	}
	if (!file_exists($small)) {
		$minifier->minify($square, $small, 20, 20);
		echo "made $small\n";
		$made++;
	}
}
echo "$made miniatures made\n";
