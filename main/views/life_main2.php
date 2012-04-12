<?php
/**
 * User: ortemij
 * Date: 02.04.12
 * Time: 9:55
 */

require_once dirname(__FILE__) . '/../includes/import.php';

import('content/Feed');

$items = Feed::get();

?>

<div id="timeline_dates" style="background-color: #fff; width: 100%;"></div>

<script type="text/javascript">
	$$(function () {
		var container = $('#timeline_dates'),
			offset = container.offset()
			;

		life.timeline = new Timeline({
			centerDate: Math.floor($('.item > div:first').attr('pipe:time') / (24 * 60 * 60))
		});
		container.append(life.timeline.getByContainer(container));
		feed.init();
		feed.redrawTimeline();

		var counter = 0;

		$(window).scroll(function (e) {
			var scrollTop = $(window).scrollTop(),
				x = offset.top - scrollTop;

			if (x <= 0) {
				container.css({
					position: 'fixed',
					top: 0
				});
			} else {
				container.css({
					position: 'static',
					top: 0
				});
			}

//			if (++counter % 10 != 0) {
//				return;
//			}
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

