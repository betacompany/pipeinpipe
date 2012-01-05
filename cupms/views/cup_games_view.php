<?php

require_once dirname(__FILE__) . '/../includes/config.php';

require_once dirname(__FILE__) . '/../templates/response.php';

require_once dirname(__FILE__) . '/../../' . MAINSITE . '/classes/cupms/Cup.php';
require_once dirname(__FILE__) . '/../../' . MAINSITE . '/classes/cupms/CupOneLap.php';
require_once dirname(__FILE__) . '/../../' . MAINSITE . '/classes/cupms/CupTwoLaps.php';
require_once dirname(__FILE__) . '/../../' . MAINSITE . '/classes/cupms/CupPlayoff.php';

require_once dirname(__FILE__) . '/../../' . MAINSITE . '/classes/user/User.php';

// remember that CupTwoLaps extends CupOneLap

function cup_games(Cup $cup) {
	if ($cup instanceof CupOneLap) {
		cup_games_grid($cup);
	} else if ($cup instanceof CupPlayoff) {
		cup_games_playoff($cup);
	} else if ($cup->isUndefined()) {
		cup_games_type($cup);
	}
}

function cup_games_type(Cup $cup) {
	echo 'undefined type';
}

function cup_games_grid(Cup $cup) {
	$grid = $cup->getGameGrid();
	if (!empty($grid)) {
		$editable = !$cup->isFinished();

		$players = $cup->getPlayers();
		$resultTable = ResultTable::getForCup($cup);
?>
<div class="grid">
	<table class="grid">
		<thead>
			<th>Имя Пайп-мена</th>
			<th>Очки</th>
			<th>В</th>
			<th>В5</th>
			<th>В6</th>
			<th>ВБ</th>
			<th>П</th>
			<th>ПБ</th>
			<th>П6</th>
			<th>П5</th>
		</thead>
		<tbody>
<?
		foreach ($resultTable as $i => $row) {
			$name = $row->getPlayer()->getShortName();
?>
			<tr>
				<td><?echo ('' . ($i + 1) . '. '.$name)?></td>
				<td><b><?=$row->getPoints()?></b></td>
				<td><b><?=$row->getWin()?></b></td>
				<td><?=$row->getWin5()?></td>
				<td><?=$row->getWin6()?></td>
				<td><?=$row->getWinb()?></td>
				<td><b><?=$row->getLose()?></b></td>
				<td><?=$row->getLoseb()?></td>
				<td><?=$row->getLose6()?></td>
				<td><?=$row->getLose5()?></td>
			</tr>
<?
		}
?>
		</tbody>
	</table>
</div>
<?
		if ($editable) {
?>
<input id="save_cup_games" type="button" value="Пересчитать Таблицу" onclick="javascript: cup.recalcResultTable(<?=$cup->getId()?>)">
<?
		}
?>
<div class="grid">
	<table class="grid">
		<thead>
			<th></th>
<?		
		for ($i = 1; $i <= count($players); $i++) {
?> 
		<th><?=$i?></th>
<?
		}
?>
		</thead>
		<tbody>
<?
		foreach ($players as $index => $rowPlayer) {
?>
			<tr>
				<td>
					<?=$index + 1?>. <?=$rowPlayer->getShortName();?>
				</td>
<?
			foreach ($players as $colPlayer) {
				$i = $cup->getPlayerIndex($rowPlayer->getId());
				$j = $cup->getPlayerIndex($colPlayer->getId());
				$game = $grid[$i][$j];
				$gameEditable = $editable && (($cup instanceof CupTwoLaps && i != j) || $i < $j);
?>
			<td<? if ($gameEditable) {?> class="game score_regularity"<?}?>>
<?
				if ($i == $j) {
					echo 'x';
				} else if ($game != null) {
					if (!$gameEditable) {
						echo $game->getScoreOrType(1) . ':' . $game->getScoreOrType(2);;
					} else {
?>
				<input type="text" value="<?=$game->getScoreOrType(1)?>" onfocus="javascript: cup.editRegularGame(this, 1, <?=$game->getId()?>)"/>:<input type="text" value="<?=$game->getScoreOrType(2)?>" onfocus="javascript: cup.editRegularGame(this, 2, <?=$game->getId()?>)"/>
<?
					}
				} else if ($game == null && $gameEditable) {
?>
				<input type="text" onfocus="javascript: cup.editRegularGame(this, 1, null, <?=$cup->getId()?>, <?=$rowPlayer->getId()?>, <?=$colPlayer->getId()?>)"/>:<input type="text" onfocus="javascript: cup.editRegularGame(this, 2, null, <?=$cup->getId()?>, <?=$rowPlayer->getId()?>, <?=$colPlayer->getId()?>)"/>
<?
				}
?>
			</td>
<?
			}
?>
			</tr>
<?
		}
?>
		</tbody>
	</table>
</div>
<?
	} else {
?>
<div>
	<p>В этом турнире пока не принимает участие ни один пайп-мен!</p>
	<p>Сначала
		<a class="link" href="#competition/<?=$cup->getCompetition()->getId()?>/players" onclick="javascript: competition.loadPlayersCup(<?=$cup->getId()?>);">добавьте в него игроков</a>,
		а потом редактируйте его матчи.
	</p>
</div>
<?
	}
}


function cup_games_playoff(CupPlayOff $cup) {
    $editable = !$cup->isFinished();
    $final = $cup->getFinalGame();
    $bronze = $cup->getBronzeGame();

	if ($editable) {
?>
<div id="edit_match_panel">
	<div id="edit_match_panel_content">
		<div id="player_selector_1" class="player_selector_play_off">
			<script type="text/javascript">
                var playersArray = <?=json(Player::getAllToHTML());?>;
                var playerSelector1 = (new DynamicSelector({
                    content: playersArray
                }))
                .setWidth(200)
                .appendTo($('#player_selector_1'));
			</script>
		</div>

		<div class="score_play_off">
			<input id="score_1" type="text" style="width: 17px;"/> :
			<input id="score_2" type="text" style="width: 17px;"/>
		</div>

		<div id="player_selector_2" class="player_selector_play_off">
			<script type="text/javascript">
                var playerSelector2 = (new DynamicSelector({
                    content: playersArray
                }))
                .setWidth(200)
                .appendTo($('#player_selector_2'));
			</script>
		</div>

		<div class="option_buttons">
			<input id="save" type="button" value="Сохранить"/>
			<input id="cancel" type="button" value="Отмена" onclick="javascript: cup.hideEditingPanel()"/>
		</div>
	</div>
</div>
<div id="sport_create_stages">
	<div>
		<input value="Создать" type="button" onclick="javascript: cup.createStages(<?=$cup->getId()?>)"/>
		<label for="maxstage"> все стадии вплоть до 1 / </label>
		<input id="maxstage" type="text" value="16"/>
	</div>
<?
		if (!$bronze && $final) {
?>
	<div id="create_bronze">
		<script type="text/javascript">
			var createBronze = new Button({
				onClick: function() {
					cup.createPlayoffGame({
						cup_id: <?=$cup->getId()?>,
						stage: 3
					});
				},
				container: 'create_bronze',
				html: 'Создать матч за III место'
			});
		</script>
	</div>
<?
		}
?>
</div>
<?
	}

	if ($final) {
		$maxStage = $cup->getMaxStage();
		$depth = log($maxStage, 2) + 1;
		$top = ($maxStage / 2) * 40 + 5;
		$left = ($depth - 1) * 165 + 10;
		$onlyFinal = $final->getPrevGameId1() == 0 && $final->getPrevGameId2() == 0;
		$height = $maxStage * 40 + 20 + ($bronze ? 60 : 0);
?>

<div class="playoff" style="height: <?=$height?>px;">
	<? if ($final) cup_games_playoff_game($final, $top, $left, $depth, $editable); ?>
	<? if ($bronze) cup_games_playoff_game($bronze, $top + $maxStage * 20 + 20, $left - (!$onlyFinal ? 20 : 10), $depth, $editable); ?>
</div>
<?
	}
}

function cup_games_playoff_game(Game $game, $top, $left, $depth, $editable = false) {
	$name1 = $game->getPmid1() > 0 ? $game->getPlayer1()->getShortName() : 'не задан';
	$name2 = $game->getPmid2() > 0 ? $game->getPlayer2()->getShortName() : 'не задан';
	$gameDivClass = 'game';
	if (!$editable){
		$gameDivClass .= ' non_editable';
	}
    //TODO finish making background images for game types
	if ($game->getType() != GAME::GAME_TYPE_COMMON){
		$gameDivClass .= ' type_' . $game->getType();
	}
?>

<div id="game_<?=$game->getId()?>" class="<?=$gameDivClass?>" style="left: <?=$left?>px; top: <?=$top?>px;"<?if($editable){?> onclick="javascript: cup.editPlayoffGame(<?=$game->getId()?>);"<?}?>>
	<div>
		<div><?=$name1?></div>
		<div><?=$game->getScoreOrType(1)?></div>
	</div>
	<div>
		<div><?=$name2?></div>
		<div><?=$game->getScoreOrType(2)?></div>
	</div>
</div>

<?
	if ($game->getStage() == 3) return;

	$prev1 = $game->getPrevGame1();
	$prev2 = $game->getPrevGame2();

	$delta = round(40 * (pow(2, $depth - 3)) - 1);
	$depth--;

	if ($prev1 != null) {
		cup_games_playoff_game($prev1, $top - $delta, $left - 165, $depth, $editable);
	} elseif ($game->getStage() != 3 && $editable) {
?> 

<div class="game_add" style="left: <?=$left-10?>px; top: <?=$top+5?>px;" onclick="javascript: cup.createPlayoffGame({cup_id: <?=$game->getCupId()?>, parent_id: <?=$game->getId()?>, stage: <?=$game->getStage() * 2?>, is_left: true});"></div>
<?
	}

	if ($prev2 != null) {
		cup_games_playoff_game($prev2, $top + $delta, $left - 165, $depth, $editable);
	} elseif ($game->getStage() != 3 && $editable) {
?>

<div class="game_add" style="left: <?=$left-10?>px; top: <?=$top+20?>px;" onclick="javascript: cup.createPlayoffGame({cup_id: <?=$game->getCupId()?>, parent_id: <?=$game->getId()?>, stage: <?=$game->getStage() * 2?>, is_left: false});"></div>
<?
	}
}

?>
