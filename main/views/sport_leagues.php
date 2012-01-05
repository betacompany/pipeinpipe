<div id="league_preview" class="body_container">
<?php
/**
 * @author Innokenty Shuvalov
 */

require_once dirname(__FILE__).'/../classes/cupms/RatingTable.php';
require_once dirname(__FILE__).'/../classes/cupms/RatingFormula.php';
require_once dirname(__FILE__).'/../classes/cupms/Competition.php';
require_once dirname(__FILE__).'/../classes/cupms/League.php';
require_once dirname(__FILE__).'/sport_league_functions.php';
require_once dirname(__FILE__).'/../includes/security.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

foreach (League::getTopLeagues($user) as $league) {
?>
<div class="body round_border">
	<a href="/sport/league/<?=$league->getId()?>" class="image">
		<img style="width: 250px" src="<?=$league->getImageURL()?>" alt="<?=$league->getName()?>"/>
	</a>

	<div id="leagues" class="content_wrapper">
		<div class="content">
			<h2 class="other">
				<a href="/sport/league/<?=$league->getId()?>"><?=$league->getName()?></a>
			</h2>
			<div class="description"><?=$league->getDescription()?></div>
<?
	if (count($league->getCompetitions()) != 0) {
?>
			<div class="league_preview_competitions" style="width: <?=count($league->getCompetitions()) * 60 + 1?>px">
<?
		foreach ($league->getCompetitionsChronologically() as $comp) {
			league_show_competition_preview_short($comp);
		}
?>
			</div>
<?
	}
?>
			<div class="clear"></div>
		</div>
	</div>

	<div class="clear"></div>
</div>
<?
}
?>
<script type="text/javascript">
	$(function () {
		$('.league_preview_competitions').draggable({
			axis: 'x',
			cursor: 'e-resize',
			drag: function(e, ui) {}
		});
		preventSelection(ge('leagues'));
	});
</script>
</div>