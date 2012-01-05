--
-- Table structure for table `p_competition`
--

CREATE TABLE IF NOT EXISTS `p_competition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `league_id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL,
  `season` varchar(16) NOT NULL,
  `date` date NOT NULL,
  `coef` double NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_cup`
--

CREATE TABLE IF NOT EXISTS `p_cup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `competition_id` int(11) NOT NULL,
  `parent_cup_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `type` enum('playoff','one-lap','two-laps','undefined') DEFAULT NULL,
  `status` enum('before','running','finished') NOT NULL DEFAULT 'before',
  PRIMARY KEY (`id`),
  KEY `competition_id` (`competition_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_forum_action`
--

CREATE TABLE IF NOT EXISTS `p_forum_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('agree','roman') NOT NULL,
  `message_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`message_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_forum_forum`
--

CREATE TABLE IF NOT EXISTS `p_forum_forum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` tinytext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_forum_last`
--

CREATE TABLE IF NOT EXISTS `p_forum_last` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target_id` int(11) NOT NULL,
  `target_type` enum('forum','part','topic') NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `target_id` (`target_id`,`target_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_forum_message`
--

CREATE TABLE IF NOT EXISTS `p_forum_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `text` text NOT NULL,
  `html` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_forum_part`
--

CREATE TABLE IF NOT EXISTS `p_forum_part` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forum_id` int(11) NOT NULL,
  `title` tinytext NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_forum_topic`
--

CREATE TABLE IF NOT EXISTS `p_forum_topic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `part_id` int(11) NOT NULL,
  `prev_topic_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `title` tinytext NOT NULL,
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `part_id` (`part_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_forum_visit`
--

CREATE TABLE IF NOT EXISTS `p_forum_visit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `target_type` enum('forum','part','topic') NOT NULL,
  `target_id` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `target_type` (`target_type`,`target_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_game`
--

CREATE TABLE IF NOT EXISTS `p_game` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cup_id` int(11) NOT NULL,
  `stage` int(11) NOT NULL,
  `tour` int(11) NOT NULL,
  `pmid1` int(11) NOT NULL,
  `pmid2` int(11) NOT NULL,
  `score1` int(11) NOT NULL,
  `score2` int(11) NOT NULL,
  `prev_game_id1` int(11) NOT NULL DEFAULT '0',
  `prev_game_id2` int(11) NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_tech` enum('0','1','f') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cup_id` (`cup_id`),
  KEY `pmid1` (`pmid1`),
  KEY `pmid2` (`pmid2`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_league`
--

CREATE TABLE IF NOT EXISTS `p_league` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `formula` enum('Grigoriev') NOT NULL DEFAULT 'Grigoriev',
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_man`
--

CREATE TABLE IF NOT EXISTS `p_man` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `surname` varchar(128) NOT NULL,
  `gender` enum('m','f') NOT NULL,
  `country` varchar(128) NOT NULL,
  `city` varchar(128) NOT NULL,
  `email` varchar(128) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_man_cup_result`
--

CREATE TABLE IF NOT EXISTS `p_man_cup_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pmid` int(11) NOT NULL,
  `cup_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `points` double NOT NULL,
  `place` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pmid` (`pmid`),
  KEY `cup_id` (`cup_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_man_cup_table`
--

CREATE TABLE IF NOT EXISTS `p_man_cup_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pmid` int(11) DEFAULT NULL,
  `cup_id` int(11) DEFAULT NULL,
  `place` int(11) NOT NULL DEFAULT '0',
  `games` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `win5` int(11) DEFAULT '0',
  `win6` int(11) DEFAULT '0',
  `winb` int(11) DEFAULT '0',
  `lose5` int(11) DEFAULT '0',
  `lose6` int(11) DEFAULT '0',
  `loseb` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cup_id` (`cup_id`),
  KEY `pmid` (`pmid`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_rating`
--

CREATE TABLE IF NOT EXISTS `p_rating` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `league_id` int(11) NOT NULL,
  `pmid` int(11) NOT NULL,
  `points` double NOT NULL,
  `date` date NOT NULL,
  `rating_place` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `league_id` (`league_id`,`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_tournament`
--

CREATE TABLE IF NOT EXISTS `p_tournament` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_user`
--

CREATE TABLE IF NOT EXISTS `p_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `surname` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_user_data`
--

CREATE TABLE IF NOT EXISTS `p_user_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `key` varchar(32) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `key` (`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `p_user_permission`
--

CREATE TABLE IF NOT EXISTS `p_user_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `status` varchar(2) NOT NULL,
  `target_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `target_id` (`target_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;