<?php

require_once dirname(__FILE__) . '/../includes/config-local.php';

function show_block_sign_in() {
?>

						<div id="sign_in_block">
							<div style="float: left;">
								<form id="sign_in_form" action="/procs/proc_main.php" method="post">
									<input type="hidden" name="method" value="sign_in" />
									<label for="sign_in_login">Логин:</label>
									<input id="sign_in_login" type="text" name="sign_in_login" />
									<label for="sign_in_login">Пароль:</label>
									<input id="sign_in_password" type="password" name="sign_in_password" />
									<label id="sign_in_button_txt" for="sign_in_button">Войти</label>									
								</form>
							</div>

							<script type="text/javascript">
								$(function () {
									$('#sign_in_password').add('#sign_in_login').keypress(function (e) {
										if (e.keyCode == 13) {
											$('#sign_in_form').submit();
										}
									});
									$('#sign_in_button').add('#sign_in_button_txt')
										.click(function () {
											$('#sign_in_form').submit();
										})
										.css({opacity: .7})
										.hover(
											function () {
												$('#sign_in_button').add('#sign_in_button_txt').animate({opacity: 1});
											},
											function () {
												$('#sign_in_button').add('#sign_in_button_txt').animate({opacity: .7});
											}
										);
								});
							</script>

							<div id="sign_in_button" class="sign_in"></div>

							<script type="text/javascript" src="/js/login_vk.js?2"></script>
							<div id="vk_login" class="vk_icon"></div>
							<!--<div id="fb_login" class="fb_icon"></div>
							<div id="tw_login" class="tw_icon"></div>-->
						</div>

<?
}

function show_block_user($user) {
	if ($user == null) {

	} else {
?>
							
						<div id="user_bar">
							<div class="photo">
								<img src="<?=$user->getImageURL(User::IMAGE_SQUARE_SMALL)?>" alt="<?=$user->getId()?>" />
							</div>
							<div class="user">
								<a href="/id<?=$user->getId()?>"><?=$user->getFullName()?></a>
								<small>
									(<a href="/sign_out">выйти</a>)
								</small>
							</div>
						</div>							
<?
	}
}

function return_sport_rating_popup($text) {
	return <<< LABEL
<table class="popup_box">
	<tbody>
		<tr class="top">
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr class="middle">
			<td></td>
			<td>$text</td>
			<td></td>
		</tr>
		<tr class="bottom">
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</tbody>
</table>
LABEL;
}

function show_sport_rating_popup($text) {
	echo return_sport_rating_popup($text);
}

/**
 * String $onclick should have such syntax:
 * <code>jsfunction(%d)</code>
 * where %d is placeholder for number of the page to handle
 * @param int $count
 * @param int $limit
 * @param int $page
 * @param string $onclick
 */
function show_paging_bar($count, $limit, $page, $onclick) {
	$last = ceil($count / $limit);
	$first_page = max(1, $page - 2);
	$last_page = min($last, $page + 2);
?>

<div class="paging">
<?
	if ($first_page != 1) {
?>

	<a href="#page=1" onclick="javascript: <?=sprintf($onclick, 1)?>;">
		<div>&laquo;</div>
	</a>
<?
	}

	for ($i = $first_page; $i <= $last_page; $i++) {
?>

	<a href="#page=<?=$i?>" onclick="javascript: <?=sprintf($onclick, $i)?>;">
		<div<?=($page == $i) ? ' class="selected"' : ''?>><?=$i?></div>
	</a>
<?
	}

	if ($last_page != $last) {
?>

	<a href="#page=1" onclick="javascript: <?=sprintf($onclick, $last)?>;">
		<div>&raquo;</div>
	</a>
<?
	}
?>

</div>

<?
}

function show_block_similar_users($users) {
?>

<div class="wrapper">
	<div class="title_bar">
		<div class="title">Возможно, Вы уже зарегистрированы на нашем сайте!</div>
		<div class="subtitle">Попробуйте найти себя среди перечисленных ниже пользователей.</div>
	</div>
	<div class="menu">
		<form action="/procs/proc_main.php" method="post">
			<input type="hidden" name="method" value="sign_in_social" />
			<input type="hidden" name="social" value="vk" />
			<input type="hidden" name="uid" value="0" />
			<label for="password">Введите пароль:</label>
			<input type="password" name="password" />
			<input type="submit" name="sign_in" value="Войти!" />
		</form>
	</div>
	<div class="body">
<?
	foreach ($users as $user) {
		$uphoto = $user->getImageURL(User::IMAGE_SQUARE);
?>

		<div class="user">
			<div class="inner">
				<div class="photo">
					<img alt="<?=$user->getFullName()?>" src="<?=$uphoto?>" />
				</div>
				<div class="name">
					<a href="/id<?=$user->getId()?>"><?=$user->getName()?><br /><?=$user->getSurname()?></a>
				</div>
			</div>
		</div>
<?
	}
?>

	</div>
</div>

<script type="text/javascript">
$('.user')
	.hover(
		function () {
			$(this).animate({backgroundColor: COLOR.FIRST_LIGHT});
		},
		function () {
			$(this).animate({backgroundColor: '#fff'});
		}
	)
	.click(
		function () {
			if ($(this).hasClass('fixed')) {
				$(this).removeClass('fixed');
				$(this).parent().parent().find('.menu').slideUp('fast');
			} else {
				$('.user').removeClass('fixed');
				$(this).addClass('fixed');
				var href = $(this).find('.name > a').attr('href');
				var splitted = href.split('id');
				var uid = splitted[1];
				$('.menu input[name=uid]').val(uid);
				$(this).parent().parent().find('.menu').slideDown('fast');
			}
		}
	);
</script>



<?
}

function show_comments_page(Item $item, $from, $limit) {
	$comments = $item->getComments($from, $limit);
	$count = $item->countComments();
?>
	<div class="title">
		<div class="count"><?=lang_number_sclon($count, 'комментарий', 'комментария', 'комментариев')?></div>
<?
	if ($count > $limit) {
		show_paging_bar($count, $limit, $from / $limit + 1, 'content.loadComments('.$item->getId().', %d)');
	}
?>

	</div>
	<div class="body">
<?
	foreach ($comments as $comment) {
		if ($comment->getType() == Comment::BASIC_COMMENT) {
			$author = $comment->getUser();
?>

		<div class="comment">
			<div class="title">
				<img src="<?=$author->getImageUrl(User::IMAGE_SQUARE_SMALL);?>" alt="<?=$author->getFullName()?>" />
				<a href="/id<?=$author->getId()?>"><?=$author->getFullName()?></a>
				<span><?=date_local($comment->getTimestamp(), DATE_LOCAL_SHORT)?></span>
			</div>
			<div class="body">
				<?=$comment->getContentParsed()?>

			</div>
		</div>
<?
		}
	}
?>

	</div>
<?
}

function show_block_comments($user, Item $item) {
?>

<script type="text/javascript">
$(document).ready(function () {
	if (getAnchorParam('page') != null) {
		content.loadComments(<?=$item->getId()?>, getAnchorParam('page'));
	}
});
</script>

<div id="comments_<?=$item->getId()?>" class="comments_block">
	<div class="comments_content">
<?
	$count = $item->countComments();
	$last = max(0, floor(($count - 1) / 5));
	show_comments_page($item, $last * 5, 5);
?>

	</div>
<?
	if ($user != null) {
?>

	<div class="add_comment" onkeypress="javascript: content.ctrlEnterHandler(event, function () { content.sendComment(<?=$item->getId()?>); })">
		<textarea name="text"></textarea>
		<div style="text-align: right;">
			<small style="cursor: pointer;" onclick="javascript: content.sendComment(<?=$item->getId()?>);" title="Я всё. Отправить!">Ctrl+Enter</small>
		</div>
	</div>
<?
	}
?>

</div>
<?
}

/**
 * @param $url must contain leading slash
 */
function show_block_sharing($url, $title) {
	require_once dirname(__FILE__) . '/../classes/social/Vkontakte.php';
	require_once dirname(__FILE__) . '/../classes/social/Facebook.php';
?>

<!--- VKontakte share -->
<script type="text/javascript">
	VK.init({
		apiId: <?=Vkontakte::VK_APP_ID?>,
		onlyWidgets: true
	});
</script>
<div id="vk_like" style="float: left;"></div>
<script type="text/javascript">
	VK.Widgets.Like("vk_like", {type: "mini"});
</script>
<!-- Facebook share -->
<!--<fb:like href="<?=MAIN_SITE_URL.$url?>" layout="button_count" show_faces="true" width="200" font="trebuchet ms"></fb:like>
<div id="fb-root" style="display: none;"></div>
<script type="text/javascript">
	FB.init({
		appId  : <?=Facebook::FACEBOOK_APP_ID?>,
		status : true,
		cookie : true,
		xfbml  : true
	});
</script>-->
<!-- Twitter share -->
<a href="http://twitter.com/share" class="twitter-share-button" data-text="<?=$title?>" data-count="horizontal" data-via="pipeinfo">Tweet</a>
<?
}

function show_block_tags($tags, $anchor = false) {
	require_once dirname(__FILE__) . '/../classes/content/Tag.php';
?>

	<div class="tags">
<?
	foreach ($tags as $tag) {
		if ($anchor) {
			$href = sprintf($anchor, $tag->getId());
?>

		<a href="<?=$href?>">
<?
		}
?>

			<div class="tag"><?=$tag->getValue()?></div>
<?
		if ($anchor) {
?>

		</a>
<?
		}
	}
?>

	</div>
<?
}

?>