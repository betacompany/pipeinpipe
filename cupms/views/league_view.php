<?php
require_once dirname(__FILE__) . '/../includes/config.php';

require_once dirname(__FILE__) . '/../../main/classes/cupms/League.php';

require_once dirname(__FILE__) . '/../../main/classes/user/User.php';

require_once dirname(__FILE__) . '/../templates/response.php';

function league_add_page() {
?>
<div id="content">
	<div id="content_header">
		Создание новой лиги
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
				<div>Формула:</div>
					<div id="formula_selector">
					<script type="text/javascript">
						var formulaSelector = (new Selector({
							content: <?=json(RatingFormula::getInstance()->getAllToArray());?>
						}))
						.appendTo($('#formula_selector'));
					</script>
					</div>
			</div>
			<div>
				<div id="create_league_button">
					<script type="text/javascript">
						var createLeagueButton = (new Button({
							onClick: league.create,
							container: 'create_league_button',
							html: "Создать"
						}));
					</script>
				</div>
			</div>
		</div>
	</div>
</div>
<?
}

function league_main_page(League $league) {
	$legueId = $league->getId();
?>
<div id="content">
	<div id="content_header">
		<?=$league->getName();?>
	</div>

	<div id="content_menu">
		<ul>
			<li id="content_menu_properties" class="selected">
				<a href="#league/<?=$legueId?>" onclick="javascript: league.loadProperties(<?=$legueId?>);">Общее</a>
			</li>
			<li id="content_menu_admins">
				<a href="#league/<?=$legueId?>/admins" onclick="javascript: league.loadAdmins(<?=$legueId?>);">Администраторы</a>
			</li>
		</ul>
	</div>

	<div id="content_body">
<? league_properties($league); ?>
	</div>
</div>
<?
}

function league_properties(League $league) {
?>
		<div class="content_properties">
			<div>
				<div>Название:</div>
				<div onclick="javascript: league.editName(this);"><?=$league->getName();?></div>
			</div>

			<div>
				<div>Формула:</div>
				<script type="text/javascript">
					var content = <?=json(RatingFormula::getInstance()->getAllToArray());?>;
				</script>
				<div onclick="javascript: league.editFormula(this, <?=$league->getId()?>, content);"><?=$league->getFormula()->getName();?></div>
			</div>

			<div>
				<div>Описание:</div>
<?
	$descr = $league->getDescription();
	if ($descr == '')
		$descr = 'Создайте описание для этой лиги!'
?>
				<div onclick="javascript: league.editDesc(this);"><?=$descr;?></div>
			</div>
		</div>

<?
}

function league_admins(League $league, User $user) {
	$canDelete = $user->hasPermission($league, 'delete_admin');
	$leagueId = $league->getId();
	$admins = array();
?>
<div>Администраторы этой лиги:</div>
<ul class="people">
<?
	foreach (User::getAll() as $oneUser) {
		if ($oneUser->hasPermission($league, 'edit')) {
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
			if ($canDelete && $uid != $user->getId() && $oneUser->isLeagueAdmin($leagueId)) {
?>
	<script type="text/javascript">
		var deleteImage = new FadingImage({
			CSSClass: 'fading_image',
			onclick: function(e) {
				league.deleteAdmin(<?=$uid?>, <?=$leagueId?>);
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
	if ($user->hasPermission($league, 'add_admin')) {
?>
<div>
	Наберите имя пользователя, чтобы добавить его в список администраторов лиги<? if (!$canDelete) { ?>,<br>
	но помните, что вы не сможете его из этого списка удалить!<?}?>
</div>
<div id="selector" style="width: 240px;">
	<script type="text/javascript">
		//$(document).ready(function() {
			var peopleSelector = (new DynamicSelector({
				content: <?=json(array_diff_value(User::getAllToHTML(), $admins));?>,
				onSelect: function(id) {
					league.makeAdmin(<?=$leagueId?>, id, <?echo $canDelete ? 'true' : 'false'?>)
				}
			}))
			.setWidth(230)
			.appendTo($('#selector'));
		//});
	</script>
</div>
<?
	}
}
?>
