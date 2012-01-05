<?php
/**
 * Description of ContentStatsDBClient
 *
 * @author Nikita
 */

require_once dirname(__FILE__) . '/MySQLResultIterator.php';

class ContentStatsDBClient {

	/**
	 *
	 * @param string $actionType Action::AGREE or Action::ROMAN.
	 * @param string $commentType Comment::BASIC_COMMENT or Comment::FORUM_MESSAGE.
	 * @param int $uid Zero by default. If zero, then the method returns Iterator over a sorted table.
	 * @return DBResultIterator.
	 */
	public static function countPassiveActionsOnComments($actionType, $commentType, $uid = 0) {
		$query = 'SELECT * FROM (
					SELECT `uid`, COUNT(*) AS `value`
					FROM
						(SELECT `target_id`
						FROM `p_content_action`
						WHERE `target_type` = \'comment\' AND `type` = ?)
						AS `nt`
						INNER JOIN
						(SELECT `id`, `uid`
						FROM `pv_content_comment`
						WHERE `type` = ?)
						AS `nt2`
						ON `nt2`.`id` = `nt`.`target_id`
					GROUP BY `uid`
					ORDER BY `value` DESC
					) AS `nt3`';
		if ($uid != 0) {
			$query .= ' WHERE `uid` = ' . intval($uid);
		}
		return new MySQLResultIterator(mysql_qw($query, $actionType, $commentType));
	}

	/**
	 *
	 * @param array $itemType Array of item types which can be <b>blog_post, forum_topic, photo, video, interview_question</b>.
	 * @param int $uid Zero by default. If zero, then the method returns Iterator over a sorted table.
	 * @return DBResultIterator
	 */
	public static function countComments($itemType, $uid = 0) {

		$query = 'SELECT *
					FROM (
						SELECT `uid`, COUNT(*) AS `value`
						FROM `pv_content_comment`
							WHERE `item_id` IN (
							SELECT `id`
							FROM `pv_content_item`
							WHERE `type` IN (';
		$i = 0;
		if (!is_array($itemType)) {
			$itemType = array($itemType);
		}
		
		foreach ($itemType as $item) {
			if ($i != 0) {
				$query .= ', ';
			}
			$query .= "'" . $item . "'";
			$i++;
		}
		$query .=')
						)
						GROUP BY `uid`
						ORDER BY `value` DESC
					) AS t1';
		if ($uid != 0) {
			$query .= ' WHERE `uid` = ' . intval($uid);
		}

		return new MySQLResultIterator(mysql_qw($query));
	}

	public static function countCommentsIterator() {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT
					`pv_content_comment`.`uid`, `pv_content_item`.`type`, COUNT(`pv_content_comment`.`id`) AS `count`,
					CONCAT(`pv_content_comment`.`uid`, \'_\', `pv_content_item`.`type`) AS `ut`
					FROM
					`pv_content_comment`, `pv_content_item`
					WHERE
					`pv_content_comment`.`item_id` = `pv_content_item`.`id`
					GROUP BY `ut`'
			)
		);
	}

	public static function countMessagesIterator() {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT
						`pv_content_comment`.`uid`, COUNT(`pv_content_comment`.`id`) AS `count`
					FROM
						`pv_content_comment`
					WHERE
						`pv_content_comment`.`type` = \'forum_message\'
					GROUP BY `uid`'
			)
		);
	}

	/**
	 *
	 * @param <type> $itemType Array of item types which can be <b>blog_post, forum_topic, photo, video, interview_question</b>
	 * @param <type> $uid Zero by default. If zero, then the method returns Iterator over a sorted table.
	 * @return DBResultIterator.
	 */
	public static function countItems($itemType, $uid = 0) {

		$query =   'SELECT *
					FROM (
						SELECT `uid`, COUNT(*) AS `value`
						FROM `pv_content_item`
						WHERE `type` IN (';
		$i = 0;
		if (!is_array($itemType)) {
			$itemType = array($itemType);
		}

		foreach ($itemType as $item) {
			if ($i++ != 0) {
				$query .= ', ';
			}
			$query .= "'" . $item . "'";
		}

		$query .= ')
						GROUP BY `uid`
						ORDER BY `value` DESC
					) AS t1 ';

		if ($uid != 0) {
			$query .= 'WHERE `uid` = ' . intval($uid);
		}

		return new MySQLResultIterator(mysql_qw($query));
	}

	/**
	 *
	 * @param string $actionType Action::AGREE or Action::ROMAN.
	 * @param string $commentType Comment::BASIC_COMMENT or Comment::FORUM_MESSAGE.
	 * @param int $uid Zero by default. If zero, then the method returns Iterator over a sorted table.
	 * @return DBResultIterator.
	 */
	public static function countActiveActionsOnComments($actionType, $commentType, $uid = 0) {
		$query =   'SELECT *
					FROM (
						SELECT `uid`, COUNT(*) AS `value`
						FROM `p_content_action`
						WHERE `type` = ?
						AND `target_id` IN (
							SELECT `id`
							FROM `pv_content_comment`
							WHERE `type` = ?
						)
						GROUP BY `uid`
						ORDER BY `value` DESC
					) AS t1';
		if ($uid != 0) {
			$query .= 'WHERE `uid` = ' . intval($uid);
		}

		return new MySQLResultIterator(mysql_qw($query, $actionType, $commentType));
	}

	/**
	 *
	 * @param string $actionType Action::AGREE or Action::ROMAN
	 * @param int $subjectId If zero, then selects all the subjects.
	 * @param int $objectId If zero, then selects all the objects.
	 * @return MySQLResultIterator Iterator over a sorted table.
	 */
	public static function countPairActions($actionType, $subjectId, $objectId) {

		$query = 'SELECT * FROM (
					SELECT `p_content_action`.`uid` AS `subject`, `pv_content_comment`.`uid` AS `object`, COUNT(*) AS `value` FROM (
						`p_content_action`
						INNER JOIN
						`pv_content_comment`
						ON `p_content_action`.`target_id` = `pv_content_comment`.`id`
					)
					WHERE `p_content_action`.`type` = ?
					GROUP BY `subject`, `object`
					ORDER BY `value` DESC
				) AS t1';
		if ($subjectId != 0) {
			$query .= ' WHERE `subject` = ' . intval($subjectId);
			if ($objectId != 0) {
				$query .= ' AND `object` = ' . intval($objectId);
			}
		} elseif ($objectId != 0) {
			$query .= ' WHERE `object` = ' . intval($objectId);
		}

		return new MySQLResultIterator(mysql_qw($query, $actionType));
	}
}
?>
