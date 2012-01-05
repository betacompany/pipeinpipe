CREATE TABLE IF NOT EXISTS `p_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `surname` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=2 ;

INSERT INTO `p_user` (`id`, `name`, `surname`) VALUES
(1, 'Имя', 'Фамилия');

CREATE TABLE IF NOT EXISTS `p_user_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `key` varchar(32) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `key` (`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=3 ;

INSERT INTO `p_user_data` (`id`, `uid`, `key`, `value`) VALUES
(1, 1, 'login', 'test'),
(2, 1, 'passhash', MD5('123'));

CREATE TABLE IF NOT EXISTS `p_user_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `status` varchar(2) NOT NULL,
  `target_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `target_id` (`target_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=2 ;

INSERT INTO `p_user_permission` (`id`, `uid`, `status`, `target_id`) VALUES
(1, 1, 'CA', 1);
