ALTER TABLE  `p_content_item`
CHANGE  `type`
`type` ENUM(  'blog_post',  'forum_topic',  'photo',  'video',  'interview_question',  'event' )
CHARACTER SET cp1251 COLLATE cp1251_general_ci NOT NULL;

