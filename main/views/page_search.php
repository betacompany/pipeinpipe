<?php

require_once dirname(__FILE__) . '/../includes/config-local.php';

?>

<div class="body_container">
	<div class="yandexform"
		 onclick="return {'bg': '#8FBC13', 'language': 'ru', 'encoding': 'utf-8', 'suggest': false, 'tld': 'ru', 'site_suggest': false, 'webopt': false, 'fontsize': 12, 'arrow': true, 'fg': '#000000', 'logo': 'rb', 'websearch': false, 'type': 2}">
		<form action="http://<?=MAIN_SITE_URL?>/search" method="get">
			<input type="hidden" name="searchid" value="1877425"/>
			<input name="text"/>
			<input type="submit" value="Найти"/>
		</form>
	</div>
	<script type="text/javascript" src="http://site.yandex.net/load/form/1/form.js" charset="utf-8"></script>

	<div id="yandex-results-outer" onclick="return {'tld': 'ru', 'language': 'ru', 'encoding': 'utf-8'}"></div>
	<script type="text/javascript" src="http://site.yandex.net/load/site.js" charset="utf-8"></script>
</div>