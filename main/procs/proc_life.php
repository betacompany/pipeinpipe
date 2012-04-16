<?php

require_once dirname(__FILE__) . '/../classes/user/Auth.php';
require_once dirname(__FILE__) . '/../classes/user/User.php';

require_once dirname(__FILE__) . '/../classes/blog/Blog.php';
require_once dirname(__FILE__) . '/../classes/blog/BlogPost.php';

require_once dirname(__FILE__) . '/../classes/content/Action.php';
require_once dirname(__FILE__) . '/../classes/content/Item.php';
require_once dirname(__FILE__) . '/../classes/content/Comment.php';
require_once dirname(__FILE__) . '/../classes/content/Group.php';
require_once dirname(__FILE__) . '/../classes/content/Feed.php';

require_once dirname(__FILE__) . '/../classes/utils/ResponseCache.php';
require_once dirname(__FILE__) . '/../classes/utils/Logger.php';

require_once dirname(__FILE__) . '/../includes/assertion.php';
require_once dirname(__FILE__) . '/../includes/error.php';
require_once dirname(__FILE__) . '/../includes/common.php';

require_once dirname(__FILE__) . '/../views/life_view.php';

$LOG = new Logger();

try {

	assertParam('method');

	$auth = new Auth();
	$user = $auth->getCurrentUser();

	switch ($_REQUEST['method']) {
	case 'load_posts':

		assertIsset('blog_id');
		assertIsset('tag_id');
		assertIsset('from');
		assertIsset('limit');
		assertIsset('description');

		$blogId =	param('blog_id');
		$tagId =	param('tag_id');
		$from =		param('from');
		$limit =	min(param('limit'), 30);
		$blog = null;
		$posts = array();
		$loadBlog = (param('description') == '1');

		if ($blogId > 0) {
			$blog = Group::getById($blogId);
			if ($blog instanceof Blog) {
				$posts = $blog->getItems($from, $limit, true, Item::CREATION);
			} else {
				$LOG->warn('Trying to load posts from non-blog group');
			}
		} else {
			if ($tagId > 0) {
				$posts = Item::getAllByTypeAndTag(Item::BLOG_POST, $tagId, $from, $limit, true, true);
			} else {
				$posts = Item::getAllByType(Item::BLOG_POST, $from, $limit, true, true);
			}
		}

		if ($loadBlog)
			life_show_blog_info($blog);

		life_show_posts($posts, $user, !$loadBlog);

		break;

	case 'load_post':

		assertIsset('post_id');

		$post = Item::getById(param('post_id'));
		if ($post instanceof BlogPost && $post->isAvailableFor($user)) {
			if ($user != null)
				$post->viewedBy($user);
			
			echo $post->getFullHTML();
		}

		break;

	case 'load_date':

		assertIsset('date');
		$date = param('date');
		assertDate($date);

		life_show_day_feed($date);

		break;

	case 'load_before':

		assertIsset('item_id');
		assertIsset('timestamp');

		$items = Feed::getBefore(intparam('item_id'), intparam('timestamp'));
		include dirname(__FILE__) . '/../views/life_timeline.php';

		break;

	case 'load_after':

		assertIsset('item_id');
		assertIsset('timestamp');

		$items = Feed::getAfter(intparam('item_id'), intparam('timestamp'));
		include dirname(__FILE__) . '/../views/life_timeline.php';

		break;

	case 'load_near':

		assertIsset('timestamp');

		$items = Feed::getNear(intparam('timestamp'));
		include dirname(__FILE__) . '/../views/life_timeline.php';

		break;

	case 'create_blog':

		if (!$auth->isAuth()) {
			echo json(array(
				'status' => 'failed',
				'reason' => 'access denied'
			));
			exit(0);
		}

		assertParam('holder_type');
		assertParam('holder_id');
		assertParam('blog_title');

		switch (param('holder_type')) {
		case 'user':
            $holder = User::getById(intparam('holder_id'));
            $title = Parser::parseStrict(textparam('blog_title'));
            $contentSource = (param('blog_description') == '') ? '' : Parser::parseSource( textparam('blog_description') );
            $contentParsed = Parser::parseDescription($contentSource);

            if ($holder->getId() == $user->getId()) {
                $blog = Group::create(Group::BLOG, 0, $title, $contentSource, $contentParsed);
                Connection::bind($blog, $user);
                echo json(array (
                    'status' => 'ok'
                ));
            } else {
                echo json(array (
                    'status' => 'failed',
                    'reason' => 'access denied'
                ));
            }

			break;

		case 'league':

			break;
		
		case 'competition':
			
			break;

		}

		break;

	case 'add_post':

		if (!$auth->isAuth()) {
			echo json(array(
				'status' => 'failed',
				'reason' => 'access denied'
			));
			exit(0);
		}

		assertParam('post_blog_id');
		assertParam('post_title');
		assertParam('post_short_source');
		assertParam('post_full_source');

		$blog = Group::getById(intparam('post_blog_id'));
		if (!$user->hasPermission($blog, 'add_post')) {
			echo json(array(
				'status' => 'failed',
				'reason' => 'you have not permission to post into this blog'
			));
			exit(0);
		}

		$contentShortSource = param('post_short_source');
		$contentFullSource = param('post_full_source');
		$title = Parser::parseStrict( param('post_title') );
		$tagIds = array_slice( explode(',', param('post_tags')), 0, 100 ); // protection for too may tags
		
		if ($blog instanceof Blog) {			
			$post = BlogPost::create(
						$blog->getId(),
						$user->getId(),
						$title,
						$contentShortSource,
						$contentFullSource
					);
			$post->addTags($tagIds, $user);
			Header('Location: /life/blog/'.$post->getId());
			exit(0);
		} else {
			echo json(array (
				'status' => 'failed',
				'reason' => 'this group is not a blog'
			));
		}

		break;

	case 'edit_post':
		if (!$auth->isAuth()) {
			echo json(array(
				'status' => 'failed',
				'reason' => 'access denied'
			));
			exit(0);
		}

		assertIsset('post_id');
		assertParam('post_blog_id');
		assertParam('post_title');
		assertParam('post_short_source');
		assertParam('post_full_source');

		$blog = Group::getById(intparam('post_blog_id'));
		if (!$user->hasPermission($blog, 'edit')) {
			echo json(array(
				'status' => 'failed',
				'reason' => 'you have not permission to edit posts in this blog'
			));
			exit(0);
		}

		$post = Item::getById(param('post_id'));
		$tagIds = array_slice( explode(',', param('post_tags')), 0, 100 ); // protection for too may tags
		
		if ($post instanceof BlogPost) {
			$post->setFull(param('post_full_source'), false);
			$post->setShort(param('post_short_source'), false);
			$post->setTitle(param('post_title'), false);
			$post->setGroupId(param('post_blog_id'), false);
			$post->update();
			
			$post->removeTags();
			$post->addTags($tagIds, $user);
			
			Header('Location: /life/blog/'.$post->getId());
			exit(0);
		} else {
			echo json(array (
				'status' => 'failed',
				'reason' => 'this item is not a blog post'
			));
		}

		break;

	case 'remove_post':

		assertIsset('post_id');

        $post = Item::getById(param('post_id'));
        if ($post instanceof BlogPost) {
            $blog = $post->getGroup();
            if ($user->hasPermission($blog, 'edit')) {
                $post->remove();
                echo json(array (
                    'status' => 'ok'
                ));
            } else {
                echo json(array (
                    'status' => 'failed',
                    'reason' => 'You have no permissions to this post'
                ));
            }
        } else {
            global $LOG;
            @$LOG->warn('Item id='.param('post_id').' is not a blog post');
        }

		break;

	case 'get_dates':
		$dates = Item::getDates();
		echo json(array(
			'status' => 'ok',
			'dates' => $dates
		));
		break;
	}

} catch (Exception $e) {
	global $LOG;
	@$LOG->exception($e);
	echo_json_exception($e);
}

?>
