					SELECT MAX(`timestamp`) AS `timestamp`, `target_id` FROM (
						# last group view for specified content item
						(
							SELECT `v`.`target_id`, `v`.`timestamp` FROM (

								(
									SELECT `target_id`, `timestamp` FROM
										`p_content_view`
									WHERE
										`target_type`='group' AND `uid`=1
								) AS `v`

								INNER JOIN

								(
									SELECT * FROM (
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
							SELECT `target_id`, `timestamp` FROM
								`p_content_view`
							WHERE
								`target_type`='item' AND `uid`=1
						)
					) AS `result`
					GROUP BY `target_id`