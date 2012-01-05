ALTER TABLE  `p_cup` ADD  `status` ENUM(  'before',  'running',  'finished' ) NOT NULL DEFAULT  'finished';

ALTER TABLE  `p_cup` CHANGE  `status`  `status` ENUM(  'before',  'running',  'finished' ) CHARACTER SET cp1251 COLLATE cp1251_general_ci NOT NULL DEFAULT 'before';