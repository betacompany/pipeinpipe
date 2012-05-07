<?php

require_once dirname(__FILE__) . '/../../includes/import.php';

import('db/MySQLResultIterator');
import('content/ItemsContainer');
import('forum/Forum');

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
					ORDER BY `d` DESC, `id` DESC ';

	public static function get($from = 0, $to = 19) {
		$dbIterator = self::selectByLimits($from, $to);
		return self::fetchItems($dbIterator);
	}

	public static function getBefore($id, $ts, $count = 20) {
		return self::fetchItems(self::selectBefore($id, $ts, $count));
	}

	public static function getAfter($id, $ts, $count = 20) {
		return self::fetchItems(self::selectAfter($id, $ts, $count));
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

	private static function selectBefore($id, $ts, $count) {
		return new MySQLResultIterator(
			mysql_qw(
				self::SELECT .
					' WHERE `creation_timestamp` != 0
						 AND (
							   DATE(FROM_UNIXTIME(`creation_timestamp`)) < ?
								OR
							   DATE(FROM_UNIXTIME(`creation_timestamp`)) = ?
								AND
								`id` < ?
							 )' .
					self::GROUP .
					' LIMIT ?',
				date('Y-m-d', $ts), date('Y-m-d', $ts), $id, $count
			)
		);
	}

	private static function selectAfter($id, $ts, $count) {
		return new MySQLResultIterator(
			mysql_qw(
				self::SELECT .
					' WHERE `creation_timestamp` != 0 AND (DATE(FROM_UNIXTIME(`creation_timestamp`)) > ? OR DATE(FROM_UNIXTIME(`creation_timestamp`)) = ? AND `id` > ?)' .
					self::GROUP .
					' LIMIT ?',
				date('Y-m-d', $ts), date('Y-m-d', $ts), $id, $count
			)
		);
	}

	private static function selectNear($ts, $count) {
		return new MySQLResultIterator(
			mysql_qw(
				self::SELECT . ' WHERE `creation_timestamp` != 0 AND `creation_timestamp` <= ? ' . self::GROUP .' LIMIT ?',
				$ts, $count
			)
		);
	}

	private static function fetchItems(DBResultIterator $dbIterator) {
		global $auth;

		$items = array();

		// Caching of needed items
		$ids = array();
		$uids = array();
		while ($dbIterator->valid()) {
			$grouped = $dbIterator->current();
			$ids = array_merge($ids, explode(",", $grouped['ids']));
			$uids[$grouped['uid']] = true;
			$dbIterator->next();
		}
		Item::cache($ids);
		User::cache(array_keys($uids));
		if ($auth->isAuth()) {
			Forum::preloadTopics($auth->uid());
		}

		$dbIterator->rewind();
		while ($dbIterator->valid()) {
			$data = $dbIterator->current();
			$grouped = explode(",", $data['ids']);
			$itemsArray = array();
			foreach ($grouped as $id) {
				$byId = Item::getById($id);
				if ($byId instanceof Photo && $byId->getContentValue() > 0) {
					continue;
				}
				$itemsArray[] = $byId;
			}

			if (count($itemsArray) > 5 && date('Y-m-d', $itemsArray[0]->getTimestamp()) == '2011-04-07' ||
				count($itemsArray) == 1 && $itemsArray[0]->getId() == 1672) {
				// nothing: hack for first site convertations made on the 7th of April
			} elseif (count($itemsArray) > 1) {
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
