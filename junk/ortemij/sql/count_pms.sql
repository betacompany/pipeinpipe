SELECT `competition_id`, COUNT(DISTINCT (`pmid`)) FROM
	`p_man_cup_result` INNER JOIN `p_cup` ON `p_man_cup_result`.`cup_id`=`p_cup`.`id`
GROUP BY `competition_id`