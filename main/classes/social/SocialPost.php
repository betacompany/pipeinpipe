<?php

require_once dirname(__FILE__) . '/../db/SocialPostDBClient.php';

/**
 * User: ortemij
 * Date: 28.03.12
 * Time: 10:32
 */
class SocialPost {

	private $id;
	private $socialWebType;
	private $socialWebAuthorId;
	private $timestamp;

	private $title;
	private $source;

	private $handled;

	private static $cache = array();

	private function __construct($id, $data = null) {
		if ($data == null) {
			$it = SocialPostDBClient::getById($id);
			if ($it->valid()) {
				$data = $it->current();
			}
		}

		$this->id				 = $data['id'];
		$this->socialWebType	 = $data['sw_type'];
		$this->socialWebAuthorId = $data['sw_author_id'];
		$this->title			 = $data['title'];
		$this->source			 = $data['source'];
		$this->handled			 = $data['handled'];
		$this->timestamp		 = $data['timestamp'];
	}

	public function getId() {
		return $this->id;
	}

	public function getSocialWebAuthorId() {
		return $this->socialWebAuthorId;
	}

	public function getSocialWebType() {
		return $this->socialWebType;
	}

	public function getSource() {
		return $this->source;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getHandleTime() {
		return $this->handled;
	}

	public function isHandled() {
		return $this->handled > 0;
	}

	public function getTimestamp() {
		return $this->timestamp;
	}

	public function makeHandled() {
		$time = time();
		if (SocialPostDBClient::setHandled($this->id, $time)) {
			$this->handled = $time;
		}
	}

	public static function getById($id) {
		if (self::$cache[$id]) {
			return self::$cache[$id];
		}
		return self::$cache[$id] = new SocialPost($id);
	}

	public static function getAllUnhandled() {
		$result = array();
		$it = SocialPostDBClient::getAllUnhandled();
		while ($it->valid()) {
			$data = $it->current();
			$result[] = new SocialPost(-1, $data);
			$it->next();
		}
		return $result;
	}
}
