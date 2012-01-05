<?php

define('BEGIN_TIME', microtime(true));

require_once dirname(__FILE__) . '/blocks.php';

require_once dirname(__FILE__) . '/../includes/lang.php';
require_once dirname(__FILE__) . '/../includes/common.php';


function document_title() {
	switch ($_SERVER['SCRIPT_NAME']) {
	case '/index.php':
		return 'Сайт про pipe-in-pipe, одномерный спорт';

	case '/profile.php':
		if (issetParam('user_id')) {
			try {
				$user = User::getById(param('user_id'));
				return $user->getFullName() . ' на сайте про пайп';
			} catch (Exception $e) {
				break;
			}
		} elseif (issetParam('player_id')) {
			try {
				$player = Player::getById(intparam('player_id'));
				return $player->getFullName() . ' на сайте про пайп';
			} catch (Exception $e) {
				break;
			}
		} elseif (issetParam('edit')) {
			global $auth, $user;
			if ($auth->isAuth()) {
				return $user->getFullName() . ' | редактирование';
			}
		} else {
			global $auth, $user;
			if ($auth->isAuth()) {
				return $user->getFullName() . ' на сайте про пайп';
			}
		}
		return;

	case '/sport.php':
		if (!issetParam('part')) {
			return 'Спортивный раздел сайта про пайп';
		}

		switch (param('part')) {
		case 'rules':
			return 'Официальные полные правила игры в pipe-in-pipe (трубу-в-трубе, пайп)';
		case 'faq':
			return 'F.A.Q. про пайп';
		case 'league':
			if (!issetParam('league_id')) {
				return 'Все лиги пайпа';
			}

			try {
				$league = League::getById(param('league_id'));
				return $league->getName() . ' на сайте про пайп';
			} catch (Exception $e) {
				break;
			}
		case 'competition':
			try {
				$competition = Competition::getById(param('comp_id'));
				return $competition->getName() . ' на сайте про пайп';
			} catch (Exception $e) {
				break;
			}
		case 'rating':
			return 'Рейтинг пайпменов мира';
		case 'formula':
			// TODO implement this case
			return 'Формула расчёта рейтинга';
		case 'statistics':
			return 'Пайп-статистика';
		case 'pipemen':
			return 'Пайпмены на сайте';
		}

		return 'Спортивный раздел сайта про пайп';
		
	case '/life.php':
		if (!issetParam('part')) {
			return 'Лента пайп-жизни';
		}

		switch (param('part')) {
		case 'blog':
			require_once dirname(__FILE__) . '/../classes/blog/Blog.php';
			require_once dirname(__FILE__) . '/../classes/blog/BlogPost.php';

			if (issetParam('post_id')) {
				$post = Item::getById(intparam('post_id'));
				if ($post instanceof BlogPost) {
					$blog = $post->getGroup();
					return  '&laquo;' . $post->getTitle() .
							'&raquo; в блоге &laquo;' .
							$blog->getTitle() . '&raquo;';
				}
			}
			return 'Блоги про пайп';
		case 'comments':
			return 'Комментарии на сайте про пайп';
		case 'people':
			return 'Люди на сайте пайпа';
		case 'blog_editor':
			return 'Редактор постов в блоге';
		}

		return 'Лента пайп-жизни';

	case '/media.php':
		require_once dirname(__FILE__) . '/../classes/content/Group.php';
		require_once dirname(__FILE__) . '/../classes/content/Item.php';

		if (!issetParam('part')) {
			return 'Медиагалерея сайта про пайп';
		}

		switch (param('part')) {
		case 'photo':
			if (!issetParam('group_id')) {
				return 'Фотогалерея сайта про пайп';
			}

			try {
				$album = Group::getById(param('group_id'));

				if (!issetParam('item_id')) {
					return 'Фотоальбом &laquo;' . $album->getTitle() . '&raquo; на сайте про пайп';
				}

				$item = Item::getById(param('item_id'));

				return '&laquo;' . $item->getTitle() . '&raquo; в фотоальбоме &laquo;' . $album->getTitle() . '&raquo; на сайте про пайп';
			} catch (Exception $e) {
				break;
			}

		case 'photo':
			if (!issetParam('group_id')) {
				return 'Видеогалерея сайта про пайп';
			}

			try {
				$album = Group::getById(param('group_id'));

				if (!issetParam('item_id')) {
					return 'Видеоальбом &laquo;' . $album->getTitle() . '&raquo; на сайте про пайп';
				}

				$item = Item::getById(param('item_id'));

				return '&laquo;' . $item->getTitle() . '&raquo; в видеоальбоме &laquo;' . $album->getTitle() . '&raquo; на сайте про пайп';
			} catch (Exception $e) {
				break;
			}

		case 'download':
			return 'FREE DOWNLOADS!!!111';
		}

		return 'Медиа-галерея сайта про пайп';

	case '/forum.php':
		if (!issetParam('forum_action')) {
			return 'Форум про пайп';
		}

		try {
			$part = Group::getById(param('part_id'));

			if (!issetParam('topic_id')) {
				return 'Раздел &laquo;' . $part->getTitle() . '&raquo; на форуме про пайп';
			}

			$topic = Item::getById(param('topic_id'));

			return 'Топик &laquo;' . $topic->getTitle() . '&raquo; на форуме про пайп';
		} catch (Exception $e) {
			break;
		}

		return 'Форум про пайп';

	}

	return 'Сайт про pipe-in-pipe, одномерный спорт';
}

global $auth;
global $user;

$title = document_title();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title><?=$title?></title>

		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="author" content="betacompany" />
		<meta name="description" content="Official web site of the pipe-in-pipe" />
		<meta name="copyright" content="International Federation of Pipe-in-pipe" />
		<meta name="keywords" content="pipe-in-pipe, пайп, пайпмен, спорт, труба-в-трубе" />
		<meta name="publisher-email" content="info@pipeinpipe.info" />
		<meta name="generator" content="NetBeans 6.9" />

		<link rel="search" type="application/opensearchdescription+xml" title="pipeinpipe.info" href="/static/opensearch.xml" />
		<link rel="apple-touch-icon" type="image/png" href="/images/icons/apple-icon.png" />

		<link rel="stylesheet" href="/css/main.css" type="text/css" />
		<link rel="stylesheet" href="/css/menu.css" type="text/css" />
		<link rel="stylesheet" href="/css/icons.css" type="text/css" />
		<link rel="stylesheet" href="/css/ui-controls.css" type="text/css" />
<?

list($script_name, $ext) = explode(".", $_SERVER['SCRIPT_NAME'], 2);
$script_name = substr($script_name, 1);

if (file_exists(dirname(__FILE__).'/../css/'.$script_name.'.css')) {
?>

		<link rel="stylesheet" href="/css/<?=$script_name?>.css" type="text/css" />
<?
}

if (isset ($_REQUEST['part']) && file_exists(dirname(__FILE__).'/../css/'.$script_name.'_'.$_REQUEST['part'].'.css')) {
?>

		<link rel="stylesheet" href="/css/<?=$script_name.'_'.$_REQUEST['part']?>.css" type="text/css" />
<?
}

if (isset ($_REQUEST['part']) && $_SERVER['SCRIPT_NAME'] == '/sport.php' && isset($_REQUEST['part']) && $_REQUEST['part'] == 'league') {
?>

		<link rel="stylesheet" href="/css/life.css" type="text/css" />
<?
}
?>
		
		<!--[if lte IE 7]>
		<link rel="stylesheet" href="/css/ieisapieceofshit.css" />
		<![endif]-->

		<link rel="icon" type="image/png" href="/favicon.png" />
		<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="/favicon.ico" />

		<script type="text/javascript" src="/js/lib-structures.js"></script>
		<script type="text/javascript" src="/js/jquery-1.5.1.min.js"></script>
		<script type="text/javascript" src="/js/api.js?2"></script>
		<script type="text/javascript" src="/js/jquery-ui-1.8.4.custom.min.js"></script>
		<script type="text/javascript" src="/js/common.js?2"></script>
		<script type="text/javascript" src="/js/error-handler.js"></script>
		<script type="text/javascript" src="/js/ui-controls.js"></script>
		<script type="text/javascript" src="/js/ui-boxes.js"></script>
		<script type="text/javascript" src="/js/content.js"></script>
		<script type="text/javascript" src="/js/menu.js"></script>
		<script type="text/javascript" src="/js/error.js"></script>
		<script type="text/javascript" src="/js/main.js"></script>

		<script type="text/javascript" src="/js/fullajax.js"></script>

		<script src="http://vkontakte.ru/js/api/openapi.js" type="text/javascript" charset="windows-1251"></script>
		<!--<script src="http://connect.facebook.net/en_US/all.js" type="text/javascript"></script>-->
		<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>

<?
if (file_exists(dirname(__FILE__).'/../js/'.$script_name.'.js')) {
?>

		<script type="text/javascript" src="/js/<?=$script_name?>.js"></script>
<?
}

if (isset ($_REQUEST['part']) && file_exists(dirname(__FILE__).'/../js/'.$script_name.'_'.$_REQUEST['part'].'.js')) {
?>

		<script type="text/javascript" src="/js/<?=$script_name.'_'.$_REQUEST['part']?>.js"></script>
<?
}

?>

	</head>
	<body>
		<!--[if lte IE 7]>
		<iframe id="old_browser" src="/static/old_browser.html" />
		<![endif]-->

		<div id="error_box"></div>
		
		<div id="layout">

			<div id="header">
				<div id="top_bar">
					<div id="top_bar_container">
<? 
if ($auth->isAuth())
	show_block_user($user);
else 
	show_block_sign_in();
?>

					</div>
				</div>

<? include dirname(__FILE__) . '/menu.php' ?>

			</div>
			<div id="body">
<?
if (!$auth->isAuth()) {
?>
				
				<div id="social_login_bar" class="body_container"></div>
<?
}
?>

