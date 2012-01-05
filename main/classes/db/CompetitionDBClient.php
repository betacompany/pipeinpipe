<?php

require_once dirname(__FILE__).'/../../includes/mysql.php';

require_once dirname(__FILE__).'/../cupms/Competition.php';

require_once dirname(__FILE__).'/MySQLResultIterator.php';

/**
 * Description of CompetitionDBClient
 *
 * @author Artyom Grigoriev aka ortemij
 * @author Andrew Solozobov
 */
class CompetitionDBClient {

	public static function insert($leagueId = 0, $tournamentId = 0, $name = "",
	                            $date = "", $coef = 0, $description = "",
								$status = Competition::STATUS_DISABLED) {
		return mysql_qw('INSERT INTO `p_competition` SET `league_id`=?, `tournament_id`=?,
                    `name`=?, `date`=?, `coef`=?, `description`=?, `status`=?',
			   $leagueId, $tournamentId, $name, $date, $coef, $description, $status);
	}

	public static function update(Competition $competition) {
		return (boolean) mysql_qw('UPDATE `p_competition` SET
							`league_id`=?,
							`tournament_id`=?,
							`name`=?,
							`date`=?,
							`coef`=?,
							`description`=?,
							`status`=?
							WHERE `id`=?',
							$competition->getLeagueId(),
							$competition->getTournamentId(),
							$competition->getName(),
							$competition->getDate(),
							$competition->getCoef(),
							$competition->getDescription(),
							$competition->getStatus(),
							$competition->getId()
						);
	}

	public static function selectAll() {
		return new MySQLResultIterator(mysql_qw('SELECT * FROM `p_competition` WHERE 1=1 ORDER BY `id` ASC'));
	}

	/**
	 * @param $leagueId
	 * @param boolean $descendive [optional] order of competitions
	 * @return DBResultIterator
	 */
	public static function getByLeagueIdChronologically($leagueId, $descendive = true) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `p_competition` WHERE `league_id`=? ORDER BY `date` ' . ($descendive ? 'DESC' : 'ASC'),
				$leagueId
			)
		);
	}

	public static function selectByLeagueId($leagueId) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM p_competition WHERE league_id=? ORDER BY id ASC',
				$leagueId
			)
		);
	}

	public static function selectById($id){
		return mysql_qw('SELECT * FROM `p_competition` WHERE `id`=?', $id);
	}

	public static function selectByTournamentId($tournamentId) {
		return mysql_qw('SELECT `id` FROM `p_competition` WHERE `tournament_id`=? ORDER BY `id` ASC', $tournamentId);
	}

	public static function selectByName($name) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT `id` FROM `p_competition` WHERE `name`=?',
				$name
			)
		);
	}

	public static function selectBefore($date, $leagueId) {
		return mysql_qw('SELECT `id` FROM `p_competition` WHERE `date`<=? and `league_id`=?', $date, $leagueId);
	}

	/**
	 * returns true if competition with such id exists in DB
	 * @param int $id
	 * @return boolean
	 */
	public static function existsById($id) {
		return (boolean) mysql_num_rows(mysql_qw('SELECT `id` FROM `p_competition` WHERE `id`=?', $id));
	}

	//возвращает true, если удаление прошло удачно, при неудаче - false
	public static function deleteById($id) {
		return (boolean) mysql_qw('DELETE FROM `p_competition` WHERE `id`=?', $id);
	}

	/**
	 * (`competition_id`, `count`)
	 * @return DBResultIterator
	 */
	public static function countPipemen() {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT
					`competition_id` AS `comp_id`,
					COUNT(DISTINCT(`pmid`)) AS `count`
				FROM
					`p_man_cup_result` 
					INNER JOIN
					`p_cup`
					ON
					`p_man_cup_result`.`cup_id`=`p_cup`.`id`
				GROUP BY `competition_id`'
			)
		);
	}

	public static function insertRegistration($compId, $uid, $pmid) {
		return mysql_qw('INSERT INTO `p_competition_register` SET `comp_id`=?, `uid`=?, `pmid`=?',
						$compId,
						$uid,
						$pmid);
	}

	public static function deleteRegistration($compId, $uid) {
		return mysql_qw('DELETE FROM `p_competition_register` WHERE `comp_id`=? AND `uid`=?', $compId, $uid);
	}

	public static function deleteRawRegistration($compId, $pmid, $uid) {
		return mysql_qw('DELETE FROM `p_competition_register` WHERE `comp_id`=? AND `pmid`=? AND `uid`=?', $compId, $pmid, $uid);
	}

	public static function selectRegisteredUsers($compId) {
		return new MySQLResultIterator(mysql_qw('SELECT * FROM
													`p_competition_register`
													INNER JOIN
													`p_user`
													ON `p_competition_register`.`uid`=`p_user`.`id`
												WHERE `comp_id`=?',
												$compId));
	}

	public static function selectRegistered($compId) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT
					`pcid`,`comp_id`,`pmid`,`uid`,`u_name`,`u_surname`,`name` AS `p_name`,`surname` AS `p_surname`
				FROM (
						SELECT
							`p_competition_register`.`id` AS `pcid`,`comp_id`,`pmid`,`uid`,`name` AS `u_name`,`surname` AS `u_surname`
						FROM
							`p_competition_register`
								LEFT JOIN
							`p_user`
								ON
								`p_competition_register`.`uid`=`p_user`.`id`
					) AS `t`
						LEFT JOIN
					`p_man`
						ON
						`t`.`pmid`=`p_man`.`id`
				WHERE `comp_id`=?
				ORDER BY `pcid` DESC', $compId
			)
		);
	}

	public static function countAll() {
		return mysql_result(
			mysql_qw('SELECT COUNT(*) FROM `p_competition`'),
			0, 0
		);
	}
}
?>