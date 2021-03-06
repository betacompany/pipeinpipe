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
    if (!$user) {
        echo_json(false, 'Сначала нужно залогиниться!');
        exit(0);
    }

    $uid = $user->getId();

    switch ($_REQUEST['method']) {
        case 'youtube':
            assertParam('video_title');
            $videoTitle = param('video_title');

            assertParam('video_link');
            $videoLink = param('video_link');

            assertParam('group_id');
            $groupId = param('group_id');

            $group = Group::getById($groupId);

            $videoId = Video::parseLink($videoLink);
            if ($videoId && $videoTitle && $group && $group->getType == Group::VIDEO_ALBUM && $user) {
                $video = Video::create($groupId, $uid, $videoTitle, $videoId);
                $tagIds = array_slice( explode(',', param('video_tags')), 0, 100 ); // protection from too many tags
                $video->addTags($tagIds, $user);
                Header("Location: /media/video/album{$groupId}/{$video->getId()}");
            } else {
                Header("Location: /media/upload/video_youtube");
            }

            exit(0);

        case 'vk_photos':
            assertParam('photos');
            $photos = json_decode(param('photos'), true);

            assertParam('group_id');
            $groupId = param('group_id');
            $group = Group::getById($groupId);

            assertParam('tags');
            $tagIds = json_decode(param('tags'));

            if(!$group || $group->getType() != Group::PHOTO_ALBUM) {
                echo_json(false, 'Нужно правильно указать альбом с фотографиями!');
            } else {
                foreach ($photos as $photo) {
                    $photoObj = Photo::create($groupId, $uid, "Фотография", array(
                        Photo::SIZE_MICRO => $photo['micro'],
                        Photo::SIZE_MINI => $photo['mini'],
                        Photo::SIZE_MIDDLE => $photo['middle'],
                        Photo::SIZE_HQ => $photo['hq']
                    ));

                    $photoObj->addTags($tagIds, $user);

                    if (!$redirect) {
                        $redirect = "/media/photo/album{$groupId}/{$photoObj->getId()}";
                    }
                }
                echo_json(true, array(
                    'redirect' => $redirect
                ));
            }

            exit(0);
    }

} catch (Exception $e) {
    global $LOG;
    @$LOG->exception($e);
    echo_json_exception($e);
}

function echo_json($status, $data = null) {
    $response = array('status' => ($status ? 'ok' : 'failed'));
    if($data) {
        if (is_string($data)) {
            $response = array_merge($response, array(
                'message' => $data
            ));
        } elseif (is_array($data)) {
            $response = array_merge($response, $data);
        }
    }
    echo json($response);
}
?>
