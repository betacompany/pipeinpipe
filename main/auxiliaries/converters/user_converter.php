<?php

require_once '../../includes/mysql.php';
require_once 'converter_library.php';

echo "<pre>\n";

mysql_qw('TRUNCATE TABLE `p_user`');
echo "`p_user` truncated\n";
flush();

mysql_qw('TRUNCATE TABLE `p_user_data`');
echo "`p_user_data` truncated\n";
flush();

$req = mysql_qw('SELECT * FROM `pipe_users` ORDER BY `id`');
while ($u = mysql_fetch_assoc($req)) {
	mysql_qw('INSERT INTO `p_user` SET `id`=?, `name`=?, `surname`=?', $u['id'], $u['name'], $u['surname']);
	mysql_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'login\', `value`=?', $u['id'], $u['login']);
	mysql_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'passhash\', `value`=?', $u['id'], $u['md5pass']);

	if ($u['pipe_man_id'] != 0) {
		mysql_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'pmid\', `value`=?', $u['id'], $u['pipe_man_id']);
	}

	if (!empty ($u['country'])) {
		mysql_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'country\', `value`=?', $u['id'], $u['country']);
	}

	if (!empty ($u['city'])) {
		mysql_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'city\', `value`=?', $u['id'], $u['city']);
	}

	if (!empty ($u['email'])) {
		mysql_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'email\', `value`=?', $u['id'], $u['email']);
	}

	if (!empty ($u['icq'])) {
		mysql_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'icq\', `value`=?', $u['id'], $u['icq']);
	}

	if (!empty ($u['skype'])) {
		mysql_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'skype\', `value`=?', $u['id'], $u['skype']);
	}

	if (!empty ($u['vkid'])) {
		mysql_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=\'vkid\', `value`=?', $u['id'], $u['vkid']);
	}

	echo "User " . $u['name'] . " " . $u['surname'] . " (" . $u['id'] . ") handled\n";
	flush();
}

echo 'DONE in ' . save_results() . 'ms';

?>
