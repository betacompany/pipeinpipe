<?php
/**
 * User: ortemij
 * Date: 06.01.12
 * Time: 18:17
 */

require_once dirname(__FILE__) . '/../../main/classes/image/OpenCVAvatarsMinifier.php';

$LOG = new Logger('../../../junk/ortemij/log.log');
$am = OpenCVAvatarsMinifier::getInstance();
$am->minify($argv[0], $argv[1], 100, 100);

?>