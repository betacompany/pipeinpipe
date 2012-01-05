<?php

require_once dirname(__FILE__) . '/../classes/user/Auth.php';
require_once dirname(__FILE__) . '/../classes/user/User.php';

require_once dirname(__FILE__) . '/../classes/forum/Forum.php';
require_once dirname(__FILE__) . '/../classes/forum/ForumMessage.php';

require_once dirname(__FILE__) . '/../classes/content/Action.php';

require_once dirname(__FILE__) . '/../classes/utils/ResponseCache.php';
require_once dirname(__FILE__) . '/../classes/utils/Logger.php';

require_once dirname(__FILE__) . '/../includes/assertion.php';
require_once dirname(__FILE__) . '/../includes/error.php';
require_once dirname(__FILE__) . '/../includes/common.php';

require_once dirname(__FILE__) . '/../views/forum_view.php';

$LOG = new Logger();

$mobile = issetParam('mobile') && param('mobile') == '1';

try {

	assertIsset($_REQUEST['method']);

	$auth = new Auth();
	$user = ($auth->isAuth()) ? $auth->getCurrentUser() : null;

	switch ($_REQUEST['method']) {
	case 'load_topics':
		assertIsset($_REQUEST['part_id']);

		$partId = intval($_REQUEST['part_id']);
		$from = isset($_REQUEST['from']) ? intval($_REQUEST['from']) : 0;
		$limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 0;

		try {
			$part = new ForumPart($partId);
			forum_show_topics($auth->uid(), $part, $from, $limit);
		} catch (Exception $e) {
			$LOG->exception($e);
		}

		break;

	case 'load_last_topics':
		assertIsset($_REQUEST['part_id']);

		$partId = intval($_REQUEST['part_id']);

		try {
			$part = new ForumPart($partId);
			$limit = Forum::MESSAGES_PER_PAGE;
			$from = floor(($part->countTopics() - 1) / $limit) * $limit;
			forum_show_topics($auth->uid(), $part, $from, $limit);
		} catch (Exception $e) {
			$LOG->exception($e);
		}

		break;

	case 'load_topics_top':
		assertIsset($_REQUEST['part_id']);

		$partId = intval($_REQUEST['part_id']);

		try {
			$part = new ForumPart($partId);
			$limit = Forum::MESSAGES_PER_PAGE;
			$from = floor(($part->countTopics() - 1) / $limit) * $limit;
			forum_show_topics($auth->uid(), $part);
		} catch (Exception $e) {
			$LOG->exception($e);
		}

		break;

	case 'load_messages':
		assertIsset($_REQUEST['topic_id']);

		$topicId = intval($_REQUEST['topic_id']);
		$from = intval(isset($_REQUEST['from']) ? $_REQUEST['from'] : 0);
		$limit = intval(isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 0);

		try {
			$topic = new ForumTopic($topicId);

			if ($topic->isClosed()) {
				// Enabling caching for closed topics
				$cache = new ResponseCache(
					"forum/topic$topicId/messages/",
					array (
						'limit' => $limit,
						'from' => $from
					)
				);
				$cache->echoAndExit();
				$cache->start();
				forum_show_messages($auth->uid(), $topic, $from, $limit);
				$cache->store();
			} else {
				forum_show_messages($auth->uid(), $topic, $from, $limit);
			}

			if (!$topic->isClosed() && $auth->isAuth()) {
				$topic->visit($user);
			}
		} catch (Exception $e) {
			$LOG->exception($e);
		}

		break;

	case 'load_last_messages':
		assertIsset($_REQUEST['topic_id']);

		$topicId = intval($_REQUEST['topic_id']);

		try {
			$topic = new ForumTopic($topicId);
			$limit = Forum::MESSAGES_PER_PAGE;
			$from = floor(($topic->countMessages() - 1) / $limit) * $limit;
			forum_show_messages($auth->uid(), $topic, $from, $limit);

			if ($auth->isAuth()) {
				$topic->visit($user);
			}
		} catch (Exception $e) {
			$LOG->exception($e);
		}

		break;

	case 'load_last_one':
		assertIsset($_REQUEST['topic_id']);

		$id = intval($_REQUEST['topic_id']);

		try {
			$topic = new ForumTopic($id);
			$last = $topic->getLastMessage();
			$count = $topic->countMessages();
			echo xml(array (
				'result' => forum_html_last_message($last),
				'count' => $count . ' ' . lang_sclon($count, 'сообщение', 'сообщения', 'сообщений')
			));

			if ($auth->isAuth()) {
				$topic->visit($user);
			}
		} catch (Exception $e) {
			$LOG->exception($e);
		}

		break;

	case 'add_message':
		assertIsset($_REQUEST['topic_id']);
		assertIsset($_REQUEST['html']);

		if ($auth->isAuth()) {
			$topicId = intval($_REQUEST['topic_id']);
			$html = $mobile ? param('html') : string_convert($_REQUEST['html']);
			$uid = $user->getId();
			$timestamp = time();

			$topic = Item::getById($topicId);
			if ($topic instanceof ForumTopic) {
				if ($msg = $topic->addComment($uid, $html, $timestamp)) {
					if (!$mobile) {
						echo json_encode(array(
							'status' => 'ok',
							'msgid' => $msg->getId()
						));
					} else {
						$referrer = $_SERVER['HTTP_REFERER'];
						$new_referrer = preg_replace('/\/page([0-9]+)/', '', $referrer);
						Header('Location: '.$new_referrer);
						exit(0);
					}

				} else {
					if (!$mobile) {
						echo json_encode(array(
							'status' => 'failed',
							'reason' => $topic->isClosed() ? 'topic is closed' : 'no access'
						));
					} else {
						redirect_back('failed');
					}
				}
			} else {
				global $LOG;
				@$LOG->warn('Item with id='.$topicId.' is not a forum topic');
				if (!$mobile) {
					echo json(array (
						'status' => 'failed',
						'reason' => 'this is not a forum topic'
					));
				} else {
					redirect_back('failed');
				}
			}

			exit(0);
		}

		if (!$mobile) {
			echo json_encode(array (
				'status' => 'failed',
				'reason' => 'access denied'
			));
		} else {
			redirect_back('access_denied');
		}

		break;

	case 'act':
		if ($auth->isAuth()) {
			$user = $auth->getCurrentUser();
			switch ($_REQUEST['method']) {
			case 'act':
				assertIsset($_REQUEST['msg_id']);
				assertIsset($_REQUEST['action']);

				try {
					$msg = new ForumMessage($_REQUEST['msg_id']);
					$type = $_REQUEST['action'];
					$actions = $msg->getActions();
					$actions = isset($actions[$type]) ? $actions[$type] : array();

					$jsonActions = array();
					foreach ($actions as $action) {
						if ($action->getAuthorId() == $user->getId()) {
							if (!$mobile) {
								echo json(array (
									'status' => 'failed',
									'reason' => 'such action exists'
								));

								exit(0);
							} else {
								redirect_back('failed');
							}
						}
					}
					
					$ok = $msg->act($user, $type);
					if (!$ok) {
						if (!$mobile) {
							echo json(array (
								'status' => 'failed'
							));
							exit(0);
						} else {
							redirect_back('failed');
						}
					}

					$actions = $msg->getActions();
					$actions = isset($actions[$type]) ? $actions[$type] : array();

					if (!$mobile) {
						echo json(array (
							'status' => 'ok',
							'type' => $type,
							'count' => count($actions)
						));
					} else {
						redirect_back('ok');
					}


				} catch (InvalidIdException $e) {
					echo_json_exception($e);
				}

				break;
			}

			exit(0);
		}

		echo json_encode(array (
			'status' => 'failed',
			'reason' => 'access denied'
		));

		break;

	case 'create_topic':
		if ($auth->isAuth()) {
			assertIsset($_REQUEST['part_id']);
			assertIsset($_REQUEST['value']);

			$partId = intval($_REQUEST['part_id']);
			$title = string_convert($_REQUEST['value']);
			$uid = $user->getId();

			try {
				$topic = ForumTopic::create($partId, $uid, $title);
				if ($topic != null) {
					echo json(array (
						'status' => 'ok',
						'topic_id' => $topic->getId()
					));
				} else {
					echo json(array (
						'status' => 'failed',
						'reason' => 'Topic is null'
					));
				}
			} catch (Exception $e) {
				echo_json_exception($e);
			}

			exit(0);
		}

		echo json_encode(array (
			'status' => 'failed',
			'reason' => 'access denied'
		));

		break;

	case 'create_continuation':
		if ($auth->isAuth()) {
			assertParam('topic_id');
			$newTopicId = $topicId = param('topic_id');
			$uid = $user->getId();

			$topic = ForumTopic::getById($topicId);
			if ($topic != null) {
				$partId = $topic->getPartId();
				$oldTitle = $topic->getTitle();
				$title = $oldTitle . " (продолжение)";
				if (preg_match("/\(продолжение\)/", $oldTitle)) {
					$title = $oldTitle;
				}
				try {
					$newTopic = ForumTopic::create($partId, $uid, $title);
					$newTopicId = $newTopic->getId();
					$topic->setNextTopic($newTopicId);
				} catch (Exception $e) {
					global $LOG;
					@$LOG->exception($e);
				}
			}

			Header("Location: /forum/part$partId/topic$newTopicId");
		}
		
		break;

	case 'load_actions':
		assertIsset($_REQUEST['msg_id']);
		assertIsset($_REQUEST['action']);

		try {
			$msg = new ForumMessage(intval($_REQUEST['msg_id']));
			$actions = $msg->getActions();
			$actions = $actions[$_REQUEST['action']];
			forum_show_actions($actions);
		}  catch (Exception $e) {
			echo $e->getMessage();
		}

		break;

	case 'edit_message':
		assertParam('msg_id');
		assertParam('text');

		if ($auth->isAuth()) {
			$msg = new ForumMessage(intparam('msg_id'));
			
			if ($msg->isEditableBy($user)) {
				$src = textparam('text');
				$msg->edit($src);

				echo json(array (
					'status' => 'ok',
					'msg' => array (
						'src' => $msg->getSource(),
						'html' => $msg->getParsed()
					)
				));

				exit(0);
			}

			echo json(array (
				'status' => 'failed',
				'reason' => 'this message can not be editable by you'
			));
		}

		echo json(array (
			'status' => 'failed',
			'reason' => 'access denied'
		));

		break;

	case 'remove_message':
		assertParam('msg_id');

		if ($auth->isAuth()) {
			$msg = new ForumMessage(intparam('msg_id'));

			if ($msg->isEditableBy($user)) {
				$topic = $msg->getTopic();
				if ($topic->isClosed()) {
					echo json(array (
						'status' => 'failed',
						'reason' => 'Topic is closed'
					));

					exit(0);
				}

				$removed = $msg->remove();

				echo json(array (
					'status' => $removed ? 'ok' : 'failed'
				));

				exit(0);
			}

			echo json(array (
				'status' => 'failed',
				'reason' => 'this message can not be editable by you'
			));

			exit(0);
		}

		echo json(array (
			'status' => 'failed',
			'reason' => 'access denied'
		));

		break;

	case 'load_stats':

		assertIsset('type');
		forum_show_stats_body(param('type'));

		break;

	}

} catch (Exception $e) {
	$LOG->exception($e);
}

?>