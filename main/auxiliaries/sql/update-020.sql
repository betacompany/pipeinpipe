CREATE TABLE  `ortemij`.`p_forum_last` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`target_id` INT NOT NULL ,
	`target_type` ENUM(  'forum',  'part',  'topic' ) NOT NULL ,
	`timestamp` INT NOT NULL ,
	INDEX (  `target_id` ,  `target_type` )
);
