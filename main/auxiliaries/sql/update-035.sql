ALTER TABLE  `p_competition`
	ADD  `status` ENUM(  'running',  'finished',  'registering',  'disabled' )
	NOT NULL DEFAULT  'disabled';

ALTER TABLE  `p_competition`
	DROP  `season` ;

UPDATE `p_competition` SET `status`='finished';

ALTER TABLE `p_cup`
	DROP `status`;

ALTER TABLE  `p_cup`
	ADD  `multiplier` DOUBLE NOT NULL DEFAULT  '-1'