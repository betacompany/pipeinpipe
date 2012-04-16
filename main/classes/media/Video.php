<?php

require_once dirname(__FILE__) . '/../content/Item.php';

/**
 * @author Artyom Grigoriev
 */
class Video extends Item {

    private static $VIDEO_CODE_PREFIXES = array(
        "<iframe ",
        "<object "
    );

	protected function  __construct($id, $item = null) {
		parent::__construct($id, $item);
		if ($this->type != Item::VIDEO) throw new Exception('Item is not video!');
	}

	public static function valueOf(Item $item) {
		return new Video(-1, $item);
	}

	/**
	 * Returns HTML-code for pasting into page
	 * @return string
	 */
	public function getSource() {
		return $this->getContentSource();
	}

    public function isVideoCode() {
        $source = $this->getSource();
        foreach (self::$VIDEO_CODE_PREFIXES as $prefix) {
            if (string_starts_with($source, $prefix)) {
                return true;
            }
        }
    }

	/**
	 * Returns title of this video
	 * @return string
	 */
	public function getTitle() {
		return $this->getContentParsed();
	}

	/**
	 * Returns URL of preview image
	 * @return string
	 */
	public function getPreviewUrl() {
		if (!file_exists(dirname(__FILE__) . '/../../content/videos/' . $this->getId() . '.jpg')) {
			return '/content/videos/default.jpg';
		}
		return '/content/videos/' . $this->getId() . '.jpg';
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

	public static function create($albumId, $uid, $title, $html) {
		return Item::create(Item::VIDEO, $albumId, $uid, time(), $html, $title);
	}

	/**
	 * Returns videos by their rating in descending order
	 * @param int $limit
	 * @return array
	 */
	public static function getAllByRating($limit) {
		return parent::getByRating(Item::VIDEO, $limit);
	}

}
?>
