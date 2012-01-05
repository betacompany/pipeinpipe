<?php
/**
 * @author Artyom Grigoriev
 */

require_once dirname(__FILE__) . '/../../main/classes/user/User.php';

$u = User::getById(10);

echo '<pre>';
print_r($u->getBlogs());
echo '</pre>';

?>
