<?php

require_once dirname(__FILE__) . '/../../includes/common.php';

require_once dirname(__FILE__) . '/../content/Group.php';
require_once dirname(__FILE__) . '/../content/Item.php';
require_once dirname(__FILE__) . '/../content/Parser.php';

require_once dirname(__FILE__) . '/../db/TagDBClient.php';

/**
 * 
 * @author Artyom Grigoriev
 */
class BlogPost extends Item {

	const DELIMITER = '===';

	public function getTitle() {
		return $this->getContentTitle();
	}

	public function getShortHTML() {
		$parsed = $this->getContentParsed();
		$array = explode(BlogPost::DELIMITER, $parsed, 2);
		return $array[0];
	}

	public function getFullHTML() {
		$parsed = $this->getContentParsed();
		$array = explode(BlogPost::DELIMITER, $parsed, 2);
		return $array[1];
	}

	public function getShortSource() {
		$parsed = $this->getContentSource();
		$array = explode(BlogPost::DELIMITER, $parsed, 2);
		return $array[0];
	}

	public function getFullSource() {
		$parsed = $this->getContentSource();
		$array = explode(BlogPost::DELIMITER, $parsed, 2);
		return $array[1];
	}

	public function setShort($source, $update = true) {
		$parsed = Parser::parseBlogPost($source);
		$this->contentSource = implode(BlogPost::DELIMITER, array($source, $this->getFullSource()));
		$this->contentParsed = implode(BlogPost::DELIMITER, array($parsed, $this->getFullHTML()));
		if ($update) $this->update();
	}

	public function setFull($source, $update = true) {
		$parsed = Parser::parseBlogPost($source);
		$this->contentSource = implode(BlogPost::DELIMITER, array($this->getShortSource(), $source));
		$this->contentParsed = implode(BlogPost::DELIMITER, array($this->getShortHTML(), $parsed));
		if ($update) $this->update();
	}

	public function setTitle($title, $update = true) {
		$this->contentTitle = Parser::parseStrict($title);
		if ($update) $this->update();
	}

	public function hasFullVersion() {
		return strlength($this->getFullHTML()) > 0;
	}

	public static function getAll($from = 0, $limit = 0, $descendive = false) {
		return parent::getAllByType(parent::BLOG_POST, $from, $limit, $descendive);
	}

	public static function getAllDescendive($from, $limit) {
		return self::getAll($from, $limit, true);
	}

	public static function valueOf(Item $other) {
		return new BlogPost(-1, $other);
	}

	public static function create($blogId, $uid, $title, $contentShortSource, $contentFullSource, $time = 0) {
		try {
			$post = parent::create(parent::BLOG_POST, $blogId, $uid, $time ? $time : time(), "", "", $title);
			if ($post instanceof BlogPost) {
				$contentShortSource = Parser::parseSource($contentShortSource);
				$contentFullSource = Parser::parseSource($contentFullSource);
				$post->setShort($contentShortSource, false);
				$post->setFull($contentFullSource, false);
				$post->update();
				return $post;
			}
		} catch (Exception $e) {
			global $LOG;
			@$LOG->exception($e);
		}

		return null;
	}
}
?>
