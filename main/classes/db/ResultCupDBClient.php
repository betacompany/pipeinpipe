<?php

require_once dirname(__FILE__).'/../../includes/mysql.php';

require_once dirname(__FILE__) . '/../db/MySQLResultIterator.php';

/**
 * Description of ResultCupDBClient
 *
 * @author Artyom Grigoriev aka ortemij
 */
class ResultCupDBClient {
	public static function select($cup_id, $pmid) {
		return db_qw('SELECT * FROM `p_man_cup_result` WHERE `cup_id`=? and `pmid`=?', $cup_id, $pmid);
	}

    public static function selectPlayersForCup($cupId) {
        return new MySQLResultIterator(db_qw('
						SELECT *
						FROM
						(
							(
								SELECT
									`pmid` AS `id`
								FROM
									`p_man_cup_result`
								WHERE
									`cup_id`=?
							) AS `ids`

							INNER JOIN
								`p_man`
							ON
								`p_man`.`id`=`ids`.`id`
						)
						',
						$cupId,
						$cupId
		));
    }

    public static function selectCups($pipeman_id) {
        return db_qw('SELECT `cup_id`, `date`, `points`, `place`
                         FROM `p_man_cup_result`
                         WHERE `pmid`=?',
                        $pipeman_id );
    }

	public static function insert($pmid, $cup_id, $date, $points, $place) {
		return db_qw('INSERT INTO `p_man_cup_result` SET `pmid`=?, `cup_id`=?, `date`=?, `points`=?, `place`=?',
						$pmid, $cup_id, $date, $points, $place);
	}

	/**
	 * $pmid and $cupId identifies row in database table
	 * other parameters are values to update that row
	 * @param <type> $pmid
	 * @param <type> $cup_id
	 * @param <type> $date
	 * @param <type> $points
	 * @param <type> $place
	 */
	public static function update($pmid, $cup_id, $date, $points, $place) {
		return db_qw('UPDATE `p_man_cup_result` SET `date`=?, `points`=?, `place`=? WHERE `pmid`=? and `cup_id`=?',
						$date, $points, $place, $pmid, $cup_id);
	}

	public static function refresh($pmid, $cup_id, $date, $points, $place) {
		self::update($pmid, $cup_id, $date, $points, $place);
		if (db_affected_rows() > 0) return;
		self::insert($pmid, $cup_id, $date, $points, $place);
	}

	public static function deleteByCupId($cup_id) {
		return (bool) db_qw('DELETE FROM `p_man_cup_result` WHERE `cup_id`=?', $cup_id);
	}

	public static function rollBack($cup_id) {
		return db_qw('UPDATE `p_man_cup_result` SET `date`=\'\', `points`=0, `place`=0 WHERE `cup_id`=?', $cup_id);
	}

	public static function selectByCupId($cupId) {
		return db_qw('SELECT * FROM `p_man_cup_result` WHERE `cup_id`=?', $cupId);
	}

	public static function getCompetitionVictories($pmid) {
		return new MySQLResultIterator(
			db_qw(
				'SELECT `p_competition`.* FROM
					(
						SELECT `competition_id` FROM
							(
								SELECT * FROM
								`p_man_cup_result`
								WHERE `place`=1 and `pmid`=?
							) AS `results`

							INNER JOIN

							`p_cup`

							ON `p_cup`.`id`=`results`.`cup_id`

						WHERE `p_cup`.`parent_cup_id`=0
					) AS `victories`

					INNER JOIN

					`p_competition`

					ON `p_competition`.`id`=`victories`.`competition_id`',
				$pmid
			)
		);
	}

	/**
	 * One row per competition of the player: the result in the top-most cup
	 * (lowest parent_cup_id, so the main one rather than a group stage).
	 * It used to GROUP BY over a subquery sorted that way, which only MySQL 5
	 * honours; MariaDB drops ORDER BY in subqueries and picks any cup.
	 */
	public static function getCompetitionPlaces($pmid) {
		return new MySQLResultIterator(
			db_qw(
				'SELECT `p_competition`.*, `results`.`place`, `p_cup`.`parent_cup_id` FROM
					`p_man_cup_result` AS `results`
					INNER JOIN `p_cup` ON `p_cup`.`id`=`results`.`cup_id`
					INNER JOIN `p_competition` ON `p_competition`.`id`=`p_cup`.`competition_id`
				WHERE `results`.`pmid`=? AND `results`.`id`=(
					SELECT `r2`.`id` FROM
						`p_man_cup_result` AS `r2`
						INNER JOIN `p_cup` AS `c2` ON `c2`.`id`=`r2`.`cup_id`
					WHERE `r2`.`pmid`=`results`.`pmid` AND `c2`.`competition_id`=`p_cup`.`competition_id`
					ORDER BY `c2`.`parent_cup_id` ASC, `c2`.`id` ASC, `r2`.`id` ASC
					LIMIT 1
				)
				ORDER BY `p_competition`.`id` DESC',
				$pmid
			)
		);
	}
}
?>
