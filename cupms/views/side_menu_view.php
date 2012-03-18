<?php
/**
 * @author Andrew Solozobov
 */
require_once '../main/classes/cupms/League.php';
require_once '../main/classes/user/User.php';

require_once dirname(__FILE__) . '/../templates/response.php';

/**
 *
 * @param <User object> $user those user that we generate menu for
 */
function getSideMenu(User $user){
?>
<ul>
<?
	if ($user->hasPermission('total_admin', 'add')) {
?>
	<li>
		<a href="#admins" onclick="javascript: loadAdmins(this);">Администраторы</a>
	</li>
<?
	}
	if ($user->hasPermission('player', 'add')) {
?>
	<li class="edit_players">
		<a href="#players" onclick="javascript: editPlayers(this);">Редактировать пайп-менов</a>
	</li>
<?
	}
	if ($user->hasPermission('league', 'add')) {
?>
	<li class="add">
		<a href="#league/new" onclick="javascript: addLeague(this);">Создать лигу</a>
	</li>
<?
	}

	$leagues = League::getAll();
	foreach ($leagues as $league) { //writing out LEAGUES
		if ( $user->hasPermission($league, 'edit') ){
?>
	<li class="folded" id="league<?=$league->getId()?>" onclick="javascript: if (window.event.srcElement==this) listSlideToggle($(this));">
		<a href="#league/<?=$league->getId()?>" onclick="javascript: editLeague(<?=$league->getId()?>, this);"><?=$league->getName()?></a>
		<ul>
			<li class="add">
				<a href="#league/<?=$league->getId()?>/new_competition" onclick="javascript: addCompetition(<?=$league->getId()?>, this);">Создать турнир</a>
			</li>
<?
			$competitions = array_reverse($league->getCompetitions());
			foreach ($competitions as $competition) { // writing out COMPETITIONS
?>
			<li id="competition<?=$competition->getId()?>" class="leaf">
				<a href="#competition/<?=$competition->getId()?>" onclick="javascript: editCompetition(<?=$competition->getId()?>, this)"><?=$competition->getName()?></a>
			</li>
<?
		}
?>
		</ul>
	</li>
<?
		} else if ($user->isLeagueCompetitionAdmin($league->getId())) {
?>
	<li class="unfolded" onclick="javascript: if (window.event.srcElement == this) listSlideToggle($(this));">
		<?=$league->getName()?>
		<ul>
<?
			$competitions = array_reverse($league->getCompetitions());
			foreach ($competitions as $competition) { // writing out COMPETITIONS
				if ($user->isCompetitionAdmin($competition->getId())) {
?>
			<li class="leaf">
				<a href="#competition/<?=$competition->getId()?>" onclick="javascript: editCompetition(<?=$competition->getId()?>, this)"><?=$competition->getName()?></a>
			</li>
<?
				}
			}
?>
		</ul>
	</li>
<?
		}
	}
?>
</ul>
<?
}

function loadTotalAdmins(User $user) {
	$canDelete = $user->hasPermission('total_admin', 'delete');
	$admins = array();
?>
<div>Администраторы сайта:</div>
<ul class="people">
<?
	foreach (User::getAll() as $oneUser) {
		if ($oneUser->isTotalAdmin()) {
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
			if ($canDelete && $uid != $user->getId()) {
?>
	<script type="text/javascript">
		var deleteImage = new FadingImage({
			CSSClass: 'fading_image',
			onclick: function(e) {
				deleteAdmin(<?=$uid?>);
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
	if ($user->hasPermission('total_admin', 'add')) {
?>
<div>
	Наберите имя пользователя, чтобы добавить его в список администраторов сайта<? if (!$canDelete) { ?>,<br>
	но помните, что вы не сможете его из этого списка удалить!<?}?>
</div>
<div id="selector" style="width: 240px;">
	<script type="text/javascript">
		//$(document).ready(function() {
			var peopleSelector = (new DynamicSelector({
				content: <?=json(array_diff_value(User::getAllToHTML(), $admins));?>,
				onSelect: function(id) {
					makeAdmin(id, <?echo $canDelete ? 'true' : 'false'?>)
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