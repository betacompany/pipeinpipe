<?
global $PATH;
global $auth;

define('END_TIME', microtime(true));
?>

	<div id="footer">
		<span>&copy; betacompany,</span>
		<span><? $y = date("Y"); if ($y > 2011): ?>2011 &ndash; <?=$y?><? else: ?>2011<? endif; ?>.</span>
		<a target="_blank" href="http://<?=MAIN_SITE_URL?><?=$PATH?>">Полная версия</a>
<? if ($auth->isAuth()): ?>
		| <a href="http://<?=MOBILE_SITE_URL?>/procs/proc_main.php?method=sign_out">Выйти</a>
<? endif; ?>
		<br/>
		<small style="color: #aaa;">
			<?printf("%.4f", END_TIME-BEGIN_TIME)?> сек.
			<?=(MYSQL_DEBUG_MODE ? ' | ' . mysql_qw() . ' ' . lang_sclon(mysql_qw(), 'запрос', 'запроса', 'запросов') : '')?>
		</small>
	</div>

<!-- Yandex.Metrika counter -->
<div style="display:none;"><script type="text/javascript">
(function(w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            w.yaCounter11291692 = new Ya.Metrika({id:11291692, enableAll: true});
        }
        catch(e) { }
    });
})(window, "yandex_metrika_callbacks");
</script></div>
<script src="//mc.yandex.ru/metrika/watch.js" type="text/javascript" defer="defer"></script>
<noscript><div><img src="//mc.yandex.ru/watch/11291692" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->