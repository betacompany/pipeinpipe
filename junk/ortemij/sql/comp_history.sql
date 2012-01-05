SELECT * FROM
(
	SELECT `p_competition`.*, `place`, `parent_cup_id` FROM
		(
			SELECT `competition_id`, `place`, `p_cup`.`parent_cup_id` FROM
				(
					SELECT * FROM
					`p_man_cup_result`
					WHERE `pmid`=3
				) AS `results`

				INNER JOIN

				`p_cup`

				ON `p_cup`.`id`=`results`.`cup_id`

			--WHERE `p_cup`.`parent_cup_id`=0
		) AS `victories`

		INNER JOIN

		`p_competition`

		ON `p_competition`.`id`=`victories`.`competition_id`
	ORDER BY `p_competition`.`id` DESC, `victories`.`parent_cup_id` ASC
) AS `r`
GROUP BY `id`
ORDER BY `id` DESC