SELECT * 
FROM (

	SELECT pmid, SUM(points) AS points, competition_id 
	FROM p_man_cup_result
	INNER JOIN 
	p_cup
	ON
	p_cup.id = p_man_cup_result.cup_id
	GROUP BY pmid, competition_id
) AS nt1 
WHERE points = (
	SELECT MAX(points) 
	FROM (
		SELECT pmid, SUM(points) AS points, competition_id 
		FROM p_man_cup_result
		INNER JOIN 
		p_cup
		ON
		p_cup.id = p_man_cup_result.cup_id
		GROUP BY pmid, competition_id
	) AS nt
)