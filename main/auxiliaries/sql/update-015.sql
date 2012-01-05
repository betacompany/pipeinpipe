CREATE TABLE  `ortemij`.`p_user` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 256 ) NOT NULL ,
`surname` VARCHAR( 256 ) NOT NULL
) ENGINE = MYISAM ;

CREATE TABLE  `ortemij`.`p_user_data` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT NOT NULL ,
`key` VARCHAR( 32 ) NOT NULL ,
`value` TEXT NOT NULL ,
INDEX (  `uid` )
) ENGINE = MYISAM ;

ALTER TABLE  `p_user_data` ADD INDEX (  `key` );

CREATE TABLE  `ortemij`.`p_user_permission` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT NOT NULL ,
`status` VARCHAR( 2 ) NOT NULL ,
`target_id` INT NOT NULL
) ENGINE = MYISAM ;

ALTER TABLE  `p_user_permission` ADD INDEX (  `uid` );

ALTER TABLE  `p_user_permission` ADD INDEX (  `status` );

ALTER TABLE  `p_user_permission` ADD INDEX (  `target_id` );