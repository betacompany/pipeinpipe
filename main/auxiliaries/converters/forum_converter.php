<?php

require_once '../../includes/mysql.php';
require_once 'converter_library.php';

$queries = array(
	'TRUNCATE TABLE	`p_content_comment`',
	'TRUNCATE TABLE `p_content_item`',
	'TRUNCATE TABLE `p_content_group`',
	'TRUNCATE TABLE `p_content_view`',
	'TRUNCATE TABLE `p_content_action`',
	'TRUNCATE TABLE `p_content_tag`',
	'TRUNCATE TABLE `p_content_tag_target`',
	'TRUNCATE TABLE `p_content_connection`',
);

echo '<pre>';

foreach ($queries as $query) {
	db_qw($query);
	echo $query . "\n";
	flush();
}

// --------------------------------------

$req = db_qw('SELECT * FROM `pipe_forum_topics` ORDER BY `id`');
while ($t = db_fetch_assoc($req)) {
	db_qw('INSERT INTO `p_content_item` SET
				`id`=?,
				`type`=?,
				`group_id`=?,
				`uid`=?,
				`creation_timestamp`=0,
				`last_comment_timestamp`=0,
				`content_source`=?,
				`content_parsed`=?,
				`closed`=?,
				`content_value`=?
			',
				$t['id'],
				'forum_topic',
				$t['partid'],
				$t['authorid'],
				$t['title'],
				'',
				$t['closed'] ? 'closed' : 'opened',
				0
			);

	if ($t['contid'] != 0) {
		db_qw('UPDATE `p_content_item` SET `content_value`=? WHERE `id`=?', $t['id'], $t['contid']);
	}

	echo 'topic ' . $t['id'] . " handled\n";

	flush();
}

echo "ALL TOPICS HANDLED\n\n";
flush();

// -------------------------------------------

$req = db_qw('SELECT MAX(`id`) FROM `pipe_forum_parts`');
$max = db_result($req, 0, 0);

$req = db_qw('SELECT * FROM `pipe_forum_parts` ORDER BY `id`');
while ($p = db_fetch_assoc($req)) {
	db_qw('INSERT INTO `p_content_group` SET
				`id`=?,
				`type`=?,
				`parent_group_id`=?,
				`title`=?,
				`content_source`=?,
				`content_parsed`=?
			',
				$p['id'],
				'forum_part',
				$p['forumid'] + $max,
				$p['title'],
				$p['description'],
				$p['description']
			);

	echo 'part ' . $p['id'] . " handled\n";
	flush();
}

echo "ALL PARTS HANDLED\n\n";
flush();

$req = db_qw('SELECT * FROM `pipe_forum_forums` ORDER BY `id`');
while ($f = db_fetch_assoc($req)) {
	db_qw(
		'INSERT INTO `p_content_group` SET
			`id`=?,
			`type`=?,
			`parent_group_id`=?,
			`title`=?,
			`content_source`=?,
			`content_parsed`=?
		',
			$f['id'] + $max,
			'forum_forum',
			0,
			$f['title'],
			'', ''
		);

	echo 'forum ' . $f['id'] . " handled\n";
	flush();
}

echo "ALL FORUMS HANDLED\n\n";
flush();

$req = db_qw('SELECT * FROM `pipe_forum_messages` ORDER BY `id`');
while ($m = db_fetch_assoc($req)) {
	$source = preg_replace('/\[quote=([^]]*)\]/', '[quote name="$1"]', $m['text']);

	db_qw(
		'INSERT INTO `p_content_comment` SET
			`id`=?,
			`type`=?,
			`item_id`=?,
			`uid`=?,
			`timestamp`=?,
			`content_source`=?,
			`content_parsed`=?
		',
			$m['id'],
			'forum_message',
			$m['topicid'],
			$m['authorid'],
			strtotime($m['timestamp']),
			$source, ''
	);

	echo 'message ' . $m['id'] . " handled\n";
	flush();
}

echo "ALL MESSAGES HANDLED\n\n";
flush();

$req = db_qw('SELECT * FROM `pipe_forum_agreements` ORDER BY `id`');
while ($a = db_fetch_assoc($req)) {
	db_qw(
		'INSERT INTO `p_content_action` SET
			`target_type`=\'comment\',
			`target_id`=?,
			`type`=\'agree\',
			`uid`=?,
			`timestamp`=?
		',
			$a['msgid'],
			$a['uid'],
			strtotime($a['timestamp'])
	);

	echo 'Agreement ' . $a['id'] . " handled\n";
	flush();
}

$req = db_qw('SELECT * FROM `pipe_forum_romanments` ORDER BY `id`');
while ($a = db_fetch_assoc($req)) {
	db_qw(
		'INSERT INTO `p_content_action` SET
			`target_type`=\'comment\',
			`target_id`=?,
			`type`=\'roman\',
			`uid`=?,
			`timestamp`=?,
			`value`=?
		',
			$a['msgid'],
			$a['uid'],
			strtotime($a['timestamp']),
			$a['coef']
	);

	echo 'Romanment ' . $a['id'] . " handled\n";
	flush();
}

echo 'Convertion finished in ';
echo save_results() . 'ms</pre>';
flush();

?>
