<?php

require_once dirname(__FILE__) . '/blocks.php';

define('MENU_DEFAULT_OPENED', ' opened');
define('MENU_DEFAULT_UNOPENED', '');

function show_menu_element($link, $id, $text, $class){
?>
							<a href="<?= $link ?>">
								<div id="<?= $id ?>" class="menu_item<?= $class ?>">
									<div class="item_text">
										<div class="item_text_wrapper"><?= $text ?></div>
									</div>
								</div>
							</a>
<?
}

function show_submenu_element($link, $id, $text, $class){
?>
							<a href="<?= $link ?>">
								<div id="<?= $id ?>" class="submenu_item<?= $class ?>">
									<div class="submenu_item_text"><?= $text ?></div>
								</div>
							</a>
<?
}

$MENU_INDEX_FILE = array('/index.php', '/profile.php', '/sign_up.php', '/page.php');
$MENU_SPORT_FILE = '/sport.php';
$MENU_LIFE_FILE = '/life.php';
$MENU_MEDIA_FILE = '/media.php';
$MENU_FORUM_FILE = '/forum.php';

/**
 * Checks if $data equals or contains the current script filename
 * and returns string $opened in this case and $not_opened otherwise.
 *
 * @param mixed $data - an array of strings or a string
 * @param string $opened
 * @param string $not_opened
 * @return string
 */
function is_opened_by_script($data, $opened = MENU_DEFAULT_OPENED, $not_opened = MENU_DEFAULT_UNOPENED) {
	if (is_array($data)) {
		return array_contains($data, $_SERVER['SCRIPT_NAME']) ? $opened : $not_opened;
	}

	return $data == $_SERVER['SCRIPT_NAME'] ? $opened : $not_opened;
}

/**
 * Gets as $data an associative array (a hash) and then checks for each key:
 * 1) if the value binded with such key is null easy it prooves if such key
 *    exists in $_REQUEST otherwise returns $not_opened
 * 2) if there is some not null value it prooves also that such value is
 *    the same as the value binded to such key in $_REQUEST otherwise returns
 *    $not_opened
 * If all these terms are true it returns $opened
 *
 * @param array $data
 * @param string $opened
 * @param string $not_opened
 * @return string
 */
function is_opened_by_request($data, $opened = MENU_DEFAULT_OPENED, $not_opened = MENU_DEFAULT_UNOPENED) {
	foreach ($data as $key => $value) {
		if ($value == null) {
			if (!isset($_REQUEST[$key])) return $not_opened;
		} else {
			if (!isset($_REQUEST[$key])) return $not_opened;
			if ($_REQUEST[$key] != $value) return $not_opened;
		}
	}

	return $opened;
}

/**
 * Makes double job of is_opened_by_script and is_opened_by_request and
 * returns $opened only if the both of those functions returns $opened
 *
 * @param mixed $data_file a string or an array of strings
 * @param array $data_request
 * @param string $opened
 * @param string $not_opened
 */
function is_opened_by_script_and_request($data_file, $data_request, $opened = MENU_DEFAULT_OPENED, $not_opened = MENU_DEFAULT_UNOPENED) {
	global $auth;

	$by_file = is_opened_by_script($data_file, $opened, $not_opened);
	if ($by_file == $not_opened) return $not_opened;
	$by_req = is_opened_by_request($data_request, $opened, $not_opened);
	if ($by_req == $not_opened) return $not_opened;
	if ($by_req != $by_file) return $not_opened;
	return $opened;
}

global $auth;
global $user;

?>
			<div id="menu">
				<div id="menu_body">
					<div id="menu_container">
<?
						show_menu_element($auth->isAuth() ? "/id{$user->getId()}" : "/", "menu_index", "главная", is_opened_by_script($MENU_INDEX_FILE));
						show_menu_element("/sport", "menu_sport", "спорт", is_opened_by_script($MENU_SPORT_FILE));
						show_menu_element("/life", "menu_life", "жизнь", is_opened_by_script($MENU_LIFE_FILE));
						show_menu_element("/media", "menu_media", "медиа", is_opened_by_script($MENU_MEDIA_FILE));
						show_menu_element("/forum", "menu_forum", "форум", is_opened_by_script($MENU_FORUM_FILE));
?>
					</div>					
				</div>

				<div id="menu_detach_bar"></div>

				<div id="submenu_body">
					<div id="submenu_container">
						<div id="submenu_index"<?=is_opened_by_script($MENU_INDEX_FILE, '', ' style="display: none;"')?>>
<?
					if (!$auth->isAuth()) { // if the user is not authorized on the site
						show_submenu_element("/sign_up", "submenu_sign_up", "регистрация", is_opened_by_script('/sign_up.php'));
						show_submenu_element("/", "submenu_common", "общая", is_opened_by_script('/index.php'));
					} else { // if the user is authorized on the site
						// link to the main page
						show_submenu_element("/", "submenu_common", "общая", is_opened_by_script('/index.php'));
						
						// link to the profile of the current user
						$isMyProfile = $_SERVER['SCRIPT_NAME'] == '/profile.php';
						$isMyProfile = $isMyProfile && (
							(!issetParam('user_id') && !issetParam('player_id') && $auth->isAuth()) ||
							($auth->isAuth() && param('user_id') == $user->getId()) ||
							($auth->isAuth() && param('player_id') == $user->getPmid())
						);
						
						show_submenu_element("/profile", "submenu_current_profile", "Ваш профиль", 
								$isMyProfile ? MENU_DEFAULT_OPENED : MENU_DEFAULT_UNOPENED);

						if ($_SERVER['SCRIPT_NAME'] == '/profile.php' && isset($_REQUEST['user_id'])) {
							if ($_REQUEST['user_id'] != $user->getId()) {
								// if authorized user views some other profile
								try {
									$viewed_user = User::getById($_REQUEST['user_id']);

									// link to the viewed profile
									show_submenu_element('/id' . $viewed_user->getId(), "submenu_profile", string_short($viewed_user->getFullName(), 30, 40), MENU_DEFAULT_OPENED);
								} catch (Exception $e) {}
							}
						} elseif ($_SERVER['SCRIPT_NAME'] == '/profile.php' && isset($_REQUEST['player_id'])) {
							if ($_REQUEST['player_id'] != $user->getPmid()) {
								// if authorized user views some other profile
								try {
									$viewed_player = Player::getById($_REQUEST['player_id']);

									// link to the viewed profile
									show_submenu_element($viewed_player->getURL(), "submenu_profile", string_short($viewed_player->getFullName(), 30, 40), MENU_DEFAULT_OPENED);
								} catch (Exception $e) {}
							}
						}

						// link to signing out
						show_submenu_element("/sign_out", "submenu_exit", "выход", MENU_DEFAULT_UNOPENED);
					}

					if ($_SERVER['SCRIPT_NAME'] == '/page.php' && $_REQUEST['part'] == 'about') {
						show_submenu_element("/about", "submenu_about", "о сайте", MENU_DEFAULT_OPENED);
					}
?>

						</div>
						<div id="submenu_sport"<?=is_opened_by_script($MENU_SPORT_FILE, '', ' style="display: none;"')?>>
<?
						show_submenu_element("/sport/rules", "submenu_rules", "правила", is_opened_by_request(array('part' => 'rules')));
						//show_submenu_element("/sport/faq", "submenu_faq", "чаво", is_opened_by_request(array('part' => 'faq')));
						
						if ($_SERVER['SCRIPT_NAME'] == '/sport.php') { // part Sport is shown
							if (isset($_REQUEST['part'])) { // not the main page of the part is shown
								if ($_REQUEST['part'] == 'league' || $_REQUEST['part'] == 'competition') { // the subpart League is shown
									if (isset($_REQUEST['league_id'])) { // some defined league is shown
										show_submenu_element("/sport/league", "submenu_leagues", "лиги", MENU_DEFAULT_UNOPENED);

										try {
											$viewed_league = League::getById($_REQUEST['league_id']);
											if (isset($_REQUEST['comp_id'])) { // some defined competition is shown
												try {
													$viewed_comp = Competition::getById($_REQUEST['comp_id']);
													show_submenu_element('/sport/league/' . $viewed_league->getId(), "submenu_league" . $viewed_league->getId(), string_short($viewed_league->getName(), 10, 15), MENU_DEFAULT_UNOPENED);
													show_submenu_element('/sport/league/' . $viewed_league->getId() . '/competition/' . $viewed_comp->getId(), "submenu_competition" . $viewed_comp->getId(), string_short($viewed_comp->getName(), 10, 15), MENU_DEFAULT_OPENED);

												} catch (Exception $e) {}
											} else { // the main page of some league is shown
												show_submenu_element('/sport/league/' . $viewed_league->getId(), "submenu_league" . $viewed_league->getId(), string_short($viewed_league->getName(), 10, 15), MENU_DEFAULT_OPENED);
											}
										} catch (Exception $e) {}
									} else { // the main page of the subpart League is shown
										show_submenu_element("/sport/league", "submenu_leagues", "лиги", MENU_DEFAULT_OPENED);
									}
								} else { // some different from League subpart of Sport is shown
									show_submenu_element("/sport/league", "submenu_leagues", "лиги", MENU_DEFAULT_UNOPENED);
								}
							} else { // the main page of the part Sport is shown
								show_submenu_element("/sport/league", "submenu_leagues", "лиги", MENU_DEFAULT_UNOPENED);
							}
						} else { // not the part Sport is shown
							show_submenu_element("/sport/league", "submenu_leagues", "лиги", MENU_DEFAULT_UNOPENED);
						}

						show_submenu_element("/sport/pipemen", "submenu_pipemen", "пайпмены", is_opened_by_request(array('part' => 'pipemen')));
						show_submenu_element("/sport/rating", "submenu_rating", "рейтинг", is_opened_by_request(array('part' => 'rating')));
						show_submenu_element("/sport/statistics", "submenu_statistics", "статистика", is_opened_by_request(array('part' => 'statistics')));
?>

						</div>
						<div id="submenu_life"<?=is_opened_by_script($MENU_LIFE_FILE, '', ' style="display: none;"')?>>
<?
						show_submenu_element("/life", "submenu_lenta", "лента", (($_SERVER['SCRIPT_NAME'] == '/life.php' && !isset($_REQUEST['part']) ? MENU_DEFAULT_OPENED : MENU_DEFAULT_UNOPENED)));
						show_submenu_element("/life/blog", "submenu_blogs", "блоги", is_opened_by_request(array('part' => 'blog')));
						if (issetParam('part') && param('part') == 'blog_editor') {
							show_submenu_element("/life/blog/new", "submenu_new_post", "написать", MENU_DEFAULT_OPENED);
						}
						show_submenu_element("/life/comments", "submenu_comments", "комментарии", is_opened_by_request(array('part' => 'comments')));
						show_submenu_element("/life/people", "submenu_people", "люди", is_opened_by_request(array('part' => 'people')));

?>

						</div>
						<div id="submenu_media"<?=is_opened_by_script($MENU_MEDIA_FILE, '', ' style="display: none;"')?>>
<?
						show_submenu_element("/media/photo", "submenu_photoalbum", "фотоальбомы", is_opened_by_request(array('part' => 'photo')));
						show_submenu_element("/media/video", "submenu_videoalbum", "видеогалерея", is_opened_by_request(array('part' => 'video')));
						show_submenu_element("/media/download", "submenu_download", "скачать", is_opened_by_request(array('part' => 'download')));
?>
							
						</div>
						<div id="submenu_forum"<?=is_opened_by_script($MENU_FORUM_FILE, '', ' style="display: none;"')?>>

						</div>
					</div>
				</div>
			</div>