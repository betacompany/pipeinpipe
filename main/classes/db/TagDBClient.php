<?php

require_once dirname(__FILE__) . '/MySQLResultIterator.php';

/**
 * @author ortemij
 */
class TagDBClient {

	public static function selectById($id) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `p_content_tag` WHERE `id`=?', $id
			)
		);
	}

	public static function selectByItem($itemId) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `p_content_tag` WHERE `target_type`=\'item\' AND `target_id`=?', $itemId
			)
		);
	}

	public static function insert($uid, $value) {
		mysql_qw('INSERT INTO `p_content_tag` SET `uid`=?, `value`=?', $uid, $value);
		return mysql_insert_id();
	}

	public static function bind($itemId, $tagId, $uid, $timestamp) {
		return (boolean) mysql_qw(
			'INSERT INTO `p_content_tag_target` SET
				`target_type`=\'item\',
				`target_id`=?,
				`tag_id`=?,
				`timestamp`=?,
				`uid`=?',
				$itemId, $tagId, $timestamp, $uid
		);
	}

	public static function getAll() {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `p_content_tag`'
			)
		);
	}

	public static function getAllByTypeWithCounts($itemType, $descendive = false) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT `tags`.*, COUNT(*) AS `count` FROM (
						SELECT `p_content_tag`.*, `target_id`, `target_type` FROM (
							`p_content_tag` LEFT JOIN `p_content_tag_target`
							ON `p_content_tag`.`id`=`p_content_tag_target`.`tag_id`
						)
					) AS `tags`
				INNER JOIN
					`pv_content_item`
				ON
					`tags`.`target_id`=`pv_content_item`.`id`
					AND `tags`.`target_type`=\'item\'
				WHERE `pv_content_item`.`type`=?
				GROUP BY `tags`.`id` ORDER BY `id` ' . ($descendive ? 'DESC' : 'ASC'),
				$itemType
			)
		);
	}

	public static function getByItemId($itemId) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT `p_content_tag`.* FROM
					`p_content_tag`
					INNER JOIN
					`p_content_tag_target`
					ON `p_content_tag`.`id`=`p_content_tag_target`.`tag_id`
				WHERE 
					`p_content_tag_target`.`target_type`=\'item\' AND
					`p_content_tag_target`.`target_id`=?',
				$itemId
			)
		);
	}

	public static function getItemsByTagId($tagId, $type) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT `p_content_item`.* FROM
					`p_content_item`
					INNER JOIN
					`p_content_tag_target`
					ON `p_content_item`.`id`=`p_content_tag_target`.`target_id`
				WHERE
					`p_content_tag_target`.`tag_id`=? AND `p_content_item`.`type`=?',
				$tagId, $type
			)
		);
	}

	public static function getItemsTaggedByUser($tagId, $uid) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT `p_content_item`.* FROM
					`p_content_item`
					INNER JOIN
					`p_content_tag_target`
					ON `p_content_item`.`id`=`p_content_tag_target`.`target_id`
				WHERE
					`p_content_tag_target`.`tag_id`=? AND `p_content_tag_target`.`uid`=?',
				$tagId, $uid
			)
		);
	}

	public static function removeTagsFor($itemId) {
		mysql_qw('DELETE FROM `p_content_tag_target` WHERE `target_type`=\'item\' AND `target_id`=?', $itemId);
	}

    public static function removeTag($itemId, $tagId) {
        mysql_qw('DELETE FROM `p_content_tag_target` WHERE `target_type`=\'item\' AND `target_id`=? AND tag_id=?', $itemId, $tagId);
    }
}
?>
