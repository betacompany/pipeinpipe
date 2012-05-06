<?php

require_once dirname(__FILE__) . '/../classes/user/Auth.php';
require_once dirname(__FILE__) . '/../classes/user/User.php';

require_once dirname(__FILE__) . '/../classes/content/Action.php';
require_once dirname(__FILE__) . '/../classes/content/Comment.php';
require_once dirname(__FILE__) . '/../classes/content/Item.php';
require_once dirname(__FILE__) . '/../classes/content/ContentFactory.php';

require_once dirname(__FILE__) . '/../includes/assertion.php';
require_once dirname(__FILE__) . '/../includes/error.php';
require_once dirname(__FILE__) . '/../includes/common.php';
require_once dirname(__FILE__) . '/../includes/date.php';
require_once dirname(__FILE__) . '/../includes/lang.php';

require_once dirname(__FILE__) . '/../views/blocks.php';

function incorrect_api() {
	echo json(array (
		'status' => 'failed',
		'reason' => 'this api is not for forum comments'
	));

	exit(0);
}

try {

	assertParam('method');

	$auth = new Auth();
	
	if ($auth->isAuth()) {
		switch (param('method')) {

		/*
		 * @param item_id
		 * @param text
		 * @format JSON
		 */
		case 'add_comment':
			
			assertParam('item_id');
			assertParam('text');

			try {
				$item = Item::getById(param('item_id'));
				if ($item instanceof ForumTopic) incorrect_api();

				$contentSource = ContentFactory::toContentSource(ContentFactory::COMMENT, textparam('text'));
				$contentParsed = ContentFactory::toContentParsed(ContentFactory::COMMENT, textparam('text'));
				$timestamp = time();
				$comment = $item->addComment(
								Comment::BASIC_COMMENT,
								$auth->uid(),
								$timestamp,
								$contentSource,
								$contentParsed
							);
				if ($comment) {
					echo json(array (
						'status' => 'ok',
						'comment' => $comment->toArray(),
						'count' => $item->countComments()
					));
					exit(0);
				} else {
					echo json(array (
						'status' => 'failed'
					));
					exit(0);
				}
			} catch (Exception $e) {
				echo_json_exception($e);
			}

			break;

		/*
		 * @param item_id
		 * @param value
		 * @format JSON
		 */
		case 'evaluate':

			assertParam('item_id');
			assertParam('value');

			$value = intparam('value');
			$itemId = intparam('item_id');

			try {
				$item = Item::getById($itemId);
				if ($item->act($auth->getCurrentUser(), Action::EVALUATION, $value)) {
					echo json(array (
						'status' => 'ok',
						'item_id' => $item->getId(),
						'value' => $item->getEvaluation()
					));
				} else {
					echo json(array (
						'status' => 'failed'
					));
				}
			} catch (Exception $e) {
				echo_json_exception($e);
			}

			break;

		/*
		 * @param target_type
		 * @param target_id
		 * @format JSON
		 */
		case 'mark_as_viewed':

			assertParam('target_type');
			assertParam('target_id');

			try {
				$user = $auth->getCurrentUser();

				switch (param('target_type')) {
				case 'group':
					$group = Group::getById(intparam('target_id'));
					$group->viewedBy($user);
					echo json(array (
						'status' => 'ok'
					));
					break;

				case 'item':
					$item = Item::getById(intparam('target_id'));
					$item->viewedBy($user);
					echo json(array (
						'status' => 'ok'
					));
					break;

				default:
					echo json(array (
						'status' => 'failed',
						'reason' => 'Incorrect target type'
					));
				}
			} catch (Exception $e) {
				echo_json_exception($e);
			}

			break;

		case 'report_bug':

			assertParam('text');
			assertParam('page');

			$topics = ForumTopic::getByTitle(param('page'));
			$topic = false;
			if (count($topics)) {
				$topic = $topics[0];
			} else {
				$topic = ForumTopic::create(Forum::BUG_PART_ID, $auth->uid(), param('page'));
			}

			if ($topic) {
				$topic->addComment($auth->uid(), textparam('text'), time());
				echo json(array(
					'status' => 'ok',
					'topic' => '/forum/part' . $topic->getPartId() . '/topic' . $topic->getId()
				));
			} else {
				echo json(array('status' => 'failed'));
			}

			break;

		}
	}

	switch (param('method')) {

	/*
	 * @param item_id
	 * @param from
	 * @param limit
	 * @format HTML
	 */
	case 'get_comments':

		assertParam('item_id');
		assertParam('from');
		assertParam('limit');

		try {
			$item = Item::getById(param('item_id'));
			if ($item instanceof ForumTopic) incorrect_api();
			$limit = min(intval(param('limit')), 20);
			$from = intval(param('from'));

			show_comments_page($item, $from, $limit);
		} catch (Exception $e) {
			
		}

		break;

	case 'get_initial_comments':

		assertParam('item_id');

		try {
			global $user;
			$item = Item::getById(intparam('item_id'));
			show_block_comments($user, $item);
		} catch (Exception $e) {

		}

		break;

	/*
	 * @param item_id
	 * @format JSON
	 */
	case 'get_evaluations':

		assertParam('item_id');

		try {
			$item = Item::getById(param('item_id'));

			if (!$item->isEvaluable()) {
				echo json(array (
					'status' => 'failed',
					'reason' => 'this item is not evaluable'
				));
			}

			$result = array();
			$avg = 0;
			$actions = $item->getActions();
			foreach ($actions as $action) {
				if ($action->getType() == Action::EVALUATION) {
					$avg += ($result[] = $action->getValue());
				}
			}
			$n = count($result);
			if ($n) {
				$avg = $avg / $n;
			}

			global $user;

			echo json(array (
				'status' => 'ok',
				'item_id' => param('item_id'),
				'actions' => $result,
				'avg' => $avg,
				'is_evaluable' => $item->isActedBy($user = $auth->getCurrentUser(), Action::EVALUATION)
			));
		} catch (Exception $e) {
			echo_json_exception($e);
		}

		break;

	/*
	 * @param item_id
	 * @format JSON
	 */
	case 'get_actions':

		assertParam('item_id');

		try {
			$actions = Action::getActionsFor(Action::TARGET_ITEM, intval(param('item_id')));
			$result = array();
			foreach ($actions as $action) {
				$result[] = $action->toArray();
			}

			echo json(array (
				'status' => 'ok',
				'item_id' => param('item_id'),
				'actions' => $result
			));
		} catch (Exception $e) {
			echo_json_exception($e);
		}

		break;
	}

} catch (Exception $e) {
	
}

?>
