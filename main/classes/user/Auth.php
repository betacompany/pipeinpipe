<?php

require_once dirname(__FILE__).'/User.php';
require_once dirname(__FILE__).'/Mobile_Detect.php';

require_once dirname(__FILE__).'/../db/UserDataDBClient.php';

require_once dirname(__FILE__).'/../social/Vkontakte.php';

require_once dirname(__FILE__) . '/../../includes/config-local.php';

require_once dirname(__FILE__) . '/../../../auth/common-auth.php';

/**
 * Description of Auth
 *
 * @author Artyom Grigoriev
 */
class Auth {

	const KEY_USE_MOBILE = 'use_mobile';
	const KEY_USE_MOBILE_SESSION = 'use_mobile_session';

	private $currentUser;
	private $currentUserLoaded = false;

	private $vk;

	private $mobileDetector;

    public function __construct() {
        $this->mobileDetector = new Mobile_Detect();
    }

    public function uid() {
        global $_COMMON_USER_ID;
		return $_COMMON_USER_ID;
    }

	public function isAuth() {
		return ($this->uid() > 0);
	}

	public function isMobile() {
		return $this->mobileDetector->isMobile() || isset($_COOKIE['i_am_a_mobile_hacker']);
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

		if ($this->uid() > 0) {
			try {
				$this->currentUser = User::getById($this->uid());
			} catch (Exception $e) {
				// TODO use error log file
				$this->currentUser = null;
			}
		} else {
			$this->currentUser = null;
		}

		$this->currentUserLoaded = true;
		return $this->currentUser;
	}

	public function loginVkontakte() {
		$this->vk = new Vkontakte();
		$status = $this->vk->login();
		if ($status == ISocialWeb::FULL_SUCCESS) {
			$user_ = $this->getCurrentUser();
			CommonAuth::forceSignIn($user_->getId(), false);
		}
		return $status;
	}

	public function loginFacebook() {
		// TODO implement this method
	}

	public function loginTwitter() {
		// TODO implement this method
	}

	public function sessionPut($key, $value) {
		$_SESSION[$key] = $value;
	}

	public function sessionGet($key) {
		return $_SESSION[$key];
	}

	private function generatePassword() {
		$password = "";
		for ($i = 0; $i < 20; $i++) {
			$character = mt_rand(32, 128);
			$password .= chr($character);
		}
		return $password;
	}
}
?>
