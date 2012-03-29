<?php
/**
 * User: ortemij
 * Date: 28.03.12
 * Time: 12:09
 */

require_once dirname(__FILE__) . '/../includes/common.php';

if (param('password') != 'yreutywerUIUihkhsdjahsduhweuUUUUU')

require_once dirname(__FILE__) . '/../classes/social/SocialPost.php';
require_once dirname(__FILE__) . '/../classes/social/CrossPost.php';

$allUnhandled = SocialPost::getAllUnhandled();


?>