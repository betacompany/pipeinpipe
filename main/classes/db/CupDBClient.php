<?php

require_once dirname(__FILE__).'/../../includes/mysql.php';

/**
 * Description of CupDBClient
 *
 * @author Artyom Grigoriev aka ortemij
 * @author Solozobov Andrew
 */
class CupDBClient {

	public static function update(Cup $cup){
		return (boolean) mysql_qw('UPDATE `p_cup` SET
		                            `competition_id`=?,
		                            `parent_cup_id`=?,
		                            `name`=?,
		                            `type`=?,
		                            `multiplier`=?
		                          WHERE `id`=?',
		                            $cup->getCompetitionId(),
		                            $cup->getParentCupId(),
		                            $cup->getName(),
		                            $cup->getType(),
		                            $cup->getMultiplier(),
									$cup->getId()
		                          );
	}

	public static function selectTypeFor($id) {
		return mysql_qw('SELECT `type` FROM `p_cup` WHERE `id`=?', $id);
	}

	public static function selectById($id) {
		return mysql_qw('SELECT * FROM `p_cup` WHERE `id`=?', $id);
	}

	public static function selectAllRegular() {
		return mysql_qw('SELECT `id`, `type` FROM `p_cup` WHERE `type`=? or `type`=? ORDER BY `id`',
		                Cup::TYPE_ONE_LAP, Cup::TYPE_TWO_LAPS
		               );
	}

	public static function selectByCompetitionId($compId) {
		return mysql_qw('SELECT `id` FROM `p_cup` WHERE `competition_id`=? ORDER BY `parent_cup_id` ASC, `id` ASC', $compId);
	}

	public static function insert($competitionId, $parentCupId, $name, $type, $multiplier = -1) {
		return mysql_qw('INSERT INTO `p_cup` SET `competition_id`=?, `parent_cup_id`=?, `name`=?, `type`=?, `multiplier`=?',
		          			$competitionId, $parentCupId, $name, $type, $multiplier
				            );
	}

	//finds the playoff cups
	public static function selectZeroParentById($comp_id){
		return mysql_qw('SELECT `id` FROM  `p_cup` WHERE `competition_id`=? AND `parent_cup_id`=0', $comp_id);
	}

	public static function selectChildrenFor($cup_id) {
		return mysql_qw('SELECT `id` FROM `p_cup` WHERE `parent_cup_id`=?', $cup_id);
	}

	public static function getCompetitionIdFor($cup_id) {
		$req = mysql_qw('SELECT `competition_id` FROM `p_cup` WHERE `id`=?', $cup_id);
		if ($result = mysql_fetch_assoc($req)) {
			return $result['competition_id'];
		}

		return 0;
	}

	//возвращает true, если удаление прошло удачно, при неудаче - false
	public static function deleteById($cup_id) {
		return (boolean) mysql_qw('DELETE FROM `p_cup` WHERE `id`=?', $cup_id);
	}

	//возвращает true, если удаление прошло удачно, при неудаче - false
	public static function deleteByCompetitionId($comp_id) {
		return (boolean) mysql_qw('DELETE FROM `p_cup` WHERE `competition_id`=?', $comp_id);
	}

	public static function selectByName($comp_id, $cup_name) {
		return mysql_qw('SELECT * FROM `p_cup` WHERE `competition_id`=? AND `name`=?', $comp_id, $cup_name);
	}
}
?>
