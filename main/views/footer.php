<?

require_once dirname(__FILE__) . '/../includes/config-local.php';

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

<?

list($script_name, $ext) = explode(".", $_SERVER['SCRIPT_NAME'], 2);
$script_name = substr($script_name, 1);

?>

<script type="text/javascript" src="/js/jquery-ui-1.8.4.custom.min.js"></script>

<? if (CLOSURE_COMPILE_ENABLED): ?>
<script type="text/javascript" src="/js/all.js"></script>
<? else: ?>
<script type="text/javascript" src="/js/lib-structures.js"></script>
<script type="text/javascript" src="/js/api.js?2"></script>
<script type="text/javascript" src="/js/common.js?2"></script>
<script type="text/javascript" src="/js/error-handler.js"></script>
<script type="text/javascript" src="/js/ui-controls.js"></script>
<script type="text/javascript" src="/js/ui-boxes.js"></script>
<script type="text/javascript" src="/js/content.js"></script>
<script type="text/javascript" src="/js/menu.js"></script>
<script type="text/javascript" src="/js/error.js"></script>
<script type="text/javascript" src="/js/main.js"></script>
<? endif; ?>

<script type="text/javascript" src="/js/fullajax.js"></script>

<script src="http://vkontakte.ru/js/api/openapi.js" type="text/javascript" charset="windows-1251"></script>
<!--<script src="http://connect.facebook.net/en_US/all.js" type="text/javascript"></script>-->
<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>

<?
if (file_exists(dirname(__FILE__).'/../js/'.$script_name.'.js')) {
	?>

<script type="text/javascript" src="/js/<?=$script_name?>.js"></script>
<?
}

if (isset ($_REQUEST['part']) && file_exists(dirname(__FILE__).'/../js/'.$script_name.'_'.$_REQUEST['part'].'.js')) {
	?>

<script type="text/javascript" src="/js/<?=$script_name.'_'.$_REQUEST['part']?>.js"></script>
<?
}

?>

<script type="text/javascript">
	for (var i = 0; i < ui_handlers.length; ++i) {
		debug('[ui] Handler-' + i + ' started');
		ui_handlers[i]();
		debug('[ui] Handler-' + i + ' finished');
	}
</script>

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
	var yaParams = {uid: "<?=$auth->uid()?>"};
</script>

<script type="text/javascript">
	(function (d, w, c) {
		(w[c] = w[c] || []).push(function() {
			try {
				w.yaCounter521134 = new Ya.Metrika({id:521134, enableAll: true, trackHash:true, webvisor:true,params:window.yaParams||{ }});
			} catch(e) {}
		});

		var n = d.getElementsByTagName("script")[0],
			s = d.createElement("script"),
			f = function () { n.parentNode.insertBefore(s, n); };
		s.type = "text/javascript";
		s.async = true;
		s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

		if (w.opera == "[object Opera]") {
			d.addEventListener("DOMContentLoaded", f);
		} else { f(); }
	})(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="//mc.yandex.ru/watch/521134" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

	</body>
</html>