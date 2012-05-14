<?php

require_once dirname(__FILE__) . '/../../includes/mysql.php';

require_once dirname(__FILE__) . '/MySQLResultIterator.php';

/**
 * @author ortemij
 */
class CommentDBClient {

	public static function getById($id) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `pv_content_comment` WHERE `id`=?', $id
			)
		);
	}

	public static function getByItem($itemId, $from, $limit) {
		if ($from == 0 && $limit == 0) {
			return new MySQLResultIterator(
				mysql_qw(
					'SELECT * FROM `pv_content_comment` WHERE `item_id`=? ORDER BY `id` ASC',
					$itemId
				)
			);
		}

		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `pv_content_comment` WHERE `item_id`=? ORDER BY `id` ASC LIMIT ?, ?',
				$itemId, intval($from), intval($limit)
			)
		);
	}

	public static function countByItem($itemId) {
		return intval(mysql_result(
			mysql_qw('SELECT COUNT(*) FROM `pv_content_comment` WHERE `item_id`=?', $itemId),
			0, 0
		));
	}

	public static function countByItemLater($itemId, $timestamp) {
		return intval(mysql_result(
			mysql_qw('SELECT COUNT(*) FROM `pv_content_comment` WHERE `item_id`=? AND `timestamp`>?', $itemId, $timestamp),
			0, 0
		));
	}

	public static function countByGroup($groupId) {
		return intval(mysql_result(
			mysql_qw('SELECT COUNT(*) FROM (
						`pv_content_comment`
						INNER JOIN
						`pv_content_item`
						ON
						`pv_content_comment`.`item_id`=`pv_content_item`.`id`
					) WHERE `group_id`=?', $groupId),
			0, 0
		));
	}

	public static function countByType($type, $uid = 0) {
		return intval(mysql_result(
			mysql_qw(
				'SELECT COUNT(*) FROM `pv_content_comment` WHERE `type`=?' . ($uid ? ' AND `uid`='.intval($uid) : ''),
				$type
			), 0, 0
		));
	}

	public static function insert($type, $itemId, $uid, $timestamp, $contentSource, $contentParsed) {
		mysql_qw(
			'INSERT INTO `p_content_comment` SET `type`=?, `item_id`=?, `uid`=?, `timestamp`=?, `content_source`=?, `content_parsed`=?',
			$type, $itemId, $uid, $timestamp, $contentSource, $contentParsed
		);
		
		return mysql_insert_id();
	}

	public static function update(Comment $comment) {
		mysql_qw(
			'UPDATE `p_content_comment` SET `type`=?,
											`item_id`=?,
											`uid`=?,
											`timestamp`=?,
											`content_source`=?,
											`content_parsed`=?
										WHERE `id`=?',
			$comment->getType(),
			$comment->getItemId(),
			$comment->getUID(),
			$comment->getTimestamp(),
			$comment->getContentSource(),
			$comment->getContentParsed(),
			$comment->getId()
		);
	}

	public static function getAll($descendive = false) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `p_content_comment` WHERE `removed`=0 ORDER BY `id` ' . ($descendive ? 'DESC' : 'ASC')
			)
		);
	}

	public static function remove($commentId) {
		return (boolean) mysql_qw(
			'UPDATE `p_content_comment` SET `removed`=1 WHERE `id`=?', $commentId
		);
	}

	public static function getCountsForItems($ids) {
		$in = array();
		foreach ($ids as $id) {
			$in[] = intval($id);
		}
		return new MySQLResultIterator(
			mysql_qw('SELECT
						COUNT(*) AS `count`, `p_content_item`.`id` AS `iid`
					  FROM
					    `p_content_item` JOIN `pv_content_comment`
					    ON `p_content_item`.`id`=`pv_content_comment`.`item_id`
					  WHERE
					    `p_content_item`.`id` IN ('.implode(",", $in).')
					  GROUP BY `iid`')
		);
	}

	public static function getByItemType($itemTypes, $from, $limit, $descendive) {
		$request = 'SELECT `pv_content_comment`.* AS `item_type` FROM
			`pv_content_comment`
			LEFT JOIN
			`pv_content_item`
			ON
			`pv_content_comment`.`item_id`=`pv_content_item`.`id`
			WHERE ';

		if (is_array($itemTypes)) {
			$request .= '`pv_content_item`.`type` IN (';
			foreach ($itemTypes as $i => $itemType) {
				if (Item::isCorrectType($itemType)) {
					$request .= '\''.$itemType.'\'';
				}
				if ($i < count($itemTypes) - 1) {
					$request .= ', ';
				}
			}
			$request .= ') ';
		} else {
			if (Item::isCorrectType($itemTypes)) {
				$request .= '`pv_content_item`.`type`=\''.$itemTypes.'\'';
			}
		}

		$request .= 'ORDER BY `id` '.($descendive ? 'DESC ' : 'ASC ');

		if ($limit != 0) {
			$request .= ' LIMIT '.intval($from).', '.intval($limit);
		}

		return new MySQLResultIterator(mysql_qw($request));
	}
}
?>
