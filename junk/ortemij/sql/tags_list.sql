SELECT `tags`.*, COUNT(*) AS `count` FROM (
		SELECT `p_content_tag`.*, `target_id`, `target_type` FROM (
			`p_content_tag` LEFT JOIN `p_content_tag_target`
			ON `p_content_tag`.`id`=`p_content_tag_target`.`tag_id`
		)
	) AS `tags`
INNER JOIN
	`pv_content_item`
ON
	`tags`.`target_id`=`pv_content_item`.`id`
	AND `tags`.`target_type`='item'
WHERE `pv_content_item`.`type`='blog_post'
GROUP BY `tags`.`id`