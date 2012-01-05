<?php

require_once dirname(__FILE__) . '/../../includes/assertion.php';
require_once dirname(__FILE__) . '/../../includes/security.php';

require_once dirname(__FILE__) . '/../db/DBResultIterator.php';

require_once dirname(__FILE__) . '/ForumDB.php';
require_once dirname(__FILE__) . '/ForumMessage.php';
require_once dirname(__FILE__) . '/ForumPart.php';

require_once dirname(__FILE__) . '/../exceptions/useful_exception_set.php';

require_once dirname(__FILE__) . '/../user/User.php';

require_once dirname(__FILE__) . '/../content/Item.php';
require_once dirname(__FILE__) . '/../content/Parser.php';

/**
 * Description of ForumTopic
 *
 * @author Artyom Grigoriev
 */
class ForumTopic extends Item {

	public function  __construct($id, $item = null, $data = null) {
		parent::__construct($id, $item, $data);
	}

	public function getPartId() {
		return $this->groupId;
	}

//	public function getPrevTopicId() {
//		return $this->prevTopicId;
//	}

	public function getTitle() {
		return $this->contentSource;
	}

	public function setTitle($title, $update = true) {
		$this->contentSource = $title;
		$this->contentParsed = '';
		if ($update) $this->update();
	}

	public function setNextTopic($topicId, $update = true) {
		$this->contentValue = intval($topicId);
		if ($update) $this->update();
	}

	public function visit(User $user) {
		$this->viewedBy($user);
	}

	public function getPart() {
		return $this->getGroup();
	}

	/**
	 * if $from == 0 and $limit == 0 all messages will be selected
	 * @param <type> $from
	 * @param <type> $limit
	 * @return <type>
	 */
	public function getMessages($from = 0, $limit = 0) {
		return $this->getComments($from, $limit);
	}

	public function countMessages() {
		return $this->countComments();
	}

	public function countNewFor($user) {
		return $this->countNewCommentsFor($user);
	}

	public function getLastMessage() {
		if ($this->countMessages() == 0) return null;
		$lastMessage = $this->getComments($this->countMessages() - 1, 1);
		if (count($lastMessage) == 0) return null;
		return $lastMessage[0];
	}

	/**
	 * returns if there are new messages for such user in such topic
	 * $user may be null, then function will return false
	 * $user - uid or instance of User
	 * @param mixed $user
	 * @return boolean
	 */
	public function hasNewFor($user) {
		return $this->hasNewCommentsFor($user);
	}


	public function addComment($uid, $html, $timestamp) {
		if ($this->isClosed()) return false;
		if (!$this->isAvailableFor($uid)) return false;
		$msg = ForumMessage::create($this->getId(), $uid, $html, $timestamp);
		if ($msg != null) {
			$this->setLastCommentTimestamp($timestamp);
		}

		return $msg;
	}

	public function getNextTopicId() {
		return $this->getContentValue();
	}

	public function hasNextTopic() {
		return $this->contentValue > 0;
	}

	/**
	 * 
	 * @param $partId
	 * @param $uid
	 * @param $title
	 * @param boolean $closed
	 * @param $prevTopicId
	 * @return ForumTopic
	 */
	public static function create($partId, $uid, $title, $closed = false, $prevTopicId = 0) {
		$partId = intval($partId);
		$title = Parser::parseStrict($title);

		assertTrue('There is no part with id=' . $partId, ForumPart::existsById($partId));
		assertTrue('There is no user with id=' . $uid, User::existsById($uid));

		return parent::create(Item::FORUM_TOPIC, $partId, $uid, time(), $title, '');
	}

	public static function valueOf($time) {
		return new ForumTopic(-1, $time);
	}

	public static function getAll() {
		return parent::getAllByType(Item::FORUM_TOPIC);
	}

	public static function getOpened() {
		return parent::getOpenedByType(Item::FORUM_TOPIC);
	}

	public static function getByTitle($title) {
		return parent::getByTypeAndContentSource(Item::FORUM_TOPIC, $title);
	}
}
?>
