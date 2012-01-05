ALTER TABLE  `p_content_item`
	ADD  `content_title` TINYTEXT NOT NULL AFTER  `last_comment_timestamp`;

ALTER VIEW `pv_content_item`
	AS SELECT * FROM `p_content_item` WHERE `removed`=0;

