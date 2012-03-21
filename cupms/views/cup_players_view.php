<?php

require_once dirname(__FILE__).'/../includes/config.php';

require_once dirname(__FILE__).'/../../main/classes/cupms/Cup.php';
require_once dirname(__FILE__).'/../../main/classes/user/User.php';


function cup_player_list(Cup $cup, User $user) {
	$players = $cup->getPlayers();
	$hasPermission = $user->hasPermission($cup->getCompetition(), 'edit');
	$finished = $cup->isFinished();
	$cupId = $cup->getId();
?>
	<ul class="people">
<?
	if (!empty($players)) {
		foreach ($players as $player) {
			if ($hasPermission) {
				$pmid = $player->getId();
?>
		<li id="person_<?=$pmid?>" class="leaf">
			<div>
				<div><?=$player->getFullName()?></div>
				<div id="person_<?=$pmid?>_image"></div>
			</div>
			<div class="clear"></div>
		</li>
<?
				if(!$finished) {
?>
		<script type="text/javascript">
//				PeopleListItem({
//					targetId: <?=$cupId?>,
//					personId: <?=$player->getId()?>,
//					personName: <?=$player->getFullName()?>,
//					onClick: cup.removePlayer
//				});
			var deleteImage = new FadingImage({
				CSSClass: 'fading_image',
				onclick: function(e) {
					cup.removePlayer(<?=$pmid?>, <?=$cupId?>);
					e.stopPropagation();
				}
			}).appendTo('person_<?=$pmid?>_image', $('#person_<?=$pmid?>'));
		</script>
<?
				}
			} else {
?>
		<li id="person_<?=$pmid?>">
			<?=$player->getFullName()?>
		</li>
<?
			}
		}
	}
?>
	</ul>
<?
}
?>
