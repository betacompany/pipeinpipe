<?php

require_once dirname(__FILE__) . '/../includes/error.php';
require_once dirname(__FILE__) . '/../includes/assertion.php';

try {
	assertIsset($_REQUEST['package']);
	switch ($_REQUEST['package']) {
	case 'signUp':
		echo json(array (
			'empty_login' => array ('text' => 'Пустой логин!'),
			'empty_password1' => array ('text' => 'Пустой пароль!'),
			'empty_email' => array ('text' => 'Пустой адрес электронной почты!'),
			'empty_name' => array ('text' => 'Пустое имя!'),
			'empty_surname' => array ('text' => 'Пустая фамилия!'),
			'occupied_login' => array ('text' => 'Такой логин занят!'),
			'occupied_email' => array ('text' => 'Такой адрес электронной почты занят!'),
			'occupied_vkid' => array (
				'text' => 'Пользователь с таким id В Контакте уже зарегистрирован на сайте',
				'callback' => 'function () { signUp.socialClick; }'
			),
			'different_password1_s' => array ('text' => 'Введённые пароли не совпадают!'),
			'userCreationFailed' => array ('text' => 'Регистрация не удалась. Повторите попытку!'),
			'test' => array (
				'text' => 'test',
				'callback' => 'function () {debug(\'test test\');}'
			)
		));
		break;
	case 'profile':
		echo json(array (
			'noFileUploaded' => array ('text' => 'Не загружен файл!'),
			'smallPhoto' => array ('text' => 'Слишком маленькая фотография'),
			'bigPhoto' => array ('text' => 'Слишком большая фотография'),

			'handleError' => 'function (id, error) {main.showErrorText(error.text);}'
		));
		break;
	}
} catch (Exception $e) {
	echo_json_exception($e);
	exit(0);
}

?>
