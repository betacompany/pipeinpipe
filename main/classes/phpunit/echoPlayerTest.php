<?php

require_once dirname(__FILE__).'/../../includes/mysql.php';
require_once dirname(__FILE__).'/../cupms/Player.php';
require_once dirname(__FILE__).'/../db/GameDBClient.php';

echo "<pre>=== Player class test ===\n";

$player = new Player(11);

function getCups($player) {
    $cups = $player->getCups();
    foreach ($cups as $cup) {
        $req = mysql_qw('SELECT `name`, `competition_id` FROM `p_cup` WHERE `id` = ?', $cup['cup_id']);
        $line = mysql_fetch_assoc($req);

        $req2 = mysql_qw("SELECT `name` FROM `p_competition` WHERE `id` = ?", $line['competition_id']);
        $line2 = mysql_fetch_assoc($req2);

        $name = $line['name'];
        $name2 = $line2['name'];
        $place = $cup['place'];
        echo "$name2 - $name - $place \n";
    }
}

function getDefeatedOpponentIds($player) {
    $defOpp = $player->getDefeatedOpponentIds();
    foreach ($defOpp as $key => $value) {
        $req = mysql_qw('SELECT `surname` FROM `p_man` WHERE `id` = ?', $key);
        if($line = mysql_fetch_assoc($req))
            $name = $line['surname'];
        echo "$name - $value\n";
    }
}

function selectOpponentsAndScore($player) {
    $req = GameDBClient::selectOpponentsAndScore($player->getId());

    while($game = mysql_fetch_assoc($req)) {
        $currReq = mysql_qw('SELECT `surname` FROM `p_man` WHERE `id` = ?', $game['opp_id']);
        $line = mysql_fetch_assoc($currReq);
        $name = $line['surname'];
        $my_score = $game['my_score'];
        $opp_score = $game['opp_score'];
        echo "me $my_score : $opp_score $name \n";
    }
}

//print_r($player->getVictories(false));
//getCups($player);
//getDefeatedOpponentIds($player);
selectOpponentsAndScore($player);

?>
