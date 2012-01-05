<?php

require_once dirname(__FILE__) . '/../content/Item.php';

/**
 * @author Artyom Grigoriev
 */
class Event extends Item {

	public function getStartTimestamp() {
		return $this->getContentValue();
	}

	/**
	 * @param string $date date('Y-m-d') as pattern
	 * @param string $startTime date('H:m:i') as pattern
	 * @param string $title
	 * @param string $description
	 * @param int $uid
	 * @return Event
	 */
	public static function create($date, $startTime, $title, $description, $uid) {
		$contentSource = $description;
		$contentParsed = Parser::parseEvent($contentSource);
		$timestamp = strtotime($date . ' ' . $startTime);

		return parent::create(
			Item::EVENT,
			0,
			$uid,
			time(),
			$contentSource,
			$contentParsed,
			$title,
			$timestamp
		);
	}

	public static function valueOf(Item $item) {
		return new Event(-1, $item, null);
	}

	/**
	 * @param int $begin timestamp of period's begin
	 * @param int $end timestamp of period's end
	 * @return array
	 */
	public static function getByPeriod($begin, $end) {
		$iterator = ItemDBClient::getByPeriodValues($begin, $end, Item::EVENT);
		return parent::makeArray($iterator);
	}
}
?>
