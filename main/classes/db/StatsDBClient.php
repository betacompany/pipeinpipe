<?php

/**
 * Description of StatsDBClient
 *
 * @author Никита
 */

require_once dirname(__FILE__).'/MySQLResultIterator.php';
class StatsDBClient {

	public static function getMaxWinPerc() {
		return new MySQLResultIterator(mysql_qw(
		'SELECT *
		FROM (

			SELECT nt10.id, victories / total *100 AS percentage
			FROM (

				SELECT id, COUNT( * ) AS victories
				FROM (

					SELECT pmid1 AS id
					FROM p_game
					WHERE score1 > score2
					UNION ALL
					SELECT pmid2 AS id
					FROM p_game
					WHERE score2 > score1
					) AS nt
					GROUP BY id
					) AS nt10
					INNER JOIN (

					SELECT id, COUNT( * ) AS total
					FROM (

						SELECT pmid1 AS id
						FROM p_game
						UNION ALL
						SELECT pmid2 AS id
						FROM p_game
					) AS nt2
					GROUP BY id
				) AS nt3 ON nt10.id = nt3.id
			WHERE nt10.id
			IN (

				SELECT pmid
				FROM (

					SELECT pmid, COUNT( DISTINCT competition_id ) AS comp_num
					FROM (

						SELECT pmid1 AS pmid, cup_id
						FROM p_game
						UNION SELECT pmid2 AS pmid, cup_id
						FROM p_game
					) AS t1
					INNER JOIN (

					SELECT id, competition_id
					FROM p_cup
					) AS t2 ON t2.id = t1.cup_id
					GROUP BY pmid
				) AS t10
				WHERE comp_num >1
			)
		) AS blah
		WHERE percentage = (

			SELECT MAX( percentage )
			FROM (

				SELECT victories / total *100 AS percentage
				FROM (

					SELECT id, COUNT( * ) AS victories
					FROM (

					SELECT pmid1 AS id
					FROM p_game
					WHERE score1 > score2
					UNION ALL
					SELECT pmid2 AS id
					FROM p_game
					WHERE score2 > score1
					) AS nt
					GROUP BY id
				) AS nt10
				INNER JOIN (

				SELECT id, COUNT( * ) AS total
				FROM (

					SELECT pmid1 AS id
					FROM p_game
					UNION ALL
					SELECT pmid2 AS id
					FROM p_game
				) AS nt2
				GROUP BY id
				) AS nt3 ON nt10.id = nt3.id
				WHERE nt10.id
				IN (

					SELECT pmid
					FROM (

						SELECT pmid, COUNT( DISTINCT competition_id ) AS comp_num
						FROM (

							SELECT pmid1 AS pmid, cup_id
							FROM p_game
							UNION SELECT pmid2 AS pmid, cup_id
							FROM p_game
						) AS t1
						INNER JOIN (

						SELECT id, competition_id
						FROM p_cup
						) AS t2 ON t2.id = t1.cup_id
						GROUP BY pmid
					) AS t10
					WHERE comp_num >1
				)
			) AS t13
		)'
		));
	}

	public static function getMaxLossPerc() {

		return new MySQLResultIterator(mysql_qw(
		'SELECT *
		FROM (

			SELECT nt10.id, losses / total *100 AS percentage
			FROM (

				SELECT id, COUNT( * ) AS losses
				FROM (

					SELECT pmid1 AS id
					FROM p_game
					WHERE score2 > score1
					UNION ALL
					SELECT pmid2 AS id
					FROM p_game
					WHERE score1 > score2
					) AS nt
					GROUP BY id
					) AS nt10
					INNER JOIN (

					SELECT id, COUNT( * ) AS total
					FROM (

						SELECT pmid1 AS id
						FROM p_game
						UNION ALL
						SELECT pmid2 AS id
						FROM p_game
					) AS nt2
					GROUP BY id
				) AS nt3 ON nt10.id = nt3.id
			WHERE nt10.id
			IN (

				SELECT pmid
				FROM (

					SELECT pmid, COUNT( DISTINCT competition_id ) AS comp_num
					FROM (

						SELECT pmid1 AS pmid, cup_id
						FROM p_game
						UNION SELECT pmid2 AS pmid, cup_id
						FROM p_game
					) AS t1
					INNER JOIN (

					SELECT id, competition_id
					FROM p_cup
					) AS t2 ON t2.id = t1.cup_id
					GROUP BY pmid
				) AS t10
				WHERE comp_num >1
			)
		) AS blah
		WHERE percentage = (

			SELECT MAX( percentage )
			FROM (

				SELECT losses / total *100 AS percentage
				FROM (

					SELECT id, COUNT( * ) AS losses
					FROM (

					SELECT pmid1 AS id
					FROM p_game
					WHERE score2 > score1
					UNION ALL
					SELECT pmid2 AS id
					FROM p_game
					WHERE score1 > score2
					) AS nt
					GROUP BY id
				) AS nt10
				INNER JOIN (

				SELECT id, COUNT( * ) AS total
				FROM (

					SELECT pmid1 AS id
					FROM p_game
					UNION ALL
					SELECT pmid2 AS id
					FROM p_game
				) AS nt2
				GROUP BY id
				) AS nt3 ON nt10.id = nt3.id
				WHERE nt10.id
				IN (

					SELECT pmid
					FROM (

						SELECT pmid, COUNT( DISTINCT competition_id ) AS comp_num
						FROM (

							SELECT pmid1 AS pmid, cup_id
							FROM p_game
							UNION SELECT pmid2 AS pmid, cup_id
							FROM p_game
						) AS t1
						INNER JOIN (

						SELECT id, competition_id
						FROM p_cup
						) AS t2 ON t2.id = t1.cup_id
						GROUP BY pmid
					) AS t10
					WHERE comp_num >1
				)
			) AS t13
		)'
		));
	}

	public static function getMaxAve() {
		return new MySQLResultIterator(mysql_qw(
		'SELECT t11.pmid, ave
		FROM (
			(

			SELECT pmid, SUM( points ) / SUM( games ) AS ave
			FROM p_man_cup_table
			GROUP BY pmid
			) AS t11
			INNER JOIN (

			SELECT pmid, COUNT( DISTINCT competition_id ) AS comp_num
			FROM (

				SELECT pmid1 AS pmid, cup_id
				FROM p_game
				UNION SELECT pmid2 AS pmid, cup_id
				FROM p_game
			) AS t1
			INNER JOIN (

				SELECT id, competition_id
				FROM p_cup
			) AS t2 ON t2.id = t1.cup_id
			GROUP BY pmid
			) AS t22 ON t11.pmid = t22.pmid
		)
		WHERE comp_num >1
		AND ave = (
		SELECT MAX( ave )
		FROM (
			(

			SELECT pmid, SUM( points ) / SUM( games ) AS ave
			FROM p_man_cup_table
			GROUP BY pmid ) AS t11
			INNER JOIN (

			SELECT pmid, COUNT( DISTINCT competition_id ) AS comp_num
			FROM (

			SELECT pmid1 AS pmid, cup_id
			FROM p_game
			UNION SELECT pmid2 AS pmid, cup_id
			FROM p_game
			) AS t1
			INNER JOIN (

			SELECT id, competition_id
			FROM p_cup
			) AS t2 ON t2.id = t1.cup_id
			GROUP BY pmid
			) AS t22 ON t11.pmid = t22.pmid
		)
		WHERE comp_num >1
		)'
		));
	}

	public static function getMaxCompetitionsWon() {
		return new MySQLResultIterator(mysql_qw(
		'SELECT * FROM (

			SELECT pmid, COUNT(*) AS comp_won FROM (
			SELECT pmid, cup_id, place
			FROM p_man_cup_result
			WHERE place = 1
			AND
			cup_id IN
			(SELECT id FROM p_cup WHERE parent_cup_id = 0)
			) AS nt GROUP BY pmid
			) AS nt2
			WHERE
			comp_won = (
				SELECT MAX(comp_won) FROM (
					SELECT pmid, COUNT(*) AS comp_won FROM (
					SELECT pmid, cup_id, place
					FROM p_man_cup_result
					WHERE place = 1
					AND
					cup_id IN
					(SELECT id FROM p_cup WHERE parent_cup_id = 0)
				) AS nt GROUP BY pmid
			) AS nt2
		)'
		));
	}

	/**
	 * Returns the pipeman with the most <b>a</b>, where <b>a</b> is a number of
	 * competitions won by this pman divided by number of competitions
	 * in which the pman took part. It is calculated only among pipemen, who participated
	 * more than in one competition.
	 * @return MySQLResultIterator
	 */
	public static function getMaxCompPerc() {
		return new MySQLResultIterator(mysql_qw(
		'SELECT * FROM
		(
			SELECT nt.pmid, comp_won / comp_num * 100 AS percentage
			FROM (

				SELECT pmid, COUNT( * ) AS comp_won
				FROM (

					SELECT pmid, cup_id, place
					FROM p_man_cup_result
					WHERE place =1
					AND cup_id
					IN (

						SELECT id
						FROM p_cup
						WHERE parent_cup_id =0
					)
				) AS nt
				GROUP BY pmid
			) AS nt
			INNER JOIN (

				SELECT pmid, COUNT( DISTINCT competition_id ) AS comp_num
				FROM (

					SELECT pmid1 AS pmid, cup_id
					FROM p_game
					UNION SELECT pmid2 AS pmid, cup_id
					FROM p_game
				) AS t1
				INNER JOIN (

					SELECT id, competition_id
					FROM p_cup
				) AS t2 ON t2.id = t1.cup_id
				GROUP BY pmid
			) AS nt2 ON nt2.pmid = nt.pmid
			WHERE comp_num > 1
		) AS new
		WHERE percentage = (
			SELECT MAX(percentage)
			FROM (
				SELECT nt.pmid, comp_won / comp_num * 100 AS percentage
				FROM (

					SELECT pmid, COUNT( * ) AS comp_won
					FROM (

						SELECT pmid, cup_id, place
						FROM p_man_cup_result
						WHERE place =1
						AND cup_id
						IN (

							SELECT id
							FROM p_cup
							WHERE parent_cup_id =0
						)
					) AS nt
					GROUP BY pmid
				) AS nt
				INNER JOIN (

					SELECT pmid, COUNT( DISTINCT competition_id ) AS comp_num
					FROM (

						SELECT pmid1 AS pmid, cup_id
						FROM p_game
						UNION SELECT pmid2 AS pmid, cup_id
						FROM p_game
					) AS t1
					INNER JOIN (

						SELECT id, competition_id
						FROM p_cup
					) AS t2 ON t2.id = t1.cup_id
					GROUP BY pmid
				) AS nt2 ON nt2.pmid = nt.pmid
				WHERE comp_num > 1
			) AS alias
		)'
		));
	}

	public static function getMaxComps() {
		return new MySQLResultIterator(mysql_qw(
		'SELECT * FROM
		(
			SELECT pmid, COUNT( DISTINCT competition_id ) AS comp_num
			FROM (

				SELECT pmid1 AS pmid, cup_id
				FROM p_game
				UNION SELECT pmid2 AS pmid, cup_id
				FROM p_game
			) AS t1
			INNER JOIN (

				SELECT id, competition_id
				FROM p_cup
			) AS t2 ON t2.id = t1.cup_id
			GROUP BY pmid
		) AS new_table1 WHERE comp_num =
		(
			SELECT MAX(comp_num) FROM
			(
				SELECT pmid, COUNT( DISTINCT competition_id ) AS comp_num
				FROM (

					SELECT pmid1 AS pmid, cup_id
					FROM p_game
					UNION SELECT pmid2 AS pmid, cup_id
					FROM p_game
				) AS t1
				INNER JOIN (

					SELECT id, competition_id
					FROM p_cup
				) AS t2 ON t2.id = t1.cup_id
				GROUP BY pmid

			) AS new_table
		)'
		));
	}
	
	public static function getMaxPoints() {
		return new MySQLResultIterator(mysql_qw(
		'SELECT * 
		FROM (

			SELECT pmid, SUM(points) AS points, competition_id 
			FROM p_man_cup_result
			INNER JOIN 
			p_cup
			ON
			p_cup.id = p_man_cup_result.cup_id
			GROUP BY pmid, competition_id
		) AS nt1 
		WHERE points = (
			SELECT MAX(points) 
			FROM (
				SELECT pmid, SUM(points) AS points, competition_id 
				FROM p_man_cup_result
				INNER JOIN 
				p_cup
				ON
				p_cup.id = p_man_cup_result.cup_id
				GROUP BY pmid, competition_id
			) AS nt
		)'		
		));
	}

	public static function getMaxDaysOnTop() {
		return new MySQLResultIterator(mysql_qw(
		'SELECT * FROM (
			SELECT pmid, COUNT(DISTINCT `date`) AS days
			FROM (
				SELECT pmid, `date`
				FROM p_rating
				WHERE rating_place = 1
				AND league_id = 1
			) AS nt
			GROUP BY pmid
			) AS nt
			WHERE days = (
				SELECT MAX(days)
				FROM (
					SELECT pmid, COUNT(DISTINCT `date`) AS days
					FROM (
						SELECT pmid, `date`
						FROM p_rating
						WHERE rating_place = 1
						AND league_id = 1
					) AS nt
					GROUP BY pmid
				) AS nt
			)'
		));
	}

	public static function getClub69() {
		return new MySQLResultIterator(mysql_qw(
			'SELECT id, COUNT( * ) AS victories
			FROM (
				SELECT pmid1 AS id
				FROM p_game
				WHERE score1 > score2
				UNION ALL
				SELECT pmid2 AS id
				FROM p_game
				WHERE score2 > score1
			) AS nt
			GROUP BY id
			ORDER BY victories DESC'));
	}

	public static function getCompWithMaxMatches() {
		return new MySQLResultIterator(mysql_qw(
			'SELECT competition_id, game_num
			FROM (
				SELECT competition_id, COUNT(*) AS game_num
				FROM (
					(SELECT cup_id AS game_cup_id
					FROM p_game) as nt
					INNER JOIN
					p_cup
					ON nt.game_cup_id = p_cup.id
				)
				GROUP BY competition_id
			) AS new_table
			WHERE game_num = (
				SELECT MAX(game_num)
				FROM (
					SELECT competition_id, COUNT(*) AS game_num
					FROM (
						(SELECT cup_id AS game_cup_id
						FROM p_game) as nt
						INNER JOIN
						p_cup
						ON nt.game_cup_id = p_cup.id
					)
					GROUP BY competition_id
				) AS new_table
			)'
		));
	}

	/**
	 *
	 * @return MySQLResultIterator
	 */
	public static function getCompsWithMaxPman() {
		return new MySQLResultIterator(mysql_qw(
			'SELECT `comp_id`, `count` FROM (
				SELECT `competition_id` AS `comp_id`, COUNT(DISTINCT(`pmid`)) AS `count`
				FROM `p_man_cup_result`
				INNER JOIN `p_cup`
				ON `p_man_cup_result`.`cup_id`=`p_cup`.`id`
				GROUP BY `competition_id`
			) AS nt WHERE `count` = (
				SELECT MAX(`count`)
				FROM (
					SELECT `competition_id` AS `comp_id`, COUNT(DISTINCT(`pmid`)) AS `count`
					FROM `p_man_cup_result`
					INNER JOIN `p_cup`
					ON `p_man_cup_result`.`cup_id`=`p_cup`.`id`
					GROUP BY `competition_id`
				) AS nt
			)'
		));
	}
}
?>
