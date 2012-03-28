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
}
