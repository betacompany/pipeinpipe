CREATE TABLE `p_user_favourite` (
`id` INT NOT NULL AUTO_INCREMENT ,
`uid` INT NOT NULL ,
`target` VARCHAR( 1024 ) NOT NULL ,
`title` VARCHAR( 1024 ) NOT NULL ,
PRIMARY KEY (  `id` ) ,
INDEX (  `target` )
) ENGINE = MYISAM ;