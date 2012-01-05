<?php
require_once dirname(__FILE__).'/../../includes/mysql.php';

/**
 * @author Innokenty Shuvalov
 */

class RatingDBClient {
	public static function select($leagueId, $date) {
		return mysql_qw('SELECT *
						 FROM (
								(
									SELECT *
										FROM `p_rating`
										WHERE `league_id`=? AND `date`=?
								) AS rating
							INNER JOIN
								(
									SELECT `id`, `name`, `surname`
										FROM `p_man`
								) AS names
							ON rating.pmid = names.id
						 )
						 ORDER BY `rating_place` ASC', $leagueId, $date);
	}

	public static function insert($leagueId, $date, $pmid, $score, $place) {
		return (boolean) mysql_qw(
					'INSERT INTO `p_rating` SET `league_id`=?, `date`=?, `pmid`=?, `points`=?, `rating_place`=?',
					$leagueId, $date, $pmid, $score, $place
				);
	}

	public static function removeFuture($date, $leagueId) {
		if ($leagueId == 0) {
			return (boolean) mysql_qw(
					'DELETE FROM `p_rating` WHERE `date`>=?', $date
				);
		}

		return (boolean) mysql_qw(
					'DELETE FROM `p_rating` WHERE `date`>=? and `league_id`=?', $date, $leagueId
				);
	}

	public static function selectByPmid($leagueId, $pmid) {
		return mysql_qw(
					'SELECT * FROM `p_rating` WHERE `league_id`=? and `pmid`=? ORDER BY `date` ASC',
					$leagueId, $pmid
				);
	}


	public static function selectByPmidInterval($begin, $end, $leagueId, $pmid) {
		return mysql_qw(
					'SELECT * FROM `p_rating` WHERE `league_id`=? and `pmid`=? and `date`>=? and `date`<=? ORDER BY `date` ASC',
					$leagueId, $pmid, $begin, $end
				);
	}

	public static function getBestRank($leagueId, $pmid) {
		$req = mysql_qw('SELECT MIN(`rating_place`) AS `place` FROM `p_rating` WHERE `league_id`=? and `pmid`=?', $leagueId, $pmid);
		if ($result = mysql_fetch_assoc($req)) {
			return $result['place'];
		}

		return 0;
	}
}
?>
