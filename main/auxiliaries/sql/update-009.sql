CREATE TABLE  `p_man_cup_table` (
 `id` INT( 11 ) AUTO_INCREMENT PRIMARY KEY ,
 `pmid` INT( 11 ) ,
 `cup_id` INT( 11 ) ,
 `win5` INT( 11 ) DEFAULT 0,
 `win6` INT( 11 ) DEFAULT 0,
 `winb` INT( 11 ) DEFAULT 0,
 `lose5` INT( 11 ) DEFAULT 0,
 `lose6` INT( 11 ) DEFAULT 0,
 `loseb` INT( 11 ) DEFAULT 0,
 FOREIGN KEY (  `cup_id` ) REFERENCES  `p_cup`.`id` ,
 FOREIGN KEY (  `pmid` ) REFERENCES  `p_man`.`id`
);

ALTER TABLE  `p_man_cup_table` ADD  `games` INT NOT NULL AFTER  `cup_id` ,
ADD  `points` INT NOT NULL AFTER  `games` ;