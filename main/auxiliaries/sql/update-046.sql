CREATE TABLE `p_social_post` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sw_type` enum('VK','FB','TW') DEFAULT NULL,
  `sw_author_id` varchar(128) NOT NULL DEFAULT '',
  `title` varchar(128) NOT NULL DEFAULT '',
  `source` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

ALTER TABLE `p_social_post` ADD `handled` INT(11)  NOT NULL  DEFAULT '0'  AFTER `source`;

ALTER TABLE `p_social_post` ADD `timestamp` INT(11)  NOT NULL  DEFAULT '0'  AFTER `sw_author_id`;
