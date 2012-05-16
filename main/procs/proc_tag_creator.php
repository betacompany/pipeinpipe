<?php
/**
 * @author Innokenty Shuvalov
 *         ishuvalov@pipeinpipe.info
 *         vk.com/innocent
 */
require_once dirname(__FILE__).'/../includes/assertion.php';
require_once dirname(__FILE__).'/../includes/common.php';
require_once dirname(__FILE__).'/../includes/error.php';
require_once dirname(__FILE__).'/../classes/user/Auth.php';
require_once dirname(__FILE__).'/../classes/user/User.php';

try {

    $auth = new Auth();
    $user = $auth->getCurrentUser();
    if (!$user) {
        echo_json(false, 'Для изменения тегов нужно залогиниться!');
        exit(0);
    }

    $method = $_REQUEST['method'];
    assertIsset($method, 'method');

    $itemId = $_REQUEST['item_id'];
    $data = $_REQUEST['data'];

    switch ($method) {
        case 'add_tag' :
            $tag = getTag($data);
            if ($itemId) {
                addTagToItem($itemId, $tag, $user);
            }
            echo_json(true, array(
                'tag_value' => $tag->getValue()
            ));
            exit(0);

        case 'remove_tag' :
            assertParam('item_id');
            $tag = getTag($data);
            $item = Item::getById($itemId);

            if ($user->hasPermission(array(
                'item' => $item,
                'tag' => $tag
            ), 'remove_tag')) {
                $item->removeTag($tag);
                echo_json(true);
            } else {
                echo_json(false, "You do not have permission to do this!");
            }
            exit(0);

        case 'create_tag' :
            $tag = Tag::create($user->getId(), $data);
            if ($itemId) {
                addTagToItem($itemId, $tag, $user);
            }
            echo_json(true, array(
                'tag_id' => $tag->getId()
            ));
            exit(0);

        case 'get_tag_suggestions' :
            echo_json(true, array(
               'tags' => Tag::getAllJSON()
            ));
            exit(0);

        default :
            echo_json(false);
            exit(0);
    }
} catch (Exception $ex) {
    echo_json_exception($ex);
};;

function getTag($data) {
    assertIsset($data, 'data');
    $tag = Tag::getById($data);
    return $tag;
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

function addTagToItem($itemId, $tag, $user) {
    $item = Item::getById($itemId);
    if ($user->hasPermission($item, 'add_tag')) {
        $item->addTag($tag, $user);
    } else {
        echo_json(false, "You do not have permission to do this!");
        exit(0);
    }
}
?>
