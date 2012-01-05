			SELECT * FROM (

				(
					SELECT
							`pv_content_comment_opened`.`id`,
							`pv_content_comment_opened`.`item_id` AS `topic_id`,
							`pv_content_comment_opened`.`timestamp` AS `message_timestamp`
					FROM
						`pv_content_comment_opened`
					WHERE
						`type`='forum_message'

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
										`target_type`='group' AND `uid`=1
								) AS `v`

								INNER JOIN

								(
									SELECT `group_id`, `target_id` FROM (
										(
											SELECT `target_id`, `timestamp` FROM
												`p_content_view`
											WHERE
												`target_type`='item' AND `uid`=1
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
								`target_type`='item' AND `uid`=1
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