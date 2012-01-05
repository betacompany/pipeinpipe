<?php
/* 
 * This class contains static database methods for class Game.
 *
 * @author Malkovsky Nikolay
 * @author Artyom Grigoriev
 * @author Innokenty Shuvalov
 */

require_once dirname(__FILE__).'/../../includes/mysql.php';

require_once dirname(__FILE__).'/../cupms/Cup.php';

require_once dirname(__FILE__) . '/MySQLResultIterator.php';

class GameDBClient {

	public static function getAll() {
		return new MySQLResultIterator(GameDBClient::selectAll());
	}

	/*
	 * General update method.
	 */
    public static function update($game) {
		return (boolean) mysql_qw('UPDATE `p_game` SET `cup_id`=?, `stage`=?,
                    `tour`=?, `pmid1`=?, `pmid2`=?, `score1`=?, `score2`=?, `time`=?, `is_tech`=?,
                    `prev_game_id1`=?, `prev_game_id2`=? WHERE `id`=?',
                        $game->getCupId(), $game->getStage(), $game->gettour(), $game->getPmid1(),
                        $game->getPmid2(), $game->getScore1(), $game->getScore2(), $game->getTime(),
                        $game->getType(), $game->getPrevGameId1(), $game->getPrevGameId2(), $game->getId()
		);
	}

	/*
	 * Score update method.
	 */
    public static function updateScore($game) {
		return mysql_qw('UPDATE `p_game` SET `score1`=?, `score2`=? WHERE `id`=?',
				$game->getScore1(), $game->getScore2(), $game->getId()
				);
    }

	/*
	 * Pipemans id update method.
	 */
	public static function updatePmid($game){
		return mysql_qw('UPDATE `p_game` SET `pmid1`=?, `pmid2`=?, WHERE `id`=?',
				$game->getPmid1(), $game->getPmid2(), $game->getId()
				);
	}

	/*
	 * Game's time update method.
	 */
	public static function updateTime($game){
		return mysql_qw('UPDATE `p_game` SET `time`=? WHERE `id`=?',
				$game->getTime(), $game->getId()
				);
	}

	/*
	 * Game's state update method.
	 */
	public static function updateIsTech($game){
		return mysql_qw(
				'UPDATE `p_game` SET `is_tech`=? WHERE `id`=?',
				$game->getType(),
				$game->getId()
		);
	}

	/*
	 * General insert method.
	 */
	public static function insert($cupId, $stage, $tour, $pmid1, $pmid2, $score1, $score2,
			$time, $isTech) {
		return mysql_qw('INSERT INTO `p_game` SET `cup_id`=?, `stage`=?,
                    `tour`=?, `pmid1`=?, `pmid2`=?, `score1`=?, `score2`=?, `time`=?, `is_tech`=?',
                       $cupId, $stage, $tour, $pmid1, $pmid2, $score1, $score2,	$time, $isTech
		);
	}

	public static function selectById($id) {
		return mysql_qw('SELECT * FROM `p_game` WHERE `id`=?', $id);
	}

	public static function selectAll() {
		return mysql_qw('SELECT `id`, `cup_id`, `stage`, `tour`, `pmid1`, `pmid2`, `score1`,
			`score2`, `time`, `is_tech` FROM `p_game` WHERE 1=1 ORDER BY `id` ASC');
	}

	/**
	 * selects distinct player ids from `p_game` for specified cup
	 * @param int $cupId
	 * @return resource
	 */
	public static function selectPlayersForCup($cupId) {
		return new MySQLResultIterator(mysql_qw('
						SELECT *
						FROM
						(
							(	SELECT
									DISTINCT (`pmid`) AS `id`
								FROM
								(
									(
										SELECT
											`p_game`.`pmid1` AS  `pmid`
										FROM
											`p_game`
										WHERE
											`cup_id`=?
									)

									UNION

									(
										SELECT
											`p_game`.`pmid2` AS  `pmid`
										FROM
											`p_game`
										WHERE
											`cup_id`=?
									)
								) AS `games`
							) AS `ids`

							INNER JOIN
								`p_man`
							ON
								`p_man`.`id`=`ids`.`id`
						)
						', $cupId, $cupId
			   ));
	}

    /**
     * selects id of all games between two pipemen in the cup
     * @param int $pmid1
     * @param int $pmid2
     * @param int $cupId
     */
    public static function selectByPmidsAndCup($pmid1, $pmid2, $cupId) {
        return mysql_qw(
                'SELECT `id` FROM `p_game` WHERE
                `pmid1`=? AND `pmid2`=? AND `cup_id`=?',
                $pmid1, $pmid2, $cupId);
    }

    public static function selectCountGamesFor($pmid, $cupId = 0) {
        if ($cupId == 0) {
            return mysql_qw('SELECT COUNT(*) FROM `p_game` WHERE `pmid1`=? or `pmid2`=?',
                        $pmid, $pmid
                    );
        }

        return mysql_qw('SELECT COUNT(*) FROM `p_game` WHERE (`pmid1`=? or `pmid2`=?) AND `cup_id`=?',
                    $pmid, $pmid, $cupId
                );
    }

    public static function selectCountWin5For($pmid, $cupId) {
        return mysql_qw('SELECT
						COUNT(*)
					FROM
						`p_game`
					WHERE
						(
							(`pmid1`=? AND `score1`=5 AND `score2`<=3)
							OR
							(`pmid2`=? AND `score2`=5 AND `score1`<=3)
						)
					AND
						`stage`=0
					AND
						`cup_id`=?',
				$pmid, $pmid, $cupId);
    }

    public static function selectCountWin6For($pmid, $cupId) {
        return mysql_qw('SELECT
						COUNT(*)
					FROM
						`p_game`
					WHERE
						(
							(`pmid1`=? AND `score1`=6 AND `score2`=4)
							OR
							(`pmid2`=? AND `score2`=6 AND `score1`=4)
						)
					AND
						`stage`=0
					AND
						`cup_id`=?',
				$pmid, $pmid, $cupId);
    }

    public static function selectCountWinbFor($pmid, $cupId) {
        return mysql_qw('SELECT
						COUNT(*)
					FROM
						`p_game`
					WHERE
						(
							(`pmid1`=? AND `score1`>6 AND `score2`<`score1`-1)
							OR
							(`pmid2`=? AND `score2`>6 AND `score1`<`score2`-1)
						)
					AND
						`stage`=0
					AND
						`cup_id`=?',
				$pmid, $pmid, $cupId);
    }

    public static function selectCountLose5For($pmid, $cupId) {
        return mysql_qw('SELECT
						COUNT(*)
					FROM
						`p_game`
					WHERE
						(
							(`pmid1`=? AND `score2`=5 AND `score1`<=3)
							OR
							(`pmid2`=? AND `score1`=5 AND `score2`<=3)
						)
					AND
						`stage`=0 
					AND
						`cup_id`=?',
				$pmid, $pmid, $cupId);
    }

    public static function selectCountLose6For($pmid, $cupId) {
        return mysql_qw('SELECT
						COUNT(*)
					FROM
						`p_game`
					WHERE
						(
							(`pmid1`=? AND `score2`=6 AND `score1`=4)
							OR
							(`pmid2`=? AND `score1`=6 AND `score2`=4)
						)
					AND
						`stage`=0 
					AND
						`cup_id`=?',
				$pmid, $pmid, $cupId);
    }

    public static function selectCountLosebFor($pmid, $cupId) {
        return mysql_qw('SELECT
						COUNT(*)
					FROM
						`p_game`
					WHERE
						(
							(`pmid1`=? AND `score2`>6 AND `score1`<`score2`-1)
							OR
							(`pmid2`=? AND `score1`>6 AND `score2`<`score1`-1)
						)
					AND
						`stage`=0
					AND
						`cup_id`=?',
				$pmid, $pmid, $cupId);
    }

    /**
     * selects all games, played by pipeman with id = parameter value,
     * including score, stage and type of victory.
     * @param <int> $pipeman_id
     */
    public static function selectGames($pipeman_id) {
        return mysql_qw(' SELECT `id`,
                                 `pmid1`,
                                 `pmid2`,
                                 `stage`,
                                 `score1`,
                                 `score2`,
                                 `is_tech` AS `tech`
                          FROM `p_game`
                          WHERE (`pmid1` = ?) OR (`pmid2` = ?)
                          ORDER BY `stage` DESC',
                           $pipeman_id,
                           $pipeman_id );
    }

    /**
     * selects all games, played by pipeman with id = parameter value,
     * including score, and opponent.
     * @param <int> $pipeman_id
     */
    public static function selectOpponentsAndScore($pipeman_id) {
        return mysql_qw(' SELECT `result`.* FROM
                          (
                               (     SELECT  `p_game`.`id`,
                                             `p_game`.`pmid2` AS `opp_id`,
                                             `p_game`.`score1` AS `my_score`,
                                             `p_game`.`score2` AS `opp_score`
                                     FROM `p_game`
                                     WHERE `pmid1` = ?

                               )
                               UNION
                               (     SELECT  `p_game`.`id`,
                                             `p_game`.`pmid1` AS `opp_id`,
                                             `p_game`.`score1` AS `opp_score`,
                                             `p_game`.`score2` AS `my_score`
                                     FROM `p_game`
                                     WHERE `pmid2` = ?
                               )
                          ) `result`
                          ORDER BY `opp_id` DESC',
                          $pipeman_id,
                          $pipeman_id );
    }

    public static function selectGamesBetween($pipeman_id1, $pipeman_id2) {
        return mysql_qw(' SELECT `id`,
                                 `pmid1`,
                                 `pmid2`,
                                 `score1`,
								 `score2`,
                                 `stage`,
                                 `cup_id`,
                                 `is_tech` AS `tech`
                          FROM `p_game`
                          WHERE ((`pmid1` = ?) AND (`pmid2` = ?)) OR ((`pmid1` = ?) AND (`pmid2` = ?))
                          ORDER BY `stage` DESC',
                           $pipeman_id1,
                           $pipeman_id2,
                           $pipeman_id2,
                           $pipeman_id1 );
    }

	public static function removeAllWith($pmid, $cupId) {
		return mysql_qw('DELETE FROM `p_game` WHERE `cup_id`=? AND (`pmid1`=? or `pmid2`=?)',
					$cupId, $pmid, $pmid
				);
	}

	public static function selectFinalFor($cupId) {
		return mysql_qw('SELECT `id` FROM `p_game` WHERE `cup_id`=? AND `stage`=1', $cupId);
	}

	public static function selectBronzeFor($cupId) {
		return mysql_qw('SELECT `id` FROM `p_game` WHERE `cup_id`=? AND `stage`=3', $cupId);
	}

	public static function getParentGameIdFor($gameId) {
		$req = mysql_qw('SELECT 
							`id`, `prev_game_id1`
						FROM
							`p_game`
						WHERE
							`prev_game_id1`=? OR `prev_game_id2`=?
						',
						$gameId,
						$gameId
			);

		if ($r = mysql_fetch_assoc($req)) {
			return $result = array(
				'parent_id' => $r['id'],
				'is_left' => $r['prev_game_id1'] == $gameId
			);
		}
	}

	public static function getMaxStageFor($cup_id) {
		$req = mysql_qw('SELECT MAX(`stage`) AS `max` FROM `p_game` WHERE `cup_id`=? AND `stage`!=3', $cup_id);
		return mysql_result($req, 0, 'max');
	}

	public static function deleteByCupId($cup_id) {
		return mysql_qw('DELETE FROM `p_game` WHERE `cup_id`=?', $cup_id);
	}

	public static function countAll() {
		return mysql_result(
			mysql_qw('SELECT COUNT(*) FROM `p_game`'),
			0, 0
		);
	}

	public static function getByStageAndCupId($stage, $cup_id) {
		return new MySQLResultIterator(
			mysql_qw(
				'SELECT * FROM `p_game` WHERE `stage`=? AND `cup_id`=?',
				$stage, $cup_id
			)
		);
	}
}
?>
