<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ortemij
 * Date: 11.06.12
 * Time: 8:37
 */

require_once dirname(__FILE__) . '/properties/commons.php';

$_COMMON_USER = false;

class CommonAuth {

	/**
	 * Defines global variable <code>$_COMMON_USER</code>
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
		$commonUser = new CommonUser($uid);
		$login = $commonUser->getLogin();
		$hash = $commonUser->getHash();
		if (!self::verify($token, $login, $hash)) {
			return;
		}
		global $_COMMON_USER;
		$_COMMON_USER = $commonUser;

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

		$commonUser = new CommonUser($uid);
		global $_COMMON_USER;
		$_COMMON_USER = $commonUser;

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

class CommonUser {
	private $id;
	private $login;
	private $hash;
	private $name;
	private $surname;

	function __construct($id) {
		$data = get_user($id);
		if (!$data) {
			throw new Exception("Invalid user id = " . $id);
		}
		$this->id = $data['id'];
		$this->login = $data['login'];
		$this->hash = $data['hash'];
		$this->name = $data['name'];
		$this->surname = $data['surname'];
	}

	public function getHash() {
		return $this->hash;
	}

	public function getId() {
		return $this->id;
	}

	public function getLogin() {
		return $this->login;
	}

	public function getName() {
		return $this->name;
	}

	public function getSurname() {
		return $this->surname;
	}
}

CommonAuth::authorize();


