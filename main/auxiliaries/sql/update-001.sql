CREATE DATABASE  `ortemij` DEFAULT CHARACTER SET cp1251 COLLATE cp1251_general_ci;

GRANT ALL PRIVILEGES ON * . * TO  'abracadabra'@'%' IDENTIFIED BY  'zcjcexkty' WITH GRANT OPTION MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 ;

CREATE TABLE  `p_men` (
 `id` INT NOT NULL AUTO_INCREMENT ,
 `name` VARCHAR( 128 ) NOT NULL ,
 `surname` VARCHAR( 128 ) NOT NULL ,
 `gender` ENUM(  'm',  'f' ) NOT NULL ,
 `country` VARCHAR( 128 ) NOT NULL ,
 `city` VARCHAR( 128 ) NOT NULL ,
 `email` VARCHAR( 128 ) NOT NULL ,
 `wiki` TEXT NOT NULL ,
 `iswpr` ENUM(  '0',  '1' ) NOT NULL ,
PRIMARY KEY (  `id` )
);

CREATE TABLE  `p_leagues` (
 `id` INT NOT NULL AUTO_INCREMENT ,
 `name` VARCHAR( 256 ) NOT NULL ,
 `wiki` TEXT NOT NULL ,
PRIMARY KEY (  `id` )
);

CREATE TABLE  `p_competitions` (
 `id` INT NOT NULL AUTO_INCREMENT ,
 `league_id` INT NOT NULL ,
 `name` VARCHAR( 128 ) NOT NULL ,
 `season` VARCHAR( 16 ) NOT NULL ,
 `date` DATE NOT NULL ,
 `coef` INT NOT NULL ,
PRIMARY KEY (  `id` )
);

ALTER TABLE  `p_competitions` ADD  `wiki` TEXT NOT NULL AFTER  `coef` ;

CREATE TABLE  `p_cups` (
 `id` INT NOT NULL AUTO_INCREMENT ,
 `competition_id` INT NOT NULL ,
 `parent_cup_id` INT NOT NULL ,
 `type` ENUM(  'playoff',  'one-lap',  'two-laps' ) NOT NULL ,
PRIMARY KEY (  `id` )
);

CREATE TABLE  `p_men_cups` (
 `id` INT NOT NULL AUTO_INCREMENT ,
 `pmid` INT NOT NULL ,
 `cup_id` INT NOT NULL ,
 `date` DATE NOT NULL ,
 `points` DOUBLE NOT NULL ,
 `place` INT NOT NULL ,
PRIMARY KEY (  `id` )
);

CREATE TABLE  `p_rating` (
 `id` INT NOT NULL AUTO_INCREMENT ,
 `league_id` INT NOT NULL ,
 `pmid` INT NOT NULL ,
 `points` DOUBLE NOT NULL ,
PRIMARY KEY (  `id` )
);

ALTER TABLE  `p_rating` ADD  `date` DATE NOT NULL AFTER  `points` ,
ADD  `rating_place` INT NOT NULL AFTER  `date` ;

CREATE TABLE  `p_games` (
 `id` INT NOT NULL AUTO_INCREMENT ,
 `cup_id` INT NOT NULL ,
 `stage` INT NOT NULL ,
 `tour` INT NOT NULL ,
 `pmid1` INT NOT NULL ,
 `pmid2` INT NOT NULL ,
 `score1` INT NOT NULL ,
 `score2` INT NOT NULL ,
 `timestamp` TIMESTAMP NOT NULL ,
 `is_tech` ENUM(  '0',  '1' ) NOT NULL ,
PRIMARY KEY (  `id` )
);