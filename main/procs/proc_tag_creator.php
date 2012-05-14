<?php
/**
 * @author Innokenty Shuvalov
 *         ishuvalov@pipeinpipe.info
 *         vk.com/innocent
 */
require_once dirname(__FILE__).'/../includes/assertion.php';
require_once dirname(__FILE__).'/../includes/common.php';
require_once dirname(__FILE__).'/../classes/user/Auth.php';
require_once dirname(__FILE__).'/../classes/user/User.php';

try {
    $method = $_REQUEST['method'];
    assertIsset($method, 'method');

    $itemId = $_REQUEST['item_id'];
    $data = $_REQUEST['data'];

    switch ($method) {
        case 'add_tag' :
            $tag = getTag($data);
            addTagToItem($itemId, $tag);
            response_json(true, array(
                'tag_value' => $tag->getValue()
            ));
            exit(0);

        case 'remove_tag' :
            if ($itemId) {
                $tag = getTag($data);
                $item = Item::getById($itemId);
                $item->removeTag($tag);
            }
            response_json(false);
            exit(0);

        case 'create_tag' :
            $auth = new Auth();
            $user = $auth->getCurrentUser();
            $tag = Tag::create($user->getId(), $data);
            addTagToItem($itemId, $tag, $user);
            response_json(true, array(
                'tag_id' => $tag->getId()
            ));
            exit(0);

        case 'get_tag_suggestions' :
            response_json(true, array(
               'tags' => Tag::getAllJSON()
            ));
            exit(0);

        default :
            response_json(false);
            exit(0);
    }
} catch (Exception $ex) {
    response_json(false, array(
        'message' => $ex->getMessage()
    ));
};;

function getTag($data) {
    assertIsset($data, 'data');
    $tag = Tag::getById($data);
    return $tag;
}

function response_json($success, $data = null) {
    $response = array('status' => ($success ? 'ok' : 'failed'));
    if($data) {
        $response = array_merge($response, $data);
    }
    echo json($response);
}

function addTagToItem($itemId, $tag, $user = null) {
    if ($itemId) {
        if (!$user) {
            $auth = new Auth();
            $user = $auth->getCurrentUser();
        }
        $item = Item::getById($itemId);
        $item->addTag($tag, $user);
    }
}
?>
