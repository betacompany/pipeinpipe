<?php

require_once dirname(__FILE__) . '/MySQLResultIterator.php';

/**
 * @author ortemij
 */
class ContentViewDBClient {

	const CONTENT_ITEM = 'item';
	const CONTENT_GROUP = 'group';

	/**
	 * Inserts or updates last view information
	 * @param <type> $contentType
	 * @param <type> $contentId
	 * @param <type> $uid
	 * @param <type> $timestamp
	 * @return boolean
	 */
	public static function refresh($contentType, $contentId, $uid, $timestamp) {
		mysql_qw(
			'UPDATE `p_content_view` SET `timestamp`=? WHERE `target_type`=? and `target_id`=? and `uid`=?',
			$timestamp, $contentType, $contentId, $uid
		);

		if (mysql_affected_rows() > 0) {
			return true;
		}

		return (boolean) mysql_qw(
			'INSERT INTO `p_content_view` SET `target_type`=?, `target_id`=?, `uid`=?, `timestamp`=?',
			$contentType, $contentId, $uid, $timestamp
		);
	}

	public static function getByContentAndUser($contentType, $contentId, $uid) {
		if ($contentType == ContentViewDBClient::CONTENT_ITEM) {
			$req = mysql_qw(
				'
					SELECT `timestamp` FROM (
						# last group view for specified content item
						(
							SELECT `id`, `v`.`timestamp` FROM (

								(
									SELECT `target_id`, `timestamp` FROM
										`p_content_view`
									WHERE
										`target_type`=\'group\' AND `uid`=?
								) AS `v`

								INNER JOIN

								(
									SELECT * FROM (
										(
											SELECT `target_id`, `timestamp` FROM
												`p_content_view`
											WHERE
												`target_type`=\'item\' AND `uid`=? AND `target_id`=?
										) AS `t`

										INNER JOIN

										`pv_content_item_opened`

										ON

										`t`.`target_id`=`pv_content_item_opened`.`id`
									)
								) AS `g`

								ON

								`v`.`target_id`=`g`.`group_id`
							)
						)

						UNION

						# last item view
						(
							SELECT `id`, `timestamp` FROM
								`p_content_view`
							WHERE
								`target_type`=\'item\' AND `uid`=? AND `target_id`=?
						)
					) AS `result`
					ORDER BY `timestamp` DESC
				',
				$uid, $uid, $contentId, $uid, $contentId
			);
		} else {
			$req = mysql_qw(
						'SELECT `timestamp` FROM `p_content_view` WHERE `target_type`=? AND `target_id`=? AND `uid`=?',
						$contentType, $contentId, $uid
					);
		}

		if (mysql_num_rows($req) == 0) return 0;

		$result = mysql_result($req, 0, 0);
		return intval($result);
	}

	/**
	 * (`timestamp`, `target_id`)
	 */
	public static function getOpenedItemViewsByUser($uid) {
		return new MySQLResultIterator(
			mysql_qw('
				SELECT MAX(`timestamp`) AS `timestamp`, `target_id` FROM (
						# last group view for specified content item
						(
							SELECT `v`.`target_id`, `v`.`timestamp` FROM (

								(
									SELECT `target_id`, `timestamp` FROM
										`p_content_view`
									WHERE
										`target_type`=\'group\' AND `uid`=?
								) AS `v`

								INNER JOIN

								(
									SELECT * FROM (
										(
											SELECT `target_id`, `timestamp` FROM
												`p_content_view`
											WHERE
												`target_type`=\'item\' AND `uid`=?
										) AS `t`

										INNER JOIN

										`pv_content_item_opened`

										ON

										`t`.`target_id`=`pv_content_item_opened`.`id`
									)
								) AS `g`

								ON

								`v`.`target_id`=`g`.`group_id`
							)
						)

						UNION

						# last item view
						(
							SELECT `target_id`, `timestamp` FROM
								`p_content_view`
							WHERE
								`target_type`=\'item\' AND `uid`=?
						)
					) AS `result`
					GROUP BY `target_id`
			', $uid, $uid, $uid)
		);
	}

	public static function getItemsWithNewCommentsForUser($uid, $from, $limit) {
		$req = '
			SELECT * FROM (

				`pv_content_item_opened`

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

										`pv_content_item`

										ON

										`t`.`target_id`=`pv_content_item`.`id`
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
					`pv_content_item_opened`.`id`=`results`.`target_id`
			)
				WHERE
					(`timestamp` IS NULL AND `last_comment_timestamp`>0)
					OR
					`timestamp`<`last_comment_timestamp`


				ORDER BY `last_comment_timestamp` DESC
				LIMIT ?, ?
		';

		return new MySQLResultIterator(mysql_qw($req, $uid, $uid, $uid, intval($from), intval($limit)));
	}

	public static function countForumNewMessages($uid) {
		$req = '
			SELECT COUNT(*) AS `count` FROM (

				(
					SELECT
							`pv_content_comment_opened`.`id`,
							`pv_content_comment_opened`.`item_id` AS `topic_id`,
							`pv_content_comment_opened`.`timestamp` AS `message_timestamp`
					FROM
						`pv_content_comment_opened`
					WHERE
						`type`=\'forum_message\'
						
				) AS `p_content_comment`

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

										`pv_content_item_opened`

										ON

										`t`.`target_id`=`pv_content_item_opened`.`id`
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
					`p_content_comment`.`topic_id`=`results`.`target_id`
			)
				WHERE
					`timestamp` IS NULL OR
					`timestamp`<`message_timestamp`

		';

		$query = mysql_qw($req, $uid, $uid, $uid);

		// if (mysql_num_rows($query) == 0) return 0;
		return mysql_result($query, 0, 'count');
	}
	
}
?>
