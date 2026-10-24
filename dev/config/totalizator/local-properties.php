<?php
// Dev stand stand-in for totalizator's lib/local-properties.php.

$DEV_PORT = '8085';
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], ':') !== false) {
	$DEV_PORT = substr($_SERVER['HTTP_HOST'], strrpos($_SERVER['HTTP_HOST'], ':') + 1);
}

define("PATH_TO_COMMON_AUTH", "/var/www/pipeinpipe.info/auth/common-auth.php");
define("MAIN_SITE_URL", "http://pipeinpipe.localhost:" . $DEV_PORT);
define("DB_USER", "totalizator");
define("DB_PASS", "dev");
define("DB_NAME", "totalizator");
define("ACCESS_KEY", "dev");
?>
