UPDATE  `ortemij`.`p_cup` SET  `type` =  'playoff' WHERE  `p_cup`.`id` =11 LIMIT 1 ;
UPDATE  `ortemij`.`p_cup` SET  `type` =  'playoff' WHERE  `p_cup`.`id` =23 LIMIT 1 ;
UPDATE  `ortemij`.`p_cup` SET  `type` =  'playoff' WHERE  `p_cup`.`id` =26 LIMIT 1 ;
UPDATE  `ortemij`.`p_cup` SET  `competition_id` =  '4' WHERE  `p_cup`.`id` =4 LIMIT 1 ;
UPDATE  `ortemij`.`p_cup` SET  `competition_id` =  '4' WHERE  `p_cup`.`id` =5 LIMIT 1 ;
UPDATE  `ortemij`.`p_cup` SET  `type` =  'playoff' WHERE  `p_cup`.`id` =6 LIMIT 1 ;

INSERT INTO  `ortemij`.`p_cup` (
`id` ,
`competition_id` ,
`parent_cup_id` ,
`name` ,
`type` ,
`status`
)
VALUES (
59 ,  '1',  '0',  '',  'playoff',  'finished'
), (
60 ,  '3',  '0',  '',  'playoff',  'finished'
);

UPDATE  `ortemij`.`p_cup` SET  `parent_cup_id` =  '59' WHERE  `p_cup`.`id` =1 LIMIT 1 ;
UPDATE  `ortemij`.`p_cup` SET  `parent_cup_id` =  '60' WHERE  `p_cup`.`id` =3 LIMIT 1 ;

UPDATE  `ortemij`.`p_game` SET  `cup_id` =  '59' WHERE  `p_game`.`id` =1 LIMIT 1 ;
UPDATE  `ortemij`.`p_game` SET  `cup_id` =  '59' WHERE  `p_game`.`id` =2 LIMIT 1 ;
UPDATE  `ortemij`.`p_game` SET  `cup_id` =  '59' WHERE  `p_game`.`id` =3 LIMIT 1 ;
UPDATE  `ortemij`.`p_game` SET  `cup_id` =  '59' WHERE  `p_game`.`id` =4 LIMIT 1 ;

UPDATE  `ortemij`.`p_game` SET  `cup_id` =  '60' WHERE  `p_game`.`id` =119 LIMIT 1 ;
UPDATE  `ortemij`.`p_game` SET  `cup_id` =  '60' WHERE  `p_game`.`id` =120 LIMIT 1 ;
UPDATE  `ortemij`.`p_game` SET  `cup_id` =  '60' WHERE  `p_game`.`id` =122 LIMIT 1 ;