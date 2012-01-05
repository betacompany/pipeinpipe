<?php

require_once '../../includes/mysql.php';
require_once '../../includes/config-local.php';

require_once 'converter_library.php';

echo "<pre>\n";

mysql_qw('DELETE FROM `p_content_group` WHERE `type`=\'photo_album\'');
echo "Albums deleted from DB\n";
flush();

mysql_qw('DELETE FROM `p_content_comment` WHERE `item_id` IN (SELECT `id` FROM `p_content_item` WHERE `type`=\'photo\')');
echo "Comments for photos deleted from DB\n";
flush();

mysql_qw('DELETE FROM `p_content_action` WHERE `target_type`=\'item\' AND `target_id` IN (SELECT `id` FROM `p_content_item` WHERE `type`=\'photo\')');
echo "Actions for photos deleted from DB\n";
flush();

mysql_qw('DELETE FROM `p_content_item` WHERE `type`=\'photo\'');
echo "Photos deleted from DB\n";
flush();

foreach (glob(dirname(__FILE__) . '/../../content/photos/*.jpg') as $photo) {
	unlink($photo);
	echo '. ';
	flush();
}
echo "\nAll photos removed from file system\n";
flush();

$aii = mysql_result(mysql_qw('SELECT MAX(`id`) + 1 FROM `p_content_item`'), 0, 0);
mysql_qw('ALTER TABLE  `p_content_item` AUTO_INCREMENT=' . $aii);
echo "Auto increment for `p_content_item` set to value of $aii\n";
flush();

$aig = mysql_result(mysql_qw('SELECT MAX(`id`) + 1 FROM `p_content_group`'), 0, 0);
mysql_qw('ALTER TABLE  `p_content_group` AUTO_INCREMENT=' . $aig);
echo "Auto increment for `p_content_group` set to value of $aig\n\n";
flush();

?>
