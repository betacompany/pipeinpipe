<?php

require_once dirname(__FILE__) . '/../../includes/assertion.php';
require_once dirname(__FILE__) . '/../../includes/security.php';
require_once dirname(__FILE__) . '/../../includes/import.php';
require_once dirname(__FILE__) . '/../../includes/log.php';

import("db/ItemDBClient");
import("db/ContentViewDBClient");
import("media/Photo");
import("media/Video");
import("forum/ForumTopic");
import("blog/BlogPost");
import("life/Event");
import("social/CrossPost");
import("content/Action");
import("content/Tag");

/**
 * @author Artyom Grigoriev
 */
class Item {

	const BLOG_POST = 'blog_post';
	const FORUM_TOPIC = 'forum_topic';
	const PHOTO = 'photo';
	const VIDEO = 'video';
	const INTERVIEW_QUESTION = 'interview_question';
	const EVENT = 'event';
	const CROSS_POST = 'cross_post';

	const ID = 'id';
	const LAST_COMMENT = 'last_comment_timestamp';
	const CREATION = 'creation_timestamp';

	public static function isCorrectType($type) {
		return
			$type == self::BLOG_POST ||
			$type == self::EVENT ||
			$type == self::FORUM_TOPIC ||
			$type == self::INTERVIEW_QUESTION ||
			$type == self::PHOTO ||
			$type == self::VIDEO ||
			$type == self::CROSS_POST;
	}

	protected $creationTimestamp;

	protected $id;
	protected $type;

	protected $groupId;
	protected $group;
	protected $groupLoaded = false;

	protected $uid;
	protected $user;
	protected $userLoaded = false;

	protected $lastCommentTimestamp;
	protected $contentTitle;
	protected $contentSource;
	protected $contentParsed;
	protected $contentValue;

	private $comments = array();
	private $commentsLoaded = false;
	private $commentsCount = -1;
	private $newCommentsCount = array();

	private $closed = false;
	private $private = false;

	private $lastView = array();

	/**
	 * Constructs new instance of class Item
	 * Is to be called with one of three ways:
	 * <ol>
	 *  <li>
	 *	 <b>new Item(id)</b> constructs instance by ID in database (loads data from there).
	 *  </li>
	 *  <li>
	 *   <b>new Item(-1, $item)</b> constructs clone of $item, new row in DB is not inserted.
	 *  </li>
	 *  <li>
	 *   <b>new Item(-1, null, $data)</b> constructs new item by passed $data, new row in DB is not inserted.
	 *  </li>
	 * </ol>
	 * @param int $id
	 * @param Item $item [optional]
	 * @param array $data [optional]
	 * @deprecated You should use Item::getById(), ::getByData(), ::valueOf() instead
	 */
	protected function  __construct($id, $item = null, $data = null) {
		if ($item != null) {
			$this->id = $item->id;
			$this->type = $item->type;
			$this->groupId = $item->groupId;
			$this->uid = $item->uid;
			$this->creationTimestamp = $item->creationTimestamp;
			$this->lastCommentTimestamp = $item->lastCommentTimestamp;
			$this->contentTitle = $item->contentTitle;
			$this->contentSource = $item->contentSource;
			$this->contentParsed = $item->contentParsed;
			$this->contentValue = $item->contentValue;
			$this->closed = $item->closed;
			$this->private = $item->private;

			return;
		}

		if ($data == null) {
			assertPositive('ID of content item should be positive', $id);
			$iterator = ItemDBClient::getById($id);
			if ($iterator->valid()) {
				$data = $iterator->current();
			} else {
				throw new InvalidIdException('There is no content item with id='.$id);
			}
		}

		$this->id = $data['id'];
		$this->type = $data['type'];
		$this->groupId = $data['group_id'];
		$this->uid = $data['uid'];
		$this->creationTimestamp = $data['creation_timestamp'];
		$this->lastCommentTimestamp = $data['last_comment_timestamp'];
		$this->contentTitle = $data['content_title'];
		$this->contentSource = $data['content_source'];
		$this->contentParsed = $data['content_parsed'];
		$this->contentValue = intval($data['content_value']);
		$this->closed = ($data['closed'] == 'closed');
		$this->private = ($data['private'] == 'private');
	}

	/**
	 * @return int ID of content item in DB
	 */
	public function getId() {
		return intval($this->id);
	}

	/**
	 * @return string type of content item, all possible values are
	 * enumerated with constants of class Item
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return int ID of smallest content group which contains this content
	 * item, zero if there is no such group
	 */
	public function getGroupId() {
		return intval($this->groupId);
	}

	/**
	 * @return Group the smallest content group containing this content item
	 */
	public function getGroup() {
		if ($this->groupLoaded) return $this->group;

		try {
			$this->group = Group::getById($this->groupId);
		} catch (Exception $e) {
			global $LOG;
			$LOG->exception($e);
		}

		$this->groupLoaded = true;
		return $this->group;
	}

	/**
	 * @return int ID of user who created or is an owner of this content item
	 */
	public function getUID() {
		return intval($this->uid);
	}

	/**
	 * @return User who created or is an owner of this content item
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
	 * @return int UNIX-timestamp of creation
	 */
	public function getTimestamp() {
		return intval($this->creationTimestamp);
	}

	/**
	 * @return int UNIX-timestamp of creation
	 */
	public function getCreationTimestamp() {
		return intval($this->creationTimestamp);
	}

	/**
	 * @return int UNIX-timestamp of last added comment
	 */
	public function getLastCommentTimestamp() {
		return intval($this->lastCommentTimestamp);
	}

	/**
	 * @return string title of content
	 */
	public function getContentTitle() {
		return $this->contentTitle;
	}

	/**
	 * @return string source of content
	 */
	public function getContentSource() {
		return $this->contentSource;
	}

	/**
	 * @return string parsed source of content
	 */
	public function getContentParsed() {
		return $this->contentParsed;
	}

	/**
	 * @return int some value you can use
	 */
	public function getContentValue() {
		return intval($this->contentValue);
	}

	/**
	 * Checks if this item is closed for commenting
	 * @return boolean
	 */
	public function isClosed() {
		return $this->closed;
	}

	/**
	 * Checks if this item is available for such user:
	 * for example, it is private topic or porno photo
	 * @param User $user
	 * @return boolean
	 */
	public function isAvailableFor($user) {
		// TODO implement!!!
		return true;
	}

	/**
	 * Updates row in DB
	 * @return boolean
	 */
	public function update() {
		return ItemDBClient::update($this);
	}

	/**
	 * Returns actions referred to this item
	 * @return array
	 */
	public function getActions() {
		return Action::getActionsFor(Action::TARGET_ITEM, $this->getId());
	}

	/**
	 * Returns array of comments
	 * If $limit == 0 and $from == 0 returns all the comments
	 * @param int $from
	 * @param int $limit
	 * @return Comment[]
	 */
	public function getComments($from = 0, $limit = 0) {
		if ($this->commentsLoaded && $from == 0 && $limit == 0) return $this->comments;

		$this->comments = Comment::getCommentsFor($this->id, $from, $limit);
		if ($from == 0 && $limit == 0) $this->commentsLoaded = true;
		return $this->comments;
	}

	public function getTags() {
		return Tag::getByItem($this);
	}

	/**
	 * @return int count of comments for this item
	 */
	public function countComments() {
		if ($this->commentsCount != -1)
			return $this->commentsCount;

		if ($this->commentsLoaded) {
			$this->commentsCount = count($this->comments);
		} else {
			$this->commentsCount = Comment::countCommentsFor($this->id);
		}

		return $this->commentsCount;
	}

	/**
	 * @return int count of new comments for this item and given user
	 */
	public function countNewCommentsFor($user) {
		$uid = $user instanceof User ? $user->getId() : $user;

		if (isset($this->newCommentsCount[$uid]))
			return $this->newCommentsCount[$uid];

		$this->newCommentsCount[$uid] =
			CommentDBClient::countByItemLater($this->id, $this->getLastViewFor($uid));

		return $this->newCommentsCount[$uid];
	}

	/**
	 * Simple creation of the comment in DB
	 * Also sets the last comment time for this item
	 * @param string $type one of comment types specified as constants in class Comment
	 * @param int $uid user ID of creator
	 * @param int $timestamp UNIX-timestamp of creation
	 * @param string $contentSource source of content
	 * @param string $contentParsed parsed source of content
	 * @return Comment created comment
	 */
	public function addComment($type, $uid, $timestamp, $contentSource, $contentParsed) {
		if ($this->isClosed()) return false;
		if (!$this->isAvailableFor($uid)) return false;
		$comment = Comment::create($type, $this->getId(), $uid, $timestamp, $contentSource, $contentParsed);
		if ($comment != null) {
			$this->setLastCommentTimestamp($timestamp);
		}

		return $comment;
	}
	
	public function addTag($tag, $user) {
		$tagId = ($tag instanceof Tag) ? $tag->getId() : $tag;
		$uid = ($user instanceof User) ? $user->getId() : $user;
		TagDBClient::bind($this->id, $tagId, $uid, time());
	}

	public function addTags($tagIds, $user) {
		foreach ($tagIds as $tagId) {
			$this->addTag($tagId, $user);
		}
	}

	public function setGroupId($groupId, $update = true) {
		$this->groupId = $groupId;
		$this->groupLoaded = false;
		$this->group = null;
		if ($update) $this->update();
	}

	/**
	 * Sets creation (or modification) time of this item
	 * @param int $timestamp
	 * @param boolean $update needness of update row in DB
	 */
	public function setTimestamp($timestamp, $update = true) {
		$this->creationTimestamp = $timestamp;
		if ($update) $this->update();
	}

	/**
	 * Sets the last comment time
	 * @param int $timestamp
	 * @param boolean $update needness of update row in DB
	 */
	public function setLastCommentTimestamp($timestamp, $update = true) {
		$this->lastCommentTimestamp = $timestamp;
		if ($update) $this->update();
	}

	/**
	 * Marks this item as viewed by specified user
	 * @param User $user
	 * @return boolean
	 */
	public function viewedBy(User $user) {
		$timestamp = time();
		return ContentViewDBClient::refresh('item', $this->getId(), $user->getId(), $timestamp);
	}

	/**
	 * Returns UNIX-timestamp of last view of this item for specified user
	 * Returns zero if there is no such
	 * $user - uid or instance of User class
	 * @param mixed $user
	 * @return int
	 */
	public function getLastViewFor($user) {
		$uid = $user;
		if ($user instanceof User) {
			$uid = $user->getId();
		}

		if (isset($this->lastView[$uid])) {
			return $this->lastView[$uid];
		}
		
		$lastView = ContentViewDBClient::getByContentAndUser(ContentViewDBClient::CONTENT_ITEM, $this->getId(), $uid);
		return $this->lastView[$uid] = $lastView;
	}

	/**
	 * Sets last view for user, writes nothing in DB!!!
	 * @param int $timestamp
	 */
	public function setLastViewForLite($user, $timestamp) {
		$uid = $user;
		if ($user instanceof User) {
			$uid = $user->getId();
		}

		$this->lastView[$uid] = $timestamp;
	}

	/**
	 * Sets the value of comments count
	 * @param $count
	 */
	public function setCommentsCountForLite($count) {
		$this->commentsCount = $count;
	}

	/**
	 * Checks if new for $user comments appeared since his last view
	 * @param mixed $user (User or int)
	 * @return boolean
	 */
	public function hasNewCommentsFor($user) {
		if ($user == null) return false;
		if (is_int($user) && $user == 0) return false;
		if ($this->isClosed()) return false;
		$lastView = $this->getLastViewFor($user);
		$lastComment = $this->getLastCommentTimestamp();
		return $lastComment > $lastView;
	}

	/**
	 * Checks if this item is evaluable.
	 * It means that somebody can evaluate this item by doing an action
	 * with type=EVALUATION. Now it is available for photos, videos and
	 * inteview questions.
	 * @return boolean
	 */
	public function isEvaluable() {
		return (
			$this->type == Item::INTERVIEW_QUESTION ||
			$this->type == Item::PHOTO ||
			$this->type == Item::VIDEO
		);
	}

	/**
	 * Make an action
	 * @param User $user
	 * @param string $type one of listed in class Action types
	 * @param int $value [optional]
	 * @return mixed (false on fail, Action on success)
	 */
	public function act(User $user, $type, $value = 0) {
		if (!Action::isActive($type, $this)) return false;
		if ($this->getUID() == $user->getId()) return false;
		if (!$this->isAvailableFor($user)) return false;
		if ($this->isActedBy($user, $type)) return false;

		return Action::create($type, Action::TARGET_ITEM, $this->id, $user->getId(), time(), $value);
	}

	/**
	 * Checks if this item was acted by such user
	 * @param User $user
	 * @param string $type
	 * @return boolean
	 */
	public function isActedBy($user, $type = Action::EVALUATION) {
		if (!($user instanceof User)) return false;
		$actions = $this->getActions();
		foreach ($actions as $action) {
			if ($action->getType() == $type) {
				if ($action->getUID() == $user->getId()) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Removes current item
	 * @return boolean
	 */
	public function remove() {
		return ItemDBClient::remove($this->getId());
	}

	public function removeTags() {
		TagDBClient::removeTagsFor($this->getId());
	}

    public function removeTag($tag) {
        TagDBClient::removeTag($this->getId(), $tag->getId());
    }

	/**
	 * Wrapper for native function "clone"
	 * @param Item $item
	 * @return Item
	 */
	public static function valueOf(Item $item) {
		return $item;
	}

	/**
	 * Check if content item with such ID exists
	 * @param int $id
	 * @return boolean
	 */
	public static function existsById($id) {
		$iterator = ItemDBClient::getById($id);
		return $iterator->valid();
	}

	private static $items = array();

	/**
	 * Constructs new item and casts it to needed class by specified type.
	 * Insertion in DB is not executed!
	 * @param array $data
	 * @return mixed (Item, BlogPost, ForumTopic, InterviewQuestion, Photo, Video)
	 */
	public static function getByData($data) {
		if (isset(self::$items[$data['id']])) return self::$items[$data['id']];

		$item = new Item(-1, null, $data);

		switch ($item->getType()) {
		case self::BLOG_POST: $item = BlogPost::valueOf($item); break;
		case self::FORUM_TOPIC: $item = ForumTopic::valueOf($item); break;
//		case self::INTERVIEW_QUESTION: $item = InterviewQuestion::valueOf($item); break;
		case self::PHOTO: $item = Photo::valueOf($item); break;
		case self::VIDEO: $item = Video::valueOf($item); break;
		case self::EVENT: $item = Event::valueOf($item); break;
		case self::CROSS_POST: $item = CrossPost::valueOf($item); break;
		}

		return $item;
	}

	/**
	 * Constructs new item by its ID loading data from DB.
	 * @param int $id
	 * @return mixed (Item, BlogPost, ForumTopic, InterviewQuestion, Photo, Video)
	 */
	public static function getById($id) {
		if (isset(self::$items[$id])) return self::$items[$id];

		$item = new Item($id);

		switch ($item->getType()) {
		case self::BLOG_POST: $item = BlogPost::valueOf($item); break;
		case self::FORUM_TOPIC: $item = ForumTopic::valueOf($item); break;
//		case self::INTERVIEW_QUESTION: $item = InterviewQuestion::valueOf($item); break;
		case self::PHOTO: $item = Photo::valueOf($item); break;
		case self::VIDEO: $item = Video::valueOf($item); break;
		case self::EVENT: $item = Event::valueOf($item); break;
		case self::CROSS_POST: $item = CrossPost::valueOf($item); break;
		}

		return self::$items[$id] = $item;
	}

	/**
	 * Returns array of Item instances by their group.
	 * Casts Item to needed class by specified type.
	 * @param int $groupId
	 * @param int $from
	 * @param int $limit
	 * @param boolean $descendive
	 * @param string $orderBy
	 * @return array
	 */
	public static function getByGroupId($groupId, $from = 0, $limit = 0, $descendive = false, $orderBy = self::ID) {
		$iterator = ItemDBClient::getByGroupId($groupId, $from, $limit, $descendive, $orderBy);
		return self::makeArray($iterator);
	}

	public static function getByPeriod($begin, $end, $type = '') {
		$iterator = ItemDBClient::getByPeriod($begin, $end, $type);
		return self::makeArray($iterator);
	}

	/**
	 * @param int $groupId
	 * @return int count of items with such group ID
	 */
	public static function countByGroupId($groupId) {
		return ItemDBClient::countByGroupId($groupId);
	}

	/**
	 *
	 * @param string $type
	 * @return int count of items with given type
	 */
	public static function countByType($type) {
		return ItemDBClient::countByType($type);
	}

	/**
	 * Creates new item and inserts new row in DB
	 * @param string $type one of the types listed in constants of this class
	 * @param int $groupId id of group, zero if there is no such
	 * @param int $uid UID of creator
	 * @param int $creationTimestamp UNIX-timestamp of the creation
	 * @param string $contentSource source of content
	 * @param string $contentParsed parsed source of content
	 * @return Item (its children)
	 */
	public static function create($type, $groupId, $uid, $creationTimestamp, $contentSource, $contentParsed, $contentTitle = "", $contentValue = 0) {
		$id = ItemDBClient::insert($type, $groupId, $uid, $creationTimestamp, $contentSource, $contentParsed, $contentTitle, $contentValue);
		try {
			$item = Item::getById($id);
			self::$items[$id] = $item;
			return $item;
		} catch (Exception $e) {
			global $LOG;
			$LOG->exception($e);
			return null;
		}
	}

	/**
	 * Returns all items
	 * @return array
	 */
	public static function getAll($limit = 0, $descendive = false) {
		$iterator = ItemDBClient::getAll($limit, $descendive);
		return self::makeArray($iterator);
	}

	/**
	 * Returns array of items with specified $type in descending order
	 * by their average evaluations
	 * @param string $type
	 * @param int $limit
	 * @return array
	 */
	public static function getByRating($type, $limit, $groupId = 0) {
		$iterator = ItemDBClient::getAllByRating($type, $limit, $groupId);
		return self::makeArray($iterator);
	}

	/**
	 * Returns array of items where appeared new comments for such user
	 * (including new items with comments)
	 * @param User $user
	 * @param int $from
	 * @param int $limit
	 * @return array
	 */
	public static function getWithNewComments($user, $from, $limit) {
		$uid = ($user instanceof User) ? $user->getId() : $user;
		$iterator = ContentViewDBClient::getItemsWithNewCommentsForUser($uid, $from, $limit);
		return self::makeArray($iterator);
	}

	public static function getAllByType($type, $from = 0, $limit = 0, $descendive = false, $orderByCreation = false) {
		$iterator = ItemDBClient::getAllByType($type, $from, $limit, $descendive, $orderByCreation);
		return self::makeArray($iterator);
	}

	public static function getOpenedByType($type) {
		$iterator = ItemDBClient::getOpenedByType($type);
		return self::makeArray($iterator);
	}

	public static function getAllByTypeAndTag($type, $tag, $from = 0, $limit = 0, $descendive = false, $orderByCreation = false) {
		$tagId = $tag;
		if ($tag instanceof Tag) {
			$tagId = $tag->getId();
		}
		$iterator = ItemDBClient::getAllByTypeAndTag($type, $tagId, $from, $limit, $descendive, $orderByCreation);
		return self::makeArray($iterator);
	}

	public static function getDates() {
		$result = array();
		$iterator = ItemDBClient::getDates();
		while ($iterator->valid()) {
			$data = $iterator->current();
			if ($data['count'] && $data['time'] > "1970-01-01")
				$result[$data['time']] = $data['count'];
			$iterator->next();
		}
		return $result;
	}

	public static function getByTypeAndContentSource($type, $src) {
		$iterator = ItemDBClient::getByTypeAndContentSource($type, $src);
		return self::makeArray($iterator);
	}

	/**
	 * Iterates over DB query result and fetches it into an array of Item instances
	 * @param DBResultIterator $iterator
	 * @return array
	 */
	protected static function makeArray(DBResultIterator $iterator) {
		$items = array ();
		while ($iterator->valid()) {
			$data = $iterator->current();
			if (isset (self::$items[$data['id']])) {
				$items[] = self::$items[$data['id']];
			} else {
				$item = Item::getByData($data);
				self::$items[$item->getId()] = $item;
				$items[] = $item;
			}
			$iterator->next();
		}
		return $items;
	}

	public static function iterator() {

	}

	public static function cache(array $ids) {
		$iterator = ItemDBClient::getByIds($ids);
		while ($iterator->valid()) {
			$data = $iterator->current();
			self::$items[$data['id']] = Item::getByData($data);
			$iterator->next();
		}
	}

}
?>