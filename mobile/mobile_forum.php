<?php
/**
 * @author Artyom Grigoriev
 */

global $auth;
global $user;

switch (param('part')) {
	case 'forum':

	require_once dirname(__FILE__) . '/../main/classes/forum/Forum.php';

	if ($auth->isAuth()) {
		Forum::init($user->getId());
	}

	$forums = Forum::getForums();
	foreach ($forums as $forum) {
		mobile_show_header($forum);
		$parts = $forum->getParts();
		foreach ($parts as $part) {
			mobile_show_part($part, $user);
		}
	}

	$PATH = "/forum";

	break;

case 'forum_part':

	require_once dirname(__FILE__) . '/../main/classes/forum/Forum.php';

	if ($auth->isAuth()) {
		Forum::init($user->getId());
	}

	assertParam('id');
	$from = issetParam('page') ? (param('page') - 1) * ITEMS_PER_PAGE: 0;
	$page = issetParam('page') ? param('page') : 0;

	$part = ForumPart::getById(intparam('id'));
	if ($part instanceof ForumPart) {

		mobile_show_header($part, "", false);

		$last_page = ceil($part->countItems() / ITEMS_PER_PAGE);
		mobile_show_pager(
			"/forum_part/{$part->getId()}/page%d",
			$page,
			$last_page,
			"/forum_part/{$part->getId()}"
		);

		echo '<table><tbody>';

		$topics = $part->getTopics($from, ITEMS_PER_PAGE);
		foreach ($topics as $topic) {
			mobile_show_topic($topic, $user);
		}
		echo '</table></tbody>';

		$PATH = "/forum/part{$part->getId()}";
	}

	break;

case 'forum_topic':

	require_once dirname(__FILE__) . '/../main/classes/forum/Forum.php';

	assertParam('id');

	$topic = ForumTopic::getById(intparam('id'));
	if ($topic instanceof ForumTopic) {

		if ($auth->isAuth()) {
			$topic->visit($user);
		}

		$closed = $topic->isClosed() ? "<div style=\"background: url('http://".
									   MAIN_SITE_URL."/images/icons/arrows.png') 0 -210px;
									   width: 30px; height: 30px;\"></div>" : "";
		mobile_show_table_header($topic, $closed);
		$count = $topic->countMessages();

		$last_page = ceil($count / ITEMS_PER_PAGE);
		$page = issetParam('page') ? param('page') : $last_page;
		$from = ($page - 1) * ITEMS_PER_PAGE;

		mobile_show_pager(
			"/forum_topic/{$topic->getId()}/page%d",
			$page,
			$last_page
		);

		echo "<table style=\"width: 100%\"><tbody>";
		$messages = $topic->getMessages($from, ITEMS_PER_PAGE);
		foreach ($messages as $message) {
			mobile_show_message($message, $user);
		}

		$count = count($messages);

		if ($count == 0) {
			echo '<tr class="b0"><td><center>Нет сообщений</center></td></tr>';
		}
		echo "</tbody></table>";

		if ($count > 0) {
			mobile_show_pager(
				"/forum_topic/{$topic->getId()}/page%d",
				$page,
				$last_page
			);
			echo "<div class=\"pg\"></div>";
		}

		if (!$topic->isClosed() && $user != null) {
			mobile_show_textarea(
				array(
					'action' => 'http://' . MAIN_SITE_URL . '/procs/proc_forum.php',
					'mobile' => '1',
					'method' => 'add_message',
					'topic_id' => $topic->getId()
				)
			);
		}
	}

	break;
}

?>
