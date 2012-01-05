<?
global $auth;
global $user;
$ya_params = array();
if ($auth->isAuth()) {
	$ya_params['uid'] = $auth->uid();
	$ya_params['uname'] = $user->getFullName();
} else {
	$ya_params['uid'] = 0;
	$ya_params['uname'] = 'guest';
}

define('END_TIME', microtime(true));
?>

			</div><!--//body-->
			<div id="footer">
				<div id="footer_container">
					<div id="footer_inner">
						&copy; Сделано в <a href="http://betacompany.spb.ru" target="_blank">betacompany</a> /
						2008 &ndash; <?=date("Y")?> /
						<?printf("%.4f", END_TIME-BEGIN_TIME)?> сек.
						<?=(MYSQL_DEBUG_MODE ? ' / ' . mysql_qw() . ' ' . lang_sclon(mysql_qw(), 'запрос', 'запроса', 'запросов') : '')?>
					</div>
					<ul id="footer_menu">
						<li><a href="/about">О сайте</a></li>
						<li><a href="http://<?=MOBILE_SITE_URL?>">Мобильная версия</a></li>
						<li><a href="http://пайп.рф">Пайп.рф</a></li>
						<li><a href="http://cupms.pipeinpipe.info">CupMS</a></li>
						<?
						global $auth;
						if ($auth->isAuth()) :?>
						<li>
							<a class="bug_report" href="#" onclick="javascript: content.reportBug();">Оставить отзыв</a>
						</li>
						<?endif;?>

					</ul>
					<div class="clear"></div>
				</div>
			</div>
		</div>

<!-- Yandex.Metrika counter -->
<div style="display:none;"><script type="text/javascript">
if (document.URL.match(/pipeinpipe.info/)) {
	var yaParams = <?=json($ya_params)?>;
	(function(w, c) {
		(w[c] = w[c] || []).push(function() {
			try {
				w.yaCounter521134 = new Ya.Metrika(521134, yaParams);
				 yaCounter521134.clickmap(true);

			} catch(e) { }
		});
	})(window, 'yandex_metrika_callbacks');
}
</script></div>
<script src="//mc.yandex.ru/metrika/watch.js" type="text/javascript" defer="defer"></script>
<noscript><div><img src="//mc.yandex.ru/watch/521134" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

	</body>
</html>