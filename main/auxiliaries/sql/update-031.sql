ALTER TABLE  `p_content_item` ADD  `removed` BOOL NOT NULL DEFAULT  '0' AFTER  `id`;
ALTER TABLE  `p_content_comment` ADD  `removed` BOOL NOT NULL DEFAULT  '0' AFTER  `id`;
ALTER TABLE  `p_content_group` ADD  `removed` BOOL NOT NULL DEFAULT  '0' AFTER  `id`;