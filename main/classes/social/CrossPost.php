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

	private $socialPost = false;

	public function getSocialPostId() {
		return $this->getContentValue();
	}

	public function getSocialPost() {
		if ($this->socialPost) {
			return $this->socialPost;
		}
		return $this->socialPost = SocialPost::getById($this->getSocialPostId());
	}

	public function getSocialWebType() {
		return $this->getSocialPost()->getSocialWebType();
	}

	public function getSocialWebAuthorName() {
		return $this->getSocialPost()->getSocialWebAuthorName();
	}

	public function getExternalUrl() {
		return $this->getSocialPost()->getUrl();
	}

	public static function valueOf(Item $item) {
		return new CrossPost(-1, $item);
	}

	/**
	 * @static
	 * @param SocialPost $post
	 * @return Item
	 * @throws InvalidArgumentException|InvalidDataException
	 */
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

		$source = $post->getContent();
		$parsed = Parser::parseSocialPost(utf8_encode($source));
		$ts = $post->getTimestamp();

		$item = parent::create(Item::CROSS_POST, 0, $uid, $ts, $source, $parsed, "", $spId);
		if ($item) {
			$post->makeHandled();
		}
		return $item;
	}
}
