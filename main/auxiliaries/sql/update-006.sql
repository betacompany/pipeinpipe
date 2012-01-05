alter table p_cups add foreign key(competition_id) references p_competitions(id);

alter table p_games add foreign key(cup_id) references p_cups(id);

alter table p_games add foreign key(pmid1) references p_men(id);

alter table p_games add foreign key(pmid2) references p_men(id);

alter table p_men_cups add foreign key(pmid) references p_men(id);

alter table p_men_cups add foreign key(cup_id) references p_cups(id);

alter table p_rating add foreign key(league_id) references p_leagues(id);

alter table p_rating add foreign key(pmid) references p_men(id);