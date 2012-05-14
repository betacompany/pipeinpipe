<?php
/**
 * @author Artyom Grigoriev
 */

require_once dirname(__FILE__) . '/../classes/blog/BlogPost.php';

require_once dirname(__FILE__) . '/life_view.php';
require_once dirname(__FILE__) . '/tag_cloud.php';

global $auth;
global $user;

if (!isset($_REQUEST['post_id'])) {
	$blogs = Group::getRootsByType(Group::BLOG);
	if ($user) Group::preloadNewItemsCountFor($user);
	$posts = Item::getAllByType(Item::BLOG_POST, 0, 10, true, true);
    $tags = Tag::getAllByType(Item::BLOG_POST, true);
?>
<center><?tag_cloud_show($tags, 300);?></center>

<div id="stream_wrapper">
	<div id="stream_container">
		<div id="stream_content">
<?
	life_show_posts($posts, $user);
?>

		</div>
		<div id="stream_loading"></div>
		<div id="stream_end"></div>
	</div>
</div>

<div id="options_wrapper">
	<div id="options">
<?
	if ($auth->isAuth()) {
?>

		<a class="option" href="/life/blog/new">
			<div class="option">
				<div>
					<div class="title">Написать пост</div>
				</div>
			</div>
		</a>
<?
	}
?>

		<a class="option" href="#blog=0" onclick="javascript: life.showAllPosts();">
			<div id="blog_0" class="option selected">
				<div>
					<div class="title">Все блоги</div>
				</div>
			</div>
		</a>
<?
	foreach ($blogs as $blog) {
		$title = $blog->getTitle();
		$owner = $blog->getOwnerDescription();
		$count = $blog->getNewItemsCount() > 0 ? '+' . $blog->getNewItemsCount() : '';
?>

		<a class="option" href="#blog=<?=$blog->getId()?>" onclick="javascript: life.showBlog(<?=$blog->getId()?>);">
			<div id="blog_<?=$blog->getId()?>" class="option">
				<div class="main">
					<div class="title"><?=$title?></div>
					<div class="owner"><?=$owner?></div>
				</div>
				<div class="count"><?=$count?></div>
			</div>
		</a>
<?
	}
?>
	</div>
	<script type="text/javascript">
		$$(function () {
			if ($('#life_container').innerWidth() < 900) {
				$('#options_wrapper').width(200);
				$('#stream_wrapper').css('margin-right', '-200px');
				$('#stream_container').css('margin-right', '230px');
			}
		});
	</script>
</div>

<div style="clear: both;"></div>

<?
} else {
	$post = Item::getById(param('post_id'));
	if ($post instanceof BlogPost && $post->isAvailableFor($user)) {
		life_show_post_full($post, $user);
	}
}

?>
