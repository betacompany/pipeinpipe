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
        return db_qw('UPDATE `p_man` SET `name` = ?,
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
            db_qw('INSERT INTO `p_man` SET   `name`=?,
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
		return db_qw('SELECT * FROM `p_man` WHERE `id`=?', $id);
    }

	public static function getAll() {
		return db_qw('SELECT * FROM `p_man`');
	}

	public static function countAll() {
		return db_result(
			db_qw('SELECT COUNT(*) FROM `p_man`'),
			0, 0
		);
	}

	public static function getGenderByName($name) {
		$req = db_qw('SELECT COUNT(*) AS `c`, `gender` FROM `p_man` WHERE `name`=? GROUP BY `gender` ORDER BY `c` DESC', $name);
		return db_num_rows($req) ? db_result($req, 0, 0) : false;
	}
}

?>
