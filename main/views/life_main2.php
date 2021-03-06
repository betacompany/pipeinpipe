<?php
/**
 * User: ortemij
 * Date: 02.04.12
 * Time: 9:55
 */

require_once dirname(__FILE__) . '/../includes/import.php';

import('content/Feed');

$items = Feed::getNear(time());

?>

<div id="timeline_dates" style="background-color: #fff; width: 100%; z-index: 10; min-width: 944px;"></div>
<div id="timeline_height" style="height: 80px; display: none;"></div>

<script type="text/javascript">
	$$(function () {
		var container = $('#timeline_dates'),
			offset = container.offset(),
			heighter = $('#timeline_height'),
			counter = 0
			;

		life.timeline = new Timeline({
			centerDate: Math.floor($('.item > div:first').attr('pipe:time') / (24 * 60 * 60))
		});
		container.append(life.timeline.getByContainer(container));

		feed.init();
		feed.redrawTimeline();

		life.timeline.onChange(function (ms) {
			var date = new Date(ms);
			setAnchorParam('date', formatYMD(date));
			feed.loadNearItems(ms);
		});

		var prevX = 1000,
			prevPosition = 'static'
			;

		$(window).scroll(function (e) {
			var scrollTop = $(window).scrollTop(),
				x = offset.top - scrollTop,
				toBottom = $('#footer').offset().top - $(window).scrollTop() - window.innerHeight;

			if (toBottom < 10) {
				feed.loadElderItems();
			}

			if (x >= 0 && x > prevX) {
				feed.loadNewerItems();
			}
			prevX = x;

			if (x <= 0 && prevPosition == 'static') {
				container.css({
					position: 'fixed',
					top: 0
				});
				heighter.show();
				prevPosition = 'fixed';
				feed.recalc();
			} else if (x > 0 && prevPosition == 'fixed') {
				container.css({
					position: 'static',
					top: 0
				});
				heighter.hide();
				prevPosition = 'static';
				feed.recalc();
			}

			if (++counter % 10 != 0) {
				return;
			}
			feed.redrawTimeline();
		});
	});
</script>

<div id="feed">
	<div class="timeline_container body_container">
		<div class="top">

		</div>
		<div class="content">
			<div class="timeline_wrapper">

<? include dirname(__FILE__) . "/life_timeline.php"; ?>

			</div>
		</div>
	</div>
</div>

