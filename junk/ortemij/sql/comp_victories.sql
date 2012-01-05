SELECT `p_competition`.* FROM
	(
		SELECT `competition_id` FROM
			(
				SELECT * FROM
				`p_man_cup_result`
				WHERE `place`=1 and `pmid`=66
			) AS `results`

			INNER JOIN

			`p_cup`

			ON `p_cup`.`id`=`results`.`cup_id`

		WHERE `p_cup`.`parent_cup_id`=0
	) AS `victories`

	INNER JOIN

	`p_competition`

	ON `p_competition`.`id`=`victories`.`competition_id`