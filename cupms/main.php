<?php

/**
 * @author Artyom Grigoriev
 * @author Andrew Solozobov
 */

require_once 'includes/config.php';
require_once 'views/side_menu_view.php';

require_once '../main/classes/user/User.php';
require_once '../main/classes/user/Auth.php';

$auth = new Auth();
if (!$auth->isAuth()) {
	Header('Location: /index.php');
	exit(0);
}

$user = $auth->getCurrentUser();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="application-name" content="CupMS 3.0" />
		<meta name="application-url" content="http://cupms.pipeinpipe.info" />
		<meta name="description" content="Cup Management System version 3.0" />

		<link rel="icon" href="images/icon32.png" />
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
		<link rel="stylesheet" href="css/main.css" type="text/css" />
		<link rel="stylesheet" href="css/content.css" type="text/css" />
		<link rel="stylesheet" href="css/ui-controls.css" type="text/css" />
		<link rel="stylesheet" href="css/games.css" type="text/css" />
		<link rel="stylesheet" href="css/players.css" type="text/css" />
		<link rel="stylesheet" href="css/competition.css" type="text/css" />
		<link rel="stylesheet" href="css/league.css" type="text/css" />

		<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="js/calendar.js"></script>
		<script type="text/javascript" src="js/error.js"></script>
		<script type="text/javascript" src="js/main.js"></script>
		<script type="text/javascript" src="js/competition.js"></script>
		<script type="text/javascript" src="js/cup.js"></script>
		<script type="text/javascript" src="js/league.js"></script>
		<script type="text/javascript" src="js/players.js"></script>
		<script type="text/javascript" src="js/zherebjator.js"></script>
		<script type="text/javascript" src="js/routing.js"></script>
		<script type="text/javascript" src="<?=MAIN_URL?>/js/lib-structures.js"></script>
		<script type="text/javascript" src="<?=MAIN_URL?>/js/ui-controls.js"></script>
		
				<!--
				This comment must NOT be deleted.
				<script type="text/javascript">
						$(document).ready(function(){
								$("*").click(function(){
										window.event.cancelBubble=true;
								});
						});
				</script>
				-->

		<title>Cup Manage System 3.0</title>
	</head>
	<body>
		<div id="layout">
                    <div align="center" id="error_panel">
                        <div class="text"></div>
                    </div>
			<div id="header">                            
                            <a href="<?=MAIN_URL?>/id<?=$user->uid()?>"><?=$user->getFullName()?></a>
                            (<a href="<?=MAIN_URL?>/authorize.php?method=sign_out">выйти</a>)
			</div>                    
			<div id="body">
				<div id="left_column">
					<?  getSideMenu($user) ?>
				</div>
				<div id="main">
					<div id="content">
						<div id="content_header">
							Cup Management System 3.0
						</div>
						<div id="content_body">
							<p>
								<b>CupMS 3.0</b> &mdash; система управления турнирами.
								Входит в состав портала <a href="http://pipeinpipe.info">pipeinpipe.info</a>
								и предназначена для использования администраторами лиг и турниров.
							</p>
							<p>
								Включает в себя широкую систему разграничений прав доступа к изменению
								спортивной информации на сайте. В приведенной ниже таблице описаны
								действия, которые может совешать указанный тип пользователя.
							</p>
							<table class="full">
								<thead>
									<th colspan="2">Тип пользователя</th>
									<th colspan="3">Игрок</th>
									<th colspan="5">Турнир</th>
									<th colspan="3">Лига</th>
								</thead>
								<tbody>
									<tr>
										<td><span class="gr">TA</span></td>
										<td>Тотальный администратор</td>

										<td><span class="g">C</span></td>
										<td><span class="y">EA</span></td>
										<td><span class="r">DA</span></td>
										
										<td><span class="g">C</span></td>
										<td><span class="y">EA</span></td>
										<td><span class="r">DA</span></td>
										<td><span class="b">SA</span></td>
										<td><span class="v">RA</span></td>
										
										<td><span class="g">C</span></td>
										<td><span class="y">EA</span></td>
										<td><span class="r">DA</span></td>
									</tr>
									<tr>
										<td><span class="gr">LA</span></td>
										<td>Администратор лиги</td>
										
										<td><span class="g">C</span></td>
										<td><span class="y">EA</span></td>
										<td></td>

										<td><span class="g">C</span></td>
										<td><span class="y">EI</span></td>
										<td><span class="r">DI</span></td>
										<td><span class="b">SI</span></td>
										<td><span class="v">RI</span></td>

										<td><span class="g">C</span></td>
										<td><span class="y">E</span></td>
										<td></td>
									</tr>
									<tr>
										<td><span class="gr">CA</span></td>
										<td>Администратор турнира</td>

										<td><span class="g">C</span></td>
										<td><span class="y">EA</span></td>
										<td></td>

										<td></td>
										<td><span class="y">E</span></td>
										<td></td>
										<td><span class="b">S</span></td>
										<td></td>

										<td></td>
										<td></td>
										<td></td>
									</tr>
									<tr>
										<td colspan="13" style="line-height: 2em;">
											<span class="g">C</span> &mdash; создание;<br/>
											<span class="y">E</span> &mdash; редактирование,
											<span class="y">EA</span> &mdash; редактирование всех,
											<span class="y">EI</span> &mdash; редактирование внутренних;<br/>
											<span class="r">DI</span> &mdash; удаление внутренних,
											<span class="r">DA</span> &mdash; удаление всех;<br/>
											<span class="b">S</span>, <span class="b">SA</span> (<span class="b">SI</span>)
											&mdash; старт/стоп турнира, всех (внутренних) турниров,<br/>
											<span class="v">RA</span> (<span class="v">RI</span>) &mdash;
											рестарт всех (внутренних) турниров.
										</td>
									</tr>
								</tbody>
							</table>

							<p>
								В левой колонке перечисленны только те турниры, к которым вы имеете доступ.
							</p>
							<p style="line-height: 2em;">
								Полный список ваших прав:
<?
	$permissions = $user->getPermissions();
	$count = count($permissions);
	foreach ($permissions as $i => $permission) {
		$matched = false;
		switch ($permission['status']) {
		case 'TA':
			echo '<span class="gr">TA</span>';
			$matched = true;
			break;
		case 'LA':
			$league = League::getById($permission['target_id']);
			echo '<span class="gr">LA</span> ' . $league->getName();
			$matched = true;
			break;
		case 'CA':
			$competition = Competition::getById($permission['target_id']);
			echo '<span class="gr">CA</span> ' . $competition->getName();
			$matched = true;
			break;
		}

		if ($matched && $i < $count - 1) {
			echo ', ';
		}
	}
?>

							</p>
						</div>
					</div>
				</div>
				<div id="footer">
					<a href="http://cupms.pipeinpipe.info">Cup Management System</a> для <a href="http://pipeinpipe.info">pipeinpipe.info</a>.
					&copy; 2010<?=(date("Y") > 2010 ? '&nbsp;&mdash;&nbsp;'.date("Y") : '')?>
				</div>
			</div>
		</div>
    </body>
</html>