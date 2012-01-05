SELECT * FROM (
SELECT pmid, COUNT(DISTINCT `date`) AS days
FROM (
	SELECT pmid, `date` 
	FROM p_rating
	WHERE rating_place = 1
	AND league_id = 1
) AS nt
GROUP BY pmid
) AS nt
WHERE days = (
	SELECT MAX(days)
	FROM (
		SELECT pmid, COUNT(DISTINCT `date`) AS days
		FROM (
			SELECT pmid, `date` 
			FROM p_rating
			WHERE rating_place = 1
			AND league_id = 1
		) AS nt
		GROUP BY pmid
	) AS nt
)