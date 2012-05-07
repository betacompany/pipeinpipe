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
	private $socialWebAuthorName;
	private $timestamp;

	private $outerId;

	private $content;

	private $handled;

	private static $cache = array();

	private function __construct($id, $data = null) {
		if ($data == null) {
			$it = SocialPostDBClient::getById($id);
			if ($it->valid()) {
				$data = $it->current();
			}
		}

		$this->id				   = $data['id'];
		$this->socialWebType	   = $data['source'];
		$this->socialWebAuthorId   = $data['user_id'];
		$this->socialWebAuthorName = $data['first_name'] ? $data['first_name'] . " " . $data['last_name'] : $data['user_id'];
		$this->outerId			   = $data['outer_id'];
		$this->content			   = $data['content'];
		$this->timestamp		   = $data['timestamp'];
		$this->handled			   = $data['handled'];
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

	public function getSocialWebAuthorName() {
		return $this->socialWebAuthorName;
	}

	public function getContent() {
		return $this->content;
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

	public function getUrl() {
		switch ($this->socialWebType) {
		case ISocialWeb::TWITTER:
			return "http://twitter.com/{$this->socialWebAuthorId}";
		case ISocialWeb::VKONTAKTE:
			return "http://vk.com/wall{$this->socialWebAuthorId}_{$this->outerId}";
		}
		return "#";
	}

	public function makeHandled() {
		if (SocialPostDBClient::setHandled($this->id)) {
			$this->handled = true;
		}
	}

	/**
	 * @static
	 * @param $id
	 * @return SocialPost
	 */
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
