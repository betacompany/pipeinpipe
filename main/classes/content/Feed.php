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

	public static function get($from = 0, $to = 19) {
		$items = array();
		$dbIterator =
			new MySQLResultIterator(
				mysql_qw(
					'SELECT
						GROUP_CONCAT(`id`) as `ids`,
						DATE(FROM_UNIXTIME(`creation_timestamp`)) as `d`,
					    IF(`uid`=0, UUID(), `uid`) as `uid`
					FROM
						`p_content_item`
						GROUP BY `type`, `uid`, `d`
						ORDER BY `id` DESC, `creation_timestamp` DESC
						LIMIT ?, ?',
					$from, $to - $from + 1
				)
			);
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
				$items[] = new ItemsContainer($itemsArray);
			} elseif (count($itemsArray) == 1) {
				$items[] = $itemsArray[0];
			}
			$dbIterator->next();
		}
		return $items;
	}
}
