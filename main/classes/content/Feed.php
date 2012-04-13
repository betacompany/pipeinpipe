<?php

require_once dirname(__FILE__) . '/../../includes/import.php';

import('db/MySQLResultIterator');
import('content/ItemsContainer');

/**
 * User: ortemij
 * Date: 11.04.12
 * Time: 23:23
 */
class Feed {

	const SELECT = 'SELECT
						GROUP_CONCAT(`id`) as `ids`,
						DATE(FROM_UNIXTIME(`creation_timestamp`)) as `d`,
					    IF(`uid`=0, UUID(), `uid`) as `uid`
					FROM
						`pv_content_item`';

	const GROUP =  'GROUP BY `type`, `uid`, `d`
					ORDER BY `id` DESC, `creation_timestamp` DESC ';

	public static function get($from = 0, $to = 19) {
		$dbIterator = self::selectByLimits($from, $to);
		return self::fetchItems($dbIterator);
	}

	public static function getBefore($id, $count = 20) {
		return self::fetchItems(self::selectBefore($id, $count));
	}

	public static function getAfter($id, $count = 20) {
		return self::fetchItems(self::selectAfter($id, $count));
	}

	public static function getNear($ts, $count = 20) {
		return self::fetchItems(self::selectNear($ts, $count));
	}

	private static function selectByLimits($from, $to) {
		return new MySQLResultIterator(
			mysql_qw(
				self::SELECT . self::GROUP . ' LIMIT ?, ?',
				$from, $to - $from + 1
			)
		);
	}

	private static function selectBefore($id, $count) {
		return new MySQLResultIterator(
			mysql_qw(
				self::SELECT . ' WHERE `id` < ? ' . self::GROUP .' LIMIT ?',
				$id, $count
			)
		);
	}

	private static function selectAfter($id, $count) {
		return new MySQLResultIterator(
			mysql_qw(
				self::SELECT . ' WHERE `id` > ? ' . self::GROUP .' LIMIT ?',
				$id, $count
			)
		);
	}

	private static function selectNear($ts, $count) {
		return new MySQLResultIterator(
			mysql_qw(
				self::SELECT . ' WHERE `creation_timestamp` <= ? ' . self::GROUP .' LIMIT ?',
				$ts, $count
			)
		);
	}

	private static function fetchItems(DBResultIterator $dbIterator) {
		$items = array();
		while ($dbIterator->valid()) {
			$grouped = $dbIterator->current();
			$ids = $grouped['ids'];
			$itemsIterator =
				new MySQLResultIterator(
					mysql_qw(
						"SELECT * FROM `p_content_item` WHERE `id` IN ($ids)"
					)
				);
			$itemsArray = array();
			while ($itemsIterator->valid()) {
				$data = $itemsIterator->current();
				$itemsArray[] = Item::getByData($data);
				$itemsIterator->next();
			}
			if (count($itemsArray) > 1) {
				if ($itemsArray[0] instanceof CrossPost) {
					$splitted = array();
					foreach ($itemsArray as $item) {
						$type = $item->getSocialWebType();
						if (!$splitted[ $type ]) {
							$splitted[ $type ] = array();
						}
						$splitted[ $type ][] = $item;
					}
					foreach ($splitted as $array) {
						$items[] = new ItemsContainer($array);
					}
				} else {
					$items[] = new ItemsContainer($itemsArray);
				}
			} elseif (count($itemsArray) == 1) {
				$items[] = $itemsArray[0];
			}
			$dbIterator->next();
		}
		return $items;
	}
}
