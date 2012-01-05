<?php
/**
 * @author Artyom Grigoriev
 */

require_once dirname(__FILE__) . '/../classes/content/Item.php';
require_once dirname(__FILE__) . '/../classes/content/Comment.php';

require_once dirname(__FILE__) . '/life_view.php';

define('COMMENTS_NUMBER', 69);

global $auth, $user;
$uid = $auth->uid();

$types = array(
	Item::BLOG_POST => 'К постам в блогах',
	Item::PHOTO => 'К фотографиям',
	Item::VIDEO => 'К видеозаписям',
	Item::EVENT => 'К событиям'
);

// in assumption that most commented items are new
Item::getAll(COMMENTS_NUMBER, true);

$comments = Comment::getByItemType(array_keys($types), 0, COMMENTS_NUMBER, true);

?>

<div id="stream_wrapper" class="single">
	<div id="stream_container">
		<div id="stream_content">
			<table>
				<tbody>
<? life_show_comments($comments, $user); ?>

				</tbody>
			</table>
		</div>
		<div id="stream_loading"></div>
		<div id="stream_end"></div>
	</div>
</div>

<div id="options_wrapper" class="single">
	<div id="options">
		<div style="margin: -10px 0px 10px 0px; font-size: 0.8em;"><?=lang_sclon(COMMENTS_NUMBER,
				'Учитывается '.COMMENTS_NUMBER.' последний комментарий',
				'Учитываются '.COMMENTS_NUMBER.' последних комментария',
				'Учитывается '.COMMENTS_NUMBER.' последних комментариев')?></div>
		<a class="option" href="#type=all" onclick="javascript: life.showComments();">
			<div id="type_all" class="option selected">
				<div class="main">
					<div class="title">Все комментарии</div>
				</div>
			</div>
		</a>
<?
foreach ($types as $type => $title) {
?>

		<a class="option" href="#type=<?=$type?>" onclick="javascript: life.showComments('<?=$type?>');">
			<div id="type_<?=$type?>" class="option">
				<div class="main">
					<div class="title"><?=$title?></div>
				</div>
			</div>
		</a>
<?
}
?>

	</div>
</div>

<div style="clear: both;"></div>
