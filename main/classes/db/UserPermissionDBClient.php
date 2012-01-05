<?php

require_once dirname(__FILE__).'/../../includes/mysql.php';

/**
 * Description of UserPermissionDBClient
 *
 * @author Artyom Grigoriev
 */
class UserPermissionDBClient {
    public static function selectByUID($uid) {
        return mysql_qw('SELECT `status`, `target_id` FROM `p_user_permission` WHERE `uid`=?', $uid);
    }

	public static function selectByLeague($leagueId) {
		return mysql_qw('SELECT `uid` FROM `p_user_permission` WHERE (`status`=? AND `target_id`=?) OR `status`=?', 'LA', $leagueId, 'TA');
	}

	public static function insert($uid, $status, $targetId) {
		return (boolean) mysql_qw('INSERT INTO `p_user_permission` SET `uid`=?, `status`=?, `target_id`=?',
					$uid, $status, $targetId
				);
	}

	public static function delete($uid, $status, $targetId) {
		return (boolean) mysql_qw('DELETE FROM `p_user_permission` WHERE `uid`=? AND `status`=? AND `target_id`=?',
					$uid, $status, $targetId
				);
	}
}
?>
