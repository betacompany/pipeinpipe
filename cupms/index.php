<?php

/*
 * На этой странице нужно сделать форму для ввода логина/пароля для входа в систему
 */

require_once dirname(__FILE__) . '/includes/config.php';

require_once dirname(__FILE__) . '/../main/classes/user/Auth.php';

$auth = new Auth();

if ($auth->isAuth()) {
	Header ('Location: /main.php');
	exit(0);
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ru">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="application-name" content="CupMS 3.0" />
		<meta name="application-url" content="http://cupms.pipeinpipe.info" />
		<meta name="description" content="Cup Management System version 3.0" />
		<link rel="icon" href="images/icon32.png" />
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
		<link rel="stylesheet" href="css/main.css" type="text/css" />
        <title>CupMS 3.0 / вход</title>
    </head>
    <body>
        <div id="loginForm">
			<img src="images/logo320.png" alt="CupMS 3.0" />
			<form action="<?=MAIN_URL?>/authorize.php" method="post">
                <input type="hidden" name="method" value="sign_in" />
				<input type="text" name="login" />
				<input type="password" name="password" />
				<input type="submit" name="login_submit" value="Войти" />
			</form>
		</div>
    </body>
</html>
