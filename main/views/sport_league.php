<div class="body_container">
<?php
/**
 * @author Innokenty Shuvalov
 */

require_once dirname(__FILE__).'/../classes/cupms/RatingTable.php';
require_once dirname(__FILE__).'/../classes/cupms/Competition.php';
require_once dirname(__FILE__).'/../classes/cupms/League.php';

require_once dirname(__FILE__).'/../includes/common.php';

require_once dirname(__FILE__).'/sport_league_functions.php';

try {
	assertIsset($_REQUEST['league_id'], 'league_id');
	$leagueId = $_REQUEST['league_id'];
	$league = League::getById($leagueId);
?>
	<div id="league_header">
		<img id="league_image" src="<?=$league->getImageURL()?>" alt="<?=$league->getName()?>"/>
		<div id="league_name">
			<h1><?=$league->getName()?></h1>
			<div><?=$league->getDescription()?></div>
		</div>
		<div style="clear: both;"></div>
	</div>

	<div id="league_body">
		<div id="left_column">
			<div>
<?

	league_show_news($league);

	$competitions = $league->getCompetitions();
	if (!empty($competitions)) {
?>

				<div id="league_competitions" class="slide_block">
<?
		league_show_competitions($league);
?>

				</div>
<?
	}

	league_show_photos($league);

	league_show_videos($league);
?>

				<div id="league_admins" class="slide_block">
					<div class="title opened">
						<div class="left">
							<div class="content">
								Администраторы Лиги
							</div>
						</div>
						<div class="right">
							<div class="quick" onclick="javascript: slideBlock.togglePart('league_admins')"></div>
						</div>
						<div style="clear: both"></div>
					</div>
					<div class="body hidden" style="display: block">
<?
	league_show_admins_list($league);
?>
						<div style="clear: both"></div>
					</div>
				</div>
			</div>
		</div>

		<div id="right_column">
			<div id="league_rating" class="slide_block">
<?
	league_show_rating($league);
?>
			</div>
		</div>
<?
} catch (Exception $e) {
	echo $e->getMessage();
}	
?>
	</div>
</div>
