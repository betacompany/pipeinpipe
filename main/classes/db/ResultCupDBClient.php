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
		return mysql_qw('SELECT * FROM `p_man_cup_result` WHERE `cup_id`=? and `pmid`=?', $cup_id, $pmid);
	}

    public static function selectPlayersForCup($cupId) {
        return new MySQLResultIterator(mysql_qw('
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
        return mysql_qw('SELECT `cup_id`, `date`, `points`, `place`
                         FROM `p_man_cup_result`
                         WHERE `pmid`=?',
                        $pipeman_id );
    }

	public static function insert($pmid, $cup_id, $date, $points, $place) {
		return mysql_qw('INSERT INTO `p_man_cup_result` SET `pmid`=?, `cup_id`=?, `date`=?, `points`=?, `place`=?',
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
		return mysql_qw('UPDATE `p_man_cup_result` SET `date`=?, `points`=?, `place`=? WHERE `pmid`=? and `cup_id`=?',
						$date, $points, $place, $pmid, $cup_id);
	}

	public static function refresh($pmid, $cup_id, $date, $points, $place) {
		self::update($pmid, $cup_id, $date, $points, $place);
		if (mysql_affected_rows() > 0) return;
		self::insert($pmid, $cup_id, $date, $points, $place);
	}

	public static function deleteByCupId($cup_id) {
		return (boolean) mysql_qw('DELETE FROM `p_man_cup_result` WHERE `cup_id`=?', $cup_id);
	}

	public static function rollBack($cup_id) {
		return mysql_qw('UPDATE `p_man_cup_result` SET `date`=\'\', `points`=0, `place`=0 WHERE `cup_id`=?', $cup_id);
	}

	public static function selectByCupId($cupId) {
		return mysql_qw('SELECT * FROM `p_man_cup_result` WHERE `cup_id`=?', $cupId);
	}

	public static function getCompetitionVictories($pmid) {
		return new MySQLResultIterator(
			mysql_qw(
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

	public static function getCompetitionPlaces($pmid) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM
					(
						SELECT `p_competition`.*, `place`, `parent_cup_id` FROM
							(
								SELECT `competition_id`, `place`, `p_cup`.`parent_cup_id` FROM
									(
										SELECT * FROM
										`p_man_cup_result`
										WHERE `pmid`=?
									) AS `results`

									INNER JOIN

									`p_cup`

									ON `p_cup`.`id`=`results`.`cup_id`
							) AS `victories`

							INNER JOIN

							`p_competition`

							ON `p_competition`.`id`=`victories`.`competition_id`
						ORDER BY `p_competition`.`id` DESC, `victories`.`parent_cup_id` ASC
					) AS `r`
					GROUP BY `id`
					ORDER BY `id` DESC',
				$pmid
			)
		);
	}
}
?>
