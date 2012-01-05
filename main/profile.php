<?php

// TODO Andrew: this if the file for user profile

require_once 'classes/user/Auth.php';
require_once 'classes/user/User.php';

require_once 'classes/cupms/Player.php';

require_once 'classes/forum/Forum.php';

require_once 'includes/log.php';

require_once 'views/profile_view.php';

try {
	include 'includes/authorize.php';
	include 'views/header.php';

	$person = null;
	$player = null;
	$tabs = array();

	global $auth, $user;
	
	if (issetParam('user_id')) {
		$person = User::getById(intparam('user_id'));
		$player = $person->getPlayer();

		$isOwner = ($person != null && $user != null && $person->getId() == $user->getId());
		$tabs = array(
			'person' => $person != null ? 'selected' : false,
			'player' => $player != null ? true : false,
			//'feed' => $isOwner ? true : false,
			'edit' => $isOwner ? true : false
		);
	} elseif (issetParam('player_id')) {
		$player = Player::getById(intparam('player_id'));
		$person = $player->getUser();

		$isOwner = ($person != null && $user != null && $person->getId() == $user->getId());
		$tabs = array(
			'person' => $person != null ? true : false,
			'player' => $player != null ? 'selected' : false,
			//'feed' => $isOwner ? true : false,
			'edit' => $isOwner ? true : false
		);
	} elseif (issetParam('edit') && $auth->isAuth()) {
		$person = $user;
		$player = $person->getPlayer();

		$isOwner = ($person != null && $user != null && $person->getId() == $user->getId());
		$tabs = array(
			'person' => $person != null ? true : false,
			'player' => $player != null ? true : false,
			//'feed' => $isOwner ? true : false,
			'edit' => $isOwner ? 'selected' : false
		);
	} elseif ($auth->isAuth()) {
		$person = $user;
		$player = $person->getPlayer();

		$isOwner = ($person != null && $user != null && $person->getId() == $user->getId());
		$tabs = array(
			'person' => $person != null ? 'selected' : false,
			'player' => $player != null ? true : false,
			//'feed' => $isOwner ? 'selected' : false,
			'edit' => $isOwner ? true : false
		);
	}

//	echo '<pre>';
//	print_r($player);
//	print_r($person);
//	print_r($tabs);
//	echo '</pre>';
?>

<div id="profile_container" class="body_container">
<?
	profile_show_body($person, $player, $tabs);
?>

</div>
<?
	include 'views/footer.php';
} catch (Exception $e) {
	echo $e->getMessage();
}

?>