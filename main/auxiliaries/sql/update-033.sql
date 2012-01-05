ALTER VIEW  `pv_content_comment_opened` AS
	SELECT  `pv_content_comment` . * ,  `pv_content_item_opened`.`group_id`
	FROM  `pv_content_comment` INNER JOIN  `pv_content_item_opened`
	ON  `pv_content_comment`.`item_id` =  `pv_content_item_opened`.`id`;