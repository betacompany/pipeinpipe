<?php
/**
 * @author Artyom Grigoriev
 */

require_once dirname(__FILE__) . '/blocks.php';

require_once dirname(__FILE__) . '/../classes/forum/ForumTopic.php';

require_once dirname(__FILE__) . '/../classes/blog/Blog.php';
require_once dirname(__FILE__) . '/../classes/blog/BlogPost.php';

require_once dirname(__FILE__) . '/../classes/media/Photo.php';
require_once dirname(__FILE__) . '/../classes/media/Video.php';

require_once dirname(__FILE__) . '/../includes/lang.php';

function life_show_blog_info(Blog $blog) {
	$holders = Connection::getHoldersFor($blog);
?>

<div class="blog">
	<h1><?=$blog->getTitle()?></h1>
	<div class="owner">
<?
	if (count($holders) > 0) {
		if (count($holders) > 1) {
			echo 'Авторы: ';
		} else {
			echo 'Автор: ';
		}
	}
	foreach ($holders as $i => $holder) {
?>

		<a href="<?=Connection::holderURL($holder)?>"><?=Connection::holderTitle($holder)?></a><?if ($i < count($holders) - 1) echo ',';?>
		
<?		
	}
?>

	</div>
	<div class="description">
		<?=$blog->getContentParsed()?>
		
	</div>
</div>
<?
}

function life_show_post_short(BlogPost $post, $showBlog = false, $showComments = true) {
	require_once dirname(__FILE__) . '/../includes/lang.php';

	$author = $post->getUser();
	$timestamp = $post->getCreationTimestamp();
	$count = $post->countComments();
?>

<div id="post_<?=$post->getId()?>" class="post">
	<h2 class="other">
		<a href="/life/blog/<?=$post->getId()?>"><?=$post->getTitle()?></a>
	</h2>
	<div class="owner">
		<img src="<?=$author->getImageURL(User::IMAGE_SQUARE_SMALL)?>" alt="<?=$author->getFullName()?>" />
		<a href="/id<?=$author->getId()?>"><?=$author->getFullName()?></a>
		<?=date_local($timestamp)?>
		<?if ($showBlog) {?>

		в блоге &laquo;<a href="/life/blog#blog=<?=$post->getGroupId()?>" onclick="javascript: life.showBlog(<?=$post->getGroupId()?>);"><?=$post->getGroup()->getTitle()?></a>&raquo;
		<?}?>

	</div>
	<div class="body">
		<?=$post->getShortHTML()?>
		
	</div>
<?if ($showComments):?>

	<div class="sub">
		<span class="comments_count"><?=lang_number_sclon($count, 'комментарий', 'комментария', 'комментариев')?></span>
		<?if ($post->hasFullVersion()) {?> | <a onclick="javascript: return life.loadPost(<?=$post->getId()?>);" href="/life/blog/<?=$post->getId()?>">подробнее...</a><?}?>

	</div>
<?endif;?>

</div>

<hr />

<?
}

function life_show_post_full(BlogPost $post, $user) {
	$author = $post->getUser();
	$timestamp = $post->getCreationTimestamp();
	$text = $post->getFullHTML() != '' ? $post->getFullHTML() : $post->getShortHTML();
	$blog = $post->getGroup();
	$editable = $user && $user->hasPermission($blog, 'edit');
	if ($user) {
		$post->viewedBy($user);
	}
?>

<div id="post">
	<h2 class="other"><?=$post->getTitle()?></h2>
	<div class="owner">
		<img src="<?=$author->getImageURL(User::IMAGE_SQUARE_SMALL)?>" alt="<?=$author->getFullName()?>" />
		<a href="/id<?=$author->getId()?>"><?=$author->getFullName()?></a>
		<?=date_local($timestamp)?>
		в блоге &laquo;<a href="/life/blog#blog=<?=$post->getGroupId()?>" onclick="javascript: life.showBlog(<?=$post->getGroupId()?>);"><?=$post->getGroup()->getTitle()?></a>&raquo;
	</div>
	<div class="body">
		<?=$text?>

	</div>
<?
	if ($editable) {
?>

	<div class="sub" style="text-align: right;">
		<span class="text_menu">
			<a href="/life/blog/<?=$post->getId()?>/edit">править</a> |
			<a href="#" onclick="javascript: life.blog.removePost(<?=$post->getId()?>);">удалить</a>
		</span>
	</div>
<?
	}
?>

	<div class="sub">
<?
	show_block_tags($post->getTags(), '/life/blog#tag=%d');
?>

	</div>
	<div class="sub">
<?
	show_block_sharing(
		'/life/blog/' . $post->getId(),
		$post->getTitle()
	);
?>

	</div>
	<div class="comments">
<?
	show_block_comments($user, $post);
?>
		
	</div>
</div>
<?
}

function life_show_posts($posts, $user, $showBlog = false, $showComments = true) {
	if (count($posts) == 0) {
		echo '';
	}

	foreach ($posts as $post) {
		if ($post instanceof BlogPost && $post->isAvailableFor($user)) {
			life_show_post_short($post, $showBlog, $showComments);
		}
	}
}

function life_get_day_feed($date) {
	$begin = strtotime($date . ' 00:00:00');
	$end = strtotime($date . ' 23:59:59');
	$items = Item::getByPeriod($begin, $end);
	$events = Event::getByPeriod($begin, $end);
	$merged = array();

	foreach ($items as $item) {
		if ($item->getType() == Item::EVENT) continue;
		$ts = round(strval($item->getCreationTimestamp()) / 60) * 60;
		if (isset($merged[$ts])) {
			$merged[$ts][] = $item;
		} else {
			$merged[$ts] = array ($item);
		}
	}

	foreach ($events as $event) {
		$ts = round(strval($event->getStartTimestamp()) / 60) * 60;
		if (isset($merged[$ts])) {
			$merged[$ts][] = $event;
		} else {
			$merged[$ts] = array ($event);
		}
	}

	return $merged;
}

function life_show_timeline($date) {
	
}

function life_show_feed_item(Item $item) {
	global $user;
	if ($item instanceof ForumTopic) {
		$topic = $item;
		$topicNew = $topic->hasNewFor($user);
		$topicClosed = $topic->isClosed();
?>

	<div<?=($topicNew && $topicClosed ? ' class="topic new closed"' : ($topicClosed ? ' class="topic closed"' : ($topicNew ? ' class="topic new"' : ' class="topic"')))?>>
		<div class="before"></div>
		<a href="/forum/part<?=$topic->getPartId()?>/topic<?=$topic->getId()?>"><?=$topic->getTitle()?></a>
	</div>
<?
	} elseif ($item instanceof Photo) {
?>

	<a href="/media/photo/album<?=$item->getGroupId()?>/<?=$item->getId()?>"><img class="photo" src="<?=$item->getUrl(Photo::SIZE_MINI)?>" alt="<?=$item->getTitle()?>" /></a>
<?
	} elseif ($item instanceof Video) {
?>

	<a href="/media/video/album<?=$item->getGroupId()?>/<?=$item->getId()?>" title="<?=$item->getTitle()?>">
		<div class="video" style="background-image: url('<?=$item->getPreviewUrl()?>');">
			<div></div>
		</div>
	</a>
<?
	} elseif ($item instanceof BlogPost) {
		$new = $item->getLastViewFor($user) == 0;
?>

	<div class="blog_post<?=$new?' new':''?>">
		<div class="before"></div>
		<a href="/life/blog/<?=$item->getId()?>"><?=$item->getTitle()?></a>
	</div>
<?
	} elseif ($item instanceof Event) {
?>

	<div class="event">
		<div class="title"><?=$item->getContentTitle()?></div>
		<div class="body"><?=$item->getContentParsed()?></div>
	</div>
<?
	} else {
		global $LOG;
		@$LOG->warn('item with id='.$item->getId().' has unhandled type');
	}
}

function life_show_feed_item_title($uid, $type, $count) {
	$u = User::getById($uid);
	$types = array();
	$display = true;
	switch ($type) {
	case Item::BLOG_POST:
		$types = array('запись', 'записи', 'записей');
		break;
	case Item::EVENT:
		$types = array('событие', 'события', 'событий');
		$display = false;
		break;
	case Item::FORUM_TOPIC:
		$types = array('топик', 'топика', 'топиков');
		break;
	case Item::PHOTO:
		$types = array('фотография', 'фотографии', 'фотографий');
		break;
	case Item::VIDEO:
		$types = array('видеозапись', 'видеозаписи', 'видеозаписей');
		break;
	default:
		$display = false;
		global $LOG;
		@$LOG->warn('Unknown type='.$type);
	}

	if ($display) {
?>

	<img src="<?=$u->getImageUrl(User::IMAGE_SQUARE_SMALL)?>" alt="<?=$u->getFullName()?>" />
	<a href="/id<?=$uid?>"><?=$u->getFullName()?></a>
	<span><?=lang_number_sclon($count, $types[0], $types[1], $types[2])?></span>
<?
	}
}

function life_show_day_feed($date = '') {
	if (!$date) $date = date('Y-m-d');
	global $user;
	$uid = $user ? $user->getId() : 0;

	$feed = life_get_day_feed($date);
	krsort($feed);
	
	$feed_times = array();
	$feed_counts = array();

	$last_uid = 0;
	$last_type = '';

	$i = 0;
	foreach ($feed as $ts => $items) {
		foreach ($items as $item) {
			try {
				$cur_type = $item->getType();
				$cur_uid = $item->getUID();
				$new_row = !($cur_type == $last_type && $cur_uid == $last_uid);
				if ($new_row) $i++;
				$key = $cur_type . '_' . $cur_uid . '_' . $i;
				if (!isset($feed_counts[$key])) {
					$feed_counts[$key] = 1;
				} else {
					$feed_counts[$key]++;
				}
				$feed_times[$key] = max($ts, $feed_times[$key]);
				$last_type = $cur_type;
				$last_uid = $cur_uid;
			} catch (Exception $e) {
				global $LOG;
				@$LOG->exception($e);
			}
		}
	}

	$last_uid = 0;
	$last_type = '';
	$first = true;

	if (count($feed)) {
?>

<table id="feed">
	<tbody>
<?
	$i = 0;
	foreach ($feed as $ts => $items) {
		foreach ($items as $item) {
			try {
				$cur_type = $item->getType();
				$cur_uid = $item->getUID();
				$new_row = !($cur_type == $last_type && $cur_uid == $last_uid);
				if ($first || $new_row) $i++;
				$key = $cur_type . '_' . $cur_uid . '_' . $i;

				if ($first) {
?>

		<tr class="row">
			<td><?=date('H:i', $feed_times[$key])?></td>
			<td class="comments_block">
				<div class="comment">
					<div class="title">
						<? life_show_feed_item_title($cur_uid, $cur_type, $feed_counts[$key]); ?>
					</div>
					<div class="body">
<?
				} else if ($new_row) {
?>

					</div>
				</div>
			</td>
		</tr>
		<tr class="row">
			<td><?=date('H:i', $feed_times[$key])?></td>
			<td class="comments_block">
				<div class="comment">
					<div class="title">
						<? life_show_feed_item_title($cur_uid, $cur_type, $feed_counts[$key]); ?>
					</div>
					<div class="body">
<?
				}

				life_show_feed_item($item);

				$last_type = $cur_type;
				$last_uid = $cur_uid;
				$first = false;
			} catch (Exception $e) {
				global $LOG;
				@$LOG->exception($e);
			}
		}
	}
?>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>
<?
	} else {
?>

<center>Лента пуста</center>
<?
	}
}

function life_show_comments($comments, $user) {
	$itemId = 0;
	foreach ($comments as $comment) {
		$author = $comment->getUser();
		$item = $comment->getItem();
?>

<tr id="comment_<?=$comment->getId()?>" class="row <?=$item->getType()?><?=($itemId != $item->getId() ? ' space' : '')?>">
	<td>
		<?=date_local($comment->getTimestamp(), DATE_LOCAL_SHORT)?>
	</td>

	<td class="comments_block">
		<div class="comment">
			<div class="title">
				<img src="<?=$author->getImageUrl(User::IMAGE_SQUARE_SMALL);?>" alt="<?=$author->getFullName()?>" />
				<a href="/id<?=$author->getId()?>"><?=$author->getFullName()?></a>
<?
			if ($itemId != $item->getId()) {
				if ($item instanceof Photo) {
?>

				<span>в комментарий к фотографии &laquo;<a href="/media/photo/album<?=$item->getGroupId()?>/<?=$item->getId()?>"><?=$comment->getItem()->getTitle()?></a>&raquo;</span>
<?
				} elseif ($item instanceof Video) {
?>

				<span>в комментарий к видеозаписи &laquo;<a href="/media/video/album<?=$item->getGroupId()?>/<?=$item->getId()?>"><?=$comment->getItem()->getTitle()?></a>&raquo;</span>
<?
				} elseif ($item instanceof BlogPost) {
?>

				<span>в комментарий к посту &laquo;<a href="/life/blog/<?=$item->getId()?>"><?=$comment->getItem()->getTitle()?></a>&raquo;</span>
<?
				} elseif ($item instanceof Event) {
?>

				<span>в комментарий к событию &laquo;<a href=""><?=$comment->getItem()->getTitle()?></a>&raquo;</span>
<?
				}
			}
?>
				
			</div>
			<div class="body">
				<?=$comment->getContentParsed()?>

			</div>
		</div>
	</td>

	<td class="item_shortcut">
<?
		if ($itemId != $item->getId()) {
			if ($item instanceof Photo) {
?>

		<a href="/media/photo/album<?=$item->getGroupId()?>/<?=$item->getId()?>">
			<img src="<?=$item->getUrl(Photo::SIZE_MINI)?>" alt="<?=$item->getTitle()?>" />
		</a>
<?
			} elseif ($item instanceof Video) {
?>

		<a href="/media/video/album<?=$item->getGroupId()?>/<?=$item->getId()?>" title="<?=$item->getTitle()?>">
			<div class="video" style="background-image: url('<?=$item->getPreviewUrl()?>');">
				<div></div>
			</div>
		</a>
<?
			} elseif ($item instanceof BlogPost) {

			} elseif ($item instanceof Event) {

			}
		}
?>

	</td>
</tr>
<?
		$itemId = $comment->getItemId();
	}
}

?>
