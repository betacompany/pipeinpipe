<?php

require_once dirname(__FILE__) . '/../db/MySQLResultIterator.php';
require_once dirname(__FILE__) . '/../../includes/mysql.php';

/**
 * Database connection class for forum
 * All methods should return DBResultIterator
 * @author ortemij
 */
class ForumDB {
    const MESSAGES = 'p_forum_message';
	const TOPICS = 'p_forum_topic';
	const PARTS = 'p_forum_part';
	const FORUMS = 'p_forum_forum';
	const ACTIONS = 'p_forum_action';
	const LAST_REFRESH = 'p_forum_last';
	const LAST_VISIT = 'p_forum_visit';

	const LAST_REFRESHED_TYPE_FORUM = 'forum';
	const LAST_REFRESHED_TYPE_PART = 'part';
	const LAST_REFRESHED_TYPE_TOPIC = 'topic';

	/**
	 * Selects message
	 * @param $id
	 * @return DBResultIterator
	 */
	public static function selectMessage($id) {
		return new MySQLResultIterator(mysql_qw(
						'SELECT * FROM `' . self::MESSAGES . '` WHERE `id`=? ORDER BY `id`',
						$id
					));
	}

	/**
	 * Selects all messages (by topic id if defined)
	 * Ordered by `id` ascendive
	 * @param $topicId
	 * @return DBResultIterator
	 */
	public static function selectMessages($topicId = 0, $from = 0, $limit = 0) {
		$request = 'SELECT * FROM `' . self::MESSAGES . '` WHERE ';
		$request .= ($topicId == 0) ? '1=1 ' : '`topic_id`='.intval($topicId).' ';
		$request .= ($limit == 0) ? '' : 'LIMIT ' . intval($from). ', ' . intval($limit);
		$resource = mysql_qw($request);
		return new MySQLResultIterator($resource);
	}

	/**
	 * Return count of messages at all or in particular topic if $topicId is specified
	 * @param $topicId
	 * @return integer
	 */
	public static function countMessages($topicId = 0) {
		$request = 'SELECT COUNT(*) FROM `' . self::MESSAGES . '` WHERE ';
		$request .= ($topicId == 0) ? '1=1' : '`topic_id`='.intval($topicId);
		$resource = mysql_qw($request);
		return mysql_result($resource, 0, 0);
	}

	/**
	 * Updates text field
	 * @return boolean
	 */
	public static function updateMessageText(ForumMessage $msg) {
		return (boolean) mysql_qw(
					'UPDATE `' . self::MESSAGES . '` SET `text`=? WHERE `id`=?',
					$msg->getText(), $msg->getId()
				);
	}

	/**
	 *
	 * @param $topicId
	 * @param $uid
	 * @param $timestamp
	 * @param $text
	 * @param $html
	 * @return integer
	 */
	public static function insertMessage($topicId, $uid, $timestamp, $text, $html) {
		mysql_qw(
			'INSERT INTO `' . self::MESSAGES . '` SET `topic_id`=?,
													  `uid`=?,
													  `timestamp`=?,
													  `text`=?,
													  `html`=?',
			$topicId, $uid, $timestamp, $text, $html
		);

		return mysql_insert_id();
	}

	/**
	 *
	 * @param ForumMessage $message
	 * @return boolean
	 */
	public static function updateMessage(ForumMessage $message) {
		return (boolean) mysql_qw(
					'UPDATE `' . self::MESSAGES . '` SET `topic_id`=?,
						                                      `uid`=?,
															  `timestamp`=?,
															  `text`=?,
															  `html`=?
													 WHERE `id`=?',
					$message->getTopicId(),
					$message->getUID(),
					$message->getTimestamp(),
					$message->getText(),
					$message->getHTML(),
					$message->getId()
				);
	}

	/**
	 * @param $topicId
	 * @return integer
	 */
	public static function getLastMessageTimestamp($topicId) {
		$resource = mysql_qw('SELECT `timestamp` FROM `' . self::MESSAGES . '` WHERE `topic_id`=? ORDER BY `id` LIMIT 1', $topicId);
		if ($msg = mysql_fetch_assoc($resource)) {
			return $msg['timestamp'];
		}

		return 0;
	}

	/**
	 * Selects all topics (by part id if defined)
	 * Ordered by `id` descendive (if part defined) and ascendive otherwise
	 * @param $partId
	 * @return DBResultIterator
	 */
	public static function selectTopics($partId = 0) {
		if ($partId == 0) {
			return new MySQLResultIterator(mysql_qw(
						'SELECT * FROM `' . self::TOPICS . '` WHERE 1=1 ORDER BY `id` ASC'
					));
		}

		return new MySQLResultIterator(mysql_qw(
						'SELECT * FROM `' . self::TOPICS . '` WHERE `part_id`=? ORDER BY `id` DESC',
						$partId
					));
	}

	/**
	 *
	 * @param $id
	 * @return DBResultIterator
	 */
	public static function selectTopic($id) {
		return new MySQLResultIterator(mysql_qw(
					'SELECT * FROM `' . self::TOPICS . '` WHERE `id`=?',
					$id
				));
	}

	/**
	 * Update
	 * @param ForumTopic $topic
	 * @return boolean
	 */
	public static function updateTopic(ForumTopic $topic) {
		return (boolean) mysql_qw(
					'UPDATE `' . self::TOPICS . '` SET `part_id`=?,
													   `prev_topic_id`=?,
													   `uid`=?,
													   `title`=?,
													   `closed`=?
												   WHERE `id`=?',
					$this->getPartId(), $this->getPrevTopicId(),
					$this->getUID(), $this->getTitle(),
					$this->isClosed() ? 1 : 0, $this->getId()
				);
	}

	/**
	 *
	 * @param $partId
	 * @param $uid
	 * @param $title
	 * @param boolean $closed
	 * @param $prevTopicId
	 * @return integer
	 */
	public static function insertTopic($partId, $uid, $title, $closed = false, $prevTopicId = 0) {
		$resource = mysql_qw(
					'INSERT INTO `' . self::TOPICS . '` SET `part_id`=?,
															`prev_topic_id`=?,
															`uid`=?,
															`title`=?,
															`closed`=?',
					$partId, $prevTopicId, $uid, $title, $closed ? 1 : 0
				);

		return mysql_insert_id();
	}

	/**
	 * @param $partId
	 * @return integer
	 */
	public static function getLastTopicTimestamp($partId) {
		$resource = mysql_qw(
					'SELECT MAX(`timestamp`) FROM (`' . 
						self::LAST_REFRESH . 
					'` LEFT JOIN `' . 
						self::TOPICS . 
					'` ON (`' . self::LAST_REFRESH . '`.`target_type`=\'topic\' and `' .
						self::LAST_REFRESH. '`.`target_id`=`' . self::TOPICS . '`.`id`))
					WHERE `part_id`=?', $partId
				);

		if ($msg = mysql_fetch_assoc($resource)) {
			return $msg['timestamp'];
		}

		return 0;
	}

	/**
	 *
	 * @param $forumId
	 * @return DBResultIterator
	 */
	public static function selectParts($forumId = 0) {
		if ($forumId == 0) {
			return new MySQLResultIterator(mysql_qw(
						'SELECT * FROM `' . self::PARTS . '` WHERE 1=1 ORDER BY `id` ASC'
					));
		}

		return new MySQLResultIterator(mysql_qw(
						'SELECT * FROM `' . self::PARTS . '` WHERE `forum_id`=? ORDER BY `id` DESC',
						$forumId
					));
	}

	/**
	 *
	 * @param $id
	 * @return DBResultIterator
	 */
	public static function selectPart($id) {
		return new MySQLResultIterator(mysql_qw(
					'SELECT * FROM `' . self::PARTS . '` WHERE `id`=?',
					$id
				));
	}

	/**
	 * @param $forumId
	 * @return integer
	 */
	public static function getLastPartTimestamp($forumId) {
		$resource = mysql_qw(
					'SELECT MAX(`timestamp`) FROM (`' .
						self::LAST_REFRESH .
					'` LEFT JOIN `' .
						self::PARTS .
					'` ON (`' . self::LAST_REFRESH . '`.`target_type`=\'part\' and `' .
						self::LAST_REFRESH. '`.`target_id`=`' . self::PARTS . '`.`id`))
					WHERE `part_id`=?', $forumId
				);

		if ($msg = mysql_fetch_assoc($resource)) {
			return $msg['timestamp'];
		}

		return 0;
	}

	public static function selectForums() {
		return new MySQLResultIterator(mysql_qw(
					'SELECT * FROM `' . self::FORUMS . '` WHERE 1=1 ORDER BY `id` ASC'
				));
	}

	/**
	 *
	 * @param $id
	 * @return DBResultIterator
	 */
	public static function selectForum($id) {
		return new MySQLResultIterator(mysql_qw(
					'SELECT * FROM `' . self::FORUMS . '` WHERE `id`=?',
					$id
				));
	}

	/**
	 *
	 * @param $targetType
	 * @param $targetId
	 * @return DBResultIterator
	 */
	public static function selectLast($targetType, $targetId) {
		return new MySQLResultIterator(mysql_qw(
					'SELECT * FROM `' . self::LAST_REFRESH . '` WHERE `target_type`=? and `target_id`=?',
					$targetType, $targetId
				));
	}

	/**
	 * @param $targetType
	 * @param $targetId
	 * @param $timestamp
	 * @result boolean
	 */
	public static function insertLast($targetType, $targetId, $timestamp) {
		self::updateLast($targetType, $targetId, $timestamp);

		if (mysql_affected_rows() > 0) return true;
		
		return (boolean) mysql_qw(
					'INSERT INTO `' . self::LAST_REFRESH . '` SET `target_type`=?, `target_id`=?, `timestamp`=?',
					$targetType, $targetId, $timestamp
				);
	}

	/**
	 * @param $targetType
	 * @param $targetId
	 * @param $timestamp
	 * @result boolean
	 */
	public static function updateLast($targetType, $targetId, $timestamp) {
		return (boolean) mysql_qw(
					'UPDATE `' . self::LAST_REFRESH . '` SET `timestamp`=? WHERE `target_type`=? and `target_id`=?',
					$timestamp, $targetType, $targetId
				);
	}

	public static function selectVisit($uid, $targetType, $targetId) {
		return new MySQLResultIterator(mysql_qw(
					'SELECT * FROM `' . self::LAST_VISIT . '` WHERE `uid`=? and `target_type`=? and `target_id`=?',
					$uid, $targetType, $targetId
				));
	}

	public static function insertVisit($uid, $targetType, $targetId, $timestamp) {
		$lastVisitTimestamp = ForumDB::getLastVisit($uid, $targetType, $targetId);
		
		if ($lastVisitTimestamp != 0) {
			return (boolean) mysql_qw(
						'UPDATE `' . self::LAST_VISIT . '` SET `timestamp`=? 
							WHERE `target_type`=? and `target_id`=? and `uid`=?',
						$timestamp, $targetType, $targetId, $uid
					);
		}

		return (boolean) mysql_qw(
					'INSERT INTO `' . self::LAST_VISIT . '` SET `target_type`=?, `target_id`=?, `uid`=?, `timestamp`=?',
					$targetType, $targetId, $uid, $timestamp
				);
	}

	public static function getLastVisit($uid, $targetType, $targetId) {
		$iterator = self::selectVisit($uid, $targetType, $targetId);
		if (!$iterator->valid()) return 0;
		$data = $iterator->current();
		return $data['timestamp'];
	}

	public static function selectAction($id) {
		return new MySQLResultIterator(
					mysql_qw(
						'SELECT * FROM `' . self::ACTIONS . '` WHERE `id`=?', $id
					)
				);
	}

	public static function selectActions($msgid) {
		return new MySQLResultIterator(
					mysql_qw(
						'SELECT * FROM `' . self::ACTIONS . '` WHERE `message_id`=? ORDER BY `id`',
						$msgid
					)
				);
	}

	public static function insertAction($msgId, $uid, $type, $timestamp, $value) {
		return (boolean) mysql_qw(
					'INSERT INTO `' . self::ACTIONS . '` SET `message_id`=?, `uid`=?, `type`=?, `timestamp`=?, `value`=?',
					$msgId, $uid, $type, $timestamp, $value
				);
	}
}
?>
