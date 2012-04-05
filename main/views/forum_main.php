<?php

require_once dirname(__FILE__) . '/forum_view.php';

global $auth, $user;
Forum::init($auth->uid());

foreach(Forum::getForums() as $forum) {
?>

	<div class="forum_forum">
		<div class="title">
			<div class="tab"><?=$forum->getTitle()?></div>
			<div class="bottom"> </div>
		</div>

		<div class="body_wrapper">
			<div class="body">
<?
	foreach($forum->getParts() as $part) {
		$partNew = $part->hasNewFor($user);
?>

				<div id="part_<?=$part->getId()?>" class="slide_block forum_part<?=($partNew ? ' new' : '')?>">
					<div class="title <?=($partNew ? ' opened' : '')?>">
						<div class="left">
							<div class="content">
								<a href="/forum/part<?=$part->getId()?>"><?=$part->getTitle()?></a>
							</div>
						</div>
						<div class="right">
							<div class="info">
								<div><?=$part->countTopics() . '&nbsp;' . lang_sclon($part->countTopics(), 'тема', 'темы', 'тем')?></div>
								<div><?=$part->countMessages() . '&nbsp;' . lang_sclon($part->countMessages(), 'сообшение', 'сообщения', 'сообщений')?></div>
							</div>
							<div class="quick" onclick="javascript: forum.togglePart(<?=$part->getId()?>);"></div>
						</div>
						<div style="clear: both;"></div>
					</div>
					<div class="body<?=$partNew ? ' block' : ' hidden'?>">
						<div class="description">
							<?=$part->getDescription();?>
						</div>
						<div class="list">
							<ul>
<?
		foreach ($part->getTopicsTop($user) as $topic) {
			$topicNew = $partNew ? $topic->hasNewFor($user) : false;
			$topicClosed = $topic->isClosed();
?>

								<li<?=($topicNew && $topicClosed ? ' class="new closed"' : ($topicClosed ? ' class="closed"' : ($topicNew ? ' class="new"' : '')))?>>
									<div class="before"></div>
									<a href="/forum/part<?=$part->getId()?>/topic<?=$topic->getId()?>"><?=$topic->getTitle()?></a>
								</li>
<?
		}
?>

							</ul>
						</div>
						<div style="clear: both;"></div>
					</div>
				</div>
<?
	}
?>

			</div>
		</div>
	</div>
<?
}

?>

	<div id="stats" class="forum_forum">
		<div class="title">
			<div class="tab">Статистика</div>
			<div class="bottom"> </div>
		</div>
		<div id="stats_selector"></div>
		<div class="body_wrapper">
			<div class="body">
<? forum_show_stats(); ?>

			</div>
		</div>
	</div>

	<script type="text/javascript">
		$$(function () {
			var partIds = slideBlock.getOpenedParts();
			for (var i = 0; i < partIds.length; i++) {
				forum.openPart(partIds[i]);
			}
		});
	</script>
