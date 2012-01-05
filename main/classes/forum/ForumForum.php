<?php

require_once dirname(__FILE__) . '/../../includes/assertion.php';
require_once dirname(__FILE__) . '/../../includes/security.php';

require_once dirname(__FILE__) . '/../db/DBResultIterator.php';

require_once dirname(__FILE__) . '/ForumDB.php';
require_once dirname(__FILE__) . '/ForumPart.php';

require_once dirname(__FILE__) . '/../content/Group.php';

require_once dirname(__FILE__) . '/../exceptions/useful_exception_set.php';

/**
 * Description of ForumForum
 *
 * @author Artyom Grigoriev
 */
class ForumForum extends Group {

	public function  __construct($id, $group = null, $data = null) {
		$id = intval($id);
		parent::__construct($id, $group, $data);
	}

	public function getParts() {
		return $this->getChildren();
	}

	public function getTimestamp() {
		if ($this->timestampLoaded) return $this->timestamp;

		$this->timestamp = 0;
		$iterator = ForumDB::selectLast(ForumDB::LAST_REFRESHED_TYPE_FORUM, $this->getId());
		if ($iterator->valid()) {
			$data = $iterator->current();
			$this->timestamp = $data['timestamp'];
		} else {
			$lastPartTimestamp = ForumDB::getLastPartTimestamp($this->getId());
			$this->setTimestamp($lastPartTimestamp);
		}

		$this->timestampLoaded = true;
		return $this->timestamp;
	}

	public function setTimestamp($timestamp) {
		ForumDB::insertLast(ForumDB::LAST_REFRESHED_TYPE_FORUM, $this->getId(), $timestamp);
		$this->timestamp = $timestamp;
	}

	public static function existsById($id) {
		$iterator = ForumDB::selectForum($id);
		return $iterator->valid();
	}

	public static function valueOf(Group $other) {
		if ($other->getType() != Group::FORUM_FORUM) {
			throw new Exception('Unable to cast ['.$other->getType().'] to ForumForum');
		}

		return new ForumForum(-1, $other);
	}
}
?>
