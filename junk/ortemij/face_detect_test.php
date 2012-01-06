<?php
/**
 * User: ortemij
 * Date: 06.01.12
 * Time: 18:17
 */

require_once dirname(__FILE__) . '/../../main/classes/image/OpenCVAvatarsMinifier.php';
require_once dirname(__FILE__) . '/../../main/classes/utils/Logger.php';

$LOG = new Logger();
$am = OpenCVAvatarsMinifier::getInstance();
$am->minify(dirname(__FILE__) . '/' . $argv[0], dirname(__FILE__) . '/' . $argv[1], 100, 100);

?>