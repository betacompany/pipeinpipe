CREATE TABLE `p_social_post` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sw_type` enum('vk','fb','tw') DEFAULT NULL,
  `sw_author_id` varchar(128) NOT NULL DEFAULT '',
  `title` varchar(128) NOT NULL DEFAULT '',
  `source` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

ALTER TABLE `p_social_post` ADD `handled` INT(11)  NOT NULL  DEFAULT '0'  AFTER `source`;

ALTER TABLE `p_social_post` ADD `timestamp` INT(11)  NOT NULL  DEFAULT '0'  AFTER `sw_author_id`;

ALTER TABLE `p_content_item` CHANGE `type` `type` ENUM('blog_post','forum_topic','photo','video','interview_question','event','cross_post')  NULL  DEFAULT NULL;

ALTER TABLE `p_social_post` ADD `url` VARCHAR(1024)  NOT NULL DEFAULT 'http://pipeinpipe.info'  AFTER `timestamp`;

ALTER TABLE `p_social_post` ADD `sw_author_name` VARCHAR(1024)  NOT NULL DEFAULT 'Некто'  AFTER `sw_author_id`;

