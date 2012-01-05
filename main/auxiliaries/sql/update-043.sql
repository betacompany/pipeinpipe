ALTER TABLE  `p_game` CHANGE  `is_tech`  `is_tech` ENUM(  '0',  't',  'f',  'd' ) DEFAULT  '0;
UPDATE  `p_game` SET  `is_tech`='d' WHERE  `score1`=`score2`;
