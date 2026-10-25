<?php

require_once '../../includes/mysql.php';
require_once 'converter_library.php';

echo "<pre>\n";

db_qw('TRUNCATE TABLE `p_user`');
echo "`p_user` truncated\n";
flush();

db_qw('TRUNCATE TABLE `p_user_data`');
echo "`p_user_data` truncated\n";
flush();

$req = db_qw('SELECT * FROM `pipe_users` ORDER BY `id`');
while ($u = db_fetch_assoc($req)) {
	db_qw('INSERT INTO `p_user` SET `id`=?, `name`=?, `surname`=?', $u['id'], $u['name'], $u['surname']);
	db_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'login\', `value`=?', $u['id'], $u['login']);
	db_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'passhash\', `value`=?', $u['id'], $u['md5pass']);

	if ($u['pipe_man_id'] != 0) {
		db_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'pmid\', `value`=?', $u['id'], $u['pipe_man_id']);
	}

	if (!empty ($u['country'])) {
		db_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'country\', `value`=?', $u['id'], $u['country']);
	}

	if (!empty ($u['city'])) {
		db_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'city\', `value`=?', $u['id'], $u['city']);
	}

	if (!empty ($u['email'])) {
		db_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'email\', `value`=?', $u['id'], $u['email']);
	}

	if (!empty ($u['icq'])) {
		db_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'icq\', `value`=?', $u['id'], $u['icq']);
	}

	if (!empty ($u['skype'])) {
		db_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'skype\', `value`=?', $u['id'], $u['skype']);
	}

	if (!empty ($u['vkid'])) {
		db_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'vkid\', `value`=?', $u['id'], $u['vkid']);
	}

	echo "User " . $u['name'] . " " . $u['surname'] . " (" . $u['id'] . ") handled\n";
	flush();
}

echo 'DONE in ' . save_results() . 'ms';

?>
