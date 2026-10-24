<?php
/**
 * @author Artyom Grigoriev
 */

require_once '../../main/includes/mysql.php';

$sql = "SELECT  `target_type` ,  `target_id` ,  `uid`,  `max`
FROM (
SELECT *, MAX(  `timestamp` ) AS  `max` , COUNT( * ) AS  `d` , CONCAT(  `target_type` ,  '_',  `target_id` ,  '_',  `uid` ) AS  `c`
FROM  `p_content_view`
GROUP BY  `c`
ORDER BY  `d` DESC
) AS  `r`
WHERE  `r`.`d` >1";

$req = db_qw($sql);

while ($line = db_fetch_assoc($req)) {
	db_qw('DELETE FROM `p_content_view` WHERE `target_type`=? AND `target_id`=? AND `uid`=?',
			$line['target_type'], $line['target_id'], $line['uid']);
	echo db_affected_rows(), "\n";
}


?>
