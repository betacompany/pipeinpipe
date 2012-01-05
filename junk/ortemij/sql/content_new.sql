SELECT * FROM (

	`p_content_item`

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
					`target_type`='item' AND `uid`=1
			)
		) AS `result`

		GROUP BY `target_id`
	) AS `results`

	ON
		`p_content_item`.`id`=`results`.`target_id`
)
	WHERE
		(`timestamp` IS NULL AND `last_comment_timestamp`>0)
		OR
		`timestamp`<`last_comment_timestamp`

