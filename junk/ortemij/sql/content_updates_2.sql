SELECT MAX(`timestamp`) AS `timestamp` FROM (
	# last group view for specified content item
	( 
		SELECT `v`.`timestamp` FROM (

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
							`target_type`='item' AND `uid`=1 AND `target_id`=307
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
		SELECT `timestamp` FROM 
			`p_content_view`
		WHERE
			`target_type`='item' AND `uid`=1 AND `target_id`=307
	) 
) AS `result`