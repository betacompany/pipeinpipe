<?php
/**
 * @author Artyom Grigoriev
 */

require_once 'classes/user/Auth.php';
require_once 'classes/user/User.php';

require_once 'includes/common.php';

require_once 'includes/log.php';
require_once 'includes/error.php';

// Buffered, so that a missing album, post or user can still get a clean 404
ob_start();
try {
	include 'includes/authorize.php';
	include 'views/header.php';

	if (!issetParam('part')) {
		include 'views/life_main2.php';
	} else {
?>

<div id="life_container" class="body_container">

<?
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
} catch (InvalidArgumentException $e) {
	// A deleted or never existing album, post, user etc.
	show_not_found();
} catch (Exception $e) {
	global $LOG;
	$LOG->exception($e);
}

?>