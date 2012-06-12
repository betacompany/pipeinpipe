<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ortemij
 * Date: 11.06.12
 * Time: 8:37
 */

require_once dirname(__FILE__) . '/properties/commons.php';

$_COMMON_USER_ID = 0;

class CommonAuth {

	/**
	 * Defines global variable <code>$_COMMON_USER_ID</code>
	 * @static
	 */
	public static function authorize() {
		$token = get_token();
		if (!$token) {
			return;
		}
		$uid = get_uid($token);
		if ($uid <= 0) {
			return;
		}
		$a = get_login_password($uid);
		if (!$a) {
			return;
		}
		if (!self::verify($token, $a['login'], $a['hash'])) {
			return;
		}
		global $_COMMON_USER_ID;
		$_COMMON_USER_ID = $uid;

		// session prolongation
		if (!is_session_only()) set_token($uid, $token);
	}

	/**
	 * @static
	 * @param string $login
	 * @param string $password
	 * @param bool $short_session
	 * @return bool
	 */
	public static function signIn($login, $password, $short_session = false) {
		$hash = md5($password);
		$uid = select_uid($login, $hash);
		if ($uid <= 0) {
			return false;
		}

		global $_COMMON_USER_ID;
		$_COMMON_USER_ID = $uid;

		$token = self::token($login, $hash);
		set_token($uid, $token, $short_session);

		return true;
	}

	/**
	 * @static
	 *
	 */
	public static function signOut() {
		delete_token(get_token());
	}

	private static function token($login, $hash) {
		$str = get_secret();
		$str .= $login;
		$str .= $hash;
		$str .= filter_chars($_SERVER['HTTP_USER_AGENT']);
		return md5($str);
	}

	private static function verify($token, $login, $hash) {
		$expected = self::token($login, $hash);
		return $token === $expected;
	}
}

CommonAuth::authorize();


