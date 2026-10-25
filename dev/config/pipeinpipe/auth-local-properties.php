<?php
// Dev stand stand-in for auth/properties/local-properties.php.

global $COMMON_AUTH_PROPERTIES;
$COMMON_AUTH_PROPERTIES['database_host'] = 'localhost';
$COMMON_AUTH_PROPERTIES['database_name'] = 'pipeinpipe';
$COMMON_AUTH_PROPERTIES['database_user'] = 'pipeman';
$COMMON_AUTH_PROPERTIES['database_password'] = 'dev';
$COMMON_AUTH_PROPERTIES['users_table'] = 'common_user';
$COMMON_AUTH_PROPERTIES['tokens_table'] = 'common_token';
$COMMON_AUTH_PROPERTIES['domain'] = '.pipeinpipe.localhost';
?>
