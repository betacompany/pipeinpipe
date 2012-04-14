<?php
/**
 * @author Artyom Grigoriev
 */

require_once dirname(__FILE__) . '/../classes/blog/Blog.php';
require_once dirname(__FILE__) . '/../classes/blog/BlogPost.php';
require_once dirname(__FILE__) . '/tag_creator.php';

global $auth, $user;

if ($auth->isAuth()) {
	$showEditor = true;
	$postTitle = '';
	$postFullSource = '';
	$postShortSource = '';
	$postId = 0;
	$postBlogId = 0;
	$tagIds = '{}';

	if (issetParam('post_id')) {
		$postId = intparam('post_id');
		$post = Item::getById($postId);
		if ($post instanceof BlogPost) {
			$blog = $post->getGroup();
			if ($user->hasPermission($blog, 'edit')) {
				$postTitle = $post->getTitle();
				$postFullSource = $post->getFullSource();
				$postShortSource = $post->getShortSource();
				$postBlogId = $blog->getId();
				$tags = $post->getTags();
				$tagIdsArray = array();
				foreach ($tags as $tag) {
					$tagIdsArray[ $tag->getId() ] = true;
				}
				$tagIds = json($tagIdsArray);
			} else {
				$showEditor = false;
			}
		} else {
			global $LOG;
			@$LOG->warn('Item id='.$postId.' is not post');
		}
	}
?>

<div id="blog_editor">
<?
	$blogs = $user->getBlogs();
	if (count($blogs) == 0) {
?>

	<div>У вас нет ни одного блога, в который вы можете писать.</div>
	<div class="title">
		<label for="title">Название блога</label>
		<input id="blog_title" type="text" name="title" value="<?=$user->getFullName();?>" />
	</div>
	<div class="subbody">
		<label for="desc">Краткое описание</label>
		<textarea id="blog_description" name="desc"></textarea>
	</div>
	<div>
		<div id="create_blog" class="button">Создать личный блог</div>
	</div>
	<script type="text/javascript">
		$(function () {
			$('#create_blog').one('click', function () {
				$.ajax({
					url: '/procs/proc_life.php',
					data: {
						method: 'create_blog',
						holder_type: 'user',
						holder_id: <?=$user->getId()?>,
						blog_title: $('#blog_title').val(),
						blog_description: $('#blog_description').val()
					},
					type: 'post',
					dataType: 'json',

					success: function (json) {
						if (!json || !json.status || json.status != 'ok') {
							main.showErrorText('Не удалось :(');
							return;
						}

						window.location.reload();
					},

					error: main.showErrorText
				});
			});
		});
	</script>
<?
	} else if ($showEditor) {
?>

	<form method="post" action="/procs/proc_life.php">
		<? if ($postId) : ?>
		<input type="hidden" name="method" value="edit_post" />
		<input type="hidden" name="post_id" value="<?=$postId?>" />
		<? else: ?>
		<input type="hidden" name="method" value="add_post" />
		<? endif; ?>
		<div class="title">
			<label for="post_title">Заголовок</label>
			<input type="text" name="post_title" id="post_title" value="<?=$postTitle?>" />
		</div>
		<div class="body">
			<label for="post_full_source">Текст поста</label>
			<textarea name="post_full_source" id="post_full_source"><?=$postFullSource?></textarea>
		</div>
		<div class="subbody">
			<label for="post_short_source">Выдержка</label>
			<textarea name="post_short_source" id="post_short_source"><?=$postShortSource?></textarea>
		</div>
		<div>
			<label for="post_tags">Тэги</label>
			<input type="hidden" name="post_tags" id="post_tags" value="" />
			<div id="tags" class="tags"></div>
			<div style="clear: both;"></div>
		</div>
<?
        tag_creator_show($post);
?>
        <div class="blog_selector">
			<label for="post_blog_id">Добавить пост в блог:</label>
			<select name="post_blog_id" id="post_blog_id">
<?
		foreach ($blogs as $blog) {
?>

				<option value="<?=$blog->getId()?>"<?=$postBlogId ? ($postBlogId == $blog->getId() ? ' selected="selected"' : '') : ''?>><?=$blog->getTitle()?></option>
<?
		}
?>

			</select>
			<div id="editor_button" class="button" onclick="javascript: fillFormTagsInput('input[name=post_tags]', <?=$postId?>)">Опубликовать</div>
<?
		if ($postId) {
?>

			<a href="/life/blog/<?=$postId?>"><div id="cancel_button" class="button">Отмена</div></a>
<?
		}
?>

		</div>
	</form>
<?
	}
?>

</div>
<?
} else {
?>

<center>Чтобы писать посты нужно авторизоваться!</center>

<?
}

?>
