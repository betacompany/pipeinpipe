<?php

require_once dirname(__FILE__) . '/../../includes/mysql.php';

require_once dirname(__FILE__) . '/MySQLResultIterator.php';

/**
 * @author ortemij
 */
class GroupDBClient {

	public static function getById($id) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `pv_content_group` WHERE `id`=?', $id
			)
		);
	}

	public static function getRootsByType($type, $descendive = false) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `pv_content_group` WHERE `parent_group_id`=0 AND `type`=? ORDER BY `id` ' .
				($descendive ? 'DESC' : 'ASC'),
				$type
			)
		);
	}

	public static function getByParentGroupId($groupId) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `pv_content_group` WHERE `parent_group_id`=? ORDER BY `id` ASC',
				$groupId
			)
		);
	}

	public static function countByParentGroupId($groupId) {
		return intval(
			mysql_result(
				mysql_qw('SELECT COUNT(*) FROM `pv_content_group` WHERE `parent_group_id`=?', $groupId),
				0, 0
			)
		);
	}

	public static function countByType($type) {
		return intval(
			mysql_result(
				mysql_qw('SELECT COUNT(*) FROM `pv_content_group` WHERE `type`=?', $type),
				0, 0
			)
		);
	}

	public static function insert($type, $parentGroupId, $title, $contentSource, $contentParsed) {
		mysql_qw('INSERT INTO
						`p_content_group`
					SET
						`type`=?,
						`parent_group_id`=?,
						`title`=?,
						`content_source`=?,
						`content_parsed`=?',
				$type, $parentGroupId, $title, $contentSource, $contentParsed);

		return mysql_insert_id();
	}

	public function remove($groupId) {
		return (boolean) mysql_qw(
			'UPDATE `p_content_group` SET `removed`=1 WHERE `id`=?', $groupId
		);
	}

	public function getAllByType($type) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `pv_content_group` WHERE `type`=?', $type
			)
		);
	}

	/**
	 * (`group_id`, `count`)
	 */
	public static function getCommentCounts() {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT `group_id`, COUNT(*) AS `count`
					FROM `pv_content_item` INNER JOIN `pv_content_comment`
					ON `pv_content_item`.`id`=`pv_content_comment`.`item_id`
				GROUP BY `group_id`'
			)
		);
	}

	public static function getNewItemsCountFor($uid) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT `group_id`, COUNT(*) AS `count` FROM (

					`pv_content_item`

					LEFT JOIN

					(
						SELECT MAX(`timestamp`) AS `timestamp`, `target_id` FROM (
							# last group view for specified content item
							(
								SELECT `v`.`id` AS `id`, `g`.`target_id` AS `target_id`, `v`.`timestamp` AS `timestamp` FROM (

									(
										SELECT `id`, `target_id`, `timestamp` FROM
											`p_content_view`
										WHERE
											`target_type`=\'group\' AND `uid`=?
									) AS `v`

									INNER JOIN

									(
										SELECT `group_id`, `target_id` FROM (
											(
												SELECT `target_id`, `timestamp` FROM
													`p_content_view`
												WHERE
													`target_type`=\'item\' AND `uid`=?
											) AS `t`

											INNER JOIN

											(	SELECT `id`, `group_id` FROM `p_content_item`	) AS `i`

											ON

											`t`.`target_id`=`i`.`id`
										)
									) AS `g`

									ON

									`v`.`target_id`=`g`.`group_id`
								)
							)

							UNION

							# last item view
							(
								SELECT `id`, `target_id`, `timestamp` FROM
									`p_content_view`
								WHERE
									`target_type`=\'item\' AND `uid`=?
							)
						) AS `result`

						GROUP BY `target_id`
					) AS `results`

					ON
						`pv_content_item`.`id`=`results`.`target_id`
				)

					WHERE
						`timestamp` IS NULL
						OR `timestamp`<`creation_timestamp`
					GROUP BY `group_id`',
					$uid, $uid, $uid
			)
		);
	}
}
?>
