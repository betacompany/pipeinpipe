
SELECT * , COUNT( * ) AS  `d` , CONCAT(  `target_type` ,  '_',  `target_id` ,  '_',  `uid` ) AS  `c`
FROM  `p_content_view`
GROUP BY  `c`
ORDER BY  `d` DESC;

DELETE FROM `p_content_view` WHERE CONCAT(  `target_type` ,  '_',  `target_id` ,  '_',  `uid` ) IN
(SELECT  `c`
FROM (
SELECT MAX(  `timestamp` ) AS  `max` , COUNT( * ) AS  `d` , CONCAT(  `target_type` ,  '_',  `target_id` ,  '_',  `uid` ) AS  `c`
FROM  `p_content_view`
GROUP BY  `c`
ORDER BY  `d` DESC
) AS  `r`
WHERE  `r`.`d` >1);


