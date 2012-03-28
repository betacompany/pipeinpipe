<?php

require_once dirname(__FILE__) . '/../content/Item.php';
require_once dirname(__FILE__) . '/../content/Parser.php';

require_once dirname(__FILE__) . '/SocialPost.php';

/**
 * User: ortemij
 * Date: 27.03.12
 * Time: 18:20
 */
class CrossPost extends Item {

	public function getSocialPostId() {
		return $this->getContentValue();
	}

	public function getSocialPost() {
		return SocialPost::getById($this->getSocialPostId());
	}

	public static function valueOf(Item $item) {
		return new CrossPost(-1, $item);
	}

	public static function create(SocialPost $post) {
		$spId = $post->getId();
		$swAuthorId = $post->getSocialWebAuthorId();
		$swType = $post->getSocialWebType();

		$key = User::keyBySocialWeb($swType);
		if ($key == null) {
			throw new InvalidDataException("Invalid social web for social post with id=" . $spId);
		}
		$users = User::getByKey($key, $swAuthorId);

		$uid = 0;
		$count = count($users);
		if ($count > 1) {
			throw new InvalidArgumentException("Many users with one social profile: [key=" . $key . ", " . $swAuthorId . "]");
		} elseif ($count == 1) {
			$uid = $users[0]->getId();
		}

		$source = $post->getSource();
		$parsed = Parser::parseSocialPost($source);
		$title = Parser::parseStrict($post->getTitle());
		$ts = $post->getTimestamp();

		$item = parent::create(Item::CROSS_POST, 0, $uid, $ts, $source, $parsed, $title, $spId);
		if ($item) {
			$post->makeHandled();
		}
		return $item;
	}
}
