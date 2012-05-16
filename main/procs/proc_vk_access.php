<?php

if (!isset($_REQUEST['code'])) {
	echo "Invalid code";
	exit(0);
}

require_once dirname(__FILE__) . '/../classes/social/Vkontakte.php';

$code = urlencode($_REQUEST['code']);

$fp = fopen(
	"https://oauth.vk.com/access_token?".
	"client_id=".Vkontakte::VK_APP_ID.
	"&client_secret=".Vkontakte::VK_APP_SHARED_SECRET.
	"&code=$code", "r");
$data = "";
while (!feof($fp)) {
	$buffer = fgets($fp, 8096);
	$data .= $buffer;
}

fclose($fp);

$json = json_decode($data);
$access_token = $json->access_token;
$expire = $json->expires_in;
$ts = $expire == 0 ? 0 : time() + $expire;
$vkid = $json->user_id;

UserDataDBClient::insertAccessToken($vkid, $access_token, $ts);

?>
<html>
<head>
	<script type="text/javascript">
		window.close();
	</script>
</head>
<body></body>
</html>