<?php
require_once dirname(__FILE__) . '/../includes/config.php';

require_once dirname(__FILE__) . '/../../' . MAINSITE . '/classes/cupms/Competition.php';
require_once dirname(__FILE__) . '/../../' . MAINSITE . '/classes/cupms/Tournament.php';
require_once dirname(__FILE__) . '/../../' . MAINSITE . '/classes/db/ResultCupDBClient.php';

require_once dirname(__FILE__) . '/../../' . MAINSITE . '/classes/user/User.php';

require_once dirname(__FILE__) . '/cup_tree_view.php';
require_once dirname(__FILE__) . '/cup_players_view.php';

require_once dirname(__FILE__) . '/../templates/response.php';

/**
 * This function echos HTML-code contianing main structure for competition
 * @param Competition $competition
 * @param User $user
 */
function competition_main_page(Competition $competition, User $user) {
	//$editable = $user->hasPermission($competition, 'edit');
?>
<div id="content">
	<div id="content_header">
		<?=$competition->getName() ?>
    </div>

	<div id="content_menu">
		<ul>
			<li id="content_menu_properties" class="selected">
				<a href="#competition/<?=$competition->getId() ?>" onclick="javascript: competition.loadProperties(<?=$competition->getId() ?>);">Общее</a>
			</li>
			<li id="content_menu_structure">
				<a href="#competition/<?=$competition->getId() ?>/structure" onclick="javascript: competition.loadStructure(<?=$competition->getId() ?>);">Структура</a>
			</li>
			<li id="content_menu_players">
				<a href="#competition/<?=$competition->getId() ?>/players" onclick="javascript: competition.loadPlayers(<?=$competition->getId() ?>);">Участники</a>
			</li>
			<li id="content_menu_games">
				<a href="#competition/<?=$competition->getId() ?>/games" onclick="javascript: competition.loadGames(<?=$competition->getId() ?>);">Матчи</a>
			</li>
			<li id="content_menu_admins">
				<a href="#competition/<?=$competition->getId()?>/admins" onclick="javascript: competition.loadAdmins(<?=$competition->getId() ?>);">Администраторы</a>
			</li>
			<li id="content_menu_monitoring">
				<a href="#competition/<?=$competition->getId()?>/monitoring" onclick="javascript: competition.loadMonitoring(<?=$competition->getId() ?>);">Мониторинг</a>
			</li>
			<li id="content_menu_delete">
				<a href="#competition/<?=$competition->getId()?>/delete" onclick="javascript: competition.loadDeleteConfirmation(<?=$competition->getId() ?>);">Удалить</a>
			</li>
		</ul>
	</div>

	<div id="content_body">
<? competition_properties($competition, $user); ?>
    </div>
</div>
<?
}

function competition_add_page($leagueId) {
?>
<div id="content">
	<div id="content_header">
		Создание нового турнира
	</div>
	<div id="content_body">
		<div class="content_properties">
			<div>
				<div>Название:</div>
				<div>
					<input type="text" name="name" />
				</div>
			</div>
			<div>
				<div>Описание:</div>
				<div>
					<textarea name="description"></textarea>
				</div>
			</div>
			<div>
				<div id="create_competition_button">
					<script type="text/javascript">
						var createCompetitionButton = new Button({
							onClick: function() {
								competition.create(<?=$leagueId?>);
							},
							container: 'create_competition_button',
							html: "Создать"
						});
					</script>
				</div>
				<div></div>
			</div>
		</div>
	</div>
</div>
<?
}

/**
 * This function echos HTML-code containing block with properties of competition
 * @param Competition $competition
 * @param User $user
 */
function competition_properties(Competition $competition, User $user) {
	$tournament = $competition->getTournament();
?>
		<div id="competition_status" style="margin-top: 10px; float: left;">
<?
	if (!$competition->hasMainCup()) {
?>
			<div id="create_main_cup">
				В этом турнире нет главного этапа, рекомендуем вам
				<a class="link" href="#competition/<?=$competition->getId()?>/structure" onclick="javascript: competition.loadStructure(<?=$competition->getId()?>);">его создать</a>.
			</div>
<?
		if ($competition->getStatus() == Competition::STATUS_DISABLED) {
?>
			<div class="competition_action_button" onclick="javascript: competition.startRegistering();">Старт регистрации</div>
			<div id="button_label">Запустить турнир, сделав его доступным на основном сайте.</div>
<?
		}
	} else switch ($competition->getStatus()) {
	case Competition::STATUS_DISABLED:
		if ($user->hasPermission($competition, 'start')) {
?> 

			<div class="competition_action_button" onclick="javascript: competition.start();">Старт</div>
			<div class="competition_action_button" onclick="javascript: competition.startRegistering();">Старт регистрации</div>
			<div id="button_label">Запустить турнир, сделав его доступным на основном сайте.</div>
<?
		}
		
		break;

	case Competition::STATUS_REGISTERING:
?>

			<div class="competition_action_button" onclick="javascript: competition.start();">Старт</div>
			<div id="button_label">Запустить турнир.</div>
<?
		break;

	case Competition::STATUS_RUNNING:
		if ($user->hasPermission($competition, 'stop')) {
?>
			<div class="competition_action_button" onclick="javascript: competition.stop('<?=$competition->getDate()?>', <?=$competition->getCoef()?>);">Стоп</div>
			<div id="button_label">
				Остановить турнир, сделав его невозможным для редактирования.
				Рассчитать все рейтинговые очки.
			</div>
<?
		}

		break;

	case Competition::STATUS_FINISHED:
		if ($user->hasPermission($competition, 'restart')) {
?>

			<div class="competition_action_button" onclick="javascript: competition.restart();">Рестарт</div>
			<div id="button_label">
				Перезапустить турнир, тем самым сделав его доступным для редактирования.
				Удалить все записи относительно рейтинговых очков.
			</div>
<?
		}

		break;
	}
?>

		</div>

		<div class="content_properties">
<?
	if (!$competition->isFinished()) {
?>
			<div>
                <div>Название:</div>
                <div onclick="javascript: competition.editName(this);"><?=$competition->getName() ?></div>
            </div>
            <div>
                <div>Серия турниров:</div>
                <div id="tournament_name" onclick="javascript: competition.showTournamentSelector();"><?=($tournament != null ? $tournament->getName() : 'не установлен')?></div>
				<div id="tournament_selector_panel">
					<div id="tournament_selector">
						<script type="text/javascript">
							var tournamentSelector = new DynamicSelector({
								content: <?=json(Tournament::getAllToArray());?>,
								onSelect: competition.editTournament
							});

							tournamentSelector.setWidth(230);
							tournamentSelector.appendTo($('#tournament_selector'));
						</script>
					</div>
					<input class="edit_tournament_button" type="button" value="Создать" onclick="javascript: competition.createTournament(<?=$competition->getId()?>, tournamentSelector.text());"/>
					<input class="edit_tournament_button" type="button" value="Отмена" onclick="javascript: competition.hideTournamentSelector();"/>
				</div>
			</div>
            <div>
                <div>Дата завершения:</div>
                <div onclick="javascript: competition.editDate(this);"><?=$competition->getDate() ?></div>
            </div>
            <div>
                <div>Коэффициент:</div>
                <div onclick="javascript: competition.editCoef(this, <?=$competition->getId()?>, <?=$user->hasPermission($competition, 'set_coef')?>);"><?=$competition->getCoef()?></div>
            </div>
            <div>
                <div>Описание:</div>
<?
	$descr = $competition->getDescription();
	if ($descr == '')
		$descr = 'Создайте описание для этого турнира!'
?>
                <div onclick="javascript: competition.editDesc(this);"><?=$descr ?></div>
            </div>
<?
	} else {
?>
			<div>
                <div>Название:</div>
				<div><?=$competition->getName()?></div>
            </div>
            <div>
                <div>Серия турниров:</div>
				<div id="tournament_name"><?=($tournament != null ? $tournament->getName() : 'не установлен')?></div>
			</div>
            <div>
                <div>Дата завершения:</div>
                <div><?=$competition->getDate()?></div>
            </div>
            <div>
                <div>Коэффициент:</div>
                <div><?=$competition->getCoef()?></div>
            </div>
            <div>
                <div>Описание:</div>
<?
	$descr = $competition->getDescription();
	if ($descr == '')
		$descr = 'Создайте описание для этого турнира!'
?>
                <div><?=$descr?></div>
            </div>
<?
	}
?>
        </div>
<?
}

/**
 *
 * @param Competition $competition
 * @param User $user
 */
function competition_structure(Competition $competition, User $user) {
	$cup = $competition->getMainCup();
	if ($cup == null) {
		//add_cup($competition, 0);
?>
<script type="text/javascript">
	newCupPanel = new AddCupPanel({
		compId: <?=$competition->getId()?>,
		parentCupId: 0,
		container: 'content_menu',
		speed: 'fast',
		onCancel: function() {
			competition.loadProperties(<?=$competition->getId()?>);
		}
	}).slideDown();

	$('#content_menu > ul > li').click(function() {
		// FIXME 'newCupPanel is undefined' как, блять, так?!
		newCupPanel.slideUp();
	});
</script>
<?
	} else {
?>
<div id="cup_tree">
	<ul>
<?
		draw_cup($cup);
?>
	</ul>
</div>
<?
	}
}

function competition_players(Competition $competition, User $user, $selectedCup = null) {
	$cups = $competition->getCupsList();
	if (count($cups) != 0) {
		if ($selectedCup == null) {
			$selectedCup = $competition->hasMainCup() ? $competition->getMainCup() : $cups[0];
		}
?>

<div id="content_submenu">
	<ul>
<?
		foreach ($cups as $cup) {
?>
		<li id="cup_<?=$cup->getId()?>"<?=($cup->getId() == $selectedCup->getId() ? " class=\"selected\"" : "")?>>
			<a href="#competition/<?=$competition->getId()?>/players/<?=$cup->getId()?>" onclick="javascript: competition.loadPlayersCup(<?=$cup->getId();?>)"><?=$cup->getName();?></a>
		</li>
<?
		}
		cup_player_list($selectedCup, $user);

		if (!($selectedCup instanceof CupPlayoff) && !($selectedCup->isFinished()) && $user->hasPermission($selectedCup->getCompetition(), 'edit')) {
			$players = array_diff_value(Player::getAll(), $selectedCup->getPlayers());
?>

	</ul>
</div>

<div>Наберите имя пайпмена, чтобы добавить его в список участников:</div>
<div id="selector" style="width: 240px;">
	<script type="text/javascript">
		//$(document).ready(function() {
			var peopleSelector = new DynamicSelector({
				content: <?=json(array_transform_toHTML($players));?>,
				onSelect: function(id) {
					cup.addPlayer(<?=$selectedCup->getId()?>, id)
				}
			});

			peopleSelector.setWidth(230);
			peopleSelector.appendTo($('#selector'));
		//});
	</script>
</div>
<?
		}
	} else {
?>

<div id="content_submenu">
	<ul>
		<li>
			<a href="#competition/<?=$competition->getId()?>/zherebjator"
			   onclick="competition.loadZherebjator(<?=$competition->getId()?>)">Жеребьятор</a>
		</li>
		<li>
			<a href="#competition/<?=$competition->getId()?>/players" 
			   class="selected"
			   onclick="competition.loadPlayers(<?=$competition->getId()?>)">Управление</a>
		</li>
	</ul>
</div>

<div style="margin-top: 40px; margin-left: 10px;">
	<p>В этом соревновании нет ещё ни одного турнира!</p>
	<p>Сначала
		<a class="link" href="#competition/<?=$competition->getId()?>/structure" onclick="javascript: competition.loadStructure(<?=$competition->getId()?>);">создайте турнир</a>,
		а потом добавляйте в него игроков.
	</p>
	<p>
		Либо вы можете воспользоваться
		<a class="link" href="#competition/<?=$competition->getId()?>/zherebjator"
		onclick="competition.loadZherebjator(<?=$competition->getId()?>)">Жеребьятором</a>,
		при помощи которого можно удобно организовать регистрацию на турнир, а также провести
		автоматическую жеребьёвку с распределением участников на корзины и группы.
	</p>
</div>
<?
	}
}

function competition_zherebjator(Competition $competition, User $user) {
?>

<div id="content_submenu">
	<ul>
		<li>
			<a href="#competition/<?=$competition->getId()?>/zherebjator"
			   class="selected"
			   onclick="competition.loadZherebjator(<?=$competition->getId()?>)">Жеребьятор</a>
		</li>
		<li>
			<a href="#competition/<?=$competition->getId()?>/players"
			   onclick="competition.loadPlayers(<?=$competition->getId()?>)">Управление</a>
		</li>
	</ul>
</div>

<div style="margin: 40px 10px;">
	<div style="margin-bottom: 20px;">
		<div id="player_selector" style="margin-right: 30px;">
			<script type="text/javascript">
				var peopleSelector = (new DynamicSelector({
					content: <?=json(Player::getAllToHTML());?>,
					onSelect: function (id) {
						zh.selected(id, peopleSelector);
					},
					onChange: function () {
						zh.selected(false, peopleSelector);
					}
				}))
				.setWidth(324)
				.appendTo($('#player_selector'));
			</script>
		</div>
		<div style="clear: both;"></div>
	</div>
	<div style="margin-bottom: 20px;">
		<div id="register_button">
			<script type="text/javascript">
				var newPlayerButton = (new Button({
					onClick: zh.register,
					container: 'register_button',
					html: "Зарегистрировать:"
				}));
			</script>
		</div>
		<div id="selected" style="float: left; position: relative; padding-top: 3px; padding-left: 5px; width: 200px;"></div>
		<div id="basket_button">
			<script type="text/javascript">
				var newPlayerButton = (new Button({
					onClick: zh.view,
					container: 'basket_button',
					html: "Просмотреть корзины"
				}));
			</script>
		</div>
		<div style="float: left; width: 140px; padding-left: 10px;">
			<span>от</span>
			<select>
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
				<option value="5">5</option>
				<option value="6">6</option>
				<option value="7">7</option>
				<option value="8">8</option>
				<option value="9">9</option>
				<option value="10">10</option>
			</select>
			<span>до</span>
			<select>
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
				<option value="5">5</option>
				<option value="6">6</option>
				<option value="7">7</option>
				<option value="8">8</option>
				<option value="9">9</option>
				<option value="10">10</option>
			</select>
			<span>чел. в группе</span>
		</div>
		<div style="clear: both;"></div>
	</div>
	<table id="reg_table" class="full">
		<thead>
			<th style="width: 30px;">№№</th>
			<th style="width: 30px;">uid</th>
			<th>имя</th>
			<th>фамилия</th>
			<th style="width: 30px;">pmid</th>
			<th>имя</th>
			<th>фамилия</th>
			<th style="width: 15px;"></th>
		</thead>
		<tbody></tbody>
	</table>
	<script type="text/javascript">
		zh._compId = location.hash.split('/')[1];
		zh.getRegistered();
	</script>
</div>
<?
}

function competition_admins(Competition $competition, User $user) {
	$canDelete = $user->hasPermission($competition, 'delete_admin');
	$compId = $competition->getId();
	$admins = array();
?>
<div>Администраторы этого турнира:</div>
<ul class="people">
<?
	foreach (User::getAll() as $oneUser) {
		if ($oneUser->hasPermission($competition, 'edit')) {
			$uid = $oneUser->getId();
			$admins[] = $oneUser->toHTML();
?>
	<li id="person_<?=$uid?>" class="leaf">
		<div>
			<div><?=$oneUser->getFullName()?></div>
			<div id="person_<?=$uid?>_image"></div>
		</div>
		<div class="clear"></div>
	</li>
<?
			if ($canDelete && $uid != $user->getId() && $oneUser->isCompetitionAdmin($compId)) {
?>
	<script type="text/javascript">
		var deleteImage = new FadingImage({
			CSSClass: 'fading_image',
			onclick: function(e) {
				competition.deleteAdmin(<?=$uid?>, <?=$compId?>);
				e.stopPropagation();
			}
		}).appendTo('person_<?=$uid?>_image', $('#person_<?=$uid?>'));
	</script>
<?
			}
		}
	}
?>
</ul>
<?
	if ($user->hasPermission($competition, 'add_admin')) {
?>
<div>
	Наберите имя пользователя, чтобы добавить его в список администраторов турнира<? if (!$canDelete) { ?>,<br>
	но помните, что вы не сможете его из этого списка удалить!<?}?>
</div>
<div id="selector" style="width: 240px;">
	<script type="text/javascript">
		//$(document).ready(function() {
			var peopleSelector = new DynamicSelector({
				content: <?=json(array_diff_value(User::getAllToHTML(), $admins));?>,
				onSelect: function(id) {
					competition.makeAdmin(<?=$compId?>, id, <?echo $canDelete ? 'true' : 'false'?>)
				}
			});

			peopleSelector.setWidth(230);
			peopleSelector.appendTo($('#selector'));
		//});
	</script>
</div>
<?
	}
}

function competition_games(Competition $competition, User $user, $selectedCup = null) {
	$cups = $competition->getCupsList();
	
	if ($competition->hasMainCup()) {
		if ($selectedCup == null) {
			$selectedCup = $competition->getMainCup();
		}
?>
<div id="content_submenu">
	<ul>
<?
		foreach ($cups as $cup) {
?>
		<li id="cup_<?=$cup->getId()?>"<?=($cup->getId() == $selectedCup->getId() ? " class=\"selected\"" : "")?>>
			<a href="#competition/<?=$competition->getId()?>/games/<?=$cup->getId()?>" onclick="javascript: competition.loadGamesCup(<?=$cup->getId();?>)"><?=$cup->getName();?></a>
		</li>
<?
		}
?>
	</ul>
</div>
<? 
		cup_games($selectedCup);
	} else {
?>
<div>
	<p>В этом соревновании нет ещё ни одного турнира!</p>
	<p>Сначала
		<a class="link" href="#competition/<?=$competition->getId()?>/structure" onclick="javascript: competition.loadStructure(<?=$competition->getId()?>);">создайте турнир</a>,
		а потом редактируйте его матчи.
	</p>
</div>
<?
	}
}

function competition_delete_confirmation ($comp_id) {
?>
<script type="text/javascript">
	var deleteCompConfirm = function() {
		competition.deleteСompetition(<?=$comp_id?>);
	};
	var deleteCompCancel = function() {
		alert('Удаление отменено!');
		competition.loadProperties(<?=$comp_id?>)
	};

	$('#delete_competition_confirm').click(deleteCompConfirm);
	$('#delete_competition_cancel').click(deleteCompCancel);
</script>
<div>
	<p>Вы уверены, что хотите <b>удалить</b> этот турнир?</p>
	<p>Восстановить его будет <b>невозможно</b>!</p>
	<input id="delete_competition_confirm" type="button" value="Удалить"/>
	<input id="delete_competition_cancel" type="button" value="Cancel"/>
</div>
<?
}

function competition_get_results_for(Cup $cup, &$storage, $stage) {
	$req = ResultCupDBClient::selectByCupId($cup->getId());
	$result = array();
	while ($d = mysql_fetch_assoc($req)) {
		$result[] = $d;
	}
	$storage[$stage] = !isset ($storage[$stage]) ? $result : array_merge($storage[$stage], $result);
	foreach ($cup->getChildren() as $child) {
		competition_get_results_for($child, $storage, $stage + 1);
	}
}

function competition_monitoring(Competition $competition) {
	$storage = array();
	$mainCup = $competition->getMainCup();
	competition_get_results_for($mainCup, $storage, 0);
	
	$data = array();
	foreach ($storage as $stage => $array) {
		foreach ($array as $datum) {
			$pm = Player::getById($datum['pmid'])->getShortName();
			$incr = $datum['points'];
			$data[$pm][$stage] = $incr;
		}
	}

	echo count($data);
?>
<pre><?print_r($data);?></pre>
<?
}

?>
