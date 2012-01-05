<?php

function mobile_show_header($something) {
	if (! ( $something instanceof Group || $something instanceof Item) ) {
		return;
	}
	echo <<< DATA
	<div class="green_header">{$something->getTitle()}</div>
DATA;
}

function mobile_show_part(ForumPart $part, $user) {
	$div_class = $part->hasNewFor($user) ? "new" : "";
	echo <<< DATA
	<div class="blue_item forum $div_class">
		<a href="/forum_part/{$part->getId()}">{$part->getTitle()}</a>
	</div>
DATA;
}

function mobile_show_table_header($something, $right, $table = true) {
	if (! ( $something instanceof Group || $something instanceof Item) ) {
		return;
	}
	if ($something instanceof ForumTopic) {
		$head = "<a href=\"/forum_part/".$something->getPartId()."\">".$something->getPart()->getTitle()."&nbsp;&raquo;</a>";
	}
	if ($table) echo "<table><thead>";
	echo <<< DATA
			<th>$head {$something->getTitle()}</th>
			<th>$right</th>
DATA;
	if ($table) echo "</thead></table>";
}

function mobile_show_topic(ForumTopic $topic, $user) {
	$has_new = $topic->hasNewFor($user) ? "new" : "";
	$new_messages = $has_new ? $topic->countNewFor($user) : "";
	echo <<< DATA
	<tr class="forum $has_new">
		<td>
			<a href="/forum_topic/{$topic->getId()}">{$topic->getTitle()}</a>
		</td>
		<td class="$has_new">
			<span>$new_messages</span>
		</td>
	</tr>
DATA;
}

function mobile_show_message(ForumMessage $message, $user) {
	$author = $message->getAuthor();
	$text   = $message->getParsed();

	$photo_url = "http://" . MAIN_SITE_URL . $author->getImageURL(User::IMAGE_SQUARE_SMALL);
	$date = str_replace(' ', '&nbsp;', date_local($message->getTimestamp(), DATE_LOCAL_SUPER_SHORT));

	$can_agree = $message->canBeActedBy(Action::AGREE, $user) ? "can" : "";
	$can_roman = $message->canBeActedBy(Action::ROMAN, $user) ? "can" : "";

	$agreed = $message->isActedBy(Action::AGREE, $user) ? "acted" : "";
	$romaned = $message->isActedBy(Action::ROMAN, $user) ? "acted" : "";

	$actions = $message->getActions();
	$agree_count = 0;
	$roman_count = 0;

	foreach ($actions as $action_h) {
		foreach ($action_h as $action) {
			switch ($action->getType()) {
			case Action::AGREE:
				$agree_count++;
				break;
			case Action::ROMAN:
				$roman_count++;
				break;
			}
		}
	}

	$show_agree = $can_agree || ($agree_count > 0);
	$show_roman = $can_roman || ($roman_count > 0);

	echo <<< DATA
	<tr class="msg_head">
		<td>
			<img class="upic" src="$photo_url"/>
		</td>
		<td class="w">
			<div class="u">{$author->getFullName()}</div>
		</td>
		<td class="date">
		    <span>$date</span>
		</td>
DATA;

	echo "<td class=\"roman $can_roman $romaned\">";
	if ($can_roman) {
		echo "<a href=\"http://".MAIN_SITE_URL.
			 "/procs/proc_forum.php?mobile=1&method=act&msg_id={$message->getId()}&action=".
			 Action::ROMAN."\">";
	}
	if ($show_roman) {
		echo "<span>$roman_count</span>";
	}
	if ($can_roman) {
		echo "</a>";
	}
	echo "</td>";

	echo "<td class=\"agree $can_agree $agreed\">";
	if ($can_agree) {
		echo "<a href=\"http://".MAIN_SITE_URL.
			 "/procs/proc_forum.php?mobile=1&method=act&msg_id={$message->getId()}&action=".
			 Action::AGREE."\">";
	}
	if ($show_agree) {
		echo "<span>$agree_count</span>";
	}
	if ($can_agree) {
		echo "</a>";
	}
	echo "</td>";

	echo <<< DATA
	</tr>
	<tr class="msg">
		<td colspan="5">$text</td>
	</tr>
DATA;

}

/**
 * @param $url_format  should contain %d at the place of page number
 * @param $current_page
 * @param $last_page
 * @param mixed $top  false if not, otherwise url of top page
 * @return void
 */
function mobile_show_pager($url_format, $current_page, $last_page, $top = false) {
	echo '<table class="pager"><tbody><tr>';
	if ($top) {
		echo "<td".($current_page == 0 ? " class=\"s\"" : "")."><a href=\"$top\">топ</a></td>";
	}

	echo "<td class=\"w\"></td>";

	$min_page = max(1, $current_page - 1);
	$max_page = min($current_page + 1, $last_page);

	if (($top && $current_page == 0) || $min_page < $max_page) {
		if ($min_page > 1) {
			$url = sprintf($url_format, 1);
			echo "<td><a href=\"$url\">&laquo;</a></td>";
		}
		for ($page = $min_page; $page <= $max_page; ++$page) {
			$url = sprintf($url_format, $page);
			echo "<td".($current_page == $page ? " class=\"s\"" : "")."><a href=\"$url\">$page</a></td>";
		}
		if ($max_page < $last_page) {
			$url = sprintf($url_format, $last_page);
			echo "<td><a href=\"$url\">&raquo;</a></td>";
		}
	}

	echo '</tbody></table>';
}

/**
 * @param array $params
 * @return void
 */
function mobile_show_textarea($params) {
	echo '<div class="ta"><form action="'.$params['action'].'">';
	foreach ($params as $k => $v) {
		if ($k != 'action') {
			echo '<input type="hidden" name="'.$k.'" value="'.$v.'"/>';
		}
	}
	echo '<textarea name="html"></textarea><input type="submit" value="Отправить"/>';
	echo '</form></div>';
}

function mobile_show_rating_row($place, $name, $surname, $img, $points) {
	$img_url = "http://" . MAIN_SITE_URL . $img;
	$points = sprintf("%.2f", $points);
	echo <<< ROW
	<tr class="vam">
	    <td>$place</td>
	    <td>
			<img class="upic" src="$img_url" />
	    </td>
	    <td class="w">$surname $name</td>
	    <td>$points</td>
	</tr>
ROW;
}

function mobile_show_league_row($place, League $league) {
	$image_url = "http://" . MAIN_SITE_URL . $league->getImageURL(League::IMAGE_SMALL);
	echo <<< ROW
	<tr class="vam">
		<td>$place</td>
		<td>
			<img class="upic" src="$image_url" />
		</td>
		<td class="w">
			<a href="/sport_league/{$league->getId()}">{$league->getName()}</a>
		</td>
	</tr>
ROW;

}

function begin_block($title) {
	if ($title) {
		echo '<div class="green_header">'.$title.'</div>';
	}
	echo '<table style="width:100%"><tbody>';
}

function end_block() {
	echo '</tbody></table>';
}

function mobile_show_league_info(League $league) {
	$pm_count = lang_number_sclon( count ($league->getPlayers()), 'пайпмен', 'пайпмена', 'пайпменов' );
	$cp_count = lang_number_sclon( count ($league->getCompetitions()), 'турнир', 'турнира', 'турниров' );
	$image_url = "http://" . MAIN_SITE_URL . $league->getImageURL(League::IMAGE_SMALL);

	echo <<< L
	<tr>
		<td rowspan="3">
			<img src="$image_url" />
		</td>
	</tr>
	<tr>
		<td class="w">
			<a href="/sport_rating/{$league->getId()}">$pm_count</a>
		</td>
	</tr>
	<tr>
		<td class="w">$cp_count</td>
	</tr>
	<tr class="b0">
		<td colspan="2" class="w">{$league->getDescription()}</td>
	</tr>
L;

}

function mobile_show_competition(Competition $competition) {
	$image_url = "http://" . MAIN_SITE_URL . $competition->getImageURL(Competition::IMAGE_SMALL);
	echo <<< C
	<tr>
	    <td>
			<img class="cpic" src="$image_url" />
	    </td>
	    <td class="w">
	        <a href="/sport_competition/{$competition->getId()}">{$competition->getName()}</a>
	    </td>
	</tr>
C;
}

function mobile_show_competition_info(Competition $competition) {
	$pm_count = lang_number_sclon( $competition->countPlayers(), "пайпмен", "пайпмена", "пайпменов" );
	$winner = $competition->getVictor();
	$verb = $winner->isMale() ? 'Выиграл' : 'Выиграла';
	$date = date_local(strtotime($competition->getDate()), DATE_LOCAL_FULL_DATE);
	$image_url = "http://" . MAIN_SITE_URL . $competition->getImageURL(Competition::IMAGE_SMALL);

	echo <<< C
	<tr>
		<td rowspan="4">
			<img src="$image_url" />
		</td>
	</tr>
	<tr>
	    <td class="w">Завершён $date</td>
	</tr>
	<tr>
	    <td class="w">Участвовало $pm_count</td>
	</tr>
	<tr>
	    <td class="w">$verb {$winner->getFullName()}</td>
	</tr>
	<tr>
		<td class="w" colspan="2">{$competition->getDescription()}</a>
	</tr>
C;
}

function mobile_show_cup_row(Cup $cup) {
	$type = '';
	switch ($cup->getType()) {
		case Cup::TYPE_ONE_LAP: $type = 'чемпионат&nbsp;в&nbsp;один&nbsp;круг'; break;
		case Cup::TYPE_TWO_LAPS: $type = 'чемпионат&nbsp;в&nbsp;два&nbsp;круга'; break;
		case Cup::TYPE_PLAYOFF: $type = 'плей<nobr/>-<nobr/>офф'; break;
	}
	echo <<< C
	<tr>
		<td class="w">
			<a href="/sport_cup/{$cup->getId()}">{$cup->getName()}</a>
		</td>
		<td>$type</td>
	</tr>
C;

}

function mobile_show_cup_one_lap(CupOneLap $cup) {
	require_once dirname(__FILE__) . '/../../main/views/sport_competition_functions.php';
	sport_show_score_table($cup, true, 1, true);
	sport_show_matches_table($cup, true);
}

function mobile_show_cup_playoff_stage(CupPlayoff $cup, $stage) {
	if ($stage == 1) {
		echo '<tr><td colspan="3" class="bold"><center>Финал</center></td></tr>';
	} else if ($stage == 3) {
		echo '<tr><td colspan="3" class="bold"><center>Матч за III место</center></td></tr>';
	} else if ($stage % 2 == 0) {
		echo '<tr><td colspan="3" class="bold"><center>1/'.$stage.' финала</center></td></tr>';
	} else {
		return;
	}
	$games = $cup->getGamesByStage($stage);
	foreach ($games as $game) {
		$score1 = strtoupper($game->getScoreOrType(1));
		$score2 = strtoupper($game->getScoreOrType(2));
		echo '<tr><td class="pl1'.($game->getVictorId() == $game->getPmid1() ? ' bold' : '').
				'">'.$game->getPlayer1()->getShortName().'</td>',
			 '<td class="score">'.$score1.'&nbsp;:&nbsp;'.$score2.'</td>',
			 '<td class="pl2'.($game->getVictorId() == $game->getPmid2() ? ' bold' : '').
				'">'.$game->getPlayer2()->getShortName().'</td></tr>';
	}
}

function mobile_show_cup_playoff_stages(CupPlayoff $cup, $stage) {
	if ($cup->getFinalGame()) {
		echo '<tr><td class="w'.($stage == 1 ? ' bold' : '').'"><a href="/sport_cup/'.$cup->getId().'/stage1">Финал</a></td></tr>';
	}
	if ($cup->getBronzeGame()) {
		echo '<tr><td class="w'.($stage == 3 ? ' bold' : '').'"><a href="/sport_cup/'.$cup->getId().'/stage3">Матч за III место</a></td></tr>';
	}
	$max_stage = $cup->getMaxStage();
	if ($max_stage % 2 == 0) {
		for ($st = 2; $st <= $max_stage; $st = $st * 2) {
			echo '<tr><td class="w'.($stage == $st ? ' bold' : '').'"><a href="/sport_cup/'.$cup->getId().'/stage'.$st.'">1/'.$st.' финала</a></td></tr>';
		}
	}
}

?>