<?php
/* 
 * This class contains static database methods for class Player.
 *
 * @author Innokenty Shuvalov
 */

require_once dirname(__FILE__).'/../../includes/mysql.php';
require_once dirname(__FILE__).'/../cupms/Player.php';

class PlayerDBClient {

    public static function update($player) {
        return mysql_qw('UPDATE `p_man` SET `name` = ?,
                                            `surname` = ?,
                                            `gender` = ?,
                                            `country` = ?,
                                            `city` = ?,
                                            `email` = ?,
                                            `description` = ?
										WHERE
											`id`=?
						',
                                            $player->getName(),
                                            $player->getSurname(),
                                            $player->getGender(),
                                            $player->getCountry(),
                                            $player->getCity(),
                                            $player->getEmail(),
                                            $player->getDescription(),
                                            $player->getId());
    }

    public static function insert($name, $surname, $gender, $country, $city,
                                    $email, $description) {
            mysql_qw('INSERT INTO `p_man` SET   `name`=?,
                                                `surname`=?,
                                                `gender`=?,
                                                `country`=?,
                                                `city`=?,
                                                `email`=?,
                                                `description`=?',
                                                $name,
                                                $surname,
                                                $gender,
                                                $country,
                                                $city,
                                                $email,
                                                $description
                                                );

    }

    public static function selectById($id) {
		return mysql_qw('SELECT * FROM `p_man` WHERE `id`=?', $id);
    }

	public static function getAll() {
		return mysql_qw('SELECT * FROM `p_man`');
	}

	public static function countAll() {
		return mysql_result(
			mysql_qw('SELECT COUNT(*) FROM `p_man`'),
			0, 0
		);
	}

	public static function getGenderByName($name) {
		$req = mysql_qw('SELECT COUNT(*) AS `c`, `gender` FROM `p_man` WHERE `name`=? GROUP BY `gender` ORDER BY `c` DESC', $name);
		return mysql_num_rows($req) ? mysql_result($req, 0, 0) : false;
	}
}

?>
