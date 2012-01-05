TRUNCATE TABLE `p_content_group`;

INSERT INTO `ortemij`.`p_content_group`
(`id`, `removed`, `type`, `parent_group_id`, `title`, `content_source`, `content_parsed`)
VALUES
(NULL, '0', 'forum_forum', '0', 'Тестовый форум', '', '');

INSERT INTO `ortemij`.`p_content_group`
(`id`, `removed`, `type`, `parent_group_id`, `title`, `content_source`, `content_parsed`)
VALUES
(NULL, '0', 'forum_part', '1', 'Тестовый раздел 1', 'Описание тестового раздела 1', 'Описание тестового раздела 1'),
(NULL, '0', 'forum_part', '1', 'Тестовый раздел 2', 'Описание тестового раздела 2', 'Описание тестового раздела 2');

INSERT INTO `ortemij`.`p_content_item`
(`id`, `removed`, `type`, `group_id`, `uid`, `creation_timestamp`, `last_comment_timestamp`, `content_title`, `content_source`, `content_parsed`, `content_value`, `closed`, `private`)
VALUES
(NULL, '0', 'forum_topic', '2', '1', '0', '1297849092', '', 'Тестовая тема 1', '', '0', 'opened', 'public'),
(NULL, '0', 'forum_topic', '3', '1', '0', '0', '', 'Тестовая тема 2', '', '0', 'opened', 'public');

INSERT INTO `ortemij`.`p_user` (`id`, `name`, `surname`) VALUES (NULL, 'Name', 'Surname');

INSERT INTO `ortemij`.`p_content_comment`
(`id`, `removed`, `type`, `item_id`, `uid`, `timestamp`, `content_source`, `content_parsed`)
VALUES
(NULL, '0', 'forum_message', '1', '2', '1297849092', 'asdfasdfasdfasdfasd', 'asdfasdfasdfasdfasd');
