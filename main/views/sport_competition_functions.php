<?php
/**
 * @autor Innokenty Shuvalov
 */
require_once dirname(__FILE__).'/../includes/config-local.php';
require_once dirname(__FILE__).'/../classes/user/User.php';

function sport_show_competition_header(Competition $competition) {
?>
<div id="competition_header">
<?
    sport_show_competition_header_left_column($competition);
    sport_show_competition_header_right_column($competition);
?>
    <div style="clear: both"></div>
</div>
<?
}

function sport_show_competition_header_left_column(Competition $competition) {
?>
<div id="left_column">
    <img src="<?=$competition->getImageURL()?>" alt="<?=$competition->getName()?>"/>
</div>
<?
}

function sport_show_competition_header_right_column(Competition $competition) {
?>
<div id="right_column">
    <div>
        <h1><?=$competition->getName()?></h1>
<?
    sport_show_competition_body($competition);
?>
    </div>
</div>
<?
}

function sport_show_competition_body(Competition $competition) {
?>
<div id="competition_body">
<?
    sport_show_competition_body_left_column($competition);
    if (!$competition->isRegistering()) {
        sport_show_competition_body_right_column($competition);
    }
?>
    <div style="clear: both"></div>
<?
    if ($competition->isRegistering()) {
        sport_show_competition_registration($competition);
    }
    sport_show_competition_photos($competition);
    sport_show_competition_videos($competition);
?>
</div>
<?
}

function sport_show_competition_body_left_column(Competition $competition) {
?>
<div id="left_column_body">
    <div><?=$competition->getDescription()?></div>
</div>
<?
}

function sport_show_competition_body_right_column(Competition $competition) {
?>
<div id="right_column_body">
<?
    sport_show_competition_info($competition);
?>
</div>
<?
}

function sport_show_competition_info(Competition $competition) {
?>
<table id="competition_info" class="round_border competition_table">
<tbody>
    <tr>
        <td>Коэффициент</td><td><?printf("%.1f",$competition->getCoef())?></td>
    </tr>
    <tr>
        <td>Участники</td><td><?=$competition->countPlayers()?></td>
    </tr>
<?
	$tour = $competition->getTournament();
	if ($tour instanceof Tournament) {
?>
        <tr>
            <td>Цикл Турниров</td><td><?=$tour->getName()?></td>
        </tr>
<?
	}
	$date = $competition->isRunning() ? 'нет' :
			'<a href="/life#date='.$competition->getDate().'">' .
			date_local(strtotime($competition->getDate()), DATE_LOCAL_FULL_DATE) .
			'</a>';
?>
        <tr>
            <td>Завершён</td><td><?=$date?></td>
        </tr>
<?
	if ($competition->isFinished()) {
		$winner = $competition->getVictor();
?>
        <tr>
            <td>Победитель</td>
            <td>
                <a href="/pm<?=$winner->getId()?>"><?=$winner->getFullName()?></a>
            </td>
        </tr>
<?
	}
?>
    </tbody>
</table>
<?
}

function sport_show_competition_registration(Competition $competition) {
?>
<div id="competition_registration">
	<div id="reg_comment_p" style="width: 410px;">
		<p>Если у вас есть пожелания по поводу времени своих матчей, а также по совместному
		участию в группе со знакомыми, укажите их, пожалуйста ниже. Мы обязательно
		постараемся их учесть при жеребьёвке.</p>
		<p><textarea  style="width: 410px;" id="reg_comment" rows="3"></textarea></p>
	</div>
<?
    global $auth;
    $user = $auth->getCurrentUser();
    $isRegistered = sport_competition_is_user_registered($user, $competition);
    sport_show_competition_register_button($competition, $user, $isRegistered);
?>
	
    <div id="login_or_register_panel"></div>
    <div id="competition_registered">
<?
		foreach ($competition->getRegisteredUsers() as $currentUser) {
			sport_show_registered_user($currentUser);
		}
?>
		
    </div>
</div>
<?
}

function sport_show_competition_register_button(Competition $competition, $user, $isRegistered) {
?>
<script type="text/javascript">
    $$(function () {
		competition.initRegistration(<?=$isRegistered ? 'true' : 'false'?>);
		window.registerButton = (new FadingButton({
			html: <?=$isRegistered ? 'competition._registeredText' : 'competition._unregisteredText'?>,
			CSSClass:'round_border',
			minOpacity:0.7,
			css:{
				'width':410,
				'height':33,
				'background-color':'#007ca7',
				'color':'white',
				'font-size':'1.8em',
				'padding-bottom':15,
				'padding-top':12,
				'text-align':'center'
			}
		}))
			.appendTo('login_or_register_panel')
			.click(function () {<?
		if ($user) {?>
			competition.registration(<?=$competition->getId()?>);
			<?
		} else {?>
			competition.loginOrRegisterPanel();
			<?
		}?>
			});
	});
</script>
<?
}

function sport_competition_is_user_registered($user, Competition $competition) {
    if ($user) {
        $uid = $user->getId();
        foreach ($competition->getRegisteredUsers() as $currentUser) {
            if ($uid == $currentUser->getId()) {
                return true;
            }
        }
    }
    return false;
}

function sport_show_competition_photos(Competition $competition) {
    $photoAlbums = Connection::getTypifiedContentGroupsFor($competition, Group::PHOTO_ALBUM);
	if (count($photoAlbums)) {
		$photoAlbum = $photoAlbums[0];
		$photos = $photoAlbum->getItems(0, 0, true);
?>
<div id="competition_photos">
    <div class="photos" style="width: <?=count($photos) * 80?>px;">
<?
		foreach ($photos as $photo) {
?>
        <a href="/media/photo/album<?=$photoAlbum->getId()?>/<?=$photo->getId()?>"><img src="<?=$photo->getPreviewUrl()?>" alt="<?=$photo->getTitle()?>" /></a>
<?
		}
?>
    </div>
    <script type="text/javascript">
        $$(function () {
            $('#competition_photos .photos').draggable({
                axis: 'x',
                cursor: 'e-resize',
                drag: function(e, ui) {}
            });
            preventSelection(ge('competition_photos'));
        });
    </script>
</div>
<?
	}
}

function sport_show_competition_videos(Competition $competition) {
    $videoAlbums = Connection::getTypifiedContentGroupsFor($competition, Group::VIDEO_ALBUM);
	if (count($videoAlbums)) {
		$videoAlbum = $videoAlbums[0];
		$videos = $videoAlbum->getItems(0, 0, true);
?>
<div id="competition_videos">
    <div class="videos" style="width: <?=count($videos) * 80?>px;">
<?
		foreach ($videos as $video) {
?>
        <a href="/media/video/album<?=$videoAlbum->getId()?>/<?=$video->getId()?>" title="<?=$video->getTitle()?>">
            <div class="video" style="background-image: url('<?=$video->getPreviewUrl()?>');">
                <div></div>
            </div>
        </a>
<?
		}
?>
    </div>
    <script type="text/javascript">
        $$(function () {
            $('#competition_videos .videos').draggable({
                axis: 'x',
                cursor: 'e-resize',
                drag: function(e, ui) {}
            });
            preventSelection(ge('competition_videos'));
        });
    </script>
</div>
<?
	}
}

function sport_competition_show_cup(Cup $cup) {
?>
<div id="competition_cup" class="competition_cup slide_block">
<?
    sport_competition_show_cup_slide_block($cup);
?>
</div>
<?
    $childCups = $cup->getChildren();
    if (!empty($childCups)) {
        sport_competition_show_children($cup, $childCups);
    }
}

function sport_competition_show_children(Cup $cup, $childCups) {
?>
<div id="competition_structure" class="slide_block">
<?
    $competition = $cup->getCompetition();
    sport_competition_show_structure_slide_block($competition, $cup->getId());
?>
</div>
<div id="competition_children_preview" class="competition_cup slide_block">
<?
    sport_competition_show_cup_children_preview($childCups);
?>
</div>
<?
}

function sport_competition_show_cup_slide_block(Cup $cup) {
?>
<div class="title opened">
	<div class="left"><?=$cup->getName()?></div>
	<div class="right">
		<div class="quick" onclick="javascript: slideBlock.togglePart('competition_cup')"></div>
	</div>
	<div class="clear"></div>
</div>
<div class="body hidden" style="display: block">
<?
	sport_competition_show_cup_body($cup);
?>
</div>
<?
}

function sport_competition_show_structure_slide_block(Competition $competition, $selectedCupId) {
?>
<div class="title opened">
	<div class="left">
		<div class="content">Структура турнира</div>
	</div>
	<div class="right">
		<div class="quick" onclick="javascript: slideBlock.togglePart('competition_structure')"></div>
	</div>
	<div style="clear: both"></div>
</div>
<div class="body hidden" style="display: block">
	<div>
<?
	sport_competition_show_structure($competition->getMainCup(), $selectedCupId);
?>
	</div>
	<div style="clear: both"></div>
</div>
<script type="text/javascript">
	$$(function () {
		competition.selectedCupId = <?=$competition->getMainCupId()?>;
	});
</script>
<?
}

function sport_competition_show_cup_body(Cup $cup) {
	if ($cup instanceof CupPlayoff) {
		sport_show_cup_playoff($cup);
	} else if ($cup instanceof CupOneLap) {
		sport_show_cup_one_lap($cup);
	} else {
		echo 'Error! Unsupported cup type';
	}
}

function sport_show_cup_playoff(CupPlayoff $cup) {
	$horizontalSpaceCoef = 1/6;
	$gameDivHeightCoef = 1/4;
	$verticalSpaceCoef = 1/2;
	$bronzeGameOffset = 1/6;

	$gameDivWidth = 1;
	$gameDivWidthWithSpace = $gameDivWidth * (1 + $horizontalSpaceCoef);

	$gameDivHeight = $gameDivWidth * $gameDivHeightCoef;
	$gameDivHeightWithSpace = $gameDivHeight * (1 + $verticalSpaceCoef);

	$maxStage = $cup->getMaxStage();
	$depth = log($maxStage, 2);

	$final = $cup->getFinalGame();
	$bronze = $cup->getBronzeGame();

    $top = ($maxStage - 1) / 2 * $gameDivHeightWithSpace;
    $left = ($maxStage == 1 && $bronze ? 0.5 : $depth) * $gameDivWidthWithSpace;

    $playoffWidth = ($depth + ($maxStage == 1 && $bronze ? 2 : 1)) * $gameDivWidthWithSpace;
    $playoffHeight = ($maxStage <= 4 && $bronze ? $maxStage + 1 : $maxStage) * $gameDivHeightWithSpace;

    $games = array();

    if ($final != null)
    sport_calculate_playoff_game(
			$games,
			$final,
			$top,
			$left,
			$depth,
			$maxStage,
			$gameDivWidthWithSpace,
			$gameDivHeightWithSpace
		);
    if ($bronze != null) {
        sport_calculate_playoff_game(
			$games,
			$bronze,
			$maxStage > 4 ? $top + $gameDivHeightWithSpace: $playoffHeight - $gameDivHeightWithSpace,
			$left - ($maxStage == 1 && $bronze ? 2 : 1) * $bronzeGameOffset * $gameDivWidthWithSpace,
			$depth,
			$maxStage,
			$gameDivWidthWithSpace,
			$gameDivHeightWithSpace
		);
	}
?>
<script type="text/javascript">
	$$(function () {
		competition.showPlayOff(
			<?=json($games)?>,
			<?=$playoffHeight?>,
			<?=$playoffWidth?>,
			<?=$gameDivWidth?>,
			<?=$gameDivHeight?>
		);
	});
</script>
<?
}

function sport_calculate_playoff_game(&$games,
                                        Game $game,
                                        $top,
                                        $left,
                                        $depth,
                                        $maxStage,
                                        $gameDivWidthWithSpace,
                                        $gameDivHeightWithSpace) {

    $gameData = array(
		'id' => $game->getId(),
		'left' => $left,
		'top' => $top,
		'stage' => $game->getStage(),
		'type' => $game->getType(),
		'tracedGames' => $game->tracePrevGameIdsToArray(),
        'victor' => $game->getVictorIndex(),
		'urls' => array(),
        'names' => array(),
        'surnames' => array(),
        'urls' => array(),
        'photos' => array(),
	);
    
    foreach ($game->getPlayers() as $which => $player) {
        if ($player) {
            $gameData['names'][$which] = $player->getName();
            $gameData['surnames'][$which] = $player->getSurname();
            $gameData['urls'][$which] = $player->getUrl();
            $gameData['photos'][$which] = $player->getImageURL();
            $gameData['scores'][$which] = $game->getScore($which);
        } else {
            $gameData['names'][$which] = '';
            $gameData['surnames'][$which] = 'Незнамо кто';
            $gameData['urls'][$which] = '';
            $gameData['photos'][$which] = Player::getImageById();
            $gameData['scores'][$which] = '';
        }
    }
    $games[] = $gameData;

    if ($game->getStage() == 3) return;

    $delta = $maxStage / $game->getStage() / 4;
    foreach($game->getPrevGames() as $which => $prevGame) {
        sport_calculate_playoff_game(
			$games,
			$prevGame,
			$top + ($which == 1 ? -1 : +1) * $delta * $gameDivHeightWithSpace,
			$left - $gameDivWidthWithSpace,
			$depth - 1,
			$maxStage,
			$gameDivWidthWithSpace,
			$gameDivHeightWithSpace
		);
    }
}

function sport_show_cup_one_lap(CupOneLap $cup) {
	$grid = $cup->getGameGrid();
	if ($grid && !empty($grid)) {
		sport_show_score_table($cup);
		sport_show_matches_table($cup);
?>
<script type="text/javascript">
	$$(function () { competition.bindGridEvents(<?=count($grid)?>); });
</script>
<?
	} elseif (!$cup->getCompetition()->isRegistering()) {
?>
<div>
	<p>В этом турнире не принимает участие ни один пайп-мен!</p>
	<p>Если хотите помочь, сообщите администраторам, что они идиоты.</p>
</div>
<?
	}
}

function sport_show_score_table(CupOneLap $cup, $full = true, $tableNumber = 1, $mobile = false, $maxLines = -1) {
	$resultTable = ResultTable::getForCup($cup);
	$row = $resultTable[0];
	if (!$row) return;
	$empty = $row->getPoints() === 0;
	$columnNumber = 1;

	$marginBottom = $maxLines > 0 ? sprintf("margin-bottom: %dpx", (30 * ($maxLines - count($resultTable)))) : "";
?>
<table style="<?=$marginBottom?>" class="competition_cup_score competition_table<?if (!$full){?> competition_cup_preview<?}?> round_border">
	<thead>
		<th<?=($full ? '>пайп-мен' : ' onclick="javascript: competition.loadCup(' . $cup->getId() . ')">' . $cup->getName())?></th>
<?
	if (!$empty) {
?>
		<th id="grid_<?=$tableNumber?>_0_<?=$columnNumber++?>">Очки</th>
		<th id="grid_<?=$tableNumber?>_0_<?=$columnNumber++?>">Ср</th>
<?
		if ($full) {
?>
		<th id="grid_<?=$tableNumber?>_0_<?=$columnNumber++?>">И</th>
		<th id="grid_<?=$tableNumber?>_0_<?=$columnNumber++?>">В</th>
		<th id="grid_<?=$tableNumber?>_0_<?=$columnNumber++?>">В5</th>
		<th id="grid_<?=$tableNumber?>_0_<?=$columnNumber++?>">В6</th>
		<th id="grid_<?=$tableNumber?>_0_<?=$columnNumber++?>">ВБ</th>
		<th id="grid_<?=$tableNumber?>_0_<?=$columnNumber++?>">П</th>
		<th id="grid_<?=$tableNumber?>_0_<?=$columnNumber++?>">ПБ</th>
		<th id="grid_<?=$tableNumber?>_0_<?=$columnNumber++?>">П6</th>
		<th id="grid_<?=$tableNumber?>_0_<?=$columnNumber++?>">П5</th>
<?
		}
	}
?>
	</thead>
	<tbody>
<?
	foreach ($resultTable as $i => $row) {
		$player = $row->getPlayer();
		$rowNumber = $i + 1;
		$columnNumber = 0;
?>
		<tr>
			<td id="grid_<?=$tableNumber?>_<?=$rowNumber . '_' . $columnNumber++?>">
				<?if (!$mobile) {?><a href="<?=$player->getURL()?>"><?}?><?=$rowNumber . '. '. $player->getShortName()?><?if (!$mobile) {?></a><?}?>
			</td>
<?
	if (!$empty) {
?>
			<td id="grid_<?=$tableNumber?>_<?=$rowNumber . '_' . $columnNumber++?>" class="important"><?=$row->getPoints()?></td>
			<td id="grid_<?=$tableNumber?>_<?=$rowNumber . '_' . $columnNumber++?>"><?printf("%.1f", $row->getAverage())?></td>
<?
		if ($full) {
?>
			<td id="grid_<?=$tableNumber?>_<?=$rowNumber . '_' . $columnNumber++?>"><?=$row->getGames()?></td>
			<td id="grid_<?=$tableNumber?>_<?=$rowNumber . '_' . $columnNumber++?>" class="important"><?=$row->getWin()?></td>
			<td id="grid_<?=$tableNumber?>_<?=$rowNumber . '_' . $columnNumber++?>"><?=$row->getWin5()?></td>
			<td id="grid_<?=$tableNumber?>_<?=$rowNumber . '_' . $columnNumber++?>"><?=$row->getWin6()?></td>
			<td id="grid_<?=$tableNumber?>_<?=$rowNumber . '_' . $columnNumber++?>"><?=$row->getWinb()?></td>
			<td id="grid_<?=$tableNumber?>_<?=$rowNumber . '_' . $columnNumber++?>" class="important"><?=$row->getLose()?></td>
			<td id="grid_<?=$tableNumber?>_<?=$rowNumber . '_' . $columnNumber++?>"><?=$row->getLoseb()?></td>
			<td id="grid_<?=$tableNumber?>_<?=$rowNumber . '_' . $columnNumber++?>"><?=$row->getLose6()?></td>
			<td id="grid_<?=$tableNumber?>_<?=$rowNumber . '_' . $columnNumber++?>"><?=$row->getLose5()?></td>
<?
		}
	}
?>
		</tr>
<?
	}
?>
	</tbody>
</table>
<?
}

function sport_show_matches_table(CupOneLap $cup, $mobile = false) {
	$grid = $cup->getGameGrid();
	$players = $cup->getPlayers();
    $gameTypeExplanations = array(
        Game::GAME_TYPE_COMMON => true,
        Game::GAME_TYPE_TECHNICAL => false,
        Game::GAME_TYPE_DRAW => false,
        Game::GAME_TYPE_FATALITY => false
    );
	if (!$mobile) {
?>
<div id="competition_cup_game_type_images"/>
<?
	}
?>
<table class="competition_cup_matches competition_table round_border">
	<thead>
		<th>пайп-мен</th>
<?
		for ($i = 1; $i <= count($players); $i++) {
?>
		<th id="grid_2_0_<?=$i?>"><?=$i?></th>
<?
		}
?>
	</thead>
	<tbody>
<?
		foreach ($players as $index => $rowPlayer) {
?>
		<tr>
			<td id="grid_2_<?=$index + 1?>_0">
				<?if (!$mobile){?><a href="<?=$rowPlayer->getURL()?>"><?}?>
					<div><?=($index + 1) . '. '. $rowPlayer->getShortName()?></div>
				<?if (!$mobile){?></a><?}?>
			</td>
<?
			foreach ($players as $colPlayer) {
				$i = $cup->getPlayerIndex($rowPlayer->getId());
				$j = $cup->getPlayerIndex($colPlayer->getId());
				$game = $grid[$i][$j];

				if ($i == $j) {
?>
			<td class="diagonal_cell"><?='x'?></td>
<?
				} else if ($game != null) {
                    $type = $game->getType();
                    if (!$mobule && !$gameTypeExplanations[$type]) {
                        $gameTypeExplanations[$type] = true;
?>
            <script type="text/javascript">
                $$(function () { competition.showGameTypeExplanation('<?=$type?>'); });
            </script>
<?
                    }
?>
			<td
                id="grid_2_<?=$index + 1?>_<?=$j + 1?>"
<?
                    if ($type != Game::GAME_TYPE_COMMON) {
?>
                class="sport_game_type_<?=$type?>"
<?
                    }
?>
                >
<?
                    echo $game->getScore1() . '&nbsp;:&nbsp;' . $game->getScore2()
?>
            </td>
<?
				} else {
?>
			<td></td>
<?
				}
			}
?>
		</tr>
<?
		}
?>
	</tbody>
</table>
<?
}

function sport_competition_show_cup_children_preview($childCups) {
	if (!empty($childCups)) {
		$maxCount = 0;
		foreach ($childCups as $childCup) {
		 	if ($childCup instanceof CupOneLap) {
		 		$n = $childCup->getPlayers();
		 		$maxCount = max($maxCount, $n);
		 	}
		}
?>
<div class="title opened">
	<div class="left">Подтурниры</div>
	<div class="right">
		<div class="quick" onclick="javascript: slideBlock.togglePart('competition_children_preview')"></div>
	</div>
	<div class="clear"></div>
</div>
<div class="body hidden" style="display: block">
	<div>
<?
		foreach ($childCups as $i => $childCup) {
			if ($childCup instanceof CupOneLap) {
				sport_show_score_table($childCup, false, $i + 3, false, $maxCount);
			}
		}
?>
	</div>
	<div class="clear"></div>
</div>
<script type="text/javascript">
	$$(function () { competition.bindGridEvents(); });
</script>
<?
	}
}

function sport_competition_show_structure(Cup $cup, $selectedCupId, $step = 1) {
	$children = $cup->getChildren();
	$cupId = $cup->getId();
?>
<div id="cup<?=$cupId?>"
	 class="cup round_border<?=$selectedCupId == $cupId ? ' selected' : ''?>"
	 onclick="javascript: competition.loadCup(<?=$cupId?>)">
	 <?=$cup->getName()?>
</div>
<?
//теперь нарисуем подтурниры...
    $count = count($children);
	if ($count > 0) {
		//может тут лучше проверить есть ли дети у детей?
		//потому что если да, то некруто рисовать колонки? или нормально?
		$maxColumns = 4;
		$minColumnLength = 2;
        $childCupsIndent = 20;

        $columns = min(array($maxColumns, intval($count / $minColumnLength)));
        $maxOffset = $count - $minColumnLength;
		$additional = $count % $columns;
		$length = intval($count / $columns) + ($additional ? 1 : 0);

		for ($offset = 0; $offset <= $maxOffset;) {
?>
<div style="float: left; margin-left: <?=($step * $childCupsIndent)?>px">
<?
			foreach (array_slice($children, $offset, $length) as $child) {
				sport_competition_show_structure($child, $selectedCupId, $step + 1);
			}

			$offset += $length;
			$length -= --$additional ? 0 : 1;
?>
</div>
<?
		}
?>
<div style="clear: both"></div>
<?
	}
}

function sport_show_registered_user(User $user) {
?>
<div id="reg<?=$user->getId()?>" class="round_border">
<?
	echo sport_html_registered_user($user);
?>
</div>
<?
}

function sport_html_registered_user(User $user) {
	$uid = $user->getId();
	$fullName = $user->getFullName();
	$src = $user->getImageURL(User::IMAGE_SQUARE);
	$name = $user->getName();
return <<< LABEL
<a href="/id$uid" target="_blank">
	<div>
		<img alt="$fullName" src="$src"/>
		<div class="username">$name</div>
	</div>
</a>
LABEL;
}
?>
