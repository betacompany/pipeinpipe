<?php

require_once dirname(__FILE__) . '/MySQLResultIterator.php';

/**
 * User: ortemij
 * Date: 28.03.12
 * Time: 11:05
 */
class SocialPostDBClient {

	public static function getById($id) {
		return new MySQLResultIterator(mysql_qw(
			'SELECT * FROM `p_social_post` WHERE `id`=?', $id
		));
	}

	public static function setHandled($id, $time) {
		return mysql_qw('UPDATE `p_social_post` SET `handled`=? WHERE `id`=?', $time, $id);
	}

	public static function getAllUnhandled() {
		return new MySQLResultIterator(mysql_qw(
			'SELECT * FROM `p_social_post` WHERE `handled`=0'
		));
	}
}
