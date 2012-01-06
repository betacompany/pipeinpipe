<?php

require_once dirname(__FILE__) . '/../classes/user/Auth.php';
require_once dirname(__FILE__) . '/../classes/user/User.php';

require_once dirname(__FILE__) . '/../includes/assertion.php';
require_once dirname(__FILE__) . '/../includes/common.php';
require_once dirname(__FILE__) . '/../includes/error.php';
require_once dirname(__FILE__) . '/../includes/config-local.php';

require_once dirname(__FILE__) . '/../views/blocks.php';

require_once dirname(__FILE__) . '/../classes/social/ISocialWeb.php';

require_once dirname(__FILE__) . '/../classes/db/UserDBClient.php';

require_once dirname(__FILE__) . '/../classes/db/UserDataDBClient.php';

try {

	assertIsset($_REQUEST['method']);

	$auth = new Auth();
	if ($auth->isAuth()) {

		switch ($_REQUEST['method']) {

		case 'sign_out':
			$auth->logOut();
			Header('Location: ' . $_SERVER['HTTP_REFERER']);
			exit(0);

		case 'profile_update':
			assertIsset('key');
			assertIsset('value');

			$key = param('key');
			assertTrue('This key is not editable', array_contains(User::getEditableKeys(), $key));
			$value = textparam('value');
			$value = string_process($value);

			$user = $auth->getCurrentUser();
			$user->put($key, $value);

			echo json(array (
				'status' => 'ok',
				'value' => $value
			));

			exit(0);

		case 'upload_photo':
			$file = $_FILES['photo'];
			$tmp = $file['tmp_name'];
			if (@is_uploaded_file($tmp)) {
				$user = $auth->getCurrentUser();
				
				$img = imagecreatefromjpeg($tmp);
				$random = $user->getId() . '_' . substr(md5(mt_rand(0, 1000) * time()), 10, 10);

				if (imagesx($img) < 200) {
					Header('Location: /profile/edit#error=profile.smallPhoto');
					exit(0);
				} elseif (imagesx($img) > 2000 || imagesy($img) > 2000) {
					Header('Location: /profile/edit#error=profile.bigPhoto');
					exit(0);
				}

				$img_resized = imagecreatetruecolor(200, 200 * imagesy($img) / imagesx($img));
				imagecopyresampled($img_resized, $img, 0, 0, 0, 0,
					imagesx($img_resized), imagesy($img_resized), imagesx($img), imagesy($img));

				imagejpeg($img_resized, dirname(__FILE__) . '/../images/users/' . $random . User::IMAGE_NORMAL);

				$old = $user->get(User::KEY_PHOTO);
				if ($old) {
					@unlink(dirname(__FILE__) . '/../images/users/' . $old . User::IMAGE_NORMAL);
					@unlink(dirname(__FILE__) . '/../images/users/' . $old . User::IMAGE_SQUARE);
					@unlink(dirname(__FILE__) . '/../images/users/' . $old . User::IMAGE_SQUARE_SMALL);
				}

				$user->put(User::KEY_PHOTO, $random);

				Header('Location: /profile/edit#photo=ok');
				exit(0);
			}

			Header('Location: /profile/edit#error=profile.noFileUploaded');
			exit(0);

		case 'miniature':
			assertIsset('x'); $x = intparam('x');
			assertIsset('y'); $y = intparam('y');
			assertIsset('w'); $w = intparam('w');
			assertIsset('h'); $h = intparam('h');

			assertTrue('Size is too small', $w >= 100);
			assertTrue('It is not square', $h == $w);

			$user = $auth->getCurrentUser();
			
			if (!$user->hasImage(User::IMAGE_NORMAL)) {
				echo json(array (
					'status' => 'failed',
					'reason' => 'no photo'
				));
				exit(0);
			}

			$photo = $user->getImageURL(User::IMAGE_NORMAL);
			$photo_prefix = $user->get(User::KEY_PHOTO);
			@unlink(dirname(__FILE__) . '/../images/users/' . $photo_prefix . User::IMAGE_SQUARE);
			@unlink(dirname(__FILE__) . '/../images/users/' . $photo_prefix . User::IMAGE_SQUARE_SMALL);

			$photo_file = dirname(__FILE__) . '/..' . $photo;
			$img = imagecreatefromjpeg($photo_file);

			assertTrue('Invalid size',
				$w <= imagesx($img) &&
				$h <= imagesy($img) &&
				$w > 0 && $h > 0);

			global $LOG;

			@$LOG->info("new miniatures by user creation started");

			$small_img = imagecreatetruecolor(100, 100);
			imagecopyresampled($small_img, $img, 0, 0, $x, $y, 100, 100, $w, $h);

			$random = $user->getId() . '_' . substr(md5(mt_rand(0, 1000) * time()), 10, 10);
			rename($photo_file, dirname(__FILE__) . '/../images/users/' . $random . User::IMAGE_NORMAL);
			$user->put(User::KEY_PHOTO, $random);

			imagejpeg($small_img,
				dirname(__FILE__) . '/../images/users/' . $random . User::IMAGE_SQUARE);

			$supersmall_img = imagecreatetruecolor(20, 20);
			imagecopyresampled($supersmall_img, $small_img, 0, 0, 0, 0, 20, 20, 100, 100);
			imagejpeg($supersmall_img,
				dirname(__FILE__) . '/../images/users/' . $random . User::IMAGE_SQUARE_SMALL);

			echo json(array (
				'status' => 'ok',
				'small' => $user->getImageURL(User::IMAGE_SQUARE),
				'supersmall' => $user->getImageURL(User::IMAGE_SQUARE_SMALL)
			));

			@$LOG->info("new miniatures by user creation finished");

			imagedestroy($img);
			imagedestroy($small_img);
			imagedestroy($supersmall_img);

			exit(0);

		case 'unlink_miniatures':

			$user = $auth->getCurrentUser();
			$photo_prefix = $user->get(User::KEY_PHOTO);

			if ($photo_prefix) {
				$sq_file = dirname(__FILE__) . '/../images/users/' . $photo_prefix . User::IMAGE_SQUARE;
				$sq_sm_file = dirname(__FILE__) . '/../images/users/' . $photo_prefix . User::IMAGE_SQUARE_SMALL;
				if (file_exists($sq_file)) {
					@unlink($sq_file);
				}
				if (file_exists($sq_sm_file)) {
					@unlink($sq_sm_file);
				}
			}

			Header('Location: /profile/edit');
			exit(0);

		case 'fave':

			assertParam('target');
			assertParam('title');

			$user = $auth->getCurrentUser();
			if (strlen(param('target')) < 1024) {
				$user->addFavourite(urldecode(param('title')), urldecode(param('target')));
			}

			if (!issetParam('mobile')) {
				echo json(array(
					'status' => 'ok'
				));
			} else {
				redirect_back('ok');
			}

			exit(0);

		case 'unfave':

			assertParam('target');

			$user = $auth->getCurrentUser();
			$user->removeFavourite(urldecode(param('target')));

			if (!issetParam('mobile')) {
				echo json(array(
					'status' => 'ok'
				));
			} else {
				redirect_back('ok');
			}
			exit(0);
		}


	} else {

		switch ($_REQUEST['method']) {
		case 'sign_in':
			$login = $_REQUEST['sign_in_login'];
			$md5pass = md5($_REQUEST['sign_in_password']);
			$auth->login($login, $md5pass, true);
			if ($auth->isAuth()) {
				$user = $auth->getCurrentUser();
				if (!issetParam('mobile')) {
					Header('Location: /id' . $user->getId());
				} else {
					redirect_back('ok');
				}
				exit(0);
			} else {
				Header('Location: ' . $_SERVER['HTTP_REFERER']);
				exit(0);
			}
			break;
			
		case 'login_vk':
			$status = $auth->loginVkontakte();
			switch ($status) {
			case ISocialWeb::FAIL:
				echo json_encode(array(
					'status' => 'failed'
				));
				break;

			case ISocialWeb::SUCCESS:
				echo json(array(
					'status' => 'success'
				));
				break;

			case ISocialWeb::FULL_SUCCESS:
				echo json(array (
					'status' => 'full_success'
				));
				break;

			}
			break;

		case 'sign_in_social':
			assertIsset($_REQUEST['social']);
			assertIsset($_REQUEST['uid']);
			assertIsset($_REQUEST['password']);

			$uid = intval($_REQUEST['uid']);
			$ucode = md5($_REQUEST['password']);

			switch ($_REQUEST['social']) {
			case ISocialWeb::VKONTAKTE:
				if ($auth->loginVkontakte() != ISocialWeb::SUCCESS) {
					Header('Location: ' . $_SERVER['HTTP_REFERER'] . '#error=');
					exit(0);
				}

				if ($auth->isVkontakteAuth()) {
					if ($uid = $auth->loginUidPass($uid, $ucode)) {
						$user = $auth->getCurrentUser();
						$user->put('vkid', $auth->getVkid());
						Header('Location: /id'.$user->getId());
						exit(0);
					} else {
						Header('Location: ' . $_SERVER['HTTP_REFERER'] . '#error');
						exit(0);
					}
				}
				break;
			}

			break;

		case 'get_users':
			$udata = array();
			foreach ($_REQUEST as $key => $value) {
				if (array_contains(User::getKeys(), $key)) {
					$udata[$key] = string_convert($value);
				}
			}

			$users = User::getNearTo($udata);
			if (!empty ($users)) {
				show_block_similar_users($users);
			} else {
				$_SESSION['udata'] = serialize($udata);
				echo "ololo";
			}
			
			break;

		case 'quick_register':
			$udata = unserialize($_SESSION['udata']);
			$newUser = User::create($udata[User::FIELD_NAME], $udata[User::FIELD_SURNAME]);
			$newId = $newUser->getId();
			if ($newId != 0) {
				$status = $auth->loginVkontakte();
				if ($status == ISocialWeb::FULL_SUCCESS) {
					$user = $auth->getUser();
					foreach ($udata as $key => $value) {
						if ($key != User::FIELD_NAME && $key != User::FIELD_SURNAME) {
							try {
								$user->put($key, $value);
							} catch (Exception $e) {
								global $LOG;
								@$LOG->exception($e);
							}
						}
					}
					echo json(array(
						'status' => 'ok'
					));
				} else {
					echo json(array(
						'status' => 'failed',
						'reason' => 'unable to login vkontakte'
					));
				}
			} else {
				echo json(array(
					'status' => 'failed',
					'reason' => 'could not create a new user'
				));
			}

			break;
		}


	}

} catch (Exception $e) {
	global $LOG;
	@$LOG->exception($e);
	echo_json_exception($e);
}



?>
