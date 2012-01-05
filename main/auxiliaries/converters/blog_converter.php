<?php
/**
 * @author Artyom Grigoriev
 */

require_once '../../includes/mysql.php';
require_once 'converter_library.php';

require_once '../../classes/blog/Blog.php';
require_once '../../classes/blog/BlogPost.php';
require_once '../../classes/content/Tag.php';
require_once '../../classes/content/Connection.php';
require_once '../../classes/user/User.php';

echo '<pre>';

mysql_qw('DELETE FROM `p_content_group` WHERE `type`=\'blog\'');
echo "Blogs deleted from DB\n";
flush();

mysql_qw('DELETE FROM `p_content_comment` WHERE `item_id` IN (SELECT `id` FROM `p_content_item` WHERE `type`=\'blog_post\')');
echo "Comments for posts deleted from DB\n";
flush();

mysql_qw('DELETE FROM `p_content_item` WHERE `type`=\'blog_post\'');
echo "Posts deleted from DB\n";
flush();

$aii = mysql_result(mysql_qw('SELECT MAX(`id`) + 1 FROM `p_content_item`'), 0, 0);
mysql_qw('ALTER TABLE  `p_content_item` AUTO_INCREMENT=' . $aii);
echo "Auto increment for `p_content_item` set to value of $aii\n";
flush();

$aig = mysql_result(mysql_qw('SELECT MAX(`id`) + 1 FROM `p_content_group`'), 0, 0) + 10;
mysql_qw('ALTER TABLE  `p_content_group` AUTO_INCREMENT=' . $aig);
echo "Auto increment for `p_content_group` set to value of $aig (+10)\n\n";
flush();

mysql_qw('TRUNCATE TABLE `p_content_tag`');
mysql_qw('TRUNCATE TABLE `p_content_tag_target`');
echo "Tags tables truncated\n\n";
flush();

mysql_qw('TRUNCATE `p_content_connection`');
echo "Connections truncated\n\n";
flush();

// key = name of the tag, value = Tag
$tags = array();

// key = UID, value = Blog
$blogs = array();

$req = mysql_qw('SELECT * FROM `pipe_blog` ORDER BY `id` ASC');
while ($post = mysql_fetch_assoc($req)) {
	$uid = $post['authorid'];
	$user = User::getById($uid);
	$tgs = explode(', ', $post['tags']);
	
	if (!isset($blogs[$uid])) {
		$blogs[$uid] = Group::create(Group::BLOG, 0, $user->getFullName(), '', '');
		Connection::bind($blogs[$uid], $user);
		echo "Blog created with id=".$blogs[$uid]->getId()."\n";
		flush();
	}

	$p = Item::create(Item::BLOG_POST, $blogs[$uid]->getId(), $uid, strtotime($post['date']),
			$post['short'] . BlogPost::DELIMITER . $post['text'],
			Parser::parseBlogPost($post['short']) . BlogPost::DELIMITER . Parser::parseBlogPost($post['text']),
			$post['title']);


	echo "Post with id=".$post['id']." inserted with id=".$p->getId()."\n";
	flush();

	foreach ($tgs as $tagname) {
		if (!isset($tags[$tagname])) {
			$tags[$tagname] = Tag::create($uid, $tagname);
			echo "\tTag $tagname created with id=".$tags[$tagname]->getId()."\n";
			flush();
		}
		$p->addTag($tags[$tagname], $uid);
		echo "\tTag $tagname added\n";
	}

	$reqc = mysql_qw('SELECT * FROM `pipe_comments` WHERE `type`=5 AND `cid`=?', $post['id']);
	while ($com = mysql_fetch_assoc($reqc)) {
		$p->addComment(Comment::BASIC_COMMENT, $com['authorid'], 0, $com['text'], '');
		echo "\tComment ".$com['id']." added\n";
	}
}

echo 'Converting finished!</pre>';
flush();

?>
