ALTER TABLE  `p_content_item` ADD  `content_value` INT NOT NULL AFTER  `content_parsed` , ADD INDEX (  `content_value` );

ALTER TABLE  `p_content_item` CHANGE  `content_value`  `content_value` INT( 11 ) NOT NULL DEFAULT  '0';

