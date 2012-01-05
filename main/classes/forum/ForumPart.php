<?php

require_once dirname(__FILE__) . '/../../includes/assertion.php';
require_once dirname(__FILE__) . '/../../includes/security.php';

require_once dirname(__FILE__) . '/../db/DBResultIterator.php';

require_once dirname(__FILE__) . '/Forum.php';
require_once dirname(__FILE__) . '/ForumTopic.php';
require_once dirname(__FILE__) . '/ForumForum.php';

require_once dirname(__FILE__) . '/../exceptions/useful_exception_set.php';

require_once dirname(__FILE__) . '/../user/User.php';

require_once dirname(__FILE__) . '/../content/Group.php';
require_once dirname(__FILE__) . '/../content/Parser.php';

/**
 * @author Artyom Grigoriev
 */
class ForumPart extends Group {

	public function  __construct($id, $group = null, $data = null) {
		parent::__construct($id, $group, $data);
		if (empty ($this->contentParsed)) {
			$this->contentParsed = Parser::parseDescription($this->contentSource);
			$this->update();
		}
	}

	public function getForumId() {
		return $this->getParentGroupId();
	}

	public function getForum() {
		return $this->getParentGroup();
	}

	public function getDescription() {
		return $this->getContentParsed();
	}

	public function getTopics($from = 0, $limit = 0) {
		return $this->getItems($from, $limit, true);
	}

	public function getTopicsOrderedByLastMessage($from = 0, $limit = 0) {
		return $this->getItems($from, $limit, true, Item::LAST_COMMENT);
	}

	public function countTopics() {
		return $this->countItems();
	}

	public function countMessages() {
		return $this->countComments();
	}

	public function hasNewFor($user) {
		foreach ($this->getTopics() as $topic) {
			if ($topic->hasNewFor($user)) return true;
		}

		return false;
	}

	/**
	 * returns last TOPICS_COUNT topics and some more if they are not read by this user
	 */
	public function getTopicsTop($user, $count = Forum::TOP_TOPICS_COUNT_SHORT) {
		$uid = $user;
		if ($user instanceof User) {
			$uid = $user->getId();
		}

		$result = array();
		$notNewIndexes = array();
		$newIndexes = array();
		$j = 0;

		foreach ($this->getTopicsOrderedByLastMessage() as $i => $topic) {
			$hasNew = $topic->hasNewFor($uid);
			if ($hasNew || $i < $count) {
				$result[$j++] = $topic;
				if (!$hasNew) {
					$notNewIndexes[] = $j - 1;
				} else {
					$newIndexes[] = $j - 1;
				}
			}
		}

		$removed = 0;
		if (count($result) > $count) {
			$tmpIndexes = array();
			$shouldBeRemoved = count($result) - $count;
			for ($j = count($notNewIndexes) - 1 - $shouldBeRemoved; $j >= 0; $j--) {
				$tmpIndexes[] = $notNewIndexes[$j];
			}

			$tmpIndexes = array_merge($tmpIndexes, $newIndexes);

			$tmpResult = array();
			foreach ($result as $j => $topic) {
				if (array_contains($tmpIndexes, $j)) {
					$tmpResult[] = $topic;
				}
			}

			$result = $tmpResult;
		}

		return $result;
	}

	/**
	 * @deprecated
	 */
	public function update() {
		// TODO implement
	}

	public static function create($forumId, $title, $description) {
		$forumId = intval($forumId);
		$title = Parser::parseStrict($title);
		$description = Parser::parseDescription($description);

		assertTrue('There is no forum with id=' . $forumId, ForumForum::existsById($forumId));

		return parent::create(Group::FORUM_PART, $forumId, $title, $description, $description);
	}

	public static function existsById($id) {
		$iterator = GroupDBClient::getById($id);
		return $iterator->valid();
	}

	public static function valueOf(Group $other) {
		if ($other->getType() != Group::FORUM_PART) {
			throw new Exception('Unable to cast ['.$other->getType().'] to ForumPart');
		}

		return new ForumPart(-1, $other);
	}

	public static function getAll() {
		return Group::getAllByType(Group::FORUM_PART);
	}
}
?>
