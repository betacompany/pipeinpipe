CREATE VIEW `pv_content_item` AS SELECT * FROM `p_content_item` WHERE `removed`=0;
CREATE VIEW `pv_content_item_opened` AS SELECT * FROM `p_content_item` WHERE `removed`=0 AND `closed`='opened';

CREATE VIEW `pv_content_comment` AS SELECT * FROM `p_content_comment` WHERE `removed`=0;
CREATE VIEW `pv_content_comment_opened` AS
	SELECT `pv_content_comment`.* FROM
		`pv_content_comment`
		INNER JOIN
		`pv_content_item_opened`
		ON
		`pv_content_comment`.`item_id`=`pv_content_item_opened`.`id`;