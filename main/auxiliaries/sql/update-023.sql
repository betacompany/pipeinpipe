CREATE TABLE  `ortemij`.`p_content_item` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`type` ENUM(  'blog_post',  'forum_topic',  'photo',  'video',  'interview_question' ) NOT NULL ,
	`group_id` INT NOT NULL ,
	`uid` INT NOT NULL ,
	`timestamp` INT NOT NULL ,
	`content_source` TEXT NOT NULL ,
	`content_parsed` TEXT NOT NULL ,
	INDEX (  `type` )
) ENGINE = MYISAM ;

CREATE TABLE  `ortemij`.`p_content_group` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`type` ENUM(  'photo_album',  'video_album',  'forum_part',  'forum_forum',  'interview',  'blog' ) NOT NULL ,
	`parent_group_id` INT NOT NULL ,
	`title` TEXT NOT NULL ,
	INDEX (  `type` )
) ENGINE = MYISAM ;

CREATE TABLE  `ortemij`.`p_content_comment` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`type` ENUM(  'basic_comment',  'forum_message' ) NOT NULL ,
	`item_id` INT NOT NULL ,
	`uid` INT NOT NULL ,
	`timestamp` INT NOT NULL ,
	`content_source` TEXT NOT NULL ,
	`content_parsed` TEXT NOT NULL ,
	INDEX (  `type` ,  `item_id` )
) ENGINE = MYISAM ;

CREATE TABLE  `ortemij`.`p_content_connection` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`content_type` ENUM(  'item',  'group' ) NOT NULL ,
	`content_id` INT NOT NULL ,
	`holder_type` ENUM(  'competition',  'league',  'user' ) NOT NULL ,
	`holder_id` INT NOT NULL
) ENGINE = MYISAM ;

ALTER TABLE  `p_content_connection` ADD INDEX (  `content_type` ,  `content_id` ) ;
ALTER TABLE  `p_content_connection` ADD INDEX (  `holder_type` ,  `holder_id` ) ;

CREATE TABLE  `ortemij`.`p_content_action` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`target_type` ENUM(  'item',  'comment' ) NOT NULL ,
	`target_id` INT NOT NULL ,
	`type` ENUM(  'agree',  'roman',  'evaluation' ) NOT NULL ,
	`uid` INT NOT NULL ,
	`timestamp` INT NOT NULL ,
	`value` INT NOT NULL ,
	INDEX (  `target_type` ,  `target_id` )
) ENGINE = MYISAM ;

CREATE TABLE  `ortemij`.`p_content_view` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`target_type` ENUM(  'item',  'group' ) NOT NULL ,
	`target_id` INT NOT NULL ,
	`uid` INT NOT NULL ,
	`timestamp` INT NOT NULL ,
	INDEX (  `target_type` ,  `target_id` )
) ENGINE = MYISAM ;
