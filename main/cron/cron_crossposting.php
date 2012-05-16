<?php
/**
 * User: ortemij
 * Date: 28.03.12
 * Time: 12:09
 */

require_once dirname(__FILE__) . '/../includes/common.php';

if (param('password') != 'yreutywerUIUihkhsdjahsduhweuUUUUU') {
	echo 'ACCESS DENIED!';
	exit(0);
}

require_once dirname(__FILE__) . '/../classes/utils/Logger.php';
require_once dirname(__FILE__) . '/../classes/utils/Lock.php';

require_once dirname(__FILE__) . '/../classes/social/SocialPost.php';
require_once dirname(__FILE__) . '/../classes/social/SocialPhoto.php';
require_once dirname(__FILE__) . '/../classes/social/CrossPost.php';
require_once dirname(__FILE__) . '/../classes/social/CrossPhoto.php';

$logger = new Logger('../../logs/cross_posting.log');
$logger->info("Cross posting started");

$lock = new Lock("crossposting", $logger);
if ($lock->isLocked()) {
	$logger->warn("Locked!");
} else {
	$lock->lock();
	try {
		$allUnhandled = SocialPost::getAllUnhandled();

		foreach ($allUnhandled as $socialPost) {
			$crossPost = CrossPost::create($socialPost);
			$logger->info("Cross post [id={$crossPost->getId()}] created from social post [id={$socialPost->getId()}]");
			$socialPhotos = SocialPhoto::getBySocialPost($socialPost);
			foreach ($socialPhotos as $socialPhoto) {
				$photo = CrossPhoto::create($socialPhoto, $crossPost);
				$logger->info("Cross photo [id={$photo->getId()}] created from social photo [id={$socialPhoto->getId()}]");
			}
		}
	} catch (Exception $e) {
		$logger->exception($e);
		echo 'ERROR!';
	}
	$lock->release();
}

$logger->info("Cross posting finished");

?>