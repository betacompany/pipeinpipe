<?php
require_once dirname(__FILE__) . '/../classes/cupms/Player.php';
require_once dirname(__FILE__) . '/../classes/cupms/League.php';

/**
 * @param Player $p
 */
function show_pipeman(Player $p) {
	$imgUrl = $p->getImageURL();
	$name = $p->getFullName();
	$leagues = $p->getLeaguesInfo();
	$city = $p->getCity();
?>

	<div class="pipeman_wrapper">
		<div class="pipeman">
			<img src="<?=$imgUrl?>" alt="<?=$name?>">
			<div class="description">
				<div class="name">
					<a href="<?=$p->getURL()?>"><?=$name?></a>
				</div>
				<div class="city">
					<?=$city?>
				</div>
<?
		if ($leagues) {
			$i = 0;
			foreach ($leagues as $league) {
				if ($i++ >= 2) {
					break;
				}
				$leagueName = League::getById($league['league_id'])->getName();
?>
				<div class="league_info">
					<?="#" . $league['place'] . " " . $leagueName?>
				</div>
<?
			}
		}
?>

			</div>
		</div>
	</div>
<?
}

?>

<div id="pipemen_container" class="body_container">
	<h2>Ну типа крутой заголовок</h2>
<?
	$players = Player::getAll();
	foreach ($players as $player) {
		show_pipeman($player);
	}
?>

</div>
<?
?>
