<?php

require_once dirname(__FILE__) . '/ISocialWeb.php';

require_once dirname(__FILE__) . '/../db/UserDataDBClient.php';

/**
 * @link http://vkontakte.ru/developers.php?o=-1&p=VK.Auth
 * @author ortemij
 */
class Vkontakte implements ISocialWeb {

	const VK_APP_ID = 1969436;
	const VK_APP_SHARED_SECRET = "f4MQlBZzzsJz0XfrtjnU";

	private $data;
	private $authorized = false;

    public function login() {
		$session = array();
		$this->data = array();
		$valid_keys = array('expire', 'mid', 'secret', 'sid', 'sig');

		// get data set from cookies
		$app_cookie = $_COOKIE['vk_app_' . self::VK_APP_ID];

		if ($app_cookie) {
			// start to parse data set
			$session_data = explode('&', $app_cookie, 10);

			foreach ($session_data as $pair) {
				list($key, $value) = explode('=', $pair, 2);

				if (empty($key) || empty($value) || !in_array($key, $valid_keys)) {
					continue;
				}

				// store all valid data into $session array
				$session[$key] = $value;
			}

			// verify if all required keys are setted
			foreach ($valid_keys as $key) {
				if (!isset($session[$key]))	return 0;
			}

			// sort $session by keys
			ksort($session);

			// start to make a sign
			$sign = '';
			foreach ($session as $key => $value) {
				if ($key != 'sig') {
					$sign .= ($key . '=' . $value);
				}
			}

			$sign .= self::VK_APP_SHARED_SECRET;
			$sign = md5($sign);

			// verify sig
			if ($session['sig'] == $sign && $session['expire'] > time()) {

				// store all useful data into special field of Auth object
				$this->data = array(
					'id' => intval($session['mid']),
					'secret' => $session['secret'],
					'sid' => $session['sid']
				);
			}
		}

		if (!empty($this->data)) {

			// get UID from DB binded with this VK id
			$uid = UserDataDBClient::getUIDByVkId($this->data['id']);

			if ($uid == 0) {
				$this->authorized = true;
				return self::SUCCESS;
			}

			$_SESSION['uid'] = $uid;
			$this->authorized = true;
			
			return self::FULL_SUCCESS;
		}

		return self::FAIL;
	}

	public function getId() {
		return isset($this->data['id']) ? $this->data['id'] : 0;
	}
}
?>
