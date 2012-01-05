ALTER TABLE  `p_game` CHANGE  `is_tech`  `is_tech` ENUM(  '0', '1',  't',  'f' ) DEFAULT  '0' ;

UPDATE `p_game` SET `is_tech`="t" WHERE  `is_tech`="1" ;

ALTER TABLE  `p_game` CHANGE  `is_tech`  `is_tech` ENUM(  '0', 't',  'f' ) DEFAULT  '0' ;
