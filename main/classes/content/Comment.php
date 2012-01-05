<?php

require_once dirname(__FILE__) . '/../db/CommentDBClient.php';

require_once dirname(__FILE__) . '/../../includes/date.php';

require_once dirname(__FILE__) . '/Parser.php';

/**
 * @author Artyom Grigoriev
 */
class Comment {

	const BASIC_COMMENT = 'basic_comment';
	const FORUM_MESSAGE = 'forum_message';

	protected $id;
	protected $type;

	protected $itemId;
	protected $item;
	protected $itemLoaded = false;

	protected $uid;
	protected $user;
	protected $userLoaded = false;

	protected $timestamp;
	protected $contentSource;
	protected $contentParsed;

	protected $actions = array();
	protected $actionsLoaded = false;

	/**
	 * Constructs new instance of class Group
	 * Is to be called with one of three ways:
	 * <ol>
	 *  <li>
	 *	 <b>new Comment(id)</b> constructs instance by ID in database (loads data from there).
	 *  </li>
	 *  <li>
	 *   <b>new Comment(-1, $comment)</b> constructs clone of $comment, new row in DB is not inserted.
	 *  </li>
	 *  <li>
	 *   <b>new Comment(-1, null, $data)</b> constructs new group by passed $data, new row in DB is not inserted.
	 *  </li>
	 * </ol>
	 * @param int $id
	 * @param Group $group [optional]
	 * @param array $data [optional]
	 * @deprecated You should use Group::getById() instead
	 */
	protected function  __construct($id, $comment = null, $data = null) {
		if ($comment != null) {
			$this->id = $comment->id;
			$this->type = $comment->type;
			$this->itemId = $comment->itemId;
			$this->uid = $comment->uid;
			$this->timestamp = $comment->timestamp;
			$this->contentSource = $comment->contentSource;
			$this->contentParsed = $comment->contentParsed;
			return;
		}

		if ($data == null) {
			assertPositive('ID of content comment should be positive!', $id);
			$iterator = CommentDBClient::getById($id);
			if ($iterator->valid()) {
				$data = $iterator->current();
			} else {
				throw new InvalidIdException('There is no comment with id=' . $id);
			}
		}

		$this->id = $data['id'];
		$this->type = $data['type'];
		$this->itemId = $data['item_id'];
		$this->uid = $data['uid'];
		$this->timestamp = $data['timestamp'];
		$this->contentSource = $data['content_source'];
		$this->contentParsed = $data['content_parsed'];
	}

	/**
	 * Returns ID of this comment
	 * @return int
	 */
	public function getId() {
		return intval($this->id);
	}

	/**
	 * Returns one of types listed in Comment class constants
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns ID of the item which is commented with this comment
	 * @return int
	 */
	public function getItemId() {
		return intval($this->itemId);
	}

	/**
	 * Returns item which is commented with this comment
	 * @return Item
	 */
	public function getItem() {
		if ($this->itemLoaded) return $this->item;

		try {
			$this->item = Item::getById($this->itemId);
		} catch (Exception $e) {
			global $LOG;
			$LOG->exception($e);
		}

		$this->itemLoaded = true;
		return $this->item;
	}

	/**
	 * Returns ID of the user who created or is an owner of this comment
	 * @return int
	 */
	public function getUID() {
		return intval($this->uid);
	}

	/**
	 * Returns the user who created or is an owner of this comment
	 * @return User
	 */
	public function getUser() {
		if ($this->userLoaded) return $this->user;

		try {
			$this->user = User::getById($this->uid);
		} catch (Exception $e) {
			global $LOG;
			$LOG->exception($e);
		}

		return $this->user;
	}

	/**
	 * Returns UNIX-timestamp of creation
	 * @return int
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * Returns content source
	 * @return string
	 */
	public function getContentSource() {
		return $this->contentSource;
	}

	/**
	 * Returns parsed content source
	 * @return string
	 */
	public function getContentParsed() {
		if (!empty($this->contentSource) && empty($this->contentParsed)) {
			$this->parseAndSetContent();
			$this->update();
		}
		return $this->contentParsed;
	}

	/**
	 * Returns array of actions of this comment
	 * @return array
	 */
	public function getActions() {
		if ($this->actionsLoaded) return $this->actions;
		
		$this->actions = Action::getActionsFor(Action::TARGET_COMMENT, $this->id);
		$this->actionsLoaded = true;
		return $this->actions;
	}

	/**
	 * Updates row in DB
	 */
	public function update() {
		CommentDBClient::update($this);
	}

	/**
	 * Returns associative array with such list of keys:
	 * id, item_id, timestamp, type, uid, content_source, content_parsed
	 * @return array
	 */
	public function toArray() {
		return array(
			'id' => $this->id,
			'item_id' => $this->itemId,
			'timestamp' => $this->timestamp,
			'time' => date_local($this->timestamp, DATE_LOCAL_SHORT),
			'type' => $this->type,
			'uid' => $this->uid,
			'content_source' => $this->contentSource,
			'content_parsed' => $this->contentParsed
		);
	}

	/**
	 * Checks if this comment could be edited by someone
	 * @param mixed $user int, User
	 * @return false
	 */
	public function isEditableBy($user) {
		$uid = ($user instanceof User) ? $user->getId() : $user;
		if ($this->getItem()->isClosed()) return false;
		if ($uid == $this->getUID()) return true;
		return false;
	}

	/**
	 * Checks if this comment could be cited by someone
	 * @param mixed $user int, User
	 * @return false
	 */
	public function isCitableBy($user) {
		$uid = ($user instanceof User) ? $user->getId() : $user;
		if ($this->getItem()->isClosed()) return false;
		if ($uid > 0) return true;
		return false;
	}

	/**
	 * Removes current item
	 * @return boolean
	 */
	public function remove() {
		return CommentDBClient::remove($this->getId());
	}

	public function canBeActedBy($actionType, $user) {
		$uid = ($user instanceof User) ? $user->getId() : $user;
		$item = $this->getItem();
		return ($uid > 0) &&
			   !$item->isClosed() &&
			   Action::isActive($actionType, $this) &&
			   $this->getAuthorId() != $uid;
	}

	public function isActedBy($actionType, $user) {
		$uid = ($user instanceof User) ? $user->getId() : $user;
		$actions = Action::getActionsFor(Action::TARGET_COMMENT, $this->getId());
		foreach ($actions as $action) {
			if ($action->getType() == $actionType) {
				if ($action->getAuthorId() == $uid) {
					return true;
				}
			}
		}
		return false;
	}

	private function parseAndSetContent() {
		$this->contentParsed = Parser::parseComment($this->contentSource);
	}

	/**
	 * Returns instance of class Comment with specified data set
	 * @param array $data
	 * @return mixed (Comment, ForumMessage) 
	 */
	public static function getByData($data) {
		$comment = new Comment(-1, null, $data);

		switch ($comment->type) {
		case self::FORUM_MESSAGE: return ForumMessage::valueOf($comment);
		case self::BASIC_COMMENT:
		default:
			return $comment;
		}
	}

	/**
	 * Returns instance of class Comment. Loads data form DB.
	 * @param int $id
	 * @return mixed (Comment, ForumMessage)
	 */
	public static function getById($id) {
		$comment = new Comment($id);

		switch ($comment->type) {
		case self::FORUM_MESSAGE: return ForumMessage::valueOf($comment);
		case self::BASIC_COMMENT:
		default:
			return $comment;
		}
	}

	/**
	 * Returns array of comments for some item
	 * @param int $itemId
	 * @param int $from [optional]
	 * @param int $limit [optional]
	 * @return array
	 */
	public static function getCommentsFor($itemId, $from = 0, $limit = 0) {
		$iterator = CommentDBClient::getByItem($itemId, $from, $limit);
		$result = array();
		while ($iterator->valid()) {
			try {
				$data = $iterator->current();
				$result[] = Comment::getByData($data);
			} catch (Exception $e) {
				global $LOG;
				$LOG->exception($e);
			}

			$iterator->next();
		}

		return $result;
	}

	/**
	 * Returns number of comments by item ID
	 * @param int $itemId
	 * @return int
	 */
	public static function countCommentsFor($itemId) {
		return CommentDBClient::countByItem($itemId);
	}

	/**
	 * Returns number of comments for group
	 * @param int $groupId
	 * @return int
	 */
	public static function countCommentsForGroup($groupId) {
		return CommentDBClient::countByGroup($groupId);
	}

	public static function countBasicComments($uid = 0) {
		return CommentDBClient::countByType(self::BASIC_COMMENT, $uid);
	}

	public static function countForumMessages($uid = 0) {
		return CommentDBClient::countByType(self::FORUM_MESSAGE, $uid);
	}

	/**
	 * Checks if there is such comment
	 * @param int $id
	 * @return boolean
	 */
	public static function existsById($id) {
		$iterator = CommentDBClient::getById($id);
		return $iterator->valid();
	}

	/**
	 * Creates new instance and inserts new row in DB.
	 * @param string $type
	 * @param int $itemId
	 * @param int $uid
	 * @param int $timestamp
	 * @param string $contentSource
	 * @param string $contentParsed
	 * @return mixed
	 */
	public static function create($type, $itemId, $uid, $timestamp, $contentSource, $contentParsed) {
		$id = CommentDBClient::insert($type, $itemId, $uid, $timestamp, $contentSource, $contentParsed);
		try {
			return Comment::getById($id);
		} catch (Exception $e) {
			global $LOG;
			$LOG->exception($e);
			return null;
		}
	}

	public static function getAll($descendive = false) {
		$iterator = CommentDBClient::getAll($descendive);
		$result = array();
		while ($iterator->valid()) {
			$data = $iterator->current();
			$result[] = Comment::getByData($data);
			$iterator->next();
		}

		return $result;
	}

	/**
	 *
	 * @param string|array $itemTypes [optional]
	 * array(Item::BLOG_POST, Item::PHOTO, Item::VIDEO) by default
	 * @param int $from [optional]
	 * @param int $limit [optional]
	 * @param boolean $descendive [optional]
	 */
	public static function getByItemType(
		$itemTypes = array(Item::BLOG_POST, Item::PHOTO, Item::VIDEO),
		$from = 0, $limit = 0, $descendive = false) 
	{
		$iterator = CommentDBClient::getByItemType($itemTypes, $from, $limit, $descendive);
		$result = array();
		while ($iterator->valid()) {
			$data = $iterator->current();
			$result[] = Comment::getByData($data);
			$iterator->next();
		}

		return $result;
	}

}
?>
