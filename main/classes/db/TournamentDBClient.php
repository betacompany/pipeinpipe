<?php

require_once dirname(__FILE__).'/../../includes/mysql.php';

require_once dirname(__FILE__).'/../cupms/Tournament.php';

/**
 * Description of TournamentDBClient
 * 
 * @author Artyom Grigoriev aka ortemij
 */
class TournamentDBClient {
    public static function update($tournament) {
		return mysql_qw('UPDATE `p_tournament` SET `name`=?, `description`=? WHERE `id`=?',
							$tournament->getName(), $tournament->getDescription(), $tournament->getId()
						);
	}

	public static function insert($name, $description) {
		return mysql_qw('INSERT INTO `p_tournament` SET `name`=?, `description`=?', $name, $description);
	}

	public static function selectById($id) {
		return mysql_qw('SELECT * FROM `p_tournament` WHERE `id`=?', $id);
	}

	public static function selectAll() {
		return mysql_qw('SELECT `id`, `name`, `description` FROM `p_tournament` WHERE 1=1 ORDER BY `id` ASC');
	}
}
?>
