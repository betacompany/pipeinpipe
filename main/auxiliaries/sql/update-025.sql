-- content group (such as forum part) also may contain description, for example
ALTER TABLE  `p_content_group` ADD  `content_source` TEXT NOT NULL AFTER  `title` ,
ADD  `content_parsed` TEXT NOT NULL AFTER  `content_source`;