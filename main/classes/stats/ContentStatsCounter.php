<?php
/**
 * Description of ForumStatsCounter
 *
 * @author Nikita
 */

require_once dirname(__FILE__) . '/../db/ContentStatsDBClient.php';
require_once dirname(__FILE__) . '/../content/Item.php';
require_once dirname(__FILE__) . '/../content/Comment.php';

require_once dirname(__FILE__) . '/../forum/Forum.php';

class ContentStatsCounter {
    
	/**
	 *
	 * @param string $actionType Action::AGREE or Action::ROMAN.
	 * @param string $commentType Comment::BASIC_COMMENT or Comment::FORUM_MESSAGE.
	 * @param int $uid Zero by default. If zero, then the method returns multielement sorted array. Otherwise returns one-element array.
	 * @return array (`uid`, `value`)
	 */
	public static function getPassiveActionsOnComments($actionType, $commentType, $uid = 0) {

		$iterator = ContentStatsDBClient::countPassiveActionsOnComments($actionType, $commentType, $uid);
		$result = array();
		while ($iterator->valid()) {
			$result[] = $iterator->current();
			$iterator->next();
		}

		return $result;
	}

	public static function getPassiveActionsOnForumMessagesNormaized($actionType) {

		$iterator = ContentStatsDBClient::countPassiveActionsOnComments($actionType, Comment::FORUM_MESSAGE);
		$result = array();
		while ($iterator->valid()) {
			$data = $iterator->current();
			$msgCount = self::getMessagesCount4User($data['uid']);
			if ($msgCount > Forum::MESSAGES_COUNT_STATS_THRESHOLD) {
				$result[] = array (
					'value' => round($msgCount ? $data['value'] / $msgCount : 0, 3),
					'uid' => $data['uid']
				);
			}
			$iterator->next();
		}

		rsort($result);
		return $result;
	}

	/**
	 *
	 * @param array $itemType Array of item types which can be <b>blog_post, forum_topic, photo, video, interview_question</b>.
	 * @param int $uid Zero by default. If zero, then the method returns multielement sorted array. Otherwise returns one-element array.
	 * @return array (`uid`, `value`)
	 */
	public static function getCommentCount($itemType, $uid = 0) {

		$iterator = ContentStatsDBClient::countComments($itemType, $uid);
		$result = array();

		while ($iterator->valid()) {
			$result[] = $iterator->current();
			$iterator->next();
		}

		return $result;
	}

	/**
	 *
	 * @param array $itemType Array of item types which can be <b>blog_post, forum_topic, photo, video, interview_question</b>.
	 * @param int $uid Zero by default. If zero, then the method returns multielement sorted array. Otherwise returns one-element array.
	 * @return array (`uid`, `value`)
	 */
	public static function getItemCount($itemType, $uid = 0) {

		$iterator = ContentStatsDBClient::countItems($itemType, $uid);
		$result = array();

		while ($iterator->valid()) {
			$result[] = $iterator->current();
			$iterator->next();
		}

		return $result;
	}

	/**
	 *
	 * @param string $actionType Action::AGREE or Action::ROMAN.
	 * @param string $commentType Comment::BASIC_COMMENT or Comment::FORUM_MESSAGE.
	 * @param int $uid Zero by default. If zero, then the method returns multielement sorted array. Otherwise returns one-element array.
	 * @return array (`uid`, `value`)
	 */
	public static function getActiveActionsOnComments($actionType, $commentType, $uid = 0) {

		$iterator = ContentStatsDBClient::countActiveActionsOnComments($actionType, $commentType, $uid);
		$result = array();
		while ($iterator->valid()) {
			$result[] = $iterator->current();
			$iterator->next();
		}

		return $result;
	}

	public static function getPairActions($actionType, $subjectId = 0, $objectId = 0) {

		$iterator = ContentStatsDBClient::countPairActions($actionType, $subjectId, $objectId);
		$result = array();
		while ($iterator->valid()) {
			$result[] = $iterator->current();
			$iterator->next();
		}

		return $result;
	}

	private static $messagesCount4User = array();
	private static $messagesCount4UserCounted = false;

	private static function getMessagesCount4User($uid) {

		if (!self::$messagesCount4UserCounted) {
			$iterator = ContentStatsDBClient::countMessagesIterator();
			while ($iterator->valid()) {
				$data = $iterator->current();
				self::$messagesCount4User[strval($data['uid'])] = intval($data['count']);
				$iterator->next();
			}
			self::$messagesCount4UserCounted = true;
		}

		return self::$messagesCount4User[ strval($uid) ];
	}
}
?>
