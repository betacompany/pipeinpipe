SELECT `time`--, COUNT(*) AS `count` FROM
FROM
	(SELECT (`creation_timestamp` DIV 86400) * 86400 AS `time`, `id`
	FROM `p_content_item` WHERE `type`!='event'
		UNION
	SELECT (`creation_timestamp` DIV 86400) * 86400 AS `time`, `id`
	FROM `p_content_item` WHERE `type`='event') AS `r`
--GROUP BY `time`