<?php

require_once dirname(__FILE__).'/../../includes/mysql.php';

/**
 * Description of UserDataDBClient
 *
 * @author Artyom Grigoriev
 */
class UserDataDBClient {
    public static function selectByUID($uid) {
        return mysql_qw('SELECT `key`, `value` FROM `p_user_data` WHERE `uid`=?', $uid);
    }

    public static function update($uid, $key, $value) {
		if ($key == User::KEY_LOGIN) {
			mysql_qw('UPDATE `common_user` SET `login`=? WHERE `id`=?', $value, $uid);
		} elseif ($key == User::KEY_PASSHASH) {
			mysql_qw('UPDATE `common_user` SET `hash`=? WHERE `id`=?', $value, $uid);
		}
        return mysql_qw('UPDATE `p_user_data` SET `value`=? WHERE `uid`=? and `key`=?', $value, $uid, $key);
    }

    public static function insert($uid, $key, $value) {
		if ($key == User::KEY_LOGIN) {
			mysql_qw('UPDATE `common_user` SET `login`=? WHERE `id`=?', $value, $uid);
		} elseif ($key == User::KEY_PASSHASH) {
			mysql_qw('UPDATE `common_user` SET `hash`=? WHERE `id`=?', $value, $uid);
		}
        return mysql_qw('INSERT INTO `p_user_data` SET `uid`=?, `key`=?, `value`=?', $uid, $key, $value);
    }

    public static function delete($uid, $key) {
        return mysql_qw('DELETE FROM `p_user_data` WHERE `uid`=? AND `key`=?', $uid, $key);
    }

	public static function getUIDByVkId($vkId) {
		$req = mysql_qw('SELECT `uid` FROM `p_user_data` WHERE `key`=\'vkid\' and `value`=?', $vkId);
		if ($row = mysql_fetch_assoc($req)) {
			return $row['uid'];
		} else {
			return 0;
		}
	}

	public static function getUIDByPmid($pmid) {
		$req = mysql_qw('SELECT `uid` FROM `p_user_data` WHERE `key`=\'pmid\' and `value`=?', $pmid);
		if ($row = mysql_fetch_assoc($req)) {
			return $row['uid'];
		} else {
			return 0;
		}
	}

	// agg_vk_access_token

	public static function getAccessTokenFor($vkid) {
		$req = mysql_qw('SELECT * FROM agg_vk_access_token WHERE user_id=? AND (expires_timestamp=0 OR expires_timestamp<?)', $vkid, time());
		if ($row = mysql_fetch_assoc($req)) {
			return $row['access_token'];
		}
		return false;
	}

	public static function insertAccessToken($vkid, $access_token, $expires = 0) {
		return mysql_qw('REPLACE INTO agg_vk_access_token (user_id, access_token, expires_timestamp) VALUES (?, ?, ?)', $vkid, $access_token, $expires);
	}
}
?>
