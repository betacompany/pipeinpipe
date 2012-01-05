<?php

require_once dirname(__FILE__) . '/../db/ConnectionDBClient.php';

require_once dirname(__FILE__) . '/Group.php';
require_once dirname(__FILE__) . '/Item.php';

require_once dirname(__FILE__) . '/../cupms/Competition.php';
require_once dirname(__FILE__) . '/../cupms/League.php';

require_once dirname(__FILE__) . '/../user/User.php';

/**
 * @author Artyom Grigoriev
 */
class Connection {

	const CONTENT_ITEM = 'item';
	const CONTENT_GROUP = 'group';
	const CONTENT_TAG = 'tag';
	
	const HOLDER_USER = 'user';
	const HOLDER_COMPETITION = 'competition';
	const HOLDER_LEAGUE = 'league';
	
	private $id;

	private $contentType;
	private $contentId;
	private $content;
	private $contentLoaded = false;

	private $holderType;
	private $holderId;
	private $holder;
	private $holderLoaded = false;

	private function  __construct($id, $data = null) {
		if ($data == null) {
			$iterator = ConnectionDBClient::getById($id);
			if ($iterator->valid()) {
				$data = $iterator->current();
			} else {
				throw new InvalidIdException('There is no connection with id='.$id);
			}
		}

		$this->id = intval($data['id']);
		$this->contentType = $data['content_type'];
		$this->contentId = intval($data['content_id']);
		$this->holderType = $data['holder_type'];
		$this->holderId = intval($data['holder_id']);
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getContentType() {
		return $this->contentType;
	}

	/**
	 * @return int
	 */
	public function getContentId() {
		return $this->contentId;
	}

	/**
	 * @global Logger $LOG
	 * @return mixed (Group, Item)
	 */
	public function getContent() {
		if ($this->contentLoaded) return $this->content;
		switch ($this->contentType) {
		case Connection::CONTENT_GROUP:
			$this->content = Group::getById($this->contentId);
			break;
		case Connection::CONTENT_ITEM:
			$this->content = Item::getById($this->contentId);
			break;
		case Connection::CONTENT_TAG:
			$this->content = Tag::getById($this->contentId);
			break;
		default:
			global $LOG;
			@$LOG->warn('Strange content type ('.$this->contentType.') for the connection (id='.$this->id.')');
			$this->content = null;
		}

		$this->contentLoaded = true;
		return $this->content;
	}

	/**
	 * @return string
	 */
	public function getHolderType() {
		return $this->holderType;
	}

	/**
	 * @return int
	 */
	public function getHolderId() {
		return $this->holderId;
	}

	/**
	 * @global Logger $LOG
	 * @return mixed (User, Connection, League)
	 */
	public function getHolder() {
		switch ($this->holderType) {
		case Connection::HOLDER_COMPETITION:
			$this->holder = Competition::getById($this->holderId);
			break;
		case Connection::HOLDER_LEAGUE:
			$this->holder = League::getById($this->holderId);
			break;
		case Connection::HOLDER_USER:
			$this->holder = User::getById($this->holderId);
			break;
		default:
			global $LOG;
			@$LOG->warn('Strange holder type ('.$this->holderType.') for the connection (id='.$this->id.')');
			$this->holder = null;
		}

		$this->holderLoaded = true;
		return $this->holder;
	}

	/**
	 * Binds some content and some holder
	 * @throws UnexpectedValueException
	 * @param mixed $content
	 * @param mixed $holder
	 * @return Connection
	 */
	public static function bind($content, $holder) {
		$contentType = self::contentType($content);
		$contentId = $content->getId();
		$holderType = self::holderType($holder);
		$holderId = $holder->getId();

		$id = ConnectionDBClient::insert($holderType, $holderId, $contentType, $contentId);
		try {
			return new Connection($id);
		} catch (Exception $e) {
			return null;
		}
	}

	/**
	 * Returns array of holders of specified content
	 * @throws UnexpectedValueException
	 * @param mixed $content Item, Group, Tag
	 * @return array of User, League, Competition instances
	 */
	public static function getHoldersFor($content) {
		$contentType = self::contentType($content);
		$iterator = ConnectionDBClient::getByContent($contentType, $content->getId());
		$holders = array();
		while ($iterator->valid()) {
			$data = $iterator->current();
			$connection = new Connection(-1, $data);
			$holders[] = $connection->getHolder();
			$iterator->next();
		}

		return $holders;
	}

	/**
	 * Returns array of content instances binded to such holder
	 * @throws UnexpectedValueException
	 * @param mixed $holder User, League, Competition
	 * @return array of Item, Group
	 */
	public static function getContentsFor($holder) {
		$holderType = self::holderType($holder);
		$iterator = ConnectionDBClient::getByHolder($holderType, $holder->getId());
		$contents = array();
		while ($iterator->valid()) {
			$data = $iterator->current();
			$connection = new Connection(-1, $data);
			$contents[] = $connection->getContent();
			$iterator->next();
		}

		return $contents;
	}

	/**
	 * Returns array of groups by specified $holder and with type of $type
	 * @param mixed $holder
	 * @param string $type
	 * @return array 
	 */
	public static function getTypifiedContentGroupsFor($holder, $type) {
		$holderType = self::holderType($holder);
		$iterator = ConnectionDBClient::getTypifiedContentGroups($holderType, $holder->getId(), $type);
		$groups = array ();
		while ($iterator->valid()) {
			$data = $iterator->current();
			$groups[] = Group::getByData($data);
			$iterator->next();
		}
		return $groups;
	}

	/**
	 *
	 */
	public static function getTypifiedContentItemsFor($holder, $type) {
		$holderType = self::holderType($holder);
		$iterator = ConnectionDBClient::getTypifiedContentItemsRecursive($holderType, $holder->getId(), $type);
		$items = array ();
		while ($iterator->valid()) {
			$data = $iterator->current();
			$items[] = Item::getByData($data);
			$iterator->next();
		}
		return $items;
	}

	/**
	 *
	 * @param User|League|Competition $holder
	 * @param string $type
	 * @return array of Item
	 */
	public static function getTaggedTypifiedContentItemsFor($holder, $type) {
		$holderType = self::holderType($holder);
		$iterator = ConnectionDBClient::getByHolder($holderType, $holder->getId());
		$items = array ();
		while ($iterator->valid()) {
			$data = $iterator->current();
			if ($data['content_type'] == self::CONTENT_TAG) {
				$tag = Tag::getById($data['content_id']);
				$taggedItems = $tag->getTaggedItems($type);
				foreach ($taggedItems as $taggedItem) {
					$items[ strval( $taggedItem->getId() ) ] = $taggedItem;
				}
			}
			$iterator->next();
		}
		ksort($items);
		return array_values($items);
	}

	/**
	 * Returns absolute site URL
	 * @return string
	 */
	public static function holderURL($holder) {
		if ($holder instanceof User) {
			return '/id' . $holder->getId();
		} elseif ($holder instanceof League) {
			return '/sport/league/' . $holder->getId();
		} elseif ($holder instanceof Competition) {
			return '/sport/league/' . $holder->getLeagueId() . '/competition/' . $holder->getId();
		} else {
			throw new UnexpectedValueException('Expected User, League, Competition');
		}
	}

	/**
	 * @return string
	 */
	public static function holderTitle($holder) {
		if ($holder instanceof User) {
			return $holder->getFullName();
		} elseif ($holder instanceof League) {
			return $holder->getName();
		} elseif ($holder instanceof Competition) {
			return $holder->getName();
		} else {
			throw new UnexpectedValueException('Expected User, League, Competition');
		}
	}

	/**
	 * @throws UnexpectedValueException
	 * @param mixed $content
	 * @return string
	 */
	private static function contentType($content) {
		if ($content instanceof Group) {
			return Connection::CONTENT_GROUP;
		} elseif ($content instanceof Item) {
			return Connection::CONTENT_ITEM;
		} elseif ($content instanceof Tag) {
			return Connection::CONTENT_TAG;
		} else {
			throw new UnexpectedValueException('Expected Group or Item instance');
		}
	}

	/**
	 * @throws UnexpectedValueException
	 * @param mixed $holder
	 * @return string
	 */
	private static function holderType($holder) {
		if ($holder instanceof User) {
			return Connection::HOLDER_USER;
		} elseif ($holder instanceof League) {
			return Connection::HOLDER_LEAGUE;
		} elseif ($holder instanceof Competition) {
			return Connection::HOLDER_COMPETITION;
		} else {
			throw new UnexpectedValueException('Expected User, League, Competition');
		}
	}
}
?>
