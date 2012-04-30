<?php

require_once dirname(__FILE__) . '/../content/Item.php';

/**
 * @author Artyom Grigoriev
 */
class Photo extends Item {

	private $urls = array();
	private $availableSizes = array();

	protected $evaluation;
	protected $evaluationLoaded = false;

	const SIZE_MICRO = '50x30';
	const SIZE_MINI = '150x100';
	const SIZE_MIDDLE = '600x400';
	const SIZE_HQ = '1024x600';

	/**
	 * @deprecated Use Item::getById() instead
	 * @param int $id
	 * @param Item $item [optional]
	 */
	protected function  __construct($id, $item = null) {
		parent::__construct($id, $item);
		if ($this->type != Item::PHOTO) throw new Exception('Item is not photo!');
		if ($this->contentParsed == '') {
			$this->parseAndSetContent();
			$this->update();
		}
		$this->urls = unserialize($this->contentSource);
		$this->availableSizes = array_keys($this->urls);
	}

	/**
	 * Casts item to photo type if possible
	 * @param Item $item
	 * @return Photo 
	 */
	public static function valueOf(Item $item) {
		return new Photo(-1, $item);
	}

	/**
	 * Returns associative array with keys in format of image size: ex. 640x480,
	 * and values - URLs of that images. URL may be relative or absolute.
	 * @return array
	 */
	public function getUrls() {
		return $this->urls;
	}

	/**
	 * Inserts new available URL with its size.
	 * @param string $size ex. 640x480
	 * @param string $url
	 * @param boolean $update 
	 */
	public function addUrl($size, $url, $update = true) {
		if (!isset($this->urls[$size])) {
			$this->availableSizes[] = $size;
		}
		
		$this->urls[$size] = $url;
		if ($update) $this->update();
	}

	/**
	 * Return size (in described below format) the nearest by square above
	 * the $size.
	 * @param string $size
	 * @return string 
	 */
	public function getNearestAvailableSize($size = self::SIZE_MIDDLE) {
		$squares = array ();
		foreach ($this->availableSizes as $avsize) {
			list ($w, $h) = explode('x', $avsize, 2);
			$avsquare = $w * $h;
			$squares["$avsquare"] = $avsize;
		}

		krsort($squares);

		list ($w, $h) = explode('x', $size, 2);
		$square = $w * $h;

		$prev = false;
		foreach ($squares as $avsquare => $avsize) {
			if (!$prev) $prev = $avsize;
			if ($avsquare - $square >= 0) {
				$prev = $avsize;
			}
		}

		return $prev;
	}

	/**
	 * Return URL of image with the nearest size by square above
	 * the $size.
	 * @param string $size
	 * @return string
	 */
	public function getUrl($size = self::SIZE_MIDDLE) {
		return $this->urls[$this->getNearestAvailableSize($size)];
	}

	/**
	 * Return URL of preview image
	 * @return string
	 */
	public function getPreviewUrl() {
		return $this->getUrl(self::SIZE_MICRO);
	}

	/**
	 * Returns the title of this photo
	 * @return string
	 */
	public function getTitle() {
		return $this->getContentParsed();
	}

	public function update() {
		$this->contentSource = serialize($this->urls);
		parent::update();
	}

	/**
	 *
	 * @return float
	 */
	public function getEvaluation() {
		$actions = $this->getActions();
		
		$value = 0;
		$count = 0;
		foreach ($actions as $action) {
			if ($action->getType() == Action::EVALUATION) {
				$count++;
				$value += $action->getValue();
			}
		}

		return ($count == 0) ? 0 : ($value / $count);
	}

	/**
	 * Creates new photo in DB
	 * @param int $albumId group ID of the album
	 * @param int $uid ID of user who is a creator or an owner of this photo
	 * @param string $title
	 * @param array $urls associative array with key=size and value=URL of a variant of the photo
	 * @return Photo
	 */
	public static function create($albumId, $uid, $title, $urls) {
		return parent::create(Item::PHOTO, $albumId, $uid, time(), serialize($urls), $title);
	}

	/**
	 * Returns photos by their rating in descending order
	 * @param int $limit
	 * @return array
	 */
	public static function getAllByRating($limit, $groupId = 0) {
		return parent::getByRating(Item::PHOTO, $limit, $groupId);
	}

	private function parseAndSetContent() {
		$this->contentParsed = 'Фотография';
	}
}
?>
