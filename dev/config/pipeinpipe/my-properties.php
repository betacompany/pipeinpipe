<?php
// Dev stand stand-in for main/config/my-properties.php (the server's is made
// from server/templates/my-properties.php).

global $PROPERTIES;

$PROPERTIES['db']['username']  = "pipeman";
$PROPERTIES['db']['password']  = "dev";
$PROPERTIES['db']['database']  = "pipeinpipe";
$PROPERTIES['db']['server']    = "localhost";
$PROPERTIES['cookies']['domain'] = ".pipeinpipe.localhost";
$PROPERTIES['main_site_url']   = "pipeinpipe.localhost:8085";
$PROPERTIES['cupms_site_url']  = "cupms.pipeinpipe.localhost:8085";
$PROPERTIES['mobile_site_url'] = "m.pipeinpipe.localhost:8085";
$PROPERTIES['error_debug_mode'] = false;
$PROPERTIES['mysql']['debug_mode'] = false;
$PROPERTIES['closure_compiler_enabled'] = true;
$PROPERTIES['version'] = 6;
$PROPERTIES['content']['social_photo_album_id'] = 138;
?>
