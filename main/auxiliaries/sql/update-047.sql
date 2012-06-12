CREATE TABLE `common_token` (
  `uid` int(11) unsigned NOT NULL,
  `token` varchar(32) NOT NULL DEFAULT '',
  `given_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip_address` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`,`token`),
  KEY `uid` (`uid`),
  KEY `token` (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `common_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  `surname` varchar(128) NOT NULL DEFAULT '',
  `login` varchar(128) NOT NULL DEFAULT '',
  `hash` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;