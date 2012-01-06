<?php
require_once dirname(__FILE__) . '/../classes/user/User.php';

require_once dirname(__FILE__) . '/../classes/utils/ResponseCache.php';

function show_user(User $user) {
	$imgUrl = $user->getImageURL(User::IMAGE_SQUARE);
	$name = $user->getFullName();
	$city = $user->getCityName();
	$id = $user->getId();
?>
	<div class="user_wrapper">
		<div class="user">
			<a href="/id<?=$id?>">
				<img src="<?=$imgUrl?>">
			</a>
			<div class="description">
				<div class="name">
					<a href="/id<?=$id?>"><?=$name?></a>
				</div>
				<div class="city">
					<?=$city?>
				</div>
			</div>
		</div>
	</div>
<?
}

$cache = new ResponseCache('life/people', array());
if ($cache->getAge() < 60) {
	echo $cache->get();
} else {
	$cache->start();
	$users = User::getAll();
?>
<div id="people_container">
<?
	foreach ($users as $user) {
		show_user($user);
	}
?>
</div>
<?
	$cache->store();
}
