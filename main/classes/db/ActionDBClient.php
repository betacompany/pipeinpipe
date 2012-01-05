<?php

require_once dirname(__FILE__) . '/../../includes/mysql.php';

require_once dirname(__FILE__) . '/MySQLResultIterator.php';

/**
 * @author ortemij
 */
class ActionDBClient {

	public static function getById($id) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `p_content_action` WHERE `id`=?', $id
			)
		);
	}

	public static function getByTarget($targetType, $targetId) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `p_content_action` WHERE `target_type`=? and `target_id`=?',
				$targetType, $targetId
			)
		);
	}

	public static function insert($type, $targetType, $targetId, $uid, $timestamp, $value) {
		mysql_qw(
			'INSERT INTO `p_content_action` SET `type`=?, `target_type`=?, `target_id`=?, `uid`=?, `timestamp`=?, `value`=?',
			$type, $targetType, $targetId, $uid, $timestamp, $value
		);

		return mysql_insert_id();
	}

	public static function countActive($type, $uid = 0) {
		return intval(mysql_result(
			mysql_qw(
				'SELECT COUNT(*) FROM `p_content_action` WHERE `type`=?' . ($uid ? ' AND `uid`='.intval($uid) : ''),
				$type
			), 0, 0
		));
	}
}
?>
