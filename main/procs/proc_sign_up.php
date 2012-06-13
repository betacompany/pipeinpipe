<?php

require_once dirname(__FILE__) . '/../includes/error.php';
require_once dirname(__FILE__) . '/../includes/assertion.php';

require_once dirname(__FILE__) . '/../classes/user/Auth.php';

require_once dirname(__FILE__) . '/../../auth/common-auth.php';

$d = array();

function sign_up_error_redirect($error) {
	global $d;

	//print_r($d);

	$result = '';
	foreach (array ('login', 'email', 'name', 'surname') as $key) {
		$result .= '&' . $key . '=' . $d[$key];
	}
	Header('Location: /sign_up#error=signUp.' . $error . $result);
	exit(0);
}

function sign_up_proof_input($key, &$value) {
	if ($key == 'email') {
		if (!($value = filter_var($value, FILTER_VALIDATE_EMAIL))) return false;
	}
	$users = User::getByKey($key, $value);
	return empty ($users);
}

try {

	assertIsset($_REQUEST['method']);

	$auth = new Auth();
	assertTrue('This function is not available for registered users', !$auth->isAuth());
	
	switch ($_REQUEST['method']) {

	/*
	 * @param field_name
	 * @param field_value
	 */
	case 'proof_input':
		
		assertIsset($_REQUEST['field_name']);
		assertIsset($_REQUEST['field_value']);

		$key = $_REQUEST['field_name'];
		$value = string_convert($_REQUEST['field_value']);

		if (empty ($value)) {
			echo json(array (
				'status' => 'ok',
				'result' => 'false'
			));
			exit(0);
		}

		if (sign_up_proof_input($key, $value)) {
			echo json(array (
				'status' => 'ok',
				'result' => 'true'
			));
		} else {
			echo json(array (
				'status' => 'ok',
				'result' => 'false'
			));
		}

		break;

	case 'sign_up':

		//print_r($_REQUEST);

		$d['login'] = $_REQUEST['sign_up_login'];
		$d['password1'] = $_REQUEST['sign_up_password1'];
		$d['password2'] = $_REQUEST['sign_up_password2'];
		$d['name'] = $_REQUEST['sign_up_name'];
		$d['surname'] = $_REQUEST['sign_up_surname'];
		$d['email'] = $_REQUEST['sign_up_email'];

		//print_r($_REQUEST);

		//print_r($d);

		if (isset($_REQUEST['sign_up_vkid']))
			$d['vkid'] = intval($_REQUEST['sign_up_vkid']);

		if (empty ($d['login'])) sign_up_error_redirect('empty_login');
		if (empty ($d['password1'])) sign_up_error_redirect('empty_password1');
		if (empty ($d['password2'])) sign_up_error_redirect('empty_password1');
		if (empty ($d['email'])) sign_up_error_redirect('empty_email');
		if (empty ($d['name'])) sign_up_error_redirect('empty_name');
		if (empty ($d['surname'])) sign_up_error_redirect('empty_surname');

		if (!sign_up_proof_input('login', $d['login']))
			sign_up_error_redirect ('occupied_login');
		if (!sign_up_proof_input('email', $d['email']))
			sign_up_error_redirect ('occupied_email');

		if (isset($d['vkid'])
				&& $d['vkid'] != 0
				&& !sign_up_proof_input('vkid', $d['vkid']))
			sign_up_error_redirect('occupied_vkid');

		if ($d['password1'] !== $d['password2'])
			sign_up_error_redirect('different_password1_s');

		$d['login'] = string_process($d['login']);
		if ($_REQUEST['sign_up_login'] != $d['login'])
			sign_up_error_redirect('incorrect_login');

		$d['name'] = string_process($d['name']);
		$d['surname'] = string_process($d['surname']);
		$d['email'] = string_process($d['email']);

		$auth = new Auth();
		$vkid = 0;

		if (isset($d['vkid']) && $d['vkid'] > 0) {
			$login = $auth->loginVkontakte();
			if ($login == ISocialWeb::SUCCESS) {
				$vkid = $auth->getVkid();
			} elseif ($login == ISocialWeb::FULL_SUCCESS) {
				$uid = $auth->getCurrentUser()->getId();
				Header('Location: /id' . $uid);
				exit(0);
			} else {
				sign_up_error_redirect('vkontakteAuthFailed');
			}
		}

		$u = User::create($d['name'], $d['surname']);
		if ($u != null) {
			$u->put('passhash', md5($d['password1']));
			$u->put('login', $d['login']);
			$u->put('email', $d['email']);

			if ($vkid > 0) {
				$u->put('vkid', $vkid);
			}

			CommonAuth::signIn($d['login'], $d['password1']);
			if (issetParam('ret')) {
				$location = urldecode(param('ret'));
				$totalizator = 'http://total.' . MAIN_SITE_URL;
				if (substr($location, 0, 6) === '/sport' || $location === $totalizator) {
					Header('Location: ' . $location);
				}
			} else {
				Header('Location: /id' . $u->getId());
			}
			exit(0);
		} else {
			sign_up_error_redirect('userCreationFailed');
		}

		break;
	}

} catch (Exception $e) {
	echo_json_exception($e);
}

?>
