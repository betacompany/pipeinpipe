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
print_r($argv);
echo dirname(__FILE__), "\n";
$am->minify(dirname(__FILE__) . '/' . $argv[1], dirname(__FILE__) . '/' . $argv[2], 100, 100);

?>