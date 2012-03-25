<?php

require_once dirname(__FILE__) . '/../includes/date.php';
require_once dirname(__FILE__) . '/../includes/lang.php';

require_once dirname(__FILE__) . '/../classes/content/Action.php';

function forum_show_paging_bar($forumGroup, $limit, $page, $new = false) {
	$count = 0; $func = 'error'; $func_last = 'error';

	if ($forumGroup instanceof ForumTopic) {
		$count = $forumGroup->countMessages();
		$func = 'loadMsgs';
		$func_last = 'loadLastMsgs';
	} else if ($forumGroup instanceof ForumPart) {
		$count = $forumGroup->countTopics();
		$func = 'loadTopics';
		$func_last = 'loadLastTopics';
	} else {
		throw new ForumException('Invalid type for paging bar. Please, use ForumTopic or ForumPart!');
	}

	$last = ceil($count / $limit);
	$first_page = max(1, $page - 2);
	$last_page = min($last, $page + 2);
?>

			<div class="paging">
<?
			if ($forumGroup instanceof ForumPart) {
?>

				<a href="#page/top" onclick="javascript: forum.loadTopicsTop(<?=$forumGroup->getId()?>);">
					<div class="new<?=$new ? ' selected' : ''?>" title="Темы с самыми новыми сообщениями">топ</div>
				</a>
<?
			}

			if ($first_page != 1) {
?>

				<a href="#page/1" onclick="javascript: forum.<?=$func?>(<?=$forumGroup->getId()?>, 0, <?=$limit?>);">
					<div>&laquo;</div>
				</a>
<?
			}

			for ($i = $first_page; $i <= $last_page; $i++) {
?>

				<a href="#page/<?=$i?>" onclick="javascript: forum.<?=$func?>(<?=$forumGroup->getId()?>, <?=($i - 1) * $limit?>, <?=$limit?>);">
					<div<?=($page == $i && !$new) ? ' class="selected"' : ''?>><?=$i?></div>
				</a>
<?
			}

			if ($last_page != $last) {
?>

				<a href="#page/<?=$last?>" onclick="javascript: forum.<?=$func_last?>(<?=$forumGroup->getId()?>);">
					<div>&raquo;</div>
				</a>
<?
			}
?>

			</div>
<?
}

function forum_show_messages($uid, ForumTopic $topic, $from = 0, $limit = 0) {
	$count = $topic->countMessages();
	$limit = ($limit == 0) ? Forum::MESSAGES_PER_PAGE : $limit;
	$page = $from / $limit + 1;
?>

			<div class="subtitle">
				В этом топике <?=$count?>&nbsp;<?=lang_sclon($count, 'сообщение', 'сообщения', 'сообщений')?>
<?
	if ($count > $limit) {
		forum_show_paging_bar($topic, $limit, $page);
	}
?>
				
			</div>

			<div class="content">
<?
	if ($count > 0) {
		foreach ($topic->getMessages($from, $limit) as $msg) {
			try {
				forum_show_message($uid, $topic, $msg);
			} catch (Exception $e) {
				global $LOG;
				$LOG->exception($e);
			}
		}
	}
?>

			</div>

<script type="text/javascript">

$('.message').hover(
	function () {
		$(this).find('.controls').fadeIn();
	},
	function () {
		$(this).find('.controls').fadeOut();
	}
);

</script>

<?
	if ($count > $limit) {
		forum_show_paging_bar($topic, $limit, $page);
	}
?>

<?
}

function forum_show_message($uid, ForumTopic $topic, ForumMessage $msg) {
	$author = $msg->getAuthor();

	$actions = $msg->getActions();

	$romaned = 0;
	if (isset ($actions[Action::ROMAN])) {
		foreach ($actions[Action::ROMAN] as $romanment) {
			$romaned += $romanment->getValue();
		}
	}

	$romaned = ($romaned >= 100) ? true : false;

?>

			<div class="message<?=($romaned ? ' romaned' : '')?>" id="msg_<?=$msg->getId()?>">
				<div class="title"<?=($romaned ? ' onclick="javascript: $(this).parent().find(\'.body\').slideToggle();" title="Это сообщение зароманено пользователями сайта. Щёлкните, чтобы развернуть."' : '')?>>
					<div class="info">
						<a class="author" href="/id<?=$author->getId()?>"><?=$author->getFullName()?></a>
						<span class="time">&mdash; <?=date_local($msg->getTimestamp())?></span>
					</div>
					<div class="actions">
<?
	foreach (Action::forumTypes() as $type) {
		$count = count($actions[$type]);
		$possibility = $msg->canBeActedBy($type, $uid);
		$yours = false;
		if ($possibility && isset($actions[$type])) {
			foreach ($actions[$type] as $action) {
				if ($action->getAuthorId() == $uid) {
					$yours = true;
					break;
				}
			}
		}

		if ($count > 0 || $possibility) {
?>

						<div<?if ($count > 0) {?>
							onmouseover="javascript: forum.showActions('<?=$type?>', <?=$msg->getId()?>);"
							onmouseout="javascript: forum.hideActions('<?=$type?>', <?=$msg->getId()?>);"<?}?>
							<?if ($possibility){?>
							onclick="javascript: forum.actMsg('<?=$type?>', <?=$msg->getId()?>);"
							title="<?=Action::getActionName($type)?>"<?}?>
							class="action <?=$type?><?=$yours?' yours':''?><?=$possibility?' act':''?><?=$count?' count':''?>"><?=$count?$count:''?></div>
<?
		}
	}

?>
					</div>
<?
	if ($msg->isEditableBy($uid)) {
?>

					<div class="controls">
						<a href="#" onclick="javascript: forum.editMsg(<?=$msg->getId()?>);">править</a>
						<a href="#" onclick="javascript: forum.removeMsg(<?=$msg->getId()?>);">удалить</a>
					</div>
<?
	} elseif ($msg->isCitableBy($uid)) {
?>

					<div class="controls">
						<a href="#" onclick="javascript: forum.citeMsg(<?=$msg->getId()?>);">цитировать</a>
					</div>
<?
	}
?>

				</div>
				<div class="body">
					<div class="content">
						<div class="text">
							<?=$msg->getParsed()?>
						</div>

						<div class="edit">
							<textarea><?=$msg->getSource()?></textarea>
<?
	if ($msg->isEditableBy($uid)) {
?>

							<div class="button" onclick="javascript: forum.saveMsg(<?=$msg->getId()?>);">Сохранить</div>
							<div class="button" onclick="javascript: forum.editMsg(<?=$msg->getId()?>);">Отменить</div>
<?
	}
?>

						</div>
					</div>
					<div class="photo">
						<img alt="<?=$author->getFullName()?>" src="<?=$author->getImageURL(User::IMAGE_SQUARE)?>" />
					</div>
					<div style="clear: both;"></div>
				</div>
			</div>
<?
}

/**
 * $limit = 0 means only topics with new messages (minimum 10 if there is no with new)
 * @param <type> $uid
 * @param ForumPart $part
 * @param <type> $from
 * @param <type> $limit
 */
function forum_show_topics($uid, ForumPart $part, $from = 0, $limit = 0) {
	$top = ($limit == 0);
	$limit = ($limit == 0) ? Forum::TOPICS_PER_PAGE : $limit;
	$page = $from / $limit + 1;
	
	$count = $part->countTopics();
	$topics = $top ? $part->getTopicsTop($uid, Forum::TOP_TOPICS_COUNT) : $part->getTopics($from, $limit);
?>

			<div class="subtitle">
				<span>
					В этом разделе <?=$count?> <?=lang_sclon($count, 'тема', 'темы', 'тем')?><? if ($uid > 0 && $count > 0) { ?><span id="topic_new_href">, <a href="#new" onclick="forum.newTopic()">создать новую</a>.</span><? } ?>
				</span>
<?
	if ($count > 0) {
		forum_show_paging_bar($part, $limit, $page, $top);
	}
?>

			</div>
			<div class="content">
<?
	if ($uid > 0) {
?>

				<div id="topic_new"<?=$count == 0 ? ' style="display: block"' : ''?> onkeypress="javascript: forum.enterHandler(event, function () { forum.createTopic(<?=$part->getId()?>); });">
					<input id="topic_new_input" type="text" />
					<div class="ok" onclick="javascript: forum.createTopic(<?=$part->getId()?>);" title="Создать тему!">&crarr;</div>
				</div>

<?
	}
	
	foreach ($topics as $topic) {
		$new = $topic->hasNewFor($uid);
		$countMsgs = $topic->countMessages();
		$countNewMsgs = $topic->countNewFor($uid);
?>

				<div class="slide_block topic<?=($new ? ' new' : '')?>" id="topic_<?=$topic->getId()?>">
					<div class="title <?=($new ? ' opened' : '')?>">
						<?if ($new && $countNewMsgs) {?><div class="appendix">+<?=$countNewMsgs?></div><?}?>
						
						<div class="left">
							<div class="content">
								<a href="/forum/part<?=$part->getId()?>/topic<?=$topic->getId()?>"><?=$topic->getTitle()?></a>
							</div>
						</div>
						<div class="right">
							<div class="info">
<?
		if ($countMsgs == 0) {
?>

								<div class="count">Тема пуста</div>
<?
		} else {
?>

								<div class="count"><?=$countMsgs?> <?=lang_sclon($countMsgs, 'сообщение', 'сообщения', 'сообщений')?></div>
<?
		}
?>

								<div class="author"><a href="/id<?=$topic->getUID()?>"><?=$topic->getUser()->getFullName()?></a></div>
							</div>
<?
		if (!$topic->isClosed()) {
?>

							<div class="quick" onclick="javascript: forum.toggleQuick(<?=$topic->getId()?>);"></div>
<?
		} elseif ($topic->hasNextTopic()) {
?>

							<a href="/forum/part<?=$part->getId()?>/topic<?=$topic->getNextTopicId()?>" title="Тема закрыта. Перейти к продолжению"><div class="quick closed"></div></a>
<?
		} else {
?>

							<div class="quick closed" title="Тема закрыта. У этой темы нет продолжения"></div>
<?
		}
?>
						</div>
						<div style="clear: both;"></div>
					</div>
<?
		if (!$topic->isClosed()) {
?>

					<div class="body<?=($new ? ' block' : ' hidden')?>">
<?
			$lastMessage = $topic->getLastMessage();
?>

						<div id="quick_<?=$topic->getId()?>" class="last_message">
<?
			if ($lastMessage == null) {
?>

							<div class="empty">Напишите первое сообщение!</div>
<?
			} else {
				forum_show_last_message($lastMessage);
			}
?>

						</div>

						<div class="quick_answer" onkeypress="javascript: forum.ctrlEnterHandler(event, function () {forum.sendQuickMsg(<?=$topic->getId()?>);});">
<?
			if ($uid > 0) {
?>

							<?if ($countMsgs > 0) {?><div class="title">Быстрый ответ</div><?}?>

							<textarea id="quick_<?=$topic->getId()?>_textarea"></textarea>
							<div style="text-align: right;">
								<small style="cursor: pointer;" onclick="javascript: forum.sendQuickMsg(<?=$topic->getId()?>);" title="Я всё. Отправить!">Ctrl+Enter</small>
							</div>
<?
			} else {
?>

							<div class="empty">
								Авторизуйтесь или <a href="/sign_up">зарегистрируйтесь</a>,
								чтобы писать здесь.
							</div>
<?
			}
?>

						</div>
						<div style="clear: both;"></div>
					</div>
<?
		}
?>
					
				</div>
<?
	}
?>

			</div>

<?
	if ($count > 0) {
		forum_show_paging_bar($part, $limit, $page, $top);
	}
}

function forum_show_last_message(ForumMessage $lastMessage) {
	echo forum_html_last_message($lastMessage);
}

function forum_html_last_message(ForumMessage $lastMessage) {
	$author = $lastMessage->getAuthor();
	$uid = $author->getId();
	$uname = $author->getFullName();
	$msgid = $lastMessage->getId();
	$msgtxt = $lastMessage->getParsed();
	$msgshort = strip_tags(string_short($msgtxt, 300, 350, '...'));

	return <<< LABEL

							<div>
								<div class="title">Последнее сообщение</div>
								<span id="shorten_$msgid"
									  onclick="javascript: $('#shorten_$msgid').fadeOut('fast', function () { $('#full_$msgid').fadeIn('fast'); })">
									<b><a href="/id$uid">$uname</a>:</b> $msgshort
								</span>
								<span id="full_$msgid" style="display: none;">
									<b><a href="/id$uid">$uname</a>:</b> $msgtxt
								</span>
							</div>
LABEL;
}

function forum_show_actions($actions) {
	if (empty ($actions)) {
		echo 'Пусто';
		return;
	}

	$type = $actions[0]->getType();
	$title = 'Действия';

	switch ($type) {
	case Action::ROMAN:
		$title = 'Зароманивания';
		break;
	case Action::AGREE:
		$title = 'Согласия';
		break;
	}
?>

<div class="popup_content <?=$type?>">
	<div class="title"><?=$title?></div>
	<div class="body">
<?
	foreach ($actions as $action) {
		$author = $action->getAuthor();
?>

		<div class="action">
			<div class="uphoto">
				<img alt="<?=$author->getFullName()?>" src="<?=$author->getImageUrl(User::IMAGE_SQUARE_SMALL)?>" />
			</div>
			<div class="uname">
				<a href="/id<?=$author->getId()?>"><?=$author->getFullName()?></a>
			</div>
<?
		if ($type == Action::ROMAN) {
?>

			<div class="value"><?=$action->getValue()?>%</div>
<?
		}
?>

		</div>
<?
	}
?>

	</div>
</div>

<?
}

function forum_show_stats($tab = '') {
	$tabs = array(
		array('id' => 'messages', 'value' => 'по сообщениям'),
		array('id' => 'topics', 'value' => 'по темам'),
		array('id' => 'active-agreements', 'value' => 'по согласиям пользователя'),
		array('id' => 'passive-agreements', 'value' => 'по согласиям с пользователем'),
		array('id' => 'passive-agreements-normalized', 'value' => 'по удельным согласиям с пользователем'),
		array('id' => 'active-romanments', 'value' => 'по зароманиваниям'),
		array('id' => 'passive-romanments', 'value' => 'по зароманиваниям сообщений пользователя'),
		array('id' => 'passive-romanments-normalized', 'value' => 'по удельным зароманиваниям сообщений пользователя'),
		array('id' => 'pair-agreements', 'value' => 'по согласиям первого со вторым'),
		array('id' => 'pair-romanments', 'value' => 'по зароманиваниям первым второго')
	);

	if (!$tab) {
		$tab = $tabs[mt_rand(0, count($tabs) - 1)];
	}
?>

	<div id="stats_body"></div>
	<script type="text/javascript">
		$$(function () {
			var statSelector = new Selector({
				content: <?=json($tabs)?>,
				onSelect: forum.showStats,
				maxOptionsCount: <?=count($tabs)?>,
				onSelect: forum.loadStats
			});

			statSelector
				.onSelect('<?=$tab['id']?>')
				.select('<?=$tab['id']?>')
				.appendTo($('#stats_selector'))
				.setWidth(400);
		});
	</script>

<?
}

function forum_show_stats_body($stats_id) {
	require_once dirname(__FILE__) . '/../classes/stats/ContentStatsCounter.php';
	User::getAll();

	$stats = array();
	switch ($stats_id) {
	case 'messages':
		$stats = ContentStatsCounter::getCommentCount(Item::FORUM_TOPIC);
		break;
	case 'topics':
		$stats = ContentStatsCounter::getItemCount(Item::FORUM_TOPIC);
		break;
	case 'active-agreements':
		$stats = ContentStatsCounter::getActiveActionsOnComments(Action::AGREE, Comment::FORUM_MESSAGE);
		break;
	case 'passive-agreements':
		$stats = ContentStatsCounter::getPassiveActionsOnComments(Action::AGREE, Comment::FORUM_MESSAGE);
		break;
	case 'active-romanments':
		$stats = ContentStatsCounter::getActiveActionsOnComments(Action::ROMAN, Comment::FORUM_MESSAGE);
		break;
	case 'passive-romanments':
		$stats = ContentStatsCounter::getPassiveActionsOnComments(Action::ROMAN, Comment::FORUM_MESSAGE);
		break;
	case 'pair-agreements':
		$stats = ContentStatsCounter::getPairActions(Action::AGREE);
		break;
	case 'pair-romanments':
		$stats = ContentStatsCounter::getPairActions(Action::ROMAN);
		break;
	case 'passive-agreements-normalized':
		$stats = ContentStatsCounter::getPassiveActionsOnForumMessagesNormaized(Action::AGREE);
		break;
	case 'passive-romanments-normalized':
		$stats = ContentStatsCounter::getPassiveActionsOnForumMessagesNormaized(Action::ROMAN);
		break;
	}

	$stats_result = array();
	switch ($stats_id) {
	case 'messages':
	case 'topics':
	case 'active-agreements':
	case 'passive-agreements':
	case 'active-romanments':
	case 'passive-romanments':
	case 'passive-agreements-normalized':
	case 'passive-romanments-normalized':
		foreach ($stats as $row) {
			$person = User::getById($row['uid']);
			if ($person) {
				$stats_result[] = array(
					'image' => '<img src="'.$person->getImageUrl(User::IMAGE_SQUARE_SMALL).'" />',
					'html' => '<a href="/id' . $person->getId() . '">' . $person->getFullName() . '</a>',
					'value' => $row['value']
				);
			}
		}
		break;
	case 'pair-agreements':
	case 'pair-romanments':
		foreach ($stats as $row) {
			$person1 = User::getById($row['subject']);
			$person2 = User::getById($row['object']);
			if ($person1 && $person2) {
				$stats_result[] = array(
					'html' => '<a href="/id' . $person1->getId() . '">' . $person1->getFullName() .
							  '</a> &rarr; <a href="/id' . $person2->getId() . '">'. $person2->getFullName() . '</a>',
					'value' => $row['value'],
					'image' => ''
				);
			}
		}
		break;
	}

	$cut = false;
	$max = isset($stats_result[0]['value']) ? $stats_result[0]['value'] : 100;
	foreach ($stats_result as $i => $row) {
		if (!$cut && $i >= 10) {
			$cut = true;
?>

	<div id="stats_cut_div" onclick="javascript: $('#stats_cut').slideToggle(); $(this).hide();">далее...</div>
	<div id="stats_cut" style="display: none;">
		<span></span>
<?
		}

		$w = round(70 * $row['value'] / $max) + 20;
?>

	<div class="line" style="width: <?=$w?>%;">
		<div class="image"><?=$row['image']?></div>
		<div class="name"><?=$row['html']?></div>
		<div class="value"><?=$row['value']?></div>
		<div style="clear: both;"></div>
	</div>
<?
	}
	
	if ($cut) {
?>

	</div>
<?
	}
}

?>