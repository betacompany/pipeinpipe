<?php
/**
 * @author Artyom Grigoriev
 */

require_once '../../main/classes/content/Connection.php';

$league = League::getById(1);

echo '<pre>';
print_r(Connection::getTypifiedContentGroupsFor($league, Group::VIDEO_ALBUM));
print_r(Connection::getTypifiedContentItemsFor($league, Item::VIDEO));
echo '</pre>';

?>
