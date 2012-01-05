<?php

require_once dirname(__FILE__) . '/../../includes/assertion.php';

require_once dirname(__FILE__) . '/../db/GroupDBClient.php';

require_once dirname(__FILE__) . '/Item.php';
require_once dirname(__FILE__) . '/Comment.php';

require_once dirname(__FILE__) . '/../forum/ForumForum.php';
require_once dirname(__FILE__) . '/../forum/ForumPart.php';

require_once dirname(__FILE__) . '/../blog/Blog.php';


/**
 * @author Artyom Grigoriev
 */
class Group {

	const PHOTO_ALBUM = 'photo_album';
	const VIDEO_ALBUM = 'video_album';
	const FORUM_PART = 'forum_part';
	const FORUM_FORUM = 'forum_forum';
	const INTERVIEW = 'interview';
	const BLOG = 'blog';

	private $id;
	private $type;

	private $parentGroupId;
	private $parentGroup;
	private $parentGroupLoaded = false;

	private $title;

	private $children;
	private $childrenLoaded = false;
	private $childrenCount = -1;

	private $items;
	private $itemsLoaded = false;
	private $itemsCount = -1;

	private $newItemsCount = -1;

	private $commentsCount = -1;

	protected $contentSource;
	protected $contentParsed;

	/**
	 * Constructs new instance of class Group
	 * Is to be called with one of three ways:
	 * <ol>
	 *  <li>
	 *	 <b>new Group(id)</b> constructs instance by ID in database (loads data from there).
	 *  </li>
	 *  <li>
	 *   <b>new Group(-1, $group)</b> constructs clone of $group, new row in DB is not inserted.
	 *  </li>
	 *  <li>
	 *   <b>new Group(-1, null, $data)</b> constructs new group by passed $data, new row in DB is not inserted.
	 *  </li>
	 * </ol>
	 * @param int $id
	 * @param Group $group [optional]
	 * @param array $data [optional]
	 * @deprecated You should use Group::getById() instead
	 */
	protected function  __construct($id, $group = null, $data = null) {
		if ($group != null) {
			$this->id = $group->id;
			$this->type = $group->type;
			$this->parentGroupId = $group->parentGroupId;
			$this->title = $group->title;
			$this->contentSource = $group->contentSource;
			$this->contentParsed = $group->contentParsed;
			return;
		}

		if ($data == null) {
			assertPositive('ID of the content group should be positive!', $id);
			$iterator = GroupDBClient::getById($id);
			if ($iterator->valid()) {
				$data = $iterator->current();
			} else {
				throw new InvalidIdException('There is no content group with id=' . $id);
			}
		}

		$this->id = $data['id'];
		$this->type = $data['type'];
		$this->parentGroupId = $data['parent_group_id'];
		$this->title = $data['title'];
		$this->contentSource = $data['content_source'];
		$this->contentParsed = $data['content_parsed'];
	}

	private static $groups = array();

	/**
	 * 
	 * @param int $id
	 * @return mixed (Group, ForumForum, ForumPart)
	 */
	public static function getById($id) {
		if (isset (self::$groups[$id])) return self::$groups[$id];

		$group = new Group($id);
		switch ($group->type) {
		case self::BLOG: $group = Blog::valueOf($group); break;
		case self::FORUM_FORUM: $group = ForumForum::valueOf($group); break;
		case self::FORUM_PART: $group = ForumPart::valueOf($group); break;
		//case self::INTERVIEW: return Interview::valueOf($group);
		//case self::PHOTO_ALBUM: return PhotoAlbum::valueOf($group);
		//case self::VIDEO_ALBUM: return VideoAlbum::valueOf($group);
		}

		return self::$groups[$id] = $group;
	}

	/**
	 * @param array $data
	 * @return mixed (Group, FroumForum, ForumPart)
	 */
	public static function getByData($data) {
		if (isset (self::$groups[$data['id']])) return self::$groups[$data['id']];

		$group = new Group(-1, null, $data);
		switch ($group->type) {
		case self::BLOG: $group = Blog::valueOf($group); break;
		case self::FORUM_FORUM: $group = ForumForum::valueOf($group); break;
		case self::FORUM_PART: $group = ForumPart::valueOf($group); break;
		}

		return self::$groups[$data['id']] = $group;
	}

	/**
	 * Returns array of groups which have not any parent group
	 * and are of such $type.
	 * @param int $type
	 * @return array
	 */
	public static function getRootsByType($type, $descendive = false) {
		$iterator = GroupDBClient::getRootsByType($type, $descendive);
		$result = array();
		while ($iterator->valid()) {
			$data = $iterator->current();
			$result[] = Group::getByData($data);
			$iterator->next();
		}

		return $result;
	}

	/**
	 * Return array of groups by parent group
	 * @param int $parentGroupId
	 * @return array
	 */
	public static function getByParentGroupId($parentGroupId) {
		$iterator = GroupDBClient::getByParentGroupId($parentGroupId);
		$result = array();
		while ($iterator->valid()) {
			$data = $iterator->current();
			$result[] = Group::getByData($data);
			$iterator->next();
		}

		return $result;
	}

	/**
	 *
	 * @return int
	 */
	public function getId() {
		return intval($this->id);
	}

	/**
	 * Returns one of group types listed as Group class constants
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns title of this group.<br />
	 * For example title may be used as a name of blog
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns ID of parent group.
	 * Zero if this group has no parent
	 * @return int
	 */
	public function getParentGroupId() {
		return intval($this->parentGroupId);
	}

	/**
	 * Returns parent group
	 * @return mixed (Group, ForumForum, ForumPart)
	 */
	public function getParentGroup() {
		if ($this->parentGroupLoaded) return $this->parentGroup;

		try {
			$this->parentGroup = Group::getById($this->parentGroupId);
		} catch (Exception $e) {
			global $LOG;
			$LOG->exception($e);
		}

		$this->parentGroupLoaded = true;
		return $this->parentGroup;
	}

	/**
	 * Returns content source.<br />
	 * For example content source may contain plain description of blog.
	 * @return string
	 */
	public function getContentSource() {
		return $this->contentSource;
	}

	/**
	 * Returns parsed content source.<br />
	 * For example parsed content may be HTML code of description
	 * to display in browser.
	 * @return string
	 */
	public function getContentParsed() {
		return $this->contentParsed;
	}

	/**
	 * Returns array of items which this group contains directly.
	 * Without params returns all of them.
	 * @param $from [optional]
	 * @param $limit [optional] max count of items in request
	 * @param $descendive [optional] descendive order
	 * @param $orderBy [optional]
	 * @return array
	 */
	public function getItems($from = 0, $limit = 0, $descendive = false, $orderBy = Item::ID) {
		if ($this->itemsLoaded && $limit == 0) return $this->items;
		$items = Item::getByGroupId($this->id, $from, $limit, $descendive, $orderBy);
		if ($limit == 0) $this->items = $items;
		if ($limit == 0) $this->itemsLoaded = true;
		return $items;
	}

	/**
	 * Returns number of items which this group contains directly
	 * @return int
	 */
	public function countItems() {
		if ($this->itemsCount != -1)
			return $this->itemsCount;

		if ($this->itemsLoaded) {
			$this->itemsCount = count($this->items);
		} else {
			$this->itemsCount = Item::countByGroupId($this->id);
		}

		return $this->itemsCount;
	}

	/**
	 * @todo
	 */
	public function getNewItemsCount() {
		return $this->newItemsCount;
	}

	/**
	 * Returns child-groups
	 * @return array
	 */
	public function getChildren() {
		if ($this->childrenLoaded) return $this->children;
		$this->children = Group::getByParentGroupId($this->id);
		$this->childrenLoaded = true;
		return $this->children;
	}

	/**
	 * Returns a number of child-groups
	 * @return int
	 */
	public function countChildren() {
		if ($this->childrenLoaded) return count($this->children);
		if ($this->childrenCount != -1) return $this->childrenCount;
		return $this->childrenCount = Group::countGroupsByParent($this->id);
	}

	/**
	 * Returns a number of comments which this group contains by
	 * containing its items.
	 * @return int
	 */
	public function countComments() {
		if ($this->commentsCount != -1) return $this->commentsCount;
		$this->commentsCount = Comment::countCommentsForGroup($this->getId());
		return $this->commentsCount;
	}

	public function setCommentsCount($count) {
		$this->commentsCount = $count;
	}

	public function setNewItemsCount($count) {
		$this->newItemsCount = $count;
	}

	/**
	 * Returns associative array with such keys:
	 * <ul>
	 *  <li>
	 *   <b>id</b> - ID of this group
	 *  </li>
	 *  <li>
	 *   <b>type</b> - type of this group
	 *  </li>
	 *  <li>
	 *   <b>parent_group_id</b> - ID of the parent of this group
	 *  </li>
	 *  <li>
	 *   <b>title</b> - title of this group
	 *  </li>
	 * </ul>
	 * @return array
	 */
	public function toArray() {
		return array (
			'id' => $this->id,
			'type' => $this->type,
			'parent_group_id' => $this->parentGroupId,
			'title' => $this->title
		);
	}

	public function viewedBy(User $user) {
		$timestamp = time();
		return ContentViewDBClient::refresh('group', $this->getId(), $user->getId(), $timestamp);
	}

	/**
	 * Removes current item
	 * @return boolean
	 */
	public function remove() {
		return GroupDBClient::remove($this->getId());
	}

	/**
	 * Checks if there is a group in DB with specified ID
	 * @return boolean
	 */
	public static function existsById($id) {
		$iterator = GroupDBClient::getById($id);
		return $iterator->valid();
	}

	private static function countGroupsByParent($groupId) {
		return GroupDBClient::countByParentGroupId($groupId);
	}

	public static function create($type, $parentGroupId, $title, $contentSource, $contentParsed) {
		$id = GroupDBClient::insert($type, $parentGroupId, $title, $contentSource, $contentParsed);
		try {
			return Group::getById($id);
		} catch (Exception $e) {
			return null;
		}
	}

	public static function getAllByType($type) {
		$iterator = GroupDBClient::getAllByType($type);
		$result = array();
		while ($iterator->valid()) {
			$data = $iterator->current();
			$result[] = Group::getByData($data);
 			$iterator->next();
		}

		return $result;
	}

	public static function preloadNewItemsCountFor($user) {
		$uid = $user;
		if ($user instanceof User) {
			$uid = $user->getId();
		}

		$iterator = GroupDBClient::getNewItemsCountFor($uid);
		while ($iterator->valid()) {
			$data = $iterator->current();
			if (isset(self::$groups[$data['group_id']])) {
				self::$groups[$data['group_id']]->setNewItemsCount($data['count']);
			}
			$iterator->next();
		}
	}

	public static function countByType($type) {
		return GroupDBClient::countByType($type);
	}
}
?>
