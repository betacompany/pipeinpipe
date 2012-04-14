<?php

require_once dirname(__FILE__) . '/../../includes/assertion.php';
require_once dirname(__FILE__) . '/../../includes/security.php';

require_once dirname(__FILE__) . '/../db/DBResultIterator.php';

require_once dirname(__FILE__) . '/ForumDB.php';
require_once dirname(__FILE__) . '/ForumForum.php';
require_once dirname(__FILE__) . '/ForumPart.php';
require_once dirname(__FILE__) . '/ForumTopic.php';
require_once dirname(__FILE__) . '/ForumMessage.php';

require_once dirname(__FILE__) . '/../content/Group.php';

require_once dirname(__FILE__) . '/../exceptions/useful_exception_set.php';

/**
 * Description of Forum
 *
 * @author Artyom Grigoriev
 */
class Forum {
	
	const MESSAGES_PER_PAGE = 10;
	const TOPICS_PER_PAGE = 10;
	const TOP_TOPICS_COUNT = 10;
	const TOP_TOPICS_COUNT_SHORT = 5;

	const BUG_PART_ID = 10;

	const MESSAGES_COUNT_STATS_THRESHOLD = 5;

	private static $forums = array();
	private static $forumsLoaded = false;

    public static function getForums() {
		return Group::getRootsByType(Group::FORUM_FORUM);
	}

	public static function countMessages($uid = 0) {
		return CommentDBClient::countByType(Comment::FORUM_MESSAGE, $uid);
	}

	public static function countActions($type) {

	}

	public static function countUsers() {

	}

	public static function countTopics($uid = 0) {
		return ItemDBClient::countByType(Item::FORUM_TOPIC, $uid);
	}

	public static function countParts() {

	}

	public static function countForums() {

	}

	public static function init($uid) {
		self::preloadTopics($uid);
		self::preloadParts();
	}

	public static function preloadTopics($uid) {
		$topics = ForumTopic::getOpened();
		if ($uid > 0) {
			$iterator = ContentViewDBClient::getOpenedItemViewsByUser($uid);
			$views = array();
			while ($iterator->valid()) {
				$view = $iterator->current();
				$views[$view['target_id']] = $view['timestamp'];
				$iterator->next();
			}

			$iterator = CommentDBClient::getCountsForItems(array_keys($views));
			$counts = array();
			while ($iterator->valid()) {
				$count = $iterator->current();
				$counts[$count['iid']] = $count['count'];
				$iterator->next();
			}

			foreach ($topics as $topic) {
				$time = isset($views[$topic->getId()]) ? $views[$topic->getId()] : 0;
				$topic->setLastViewForLite($uid, $time);
				$count = isset($counts[$topic->getId()]) ? $counts[$topic->getId()] : 0;
				$topic->setCommentsCountForLite($count);
			}
		}
	}

	private static function preloadParts() {
		$countIterator = GroupDBClient::getCommentCounts();
		$counts = array();
		while ($countIterator->valid()) {
			$data = $countIterator->current();
			$counts[$data['group_id']] = $data['count'];
			$countIterator->next();
		}

		$parts = ForumPart::getAll();
		foreach ($parts as $part) {
			$count = isset ($counts[$part->getId()]) ? $counts[$part->getId()] : 0;
			$part->setCommentsCount($count);
		}
	}
}
?>
