<?php

require_once dirname(__FILE__).'/../../includes/mysql.php';
/**
 * @author Malkovsky Nikolay
 */
class LeagueDBClient {

    public static function selectLeaguesForPlayer($pmid) {
        return mysql_qw('SELECT DISTINCT(`league_id`)
                         FROM `p_rating`
                         WHERE `pmid`=? ORDER BY `league_id` ASC',
                        $pmid);
    }

	//TODO delete or implement this method (it's more like a pattern now)
    public static function selectLeaguesForUser($uid) {
        return mysql_qw('
						SELECT * FROM
						(
							(
								SELECT
									DISTINCT(`league_id`), `pmid`
								FROM
									`p_league`
							) AS `league_ids`

							INNER JOIN

							(
								SELECT
									`value` AS `pmid`
								FROM
									`p_user_data`
								WHERE
									`uid`=?
							) AS `players`

							ON `league_ids`.`pmid`=`players`.`pmid`
							ORDER BY `league_id` ASC
						)',
                        $uid);
    }

	public static function insert($name, $description, $formula) {
		return mysql_qw('INSERT INTO `p_league` SET `name`=?, `description`=?, `formula`=?', $name, $description, $formula);
	}

	public static function existsById($id) {
		return (boolean) mysql_num_rows(mysql_qw('SELECT `id` FROM `p_league` WHERE `id`=?', $id));
	}

	public static function selectById($id) {
		return mysql_qw('SELECT * FROM `p_league` WHERE `id`=?', $id);
	}

	public static function selectByName($name) {
		return mysql_qw('SELECT `id` FROM `p_league` WHERE `name`=?', $name);
	}

	/**
	 * @author Malkovsky Nikolay
	 *
	 * @brief this function gets all leagues from the p_league db.
	 * @return mysql resource
	 */
	public static function selectAll() {
		return new MySQLResultIterator(mysql_qw('SELECT * FROM `p_league` ORDER BY `id` ASC'));
	}

	public static function setName($leagueId, $name) {
		return (boolean) mysql_qw('UPDATE `p_league` SET `name`=? WHERE `id`=?', $name, $leagueId);
	}

	public static function setDescr($leagueId, $description) {
		return (boolean) mysql_qw('UPDATE `p_league` SET `description`=? WHERE `id`=?', $description, $leagueId);;
	}

	public static function setFormula($leagueId, $formula) {
		if ($formula instanceof RatingFormula)
			$formula = $formula->getName();
		return (boolean) mysql_qw('UPDATE `p_league` SET `formula`=? WHERE `id`=?', $formula, $leagueId);;
	}

	public static function selectPlayers($leagueId) {
		return new MySQLResultIterator(
				mysql_qw(
					'SELECT *
					FROM (
							SELECT DISTINCT(`pmid`) AS `id`
							FROM `p_rating`
							WHERE `league_id`=?
						) AS `league_members`

						INNER JOIN
							`p_man`
						ON
							`league_members`.`id`=`p_man`.`id`',
					$leagueId
				)
			);
	}

	public static function countAll() {
		return mysql_result(
			mysql_qw('SELECT COUNT(*) FROM `p_league`'),
			0, 0
		);
	}
}
?>