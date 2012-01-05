<?php

require_once dirname(__FILE__) . '/forum_view.php';

global $auth;
global $user;

try {
	$topic = new ForumTopic($_REQUEST['topic_id']);
	if ($topic->getPartId() != $_REQUEST['part_id']) {
		throw new ForumException('There is no such topic in such part');
	}

	if ($auth->isAuth()) {
		$topic->visit($user);
	}

	$count = $topic->countMessages();
	$from = floor(($count - 1) / 10) * 10;
	$limit = Forum::MESSAGES_PER_PAGE;

	$part = $topic->getPart();
	$forum = $part->getForum();

?>

	<div id="forum_topic">
		<div class="title">
			<a href="/forum"><?=$forum->getTitle()?></a> &rsaquo;
			<a href="/forum/part<?=$part->getId()?>"><?=$part->getTitle()?></a> &rsaquo;
			<span><?=$topic->getTitle()?></span>
<?
		if ($topic->isClosed() && $topic->hasNextTopic()) {
?>

			<a href="/forum/part<?=$part->getId()?>/topic<?=$topic->getNextTopicId()?>" title="Тема закрыта. Перейти к продолжению"><div class="closed"></div></a>
<?
		} elseif ($topic->isClosed()) {
?>

			<div class="closed" title="Тема закрыта. У этой темы нет продолжения"></div>
<?
		}
?>
		</div>
		<div class="body">
<?
	if ($topic->isAvailableFor($auth->uid())) {
?>

			<div id="topic_<?=$topic->getId()?>">
<?
		forum_show_messages($auth->isAuth() ? $user->getId() : 0, $topic, $from, $limit);
?>

			</div>

<?
		if ($auth->isAuth() && !$topic->isClosed()) {
?>

			<div id="new_message" onkeypress="javascript: keyPressHandler(event);">
				<div id="new_message_wrapper">
					<div id="textarea">
						<div id="instruments"></div>
						<div>
							<textarea id="textarea_field" name="new_message"></textarea>
							<div id="add_message_button" class="button" onclick="javascript: forum.sendMsg(<?=$topic->getId()?>);">Отправить</div>
						</div>
					</div>
				</div>
				<div class="photo">
					<img id="your_photo" alt="<?=$user->getFullName()?>" src="<?=$user->getImageUrl(User::IMAGE_SQUARE)?>" />
				</div>
				<div style="clear: both;"></div>
			</div>

			<script type="text/javascript">
				var keyPressHandler = function (event) {
					if (event.ctrlKey && (event.keyCode == 10 || event.keyCode == 13)) {
						forum.sendMsg(<?=$topic->getId()?>);
					}
				};
			</script>
<?
		} elseif ($topic->isClosed() && $auth->isAuth()) {
?>

			<div style="margin: 10px 5px 5px 5px;">
				Эта тема закрыта, поэтому сюда уже нельзя писать новые сообщения.<br/>
				Однако, если Вы считаете необходимым возобновить обсужение,
				<a href="/procs/proc_forum.php?method=create_continuation&topic_id=<?=$topic->getId()?>">создайте его продолжение</a>.
			</div>
<?
		}
	}

?>

		</div>
	</div>
<?
} catch (Exception $e) {
	global $LOG;
	@$LOG->exception($e);
}
?>
