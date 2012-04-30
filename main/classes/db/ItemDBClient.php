<?php

require_once dirname(__FILE__) . '/../../includes/mysql.php';

require_once dirname(__FILE__) . '/MySQLResultIterator.php';

require_once dirname(__FILE__) . '/../content/Item.php';

/**
 * @author ortemij
 */
class ItemDBClient {

	public static function getById($id) {
		return new MySQLResultIterator(
			mysql_qw('SELECT * FROM `p_content_item` WHERE `id`=? AND `removed`=0', $id)
		);
	}

	public static function getByIds($ids) {
		$in = array();
		foreach ($ids as $id) {
			$in[] = intval($id);
		}
		return new MySQLResultIterator(
			mysql_qw('SELECT * FROM `p_content_item` WHERE `id` IN ('.implode(",", $in).')')
		);
	}

	public static function getByGroupId($groupId, $from, $limit, $descendive = false, $orderBy = 'id') {
		$req = null;
		if ($limit == 0) {
			$req = mysql_qw(
				'SELECT * FROM `pv_content_item` WHERE `group_id`=? ORDER BY '.
					'`' . $orderBy . '` ' . ($descendive ? 'DESC' : 'ASC'),
				$groupId
			);
		} else {
			$req = mysql_qw(
				'SELECT * FROM `pv_content_item` WHERE `group_id`=? ORDER BY '.
					'`' . $orderBy . '` ' . ($descendive ? 'DESC' : 'ASC') . ' LIMIT ?, ?',
				$groupId, intval($from), intval($limit)
			);
		}

		return new MySQLResultIterator($req);
	}

	public static function getAllByType($type, $from = 0, $limit = 0, $descendive = false, $orderByCreation = false) {
		if ($limit == 0) {
			return new MySQLResultIterator(
				mysql_qw('SELECT * FROM `pv_content_item` WHERE `type`=? ORDER BY '.
					(!$orderByCreation ? '`id`' : '`creation_timestamp`').' ' .
					($descendive ? 'DESC' : 'ASC'), $type)
			);
		}

		return new MySQLResultIterator(
			mysql_qw('SELECT * FROM `pv_content_item` WHERE `type`=? ORDER BY '.
				(!$orderByCreation ? '`id`' : '`creation_timestamp`').' ' .
				($descendive ? 'DESC' : 'ASC') . ' LIMIT ?, ?',
					$type, intval($from), intval($limit))
		);
	}

	public static function getOpenedByType($type) {
		return new MySQLResultIterator(
			mysql_qw('SELECT * FROM `pv_content_item_opened` WHERE `type`=?', $type)
		);
	}

	public static function countByGroupId($groupId) {
		return intval(mysql_result(
			mysql_qw(
				'SELECT COUNT(*) FROM `pv_content_item` WHERE `group_id`=?',
				$groupId
			), 0, 0
		));
	}

	public static function countByType($type, $uid = 0) {
		return intval(mysql_result(
			mysql_qw(
				'SELECT COUNT(*) FROM `pv_content_item` WHERE `type`=?' . ($uid ? ' AND `uid`='.intval($uid) : ''),
				$type
			), 0, 0
		));
	}

	/**
	 * Updates data in DB and returns the number of affected rows
	 * @param Item $item
	 * @return integer
	 */
	public static function update(Item $item) {
		mysql_qw(
			'UPDATE `p_content_item` SET 
				`type`=?,
				`group_id`=?,
				`uid`=?,
				`creation_timestamp`=?,
				`last_comment_timestamp`=?,
				`content_title`=?,
				`content_source`=?,
				`content_parsed`=?,
				`content_value`=?
			WHERE `id`=?',

			$item->getType(),
			$item->getGroupId(),
			$item->getUID(),
			$item->getCreationTimestamp(),
			$item->getLastCommentTimestamp(),
			$item->getContentTitle(),
			$item->getContentSource(),
			$item->getContentParsed(),
			$item->getContentValue(),
			$item->getId()
		);

		return mysql_affected_rows();
	}

	public static function insert($type, $groupId, $uid, $creationTimestamp, $contentSource, $contentParsed, $contentTitle = '', $contentValue) {
		mysql_qw(
			'INSERT INTO `p_content_item` SET   `type`=?,
												`group_id`=?,
												`uid`=?,
												`creation_timestamp`=?,
												`last_comment_timestamp`=0,
												`content_source`=?,
												`content_parsed`=?,
												`content_title`=?,
												`content_value`=?',
			$type, $groupId, $uid, $creationTimestamp, $contentSource, $contentParsed, $contentTitle, $contentValue
		);

		return mysql_insert_id();
	}

	public static function getAll($limit = 0, $descendive = false) {
		if ($limit == 0) return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `pv_content_item` ORDER BY `id` '.($descendive ? 'DESC' : 'ASC')
			)
		);

		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `pv_content_item` ORDER BY `id` '.($descendive ? 'DESC' : 'ASC').' LIMIT ?', intval($limit)
			)
		);
	}

	public static function getAllByRating($type, $limit, $groupId = 0) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM (

					(
						SELECT * FROM 
							`pv_content_item`
						WHERE
							`type`=?'.($groupId ? " AND `group_id`=".intval($groupId) : "").'
					) `r1`

					LEFT JOIN

					(
						SELECT `target_id` AS `item_id`, AVG(`value`) AS `avg`, COUNT(`value`) AS `cnt` FROM
							`p_content_action`
						WHERE
							`type`=\'evaluation\' AND `target_type`=\'item\'
						GROUP BY `item_id`
					) `r2`

					ON `r2`.`item_id`=`r1`.`id`

				) ORDER BY `avg` DESC, `cnt` DESC LIMIT ?',
					$type, $limit
			)
		);
	}

	public static function remove($itemId) {
		return (boolean) mysql_qw(
			'UPDATE `p_content_item` SET `removed`=1 WHERE `id`=?', $itemId
		);
	}

	public static function getAllByTypeAndTag($type, $tagId, $from, $limit, $descendive = false, $orderByCreation = false) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT `pv_content_item`.* FROM
					`pv_content_item` INNER JOIN `p_content_tag_target`
					ON `pv_content_item`.`id`=`p_content_tag_target`.`target_id`
				WHERE
					`p_content_tag_target`.`tag_id`=? AND
					`p_content_tag_target`.`target_type`=\'item\' AND
					`pv_content_item`.`type`=? ORDER BY '.
				(!$orderByCreation ? '`id`' : '`creation_timestamp`').' '
				. ($descendive ? 'DESC' : 'ASC')
				. ($limit == 0 ? '' : ' LIMIT ' . intval($from) . ', ' . intval($limit))
				, $tagId, $type
			)
		);
	}

	public static function getByPeriod($begin, $end, $type = '') {
		if (!$type) return new MySQLResultIterator(mysql_qw(
			'SELECT * FROM `pv_content_item` 
			WHERE `creation_timestamp`>=? AND `creation_timestamp`<=?
			ORDER BY `content_value` ASC',
			$begin, $end
		));

		return new MySQLResultIterator(mysql_qw(
			'SELECT * FROM `pv_content_item` 
			WHERE `type`=? AND `creation_timestamp`>=? AND `creation_timestamp`<=?
			ORDER BY `content_value` ASC',
			$type, $begin, $end
		));
	}

	public static function getByPeriodValues($begin, $end, $type = '') {
		if (!$type) return new MySQLResultIterator(mysql_qw(
			'SELECT * FROM `pv_content_item`
			WHERE `content_value`>=? AND `content_value`<=?
			ORDER BY `content_value` ASC',
			$begin, $end
		));

		return new MySQLResultIterator(mysql_qw(
			'SELECT * FROM `pv_content_item`
			WHERE `type`=? AND `content_value`>=? AND `content_value`<=?
			ORDER BY `content_value` ASC',
			$type, $begin, $end
		));
	}

	public static function getDates() {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT `time`, COUNT(*) AS `count` FROM
					(SELECT DATE(FROM_UNIXTIME(`creation_timestamp`)) AS `time`, `id`
					FROM `pv_content_item` WHERE `type`!=\'event\'
						UNION
					SELECT DATE(FROM_UNIXTIME(`content_value`)) AS `time`, `id`
					FROM `pv_content_item` WHERE `type`=\'event\') AS `r`
				GROUP BY `time`'
			)
		);
	}

	public static function getByTypeAndContentSource($type, $src) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `pv_content_item` WHERE `type`=? AND `content_source`=?',
				$type, $src
			)
		);
	}
}
?>
