<?php

require_once dirname(__FILE__).'/../../includes/mysql.php';

require_once dirname(__FILE__).'/../cupms/ResultTable.php';

/**
 * Description of ResultTableDBClient
 *
 * @author Artyom Grigoriev aka ortemij
 */
class ResultTableDBClient {
	/**
	 * SELECT * FROM `p_man_cup_table` WHERE `pmid`=$pmid and `cup_id`=$cupId
	 * @param int $pmid
	 * @param int $cupId
	 * @return resource
	 */
    public static function select($pmid, $cupId) {
		return mysql_qw('SELECT * FROM `p_man_cup_table` WHERE `pmid`=? and `cup_id`=?', $pmid, $cupId);
	}

	/**
	 * UPDATE `p_man_cup_table` SET ... WHERE `id`=?
	 * @param ResultTable $resultTable
	 * @return resource
	 */
	public static function update($resultTable) {
		return mysql_qw('UPDATE `p_man_cup_table` SET `pmid`=?, `cup_id`=?, `place`=?, `games`=?, `points`=?, `win5`=?, `win6`=?, `winb`=?, `lose5`=?, `lose6`=?, `loseb`=? WHERE `id`=?',
					$resultTable->getPmid(),
					$resultTable->getCupId(),
                    $resultTable->getPlace(),
                    $resultTable->getGames(),
                    $resultTable->getPoints(),
					$resultTable->getWin5(),
					$resultTable->getWin6(),
					$resultTable->getWinb(),
					$resultTable->getLose5(),
					$resultTable->getLose6(),
					$resultTable->getLoseb(),
					$resultTable->getId()
				);
	}

    public static function insert($resultTable) {
        return mysql_qw('INSERT INTO `p_man_cup_table` SET `pmid`=?, `cup_id`=?, `place`=?, `games`=?, `points`=?, `win5`=?, `win6`=?, `winb`=?, `lose5`=?, `lose6`=?, `loseb`=?',
					$resultTable->getPmid(),
					$resultTable->getCupId(),
                    $resultTable->getPlace(),
                    $resultTable->getGames(),
                    $resultTable->getPoints(),
					$resultTable->getWin5(),
					$resultTable->getWin6(),
					$resultTable->getWinb(),
					$resultTable->getLose5(),
					$resultTable->getLose6(),
					$resultTable->getLoseb()
				);
    }

    /**
     * @param int $cupId
     * @return resource
     */
    public static function selectPmidsForCup($cupId) {
        return mysql_qw('SELECT `pmid` FROM `p_man_cup_table` WHERE `cup_id`=? ORDER BY `place`, `pmid`', $cupId);
    }

    public static function selectPmidByPlaceInCup($place, $cupId) {
        return mysql_qw('SELECT `pmid` FROM `p_man_cup_table` WHERE `place`=? and `cup_id`=?', $place, $cupId);
    }

	public static function insertPmidInCup($pmid, $cupId) {
		$place = mysql_num_rows(self::selectPmidsForCup($cupId)) + 1;
		return (boolean) mysql_qw('INSERT INTO `p_man_cup_table` SET `pmid`=?, `cup_id`=?, `place`=?', $pmid, $cupId, $place);
	}

	public static function deleteByCupId($cup_id) {
		return mysql_qw('DELETE FROM `p_man_cup_table` WHERE `cup_id`=?', $cup_id);
	}

	public static function deletePlayer($cupId, $pmid) {
		return (boolean) mysql_qw('DELETE FROM `p_man_cup_table` WHERE `cup_id`=? and `pmid`=?', $cupId, $pmid);
	}
}
?>
