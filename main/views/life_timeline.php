<?php
/**
 * User: ortemij
 * Date: 02.04.12
 * Time: 9:47
 */

require_once dirname(__FILE__)  . "/../includes/date.php";

require_once dirname(__FILE__) . "/life_view.php";

global $items;

foreach ($items as $item):
?>

<div class="item">
	<? life_show_feed_item2($item) ?>
</div>
<?
endforeach;

function life_show_feed_item2(Item $item) {
	$itemClass = $item->getType();
	if ($item instanceof CrossPost) {
		$itemClass .= " {$item->getSocialWebType()}";
	}
	if ($item instanceof ItemsContainer) {
		$items = $item->getItems();
		if (count($items) && $items[0] instanceof CrossPost) {
			$added = array();
			foreach ($items as $it) {
				$added[ $it->getSocialWebType() ] = true;
			}
			foreach ($added as $type => $v) {
				$itemClass .= " $type";
			}
		}
		$itemId = array();
		foreach ($items as $it) {
			$itemId[] = $it->getId();
		}
		sort($itemId);
	} else {
		$itemId = array($item->getId());
	}
	
	$u = $item->getUser();

	$isCrossPost = $item instanceof CrossPost;
?>

<div class="<?=$itemClass?>" pipe:low-bound-id="<?=$itemId[0]?>" pipe:up-bound-id="<?=$itemId[count($itemId) - 1]?>" pipe:time="<?=$item->getCreationTimestamp()?>">
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
	$isBlogPost = $item instanceof BlogPost;
	$isEvent = $item instanceof Event;
	$isPhoto = $item instanceof Photo;
	$isVideo = $item instanceof Video;
	$isForumTopic = $item instanceof ForumTopic;

	if ($isEvent || $isCrossPost) {
		echo "<p>{$item->getContentParsed()}</p>";
	} elseif ($isForumTopic) {
		global $user;
		$topicNew = $item->hasNewFor($user);
		$topicClosed = $item->isClosed();
?>

		<div<?=($topicNew && $topicClosed ? ' class="topic new closed"' : ($topicClosed ? ' class="topic closed"' : ($topicNew ? ' class="topic new"' : ' class="topic"')))?>>
			<a href="/forum/part<?=$item->getPartId()?>/topic<?=$item->getId()?>"><?=$item->getTitle()?></a>
		</div>
<?
	} elseif ($isPhoto) {
?>

		<a href="/media/photo/album<?=$item->getGroupId()?>/<?=$item->getId()?>"><img class="photo" src="<?=$item->getUrl(Photo::SIZE_MINI)?>" alt="<?=$item->getTitle()?>" /></a>
<?
	} elseif ($isVideo) {
?>

		<a href="/media/video/album<?=$item->getGroupId()?>/<?=$item->getId()?>" title="<?=$item->getTitle()?>">
			<div class="video" style="background-image: url('<?=$item->getPreviewUrl()?>');">
				<div></div>
			</div>
		</a>
<?
	} elseif ($isBlogPost) {
?>
		
		<p>
			<div>
				<a href="/life/blog/<?=$item->getId()?>"><?=$item->getTitle()?></a>
			</div>
			<?=$item->getShortHTML()?>
		</p>
<?
	}
}
?>
