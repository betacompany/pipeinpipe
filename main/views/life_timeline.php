<?php
/**
 * User: ortemij
 * Date: 02.04.12
 * Time: 9:47
 */

require_once dirname(__FILE__)  . "/../includes/date.php";

require_once dirname(__FILE__) . "/life_view.php";

global $items;

?>

<div id="timeline_dates" style="background-color: #fff; width: 100%;"></div>

<script type="text/javascript">
	$$(function () {
		var container = $('#timeline_dates'),
			offset = container.offset()
			;

		life.timeline = new Timeline({});
		container.append(life.timeline.getByContainer(container));

		$(window).scroll(function (e) {
			var x = offset.top - $(window).scrollTop();
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
		});
	});
</script>

<div id="feed">
	<div class="timeline_container body_container">
		<div class="top">

		</div>
		<div class="content">
			<div class="timeline_wrapper">
				<? foreach ($items as $item): ?>
				<div class="item">
					<? life_show_feed_item2($item) ?>
				</div>
				<? endforeach;?>
			</div>
		</div>
	</div>
</div>

<?

function life_show_feed_item2(Item $item) {
	$itemClass = $item->getType();
	if ($item instanceof CrossPost) {
		$itemClass .= " {$item->getSocialWebType()}";
	}
	
	$u = $item->getUser();

	$isCrossPost = $item instanceof CrossPost;
?>

<div class="<?=$itemClass?>">
	<div class="title">
		<? if ($u) { ?>
		<a href="<?=$u->getURL()?>"><?=$u->getFullName()?></a>
			<? if ($isCrossPost) { ?>
			via <a href="<?=$item->getExternalUrl()?>"><?=$item->getSocialWebAuthorName()?></a>
			<? } ?>
		<? } elseif($isCrossPost) { ?>
		<a href="<?=$item->getExternalUrl()?>"><?=$item->getSocialWebAuthorName()?></a>
		<? } ?>
	</div>
	<div class="body">
<?
	if ($item instanceof ItemsContainer) {
		foreach ($item->getItems() as $i) {
			life_show_item_content($i);
		}
	} else {
		life_show_item_content($item);
	}
?>

	</div>
</div>
<?
}

function life_show_item_content(Item $item) {
	$isCrossPost = $item instanceof CrossPost;
	$isEvent = $item instanceof Event;

	if ($isEvent || $isCrossPost) {
		echo "<p>{$item->getContentParsed()}</p>";
	} else {

	}
}
?>
