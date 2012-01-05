ALTER TABLE  `p_content_item` CHANGE  `timestamp`  `creation_timestamp` INT( 11 ) NOT NULL;
ALTER TABLE  `p_content_item` ADD  `last_comment_timestamp` INT NOT NULL AFTER  `creation_timestamp`;
