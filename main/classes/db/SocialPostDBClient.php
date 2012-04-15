<?php

require_once dirname(__FILE__) . '/MySQLResultIterator.php';

/**
 * User: ortemij
 * Date: 28.03.12
 * Time: 11:05
 *
 * CREATE TABLE agg_post (
 *   id Integer PRIMARY KEY AUTO_INCREMENT,
 *   source Varchar(32),
 *   user_id Varchar(64),
 *   outer_id Bigint,
 *   date Datetime,
 *   content Blob,
 *   viewed Bool Default false
 * );
 */
class SocialPostDBClient {

	public static function getById($id) {
		return new MySQLResultIterator(mysql_qw(
			'SELECT *, UNIX_TIMESTAMP(`date`) as `timestamp` FROM `agg_post` WHERE `id`=?', $id
		));
	}

	public static function setHandled($id) {
		return mysql_qw('UPDATE `agg_post` SET `viewed`=? WHERE `id`=?', 1, $id);
	}

	public static function getAllUnhandled() {
		return new MySQLResultIterator(mysql_qw(
			'SELECT *, UNIX_TIMESTAMP(`date`) as `timestamp` FROM `agg_post` WHERE `viewed`=0'
		));
	}
}
