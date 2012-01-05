<?php

/**
 * @author Artyom Grigoriev
 * Converts old pipemen data stored in `pipe_men` into new structure of `p_man`
 */

require_once '../../includes/mysql.php';
require_once 'converter_library.php';

echo "<pre>Convertion started\n";
flush();

mysql_qw('TRUNCATE TABLE `p_man`');
echo "`p_man` truncated.\n";
flush();

$req = mysql_qw('SELECT * FROM `pipe_men` WHERE 1=1 ORDER BY `id` ASC');
while ($pm = mysql_fetch_assoc($req)) {
	$email = $pm['email'];

	if (empty($email)) {
		$r = mysql_qw('SELECT `email` FROM `pipe_users` WHERE `pmid`=?', $pm['id']);
		if ($u = mysql_fetch_assoc($r)) {
			$email = $u['email'];
		}
	}

	mysql_qw('
		INSERT INTO `p_man` SET
		`id`=?, `name`=?, `surname`=?, `gender`=?, `country`=?, `city`=?, `email`=?, `description`=?
	',
		$pm['id'], $pm['name'], $pm['surname'], $pm['gender'], $pm['country'], $pm['city'], $email, $pm['html']
	);

	$id = $pm['id'];
	echo "id=$id inserted\n";
	flush();
}

echo 'Convertion finished in ';
echo save_results().'ms</pre>';

?>
