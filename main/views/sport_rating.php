<?

require_once dirname(__FILE__) . '/../classes/cupms/League.php';

global $auth;

?>
<script type="text/javascript">
var y, m, d;
if (getAnchorParam('date') != null) {
	var dt = getAnchorParam('date').split('-');
	y = dt[0]; m = dt[1]; d = dt[2];
} else {
	var jdate = new Date();
	y = jdate.getFullYear();
	m = jdate.getMonth() + 1;
	d = jdate.getDate();
	if (m < 10) m = '0' + m;
	if (d < 10) d = '0' + d;
}

var date = y + '-' + m + '-' + d;
var leagueId = getAnchorParam('league') == null ? 1 : getAnchorParam('league');
var reload = function () {
	$('#here').attr('href', document.URL.substr(0, document.URL.indexOf('#')) + '#league=' + leagueId + '&date=' + date);
	$('#csv').attr('href', '/sport/rating/' + leagueId + '-' + date + '.csv');

	rating.__pmids_all = new Array();
	rating.__pmids = {
		length: 0
	};
	rating.__info_loaded = {};
	rating.__movement = null;

	rating.load(function () {
		rating.state('initial');
	});
}

</script>

<div id="rating_selector_container">
	<div id="rating_selector" style="display: none;">
		<div style="float: left;">
			<select name="league_id" onchange="javascript: leagueId = $('select[name=league_id]').val(); reload();">
<?
foreach (League::getAll() as $league) {
?>

				<option value="<?=$league->getId()?>"><?=$league->getName()?></option>
<?
}
?>

			</select>
		</div>

		<div style="float: left; padding-left: 10px;" id="date_selector"></div>
		<script type="text/javascript">
			var ds = new DateSelector({
				date: date,
				onSelect: function (dt) {
					leagueId = $('select[name=league_id]').val();
					date = dt;
					reload();
				},
				hideOnSelect: true,
				minDate: {d: 23, m: 9, y: 2009},
				maxDate: {d: <?=date('j')?>, m: <?=date('n')?>, y: <?=date('Y') + 1?>}
			});
			$(function () {
				ds.appendTo($('#date_selector'));
			});
		</script>

		<div style="float: left;">
			<a id="here" href="">Ссылка сюда</a> |
		
<?
if ($auth->isAuth()) {
	$pmid = $user->getPmid();
	if ($pmid) {
?>

			<a id="show_me" href="#<?=$pmid?>">Найти меня</a> |
<?
	}
}
?>
			<a id="csv" href="">CSV</a>
		</div>

		<div class="clear"></div>
	</div>
</div>

<div id="rating_container">
	<div id="rating_left">
		<div id="rating_left_content">

		</div>
	</div>
	<ul id="rating"></ul>
	<div id="rating_right">
		<div id="rating_compare">
            <div class="body chart" id="comparison_chart"></div>


		</div>
		<div id="rating_right_content">
			
		</div>
	</div>
</div>

<script type="text/javascript">
$(function () {
	$('select[name=league_id]').val(leagueId);
	$('input[name=day]').val(d);
	$('select[name=month]').val(m);
	$('input[name=year]').val(y);
	$('#here').attr('href', document.URL.substr(0, document.URL.indexOf('#')) + '#league=' + leagueId + '&date=' + date);
	$('#csv').attr('href', '/sport/rating/' + leagueId + '-' + date + '.csv');

	rating.load(function () {
		rating.state('initial');
	});
});
</script>
