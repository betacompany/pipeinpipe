ALTER TABLE p_competitions RENAME p_competition;

ALTER TABLE p_cups RENAME p_cup;

ALTER TABLE p_games RENAME p_game;

ALTER TABLE p_leagues RENAME p_league;

ALTER TABLE p_men RENAME p_man;

ALTER TABLE p_men_cups RENAME p_man_cup_result;

ALTER TABLE p_tournaments RENAME p_tournament;

ALTER TABLE p_competition CHANGE wiki description text;

ALTER TABLE p_league CHANGE wiki description text;

ALTER TABLE p_man CHANGE wiki description text;

ALTER TABLE p_tournament CHANGE wiki description text;

ALTER TABLE `p_game` CHANGE `timestamp` `time` timestamp;

ALTER TABLE `p_man` CHANGE `iswpr` `is_approved` enum('0', '1');
