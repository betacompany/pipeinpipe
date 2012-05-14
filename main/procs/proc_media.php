<?php

require_once dirname(__FILE__) . '/../classes/user/Auth.php';
require_once dirname(__FILE__) . '/../classes/user/User.php';

require_once dirname(__FILE__) . '/../classes/media/Photo.php';
require_once dirname(__FILE__) . '/../classes/media/Video.php';

require_once dirname(__FILE__) . '/../classes/content/Action.php';
require_once dirname(__FILE__) . '/../classes/content/Item.php';
require_once dirname(__FILE__) . '/../classes/content/Comment.php';
require_once dirname(__FILE__) . '/../classes/content/Group.php';

require_once dirname(__FILE__) . '/../classes/utils/ResponseCache.php';

require_once dirname(__FILE__) . '/../includes/assertion.php';
require_once dirname(__FILE__) . '/../includes/error.php';
require_once dirname(__FILE__) . '/../includes/common.php';

require_once dirname(__FILE__) . '/../views/media_view.php';

try {

	assertParam('method');

	$auth = new Auth();
	$user = $auth->getCurrentUser();

	switch ($_REQUEST['method']) {
	case 'load_items':

		assertParam('group_id');
		assertParam('from');
		assertParam('limit');

		try {
			$group = Group::getById(intparam('group_id'));
			media_show_preview_items($auth->uid(), $group, intparam('from'), intparam('limit'));
		} catch (Exception $e) {
			echo 'Error';
		}

		break;

	case 'load_slideshow':

		assertParam('size');
		assertParam('group_id');

		try {
			$group = Group::getById(intparam('group_id'));
			if ($group->getType() != Group::PHOTO_ALBUM) {
				echo json(array (
					'status' => 'failed',
					'reason' => 'Not photo album'
				));
				exit(0);
			}

			if ($user != null) {
				$group->viewedBy($user);
			}

			$photos = $group->getItems();
			$response = array();
			foreach ($photos as $photo) {
				$response[$photo->getId()] = array (
					'full' => $photo->getUrl(param('size')),
					'preview' => $photo->getPreviewUrl()
				);
			}

			echo json($response);
		} catch (Exception $e) {
			echo_json_exception($e);
		}

		break;

    case 'remove_item':

        assertParam('item_id');

        $item = Item::getById(param('item_id'));
        if ($user->hasPermission($item, 'remove')) {
            $item->remove();
            echo json(array (
                'status' => 'ok'
            ));
        } else {
            echo json(array (
                'status' => 'failed',
                'reason' => 'You have no permission to remove this item!'
            ));
        }

        break;

    case 'set_video_title':

        assertParam('video_id');
        assertParam('title');

        $video = Video::getById(param('video_id'));
        if ($user->hasPermission($video, 'edit')) {
            $video->setTitle(param('title'));
            echo json(array (
                'status' => 'ok'
            ));
        } else {
            echo json(array (
                'status' => 'failed',
                'reason' => 'You have no permission to edit this vid!'
            ));
        }


        break;

    default:
        echo json(array (
            'status' => 'failed',
            'reason' => 'Unsupported method'
        ));

        break;
    }


} catch (Exception $e) {
	// TODO use error log file
	echo_json_exception($e);
}

?>
