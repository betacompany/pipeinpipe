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
class AggregatorDBClient {

	const QUERY = 'SELECT
				`agg_post`.*,
				UNIX_TIMESTAMP(`date`) as `timestamp`,
	            `agg_user`.`first_name`,
	            `agg_user`.`last_name`
			FROM
				`agg_post`
				LEFT JOIN
				`agg_user`
				ON `agg_post`.`source`=`agg_user`.`source` AND `agg_post`.`user_id`=`agg_user`.`id`';

	public static function getPostById($id) {
		return new MySQLResultIterator(mysql_qw(
			self::QUERY . ' WHERE `agg_post`.`id`=?', intval($id)
		));
	}

	public static function setPostHandled($id) {
		return mysql_qw('UPDATE `agg_post` SET `viewed`=? WHERE `id`=?', 1, intval($id));
	}

	public static function getAllUnhandledPosts() {
		return new MySQLResultIterator(mysql_qw(
			self::QUERY . ' WHERE `viewed`=0'
		));
	}

	public static function getPhotoById($id) {
		return new MySQLResultIterator(mysql_qw(
			'SELECT * FROM `agg_photo` WHERE `id`=?', intval($id)
		));
	}

	public static function getPhotoByPostId($agg_post_id) {
		return new MySQLResultIterator(mysql_qw(
			'SELECT * FROM `agg_photo` WHERE `agg_post_id`=?', intval($agg_post_id)
		));
	}
}
