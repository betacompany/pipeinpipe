CREATE TABLE `p_competition_register` (
`id` INT NOT NULL AUTO_INCREMENT ,
`comp_id` INT NOT NULL ,
`uid` INT NOT NULL ,
`pmid` INT NOT NULL ,
PRIMARY KEY ( `id` ) ,
INDEX ( `comp_id` )
);