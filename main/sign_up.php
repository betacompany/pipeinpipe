<?php

require_once 'classes/user/Auth.php';
require_once 'classes/user/User.php';

require_once 'includes/log.php';

try {
	include 'includes/authorize.php';
	
	$auth = new Auth();
	if ($auth->isAuth()) {
		$uid = $auth->getCurrentUser()->getId();
		Header('Location: /id' . $uid);
		exit(0);
	}

	include 'views/header.php';

?>

<div id="sign_up_container" class="body_container">
	<h1>Регистрация</h1>
	<div class="left_column">
		<form id="sign_up" action="/procs/proc_sign_up.php" method="post">
			<? if (issetParam('ret')): ?>
			<input type="hidden" name="ret" value="<?=urlencode(param('ret'))?>"/>
			<? endif; ?>
			<input type="hidden" name="method" value="sign_up" />
			
			<div class="block">
				<div>
					<div class="label">
						<div>Логин:</div>
					</div>
					<div class="elem">
						<div><input type="text" autocomplete="off" name="sign_up_login" /></div>
					</div>
				</div>
				<div>
					<div class="label">
						<div>E-mail:</div>
					</div>
					<div class="elem">
						<div><input type="text" autocomplete="off" name="sign_up_email" /></div>
					</div>
				</div>
				<div>
					<div class="label">
						<div>Пароль (первый раз):</div>
					</div>
					<div class="elem">
						<div><input type="password" autocomplete="off" name="sign_up_password1" /></div>
					</div>
				</div>
				<div>
					<div class="label">
						<div>Пароль (второй раз):</div>
					</div>
					<div class="elem">
						<div><input type="password" name="sign_up_password2" /></div>
					</div>
				</div>
			</div>

			<div class="block">
				<div>
					<div class="label">
						<div>Имя:</div>
					</div>
					<div class="elem">
						<div><input type="text" name="sign_up_name" /></div>
					</div>
				</div>
				<div>
					<div class="label">
						<div>Фамилия:</div>
					</div>
					<div class="elem">
						<div><input type="text" name="sign_up_surname" /></div>
					</div>
				</div>
			</div>

			<div class="block" id="social_icons">
				<div>
					<div class="label">
						<div>Вы в соцсетях:</div>
					</div>
					<div class="elem">
						<div class="vk_icon_large">
							<input type="hidden" name="sign_up_vkid" value="0" />
							<div class="social_id"></div>
						</div>
					</div>
				</div>
			</div>

			<!--<div class="block">
				<div>
					<div class="label">
						<div>Страна:</div>
					</div>
					<div class="elem" id="country"></div>
				</div>
				<div>
					<div class="label">
						<div>Город:</div>
					</div>
					<div class="elem" id="city"></div>
				</div>
			</div>-->

			<div class="block">
				<div>
					<div class="label">
						<div></div>
					</div>
					<div class="elem">
						<div>
							<!-- TODO captcha -->
						</div>
					</div>
				</div>
			</div>


			<div class="block">
				<div>
					<div class="label">
						<div></div>
					</div>
					<div class="elem">
						<div>
							<input id="sign_up_button"
								   type="submit"
								   name="sign_up_submit"
								   value="Зарегистрироваться" />
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
	<div class="right_column">
		<div id="error_box"></div>
	</div>
	<div style="clear: both;"></div>
</div>

<?
	include 'views/footer.php';
} catch (Exception $e) {
	global $LOG;
	$LOG->exception($e);
}

?>