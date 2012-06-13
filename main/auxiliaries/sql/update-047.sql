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
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `disabled` (`disabled`)
) ENGINE=MyISAM AUTO_INCREMENT=281 DEFAULT CHARSET=utf8;

insert into `common_user` (`id`, `name`, `surname`, `login`, `hash`, `disabled`)
select
  `pu`.`id`, `pu`.`name`, `pu`.`surname`, `pul`.`value` as `login`, `pup`.`value` as `hash`, 0
from
  `p_user` `pu`
  left join
  (select `uid`, `value` from `p_user_data` where `key`='login') `pul`
  on `pu`.`id`=`pul`.`uid`
  left join
  (select `uid`, `value` from `p_user_data` where `key`='passhash') `pup`
  on `pu`.`id`=`pup`.`uid`;

DROP TABLE `p_user`;

create view `p_user` as select `id`, `name`, `surname` from `common_user` where `disabled`=0;


