<?php
/**
 * @author Innokenty Shuvalov
 */
require_once dirname(__FILE__).'/../views/sport_league_functions.php';
require_once dirname(__FILE__).'/../classes/cupms/League.php';
require_once dirname(__FILE__).'/../classes/cupms/RatingTable.php';

try {
	assertIsset($_REQUEST['method'], 'method');
	$method = $_REQUEST['method'];

	switch ($method) {
		case 'load_competitions_page' :
		case 'load_pipemen_page':
			
			assertIsset($_REQUEST['page'], 'page');
			$page = intval($_REQUEST['page']);
			
			assertIsset($_REQUEST['league_id'], 'league_id');
			$leagueId = intval($_REQUEST['league_id']);
			
			switch ($method) {
				case 'load_competitions_page' :
					league_show_competitions(League::getById($leagueId), $page);
					exit(0);

				case 'load_pipemen_page':
					league_show_rating(League::getById($leagueId), $page);
					exit(0);
			}
		
			exit(0);
	}

} catch (Exception $ex) {
	echo $ex->getMessage();
}

?>
