DROP TABLE  `p_forum_action`;
DROP TABLE  `p_forum_forum`;
DROP TABLE  `p_forum_last`;
DROP TABLE  `p_forum_message`;
DROP TABLE  `p_forum_part`;
DROP TABLE  `p_forum_topic`;
DROP TABLE  `p_forum_visit`;

CREATE TABLE  `ortemij`.`p_content_tag` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`uid` INT NOT NULL ,
	`value` TEXT NOT NULL
);

CREATE TABLE  `ortemij`.`p_content_tag_target` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`tag_id` INT NOT NULL ,
	`uid` INT NOT NULL ,
	`timestamp` INT NOT NULL ,
	`target_type` ENUM(  'item' ) NOT NULL ,
	`target_id` INT NOT NULL ,
	INDEX (  `target_type` ,  `target_id` )
);

ALTER TABLE  `p_content_tag_target` ADD INDEX (  `tag_id` ) ;

ALTER TABLE  `p_content_item` ADD  `closed` ENUM(  'opened',  'closed' ) NOT NULL ,
ADD  `private` ENUM(  'public',  'private' ) NOT NULL;