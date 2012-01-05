<?php
/**
 * @author Artyom Grigoriev
 */

require_once dirname(__FILE__) . '/life_view.php';

?>

<div id="stream_wrapper" class="single">
	<div id="stream_container">
		<div id="stream_content">
<? life_show_day_feed(); ?>

		</div>
		<div id="stream_loading"></div>
		<div id="stream_end"></div>
	</div>
</div>

<div id="options_wrapper" class="single" style="min-height: 300px;">
	<div id="options">
		<div style="margin: -10px 0px 10px 0px; font-size: 0.8em;">
			Можете выбрать любой интересующий Вас день:
		</div>
		<div id="calendar"></div>
		<script type="text/javascript">
			var date = getAnchorParam('date');
			var ds = new DateSelector({
				date: date ? date : '<?=date('Y-m-d')?>',
				minDate: {y: 2008, m: 9, d: 23},
				maxDate: {y: <?=date('Y')+1?>, m: <?=date('n')?>, d: <?=date('j')?>},
				onSelect: life.feed.selectHandler,
				anchor: true,
				select: true,
				dateChecked: life.feed.dateChecked
			});
			$(function () {
				ds.appendTo($('#calendar'));
				if (date) life.feed.selectHandler(date);
			});
		</script>
	</div>
</div>

<div style="clear: both;"></div>