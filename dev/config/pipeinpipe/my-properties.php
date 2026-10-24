<?php
// Dev stand stand-in for main/config/my-properties.php (prod copy lives in
// ~/pipeinpipe.info.conf.d on the server). Must stay PHP 5.5 compatible.

global $PROPERTIES;

// Links between the sites keep whichever port (PHP version) you came in on
$DEV_PORT = '8085';
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], ':') !== false) {
	$DEV_PORT = substr($_SERVER['HTTP_HOST'], strrpos($_SERVER['HTTP_HOST'], ':') + 1);
}

$PROPERTIES['db']['username']  = "pipeman";
$PROPERTIES['db']['password']  = "dev";
$PROPERTIES['db']['database']  = "pipeinpipe";
$PROPERTIES['db']['server']    = "localhost";
$PROPERTIES['cookies']['domain'] = ".pipeinpipe.localhost";
$PROPERTIES['main_site_url']   = "pipeinpipe.localhost:" . $DEV_PORT;
$PROPERTIES['cupms_site_url']  = "cupms.pipeinpipe.localhost:" . $DEV_PORT;
$PROPERTIES['mobile_site_url'] = "m.pipeinpipe.localhost:" . $DEV_PORT;
$PROPERTIES['error_debug_mode'] = false;
$PROPERTIES['mysql']['debug_mode'] = false;
$PROPERTIES['closure_compiler_enabled'] = true;
$PROPERTIES['version'] = 6;
$PROPERTIES['content']['social_photo_album_id'] = 138;
?>
