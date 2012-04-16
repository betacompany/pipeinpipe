<?php
/**
 * @author Innokenty Shuvalov
 *         ishuvalov@pipeinpipe.info
 *         vk.com/innocent
 */

require_once dirname(__FILE__) . '/../classes/user/Auth.php';
require_once dirname(__FILE__) . '/../classes/user/User.php';

require_once dirname(__FILE__) . '/../classes/content/Item.php';
require_once dirname(__FILE__) . '/../classes/media/Video.php';

require_once dirname(__FILE__) . '/../classes/utils/ResponseCache.php';
require_once dirname(__FILE__) . '/../classes/utils/Logger.php';

require_once dirname(__FILE__) . '/../includes/assertion.php';
require_once dirname(__FILE__) . '/../includes/error.php';
require_once dirname(__FILE__) . '/../includes/common.php';

$LOG = new Logger();

try {

    assertParam('method');

    $auth = new Auth();
    $user = $auth->getCurrentUser();

	switch ($_REQUEST['method']) {
        case 'youtube':
            assertParam('video_title');
            $videoTitle = param('video_title');

            assertParam('video_link');
            $videoLink = param('video_link');

            assertParam('group_id');
            $groupId = param('group_id');

            $videoId = Video::parseLink($videoLink);
            if ($videoId && $videoTitle && $groupId && user) {
                $video = Video::create($groupId, $user->getId(), $videoTitle, $videoId);
                Header("Location: /media/video/album{$groupId}/{$video->getId()}");
            } else {
                Header("Location: /media/upload/video_youtube");
            }


            exit(0);
    }

} catch (Exception $e) {
    global $LOG;
    @$LOG->exception($e);
    echo_json_exception($e);
}
?>
