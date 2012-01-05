USE `ortemij`;

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

ALTER VIEW  `pv_content_comment_opened` AS
	SELECT  `pv_content_comment` . * ,  `pv_content_item_opened`.`group_id`
	FROM  `pv_content_comment` INNER JOIN  `pv_content_item_opened`
	ON  `pv_content_comment`.`item_id` =  `pv_content_item_opened`.`id`;

CREATE VIEW `pv_content_group` AS SELECT * FROM `p_content_group` WHERE `removed`=0;

ALTER TABLE  `p_competition`
	ADD  `status` ENUM(  'running',  'finished',  'registering',  'disabled' )
	NOT NULL DEFAULT  'disabled';

ALTER TABLE  `p_competition`
	DROP  `season` ;

UPDATE `p_competition` SET `status`='finished';

ALTER TABLE `p_cup`
	DROP `status`;

ALTER TABLE  `p_cup`
	ADD  `multiplier` DOUBLE NOT NULL DEFAULT  '-1';

ALTER TABLE  `p_content_item`
	ADD  `content_title` TINYTEXT NOT NULL AFTER  `last_comment_timestamp`;

ALTER VIEW `pv_content_item`
	AS SELECT * FROM `p_content_item` WHERE `removed`=0;

UPDATE  `ortemij`.`p_cup` SET  `type` =  'playoff' WHERE  `p_cup`.`id` =11 LIMIT 1 ;

CREATE TABLE `p_competition_register` (
`id` INT NOT NULL AUTO_INCREMENT ,
`comp_id` INT NOT NULL ,
`uid` INT NOT NULL ,
`pmid` INT NOT NULL ,
PRIMARY KEY ( `id` ) ,
INDEX ( `comp_id` )
);

ALTER TABLE  `p_content_item`
CHANGE  `type`
`type` ENUM(  'blog_post',  'forum_topic',  'photo',  'video',  'interview_question',  'event' )
CHARACTER SET cp1251 COLLATE cp1251_general_ci NOT NULL;

