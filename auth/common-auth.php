<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ortemij
 * Date: 11.06.12
 * Time: 8:37
 */

require_once dirname(__FILE__) . '/properties/commons.php';

class CommonAuth {

	/**
	 * @static
	 * @return CommonUser or <code>null</code> if authorization failed
	 */
	public static function authorize() {
		$token = get_token();
		$uid = get_uid($token);
		$commonUser = new CommonUser($uid);
		$login = $commonUser->getLogin();
		$hash = $commonUser->getHash();
		if (!self::verify($token, $login, $hash)) {
			return null;
		}
		return $commonUser;
	}

	private static function verify($token, $login, $hash) {
		$str = get_secret();
		$str .= $login;
		$str .= $hash;
		$str .= filter_chars($_SERVER['HTTP_USER_AGENT']);
		echo $str, "<br/>";
		$expected = md5($str);
		echo $expected;
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


