CREATE TABLE  `ortemij`.`p_forum_message` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`topic_id` INT NOT NULL ,
	`uid` INT NOT NULL ,
	`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	`text` TEXT NOT NULL ,
	`html` TEXT NOT NULL ,
	INDEX (  `topic_id` )
);

CREATE TABLE  `ortemij`.`p_forum_topic` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`part_id` INT NOT NULL ,
	`prev_topic_id` INT NOT NULL ,
	`uid` INT NOT NULL ,
	`title` TINYTEXT NOT NULL ,
	`closed` BOOL NOT NULL DEFAULT  '0',
	INDEX (  `part_id` )
);

CREATE TABLE  `ortemij`.`p_forum_part` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`forum_id` INT NOT NULL ,
	`title` TINYTEXT NOT NULL ,
	`description` TEXT NOT NULL
);

CREATE TABLE  `ortemij`.`p_forum_forum` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`title` TINYTEXT NOT NULL
);

ALTER TABLE  `p_forum_message` CHANGE  `timestamp`  `timestamp` INT NOT NULL;

CREATE TABLE  `ortemij`.`p_forum_action` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`type` ENUM(  'agree',  'roman' ) NOT NULL ,
	`message_id` INT NOT NULL ,
	`uid` INT NOT NULL ,
	`timestamp` INT NOT NULL ,
	`value` INT NOT NULL ,
	INDEX (  `type` ,  `message_id` )
);

CREATE TABLE  `ortemij`.`p_forum_visit` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`uid` INT NOT NULL ,
	`target_type` ENUM(  'forum',  'part',  'topic' ) NOT NULL ,
	`target_id` INT NOT NULL ,
	`timestamp` INT NOT NULL ,
	INDEX (  `target_type` ,  `target_id` )
);