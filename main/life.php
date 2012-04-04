<?php
/**
 * @author Artyom Grigoriev
 */

require_once 'classes/user/Auth.php';
require_once 'classes/user/User.php';

require_once 'includes/common.php';

require_once 'includes/log.php';

try {
	include 'includes/authorize.php';
	include 'views/header.php';
?>

<div id="life_container" class="body_container">

<?
	if (!issetParam('part')) {
		include 'views/life_main2.php';
	} else {
		switch (param('part')) {
		case 'comments':
			include 'views/life_comments.php';
			break;
		case 'blog':
			include 'views/life_blog.php';
			break;
		case 'people':
			include 'views/life_people.php';
			break;
		case 'blog_editor':
			include 'views/life_blog_editor.php';
			break;
		}
	}
?>

</div>
<?
	include 'views/footer.php';
} catch (Exception $e) {
	global $LOG;
	$LOG->exception($e);
}

?>