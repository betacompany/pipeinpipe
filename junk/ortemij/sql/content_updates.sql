SELECT COUNT(*), `type` FROM (
	`p_content_item`
	LEFT JOIN
	(
		SELECT `timestamp` AS `last_view_timestamp`, `target_id` FROM 
		`p_content_view`
		WHERE 
			`p_content_view`.`target_type`='item'
			AND
			`uid`=1
	) AS `t`
	ON
	`p_content_item`.`id`=`t`.`target_id` 
		AND 
			(`t`.`last_view_timestamp`<`p_content_item`.`last_comment_timestamp`
				OR
			`t`.`last_view_timestamp`<`p_content_item`.`creation_timestamp`)
) GROUP BY `type`