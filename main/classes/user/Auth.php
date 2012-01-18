<?php

require_once dirname(__FILE__).'/User.php';
require_once dirname(__FILE__).'/Mobile_Detect.php';

require_once dirname(__FILE__).'/../db/UserDataDBClient.php';

require_once dirname(__FILE__).'/../social/Vkontakte.php';

require_once dirname(__FILE__) . '/../../includes/config-local.php';

/**
 * Description of Auth
 *
 * @author Artyom Grigoriev
 */
class Auth {

	private $currentUser;
	private $currentUserLoaded = false;

	private $vk;

	private $mobileDetector;

    public function __construct() {
        @session_start();
        if (!isset($_SESSION['uid'])) $this->loginCookie();
		$this->mobileDetector = new Mobile_Detect();
    }

    public function uid() {
        return isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
    }

	public function isAuth() {
		return ($this->uid() > 0);
	}

	public function isMobile() {
		return $this->mobileDetector->isMobile();
	}

	public function isVkontakteAuth() {
		if (!isset($this->vk)) return false;
		return ($this->vk->getId() > 0);
	}

	public function getVkid() {
		return $this->vk->getId();
	}

	/**
	 * @return User
	 */
	public function getCurrentUser() {
		if ($this->currentUserLoaded) return $this->currentUser;

		try {
			$this->currentUser = User::getById($this->uid());
		} catch (Exception $e) {
			// TODO use error log file
			$this->currentUser = null;
		}

		$this->currentUserLoaded = true;
		return $this->currentUser;
	}

    public function login($login, $md5pass, $setCookie = false) {
        if ($uid = UserDataDBClient::isSuchUser($login, $md5pass)) {
			$_SESSION['uid'] = $uid;
			if ($setCookie) $this->setCookie($uid, $md5pass);
			return $uid;
		}

		return 0;
    }

	public function loginUidPass($uid, $md5pass, $setCookie = false) {
		if ($uid = UserDataDBClient::isSuchUserByUid($uid, $md5pass)) {
			$_SESSION['uid'] = $uid;
			if ($setCookie) $this->setCookie($uid, $md5pass);
			return $uid;
		}

		return 0;
	}

	private function setCookie($uid, $ucode) {
		setcookie('uid', $uid, time() + COOKIES_EXPIRE, '/', COOKIES_DOMAIN, COOKIES_SECURE, COOKIES_HTTP);
		setcookie('ucode', $ucode, time() + COOKIES_EXPIRE, '/', COOKIES_DOMAIN, COOKIES_SECURE, COOKIES_HTTP);
	}
	
	private function setTempCookie($uid, $utcode) {
		setcookie('uid', $uid, time() + COOKIES_EXPIRE, '/', COOKIES_DOMAIN, COOKIES_SECURE, COOKIES_HTTP);
		setcookie('utcode', $utcode, time() + COOKIES_EXPIRE, '/', COOKIES_DOMAIN, COOKIES_SECURE, COOKIES_HTTP);
	}

    public function loginCookie() {
        if (isset($_COOKIE['uid']) && isset($_COOKIE['ucode'])) {
            return $this->loginUidPass($_COOKIE['uid'], $_COOKIE['ucode']);
        }
		
		if (isset($_COOKIE['uid']) && isset($_COOKIE['utcode'])) {
			return $this->loginUidPass($_COOKIE['uid'], $_COOKIE['utcode']);
		}

        return 0;
    }

	public function loginVkontakte() {
		$this->vk = new Vkontakte();
		$status = $this->vk->login();
		if ($status == ISocialWeb::FULL_SUCCESS) {
			$user_ = $this->getCurrentUser();
			$passhash = $user_->get(User::KEY_PASSHASH);
			if (!$passhash) {
				$this->saveTempPass();
			} else {
				$this->setCookie($user_->getId(), $passhash);
			}	
		}
		return $status;
	}

	public function loginFacebook() {
		// TODO implement this method
	}

	public function loginTwitter() {
		// TODO implement this method
	}

	public function logOut() {
		if (!$this->isAuth()) return;
		$this->destroy();
	}

	public function sessionPut($key, $value) {
		$_SESSION[$key] = $value;
	}

	public function sessionGet($key) {
		return $_SESSION[$key];
	}

	private function destroy() {
		$_SESSION['uid'] = 0;
		unset($_SESSION['uid']);
		setcookie('uid', 0, time(), '/', COOKIES_DOMAIN, COOKIES_SECURE, COOKIES_HTTP);
		setcookie('ucode', '', time(), '/', COOKIES_DOMAIN, COOKIES_SECURE, COOKIES_HTTP);
		$this->currentUserLoaded = false;
	}
	
	private function generatePassword() {
		$password = "";
		for ($i = 0; $i < 20; $i++) {
			$character = mt_rand(32, 128);
			$password .= chr($character);
		}
		return $password;
	}
	
	private function saveTempPass() {
		if (!$this->currentUser->get(User::KEY_TEMP_PASSHASH)) {
			$password = $this->generatePassword();
			$utcode = md5($password);
			$this->currentUser->put(User::KEY_TEMP_PASSHASH, $utcode);
		} else {
			$utcode = $this->currentUser->get(User::KEY_TEMP_PASSHASH);
		}		
		$this->setTempCookie($this->currentUser->getId(), $utcode);
	}
	
}
?>
