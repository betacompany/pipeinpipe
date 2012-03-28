<?php
/**
 * User: ortemij
 * Date: 28.03.12
 * Time: 10:32
 */
class SocialPost {

	private $id;
	private $socialWebType;
	private $socialWebAuthorId;

	private $title;
	private $source;

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

	public static function getById($id) {
		if (self::$cache[$id]) {
			return self::$cache[$id];
		}
		return self::$cache[$id] = new SocialPost($id);
	}
}
