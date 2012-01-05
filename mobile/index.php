<?php

define('BEGIN_TIME', microtime(true));

require_once dirname(__FILE__) . '/../main/includes/config-local.php';
require_once dirname(__FILE__) . '/../main/includes/authorize.php';
require_once dirname(__FILE__) . '/../main/includes/common.php';
require_once dirname(__FILE__) . '/../main/views/life_view.php';

require_once dirname(__FILE__) . '/templates/mobile_view.php';

$PATH = "";

define('ITEMS_PER_PAGE', 10);

global $auth;
global $user;

define('CURRENT_URL',
		'http://' . MOBILE_SITE_URL . '/' .
		param('part') .
		(issetParam('id') ? '/' . param('id') : '')
);

$isFave = $auth->isAuth() ? $user->checkFavourite(CURRENT_URL) : false;

$title = 'Мобильный пайпосайт';
switch (param('part')) {
case 'sport':
	$title .= ' / Спорт';
	break;
case 'sport_league':
	if (issetParam('id')) {
		$league = League::getById(intparam('id'));
		if ($league instanceof League) {
			$title .= ' / ' . $league->getName();
		}
	}
	break;
case 'sport_rating':
	if (issetParam('id')) {
		$league = League::getById(intparam('id'));
		if ($league instanceof League) {
			$title .= ' / Рейтинг / ' . $league->getName();
		}
	}
	break;
case 'sport_competition':
	if (issetParam('id')) {
		$comp = Competition::getById(intparam('id'));
		if ($comp instanceof Competition) {
			$title .= ' / ' . $comp->getName();
		}
	}
	break;
case 'sport_cup':
	if (issetParam('id')) {
		$cup = CupFactory::getCupById(intparam('id'));
		if ($cup instanceof Cup) {
			$title .= ' / ' . $cup->getCompetition()->getName() . ' / ' . $cup->getName();
		}
	}
	break;
case 'forum':
	$title .= ' / Форум';
	break;
case 'forum_part':
	if (issetParam('id')) {
		$part = ForumPart::getById(intparam('id'));
		if ($part instanceof ForumPart) {
			$title .= ' / Разделы форума / ' . $part->getTitle();
		}
	}
	break;
case 'forum_topic':
	if (issetParam('id')) {
		$topic = ForumTopic::getById(intparam('id'));
		if ($topic instanceof ForumTopic) {
			$title .= ' / Топик / ' . $topic->getTitle();
		}
	}
	break;
}
define('CURRENT_TITLE', $title);

?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=windows-1251"/>
		<title><?=CURRENT_TITLE?></title>
		<link rel="stylesheet" type="text/css" href="/css/main.css" charset="windows-1251"/>

		<script src="http://<?=MAIN_SITE_URL?>/js/jquery-1.5.1.min.js" type="text/javascript"></script>
<? if (param('part') == ''): ?>
		<script src="http://<?=MAIN_SITE_URL?>/js/login_vk.js" type="text/javascript"></script>
		<script src="http://vkontakte.ru/js/api/openapi.js" type="text/javascript" charset="windows-1251"></script>
<? endif; ?>

	</head>
	<body>

<? if ($auth->isAuth() && issetParam('part')): ?>

		<a href="/procs/proc_main.php?method=<?=($isFave?'unfave':'fave')?>&target=<?=urlencode(CURRENT_URL)?>&title=<?=urlencode(CURRENT_TITLE)?>">
			<div id="fave"<?if($isFave) {?> class="faved"<?}?>></div>
		</a>
<? endif; ?>

	<!--<? print_r($_REQUEST); ?>-->

<? include 'templates/header.php'; ?>

		<div class="body">
<?

if (!issetParam('part')) {
	if (!$auth->isAuth()) {
?>
			<form action="/procs/proc_main.php" method="POST">
				<input type="hidden" name="method" value="sign_in"/>
<?
		begin_block('Авторизация');
?>
			
			<tr>
				<td>Логин:</td>
				<td class="w"><input style="width: 90%;" type="text" name="sign_in_login"/></td>
			</tr>
			<tr>
				<td>Пароль:</td>
				<td class="w"><input style="width: 90%;" type="password" name="sign_in_password"/></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" value="Войти"/></td>
			</tr>
			<tr><td class="w" colspan="2"><a href="#" id="vk_login">Войти В Контакте</a></td></tr>
<?
		end_block();
?>
			</form>
<?
	} else {

		Forum::init($user->getId());
		$topics = ForumTopic::getOpened();
		$topicsNew = array();
		foreach ($topics as $topic){
			if ($topic->hasNewCommentsFor($user)) {
				$topicsNew[] = $topic;
			}
		}

		begin_block('Новое на форуме');
		$count = count($topicsNew);
		if ($count > 0) {
			$i = 0;
			foreach ($topicsNew as $topic) {
				mobile_show_topic($topic, $user);
				if (++$i > 10) break;
			}
			if ($i < $count) {
				echo '<tr><td colspan="2">...и ещё '.lang_number_sclon($count - $i, 'топик', 'топика', 'топиков').'</td></tr>';
			}
		} else {
			echo <<< BLOCK
			<tr>
				<td class="w">
					Сейчас на форуме нет ничего нового.
				</td>
			</tr>
BLOCK;
		}
		end_block();

		begin_block('Избранное');
		$favourites = $user->getFavouritesBySubTarget(MOBILE_SITE_URL);
		if (count ($favourites) > 0) {
			foreach ($favourites as $target=>$title) {
				echo '<tr><td class="w"><a href="'.$target.'">'.$title.'</a></td></tr>';
			}
		} else {
			echo <<< BLOCK
			<tr>
				<td class="w">
					Вы можете добавлять в этот блок ссылки на интересные страницы мобильной версии
					пайпосайта, нажимая на звёздочку в правом верхнем углу.
				</td>
			</tr>
BLOCK;
		}
		end_block();
	}
} else {

	switch (substr(param('part'), 0, 5)) {
	case 'sport':
		include 'mobile_sport.php';
		break;
	case 'forum':
		include 'mobile_forum.php';
		break;
	}

}
?>
		</div>

<? include 'templates/footer.php'; ?>

	</body>
</html>