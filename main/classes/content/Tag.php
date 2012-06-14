<?php

require_once dirname(__FILE__) . '/../db/TagDBClient.php';

require_once dirname(__FILE__) . '/../utils/IJsonSerializable.php';

require_once dirname(__FILE__) . '/../../includes/common.php';

/**
 * @author ortemij
 */
class Tag implements IJsonSerializable {

	private $id;
	private $uid;
	private $value;

	private $count = -1;

	public function  __construct($id, $data = null) {
		if ($data == null) {
			$iterator = TagDBClient::selectById($id);
			if ($iterator->valid()) {
				$data = $iterator->current();
			} else {
				throw new InvalidIdException('There is no tag with id=' . $id);
			}
		}

		$this->id = $data['id'];
		$this->uid = $data['uid'];
		$this->value = $data['value'];
	}

	public function getId() {
		return $this->id;
	}

	public function getUID() {
		return $this->uid;
	}

	public function getUser() {
		return User::getById($this->uid);
	}

	public function getValue() {
		return $this->value;
	}

	public function getCount() {
		return $this->count;
	}

	public function setCount($count) {
		$this->count = $count;
	}

	public function getTaggedItems($type) {
		$iterator = TagDBClient::getItemsByTagId($this->id, $type);
        return Item::getByDataIterator($iterator);
	}

	public function getItemsTaggedByUser(User $user = null) {
        if (!$user) {
            global $auth;
            $user = $auth->getCurrentUser();
        }
		$iterator = TagDBClient::getItemsTaggedByUser($this->id, $user->getId());
        return Item::getByDataIterator($iterator);
	}

    public function hasTaggedItem(Item $item) {
        return array_contains($this->getTaggedItems($item->getType()), $item);
    }

    public function hasItemTaggedByUser(Item $item, User $user = null) {
        return array_contains($this->getItemsTaggedByUser($user), $item);
    }

	public static function create($uid, $value) {
		assertTrue('There is no user with id=' . $uid, User::existsById($uid));
		$value = string_process($value, SECURITY_STRICT);

		$id = TagDBClient::insert($uid, $value);
		return new Tag($id);
	}

	// FIXME
	public static $max;
	
	private static $tags = array ();

	public static function getAll() {
		$iterator = TagDBClient::getAll();
		while ($iterator->valid()) {
			$data = $iterator->current();
			self::$tags[ $data['id'] ] = new Tag(-1, $data);
			$iterator->next();
		}
		return array_values(self::$tags);
	}

	public static function getAllJSON() {
		$result = array ();
		foreach (self::getAll() as $tag) {
			$json = array ();
			$json['id'] = $tag->getId();
			$json['value'] = $tag->getValue();
			$result[] = $json;
		}
		return json($result);
	}

	public static function getAllByType($type, $descendive = false) {
		$iterator = TagDBClient::getAllByTypeWithCounts($type, $descendive);
		$result = array();
		while ($iterator->valid()) {
			$data = $iterator->current();
			$tag = new Tag(-1, $data);
			$tag->setCount($data['count']);
			self::$max = max($data['count'], self::$max);
			$result[] = $tag;
			$iterator->next();
		}
		return $result;
	}

	public static function getByItem($item) {
		$iterator = TagDBClient::getByItemId($item->getId());
		$result = array();
		while ($iterator->valid()) {
			$data = $iterator->current();
			$result[] = new Tag(-1, $data);
			$iterator->next();
		}
		return $result;
	}

	public static function getById($id) {
		$iterator = TagDBClient::selectById($id);
		if ($iterator->valid()) {
			$data = $iterator->current();
			return new Tag(-1, $data);
		}
		return null;
	}

	public function toJson() {
		return json(array(
			'id' => $this->id,
			'uid' => $this->uid,
			'value' => $this->value
		));
	}
}
?>
