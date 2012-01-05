<?php

require_once dirname(__FILE__) . '/../../includes/assertion.php';
require_once dirname(__FILE__) . '/../../includes/security.php';

require_once dirname(__FILE__) . '/../db/DBResultIterator.php';

require_once dirname(__FILE__) . '/ForumDB.php';
require_once dirname(__FILE__) . '/ForumTopic.php';

require_once dirname(__FILE__) . '/../exceptions/useful_exception_set.php';

require_once dirname(__FILE__) . '/../user/User.php';

require_once dirname(__FILE__) . '/../content/Comment.php';
require_once dirname(__FILE__) . '/../content/Parser.php';

/**
 * Description of ForumMessage
 *
 * @author Artyom Grigoriev
 */
class ForumMessage extends Comment {

	public function __construct($id, $comment = null, $data = null) {
		$id = intval($id);
		parent::__construct($id, $comment, $data);
		if ($this->contentParsed == '') {
			$this->contentParsed = Parser::parseForumMessage($this->contentSource);
			$this->update();
		}
	}
	
	public function getTopicId() {
		return $this->itemId;
	}

	public function getAuthorId() {
		return $this->uid;
	}

	public function getAuthor() {
		return $this->getUser();
	}

	public function getParsed() {
		return $this->contentParsed;
	}

	public function getSource() {
		return $this->contentSource;
	}

	public function setSource($text, $update = true) {
		$this->contentSource = $text;
		if ($update) $this->update();
	}

	public function setParsed($html, $update = true) {
		$this->contentSource = $html;
		if ($update) $this->update();
	}

	public function edit($source, $update = true) {
		$this->contentSource = $source  .
				"\n\n<small>Отредактировано: ".date_local(time(), DATE_LOCAL_FULL)."</small>";
		$this->contentParsed = Parser::parseForumMessage($this->contentSource);
		if ($update) $this->update();
	}

	/**
	 *
	 * @return ForumTopic
	 */
	public function getTopic() {
		return $this->getItem();
	}

	public function getActions() {
		$actions = parent::getActions();
		$result = array();
		foreach ($actions as $action) {
			$result[ $action->getType() ][] = $action;
		}

		return $result;
	}

	public function act(User $user, $type) {
		if (!Action::isActive($type, $this)) return false;
		if ($this->getAuthorId() == $user->getId()) return false;

		$topic = $this->getTopic();
		if ($topic->isClosed()) return false;
		if (!$topic->isAvailableFor($user->getId())) return false;

		$value = 0;

		switch ($type) {
		case Action::AGREE: $value = 0; break;
		case Action::ROMAN: $value = mt_rand(20, 40); break;
		default:
			throw new Exception('Unsupported type of forum action! ' . $type);
		}

		Action::create($type, Action::TARGET_COMMENT, $this->getId(), $user->getId(), time(), $value);
		$this->actionsLoaded = false;
		$this->actions = array();

		return true;
	}

	public static function create($topicId, $uid, $html, $timestamp) {
		$topicId = intval($topicId);
		$uid = intval($uid);
		if ($timestamp == 0) {
			$timestamp = time();
		}

		assertTrue('There is no topic with id=' . $topicId, ForumTopic::existsById($topicId));
		assertTrue('There is no user with id=' . $uid, User::existsById($uid));

		$text = Parser::parseSource($html);
		$html = Parser::parseForumMessage($text);

		$id = parent::create(parent::FORUM_MESSAGE, $topicId, $uid, $timestamp, $text, $html);

		$message = null;
		try {
			$message = new ForumMessage($id);
		} catch (InvalidIdException $e) {
			// TODO use error log file
			echo $e->getMessage();
		}

		$topic = $message->getTopic();
		$topic->setTimestamp($message->getTimestamp());

		return $message;
	}

	public static function valueOf($comment) {
		return new ForumMessage(-1, $comment);
	}
	
}
?>
