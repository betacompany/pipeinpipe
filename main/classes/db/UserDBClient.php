<?php

require_once dirname(__FILE__).'/../../includes/mysql.php';

require_once dirname(__FILE__) . '/MySQLResultIterator.php';

/**
 * works with `p_user` table
 * @author Artyom Grigoriev
 */
class UserDBClient {
    public static function selectById($id) {
        return mysql_qw('SELECT `id`, `name`, `surname` FROM `p_user` WHERE `id`=?', $id);
    }

	public static function selectAll() {
		return mysql_qw('SELECT * FROM `p_user`');
	}

	public static function getNearTo($userData) {
		$needAnd = false;
		$req = 'SELECT `p_user`.`id` AS `uid` FROM `p_user` LEFT JOIN `p_user_data` ON `p_user`.`id`=`p_user_data`.`uid` WHERE ';

		if (array_key_exists('name', $userData)) {
			if ($needAnd) $req .= ' AND ';
			$req .= ' `p_user`.`name`=\''.mysql_escape_string($userData['name']).'\' ';
			$needAnd = true;
		}

		if (array_key_exists('surname', $userData)) {
			if ($needAnd) $req .= ' AND ';
			$req .= ' `p_user`.`surname`=\''.mysql_escape_string($userData['surname']).'\' ';
			$needAnd = true;
		}

		$userData = array_diff_key($userData, array('name'=>'', 'surname'=>''));

		if (count($userData) > 0) {
			if ($needAnd) $req .= ' OR (';
			$needOr = false;

			foreach ($userData as $key => $value) {
				if ($key == 'name' || $key == 'surname') continue;

				if ($needOr) $req .= ' OR ';
				$req .= '(`p_user_data`.`key`=\''.mysql_escape_string($key).'\' AND `p_user_data`.`value`=\''.mysql_escape_string($value).'\')';
			}

			$req .= ')';
		}

		$req .= ' GROUP BY `uid`';

		return new MySQLResultIterator(
			mysql_qw($req)
		);
	}

	public static function getByKey($key, $value) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM (
					`p_user`
					INNER JOIN
						(
							SELECT
								*
							FROM
								`p_user_data`
							WHERE
								`key`=? AND `value`=?
						) AS `data`
					ON
						`p_user`.`id`=`data`.`uid`
					)',
					$key, $value
			)
		);
	}

	public static function insert($name, $surname) {
		mysql_qw('INSERT INTO `p_user` SET `name`=?, `surname`=?', $name, $surname);
		return mysql_insert_id();
	}

	public static function insertFavourite($uid, $target, $title) {
		mysql_qw('INSERT INTO `p_user_favourite` SET `uid`=?, `target`=?, `title`=?',
				$uid, $target, $title);
		return mysql_insert_id();
	}

	public static function deleteFavourite($uid, $target) {
		return (boolean) mysql_qw('DELETE FROM `p_user_favourite` WHERE `uid`=? AND `target`=?',
				$uid, $target);
	}

	public static function getFavourites($uid, $like = false) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `p_user_favourite` WHERE `uid`=?'.
				($like ? ' AND `target` LIKE ? ORDER BY `id`' : ' ORDER BY `id` --?'),
				$uid, $like
			)
		);
	}

	public static function checkFavourite($uid, $target) {
		return mysql_num_rows(
			mysql_qw(
				'SELECT * FROM `p_user_favourite` WHERE `uid`=? AND `target`=?',
				$uid, $target
			)
		) > 0;
	}
}
?>
