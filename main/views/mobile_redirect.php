<?php

global $mobile_url;

?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8"/>
</head>
<body>
	Нам показалось, что вы зашли на сайт с мобильного устройства.
	Хотите <a href="/procs/proc_mobile_redirect.php?decision=main">продолжить просмотр обычной версии</a>
	или <a href="/procs/proc_mobile_redirect.php?decision=mobile&url=<?=urlencode($mobile_url)?>">перейти на мобильную</a>?
</body>
</html>