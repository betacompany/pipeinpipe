<?php

require_once dirname(__FILE__) . '/../utils/Trie.php';

require_once dirname(__FILE__) . '/../blog/BlogPost.php';
require_once dirname(__FILE__) . '/../media/Photo.php';
require_once dirname(__FILE__) . '/../media/Video.php';

require_once dirname(__FILE__) . '/../../includes/security.php';

require_once dirname(__FILE__) . '/../utils/Logger.php';
/**
 * @author Malkovsky Nikolay
 * @author Artyom Grigoriev
 */
class Parser {

	/**
	 * Parses source and erases all tags
	 * @var Parser
	 */
	private static $strictParser = false;

	public static function parseStrict($source) {

		if (!self::$strictParser) {
			$replacer = new TextReplacer();
			self::$strictParser = new Parser($replacer);
		}
		$parsed_html = self::$strictParser->parseHTML($source);
		return self::$strictParser->replaceNewLine($parsed_html);
	}

	/**
	 * Parses sources.
	 * @var Parser
	 */
	private static $sourceParser = false;

	public static function parseSource($source) {

		if (!self::$sourceParser) {
			$replacer = new TextReplacer();
			self::$sourceParser = new Parser($replacer);
		}
		return self::$sourceParser->parseHTML($source);
	}

	private static $descriptionParser = false;

	public static function parseDescription($source) {
		
		if (!self::$descriptionParser) {
			$replacer = new PipeMLReplacer();
			self::$descriptionParser = new Parser($replacer);
		}
		$parsed_html = self::$descriptionParser->parseHTML($source);
		$parsed_PipeML = self::$descriptionParser->parsePipeML($parsed_html);
		return self::$descriptionParser->replaceNewLine($parsed_PipeML);
	}

	public static function parseComment($source) {
		return self::parseDescription($source);
	}

	public static function parseForumMessage($source) {
		return self::parseDescription($source);
	}

	public static function parseBlogPost($source) {
		return self::parseDescription($source);
	}

	public static function parseEvent($source) {
		return self::parseDescription($source);
	}

	public static function parseSocialPost($source, $socialWebType) {
		$text = self::parseStrict($source);
		switch ($socialWebType) {
		case ISocialWeb::VKONTAKTE:
			return mb_ereg_replace('\[((id|club|event)[0-9]+)\|([^\]]+)\]', '<a href="http://vk.com/\\1">\\3</a>', $text);
		case ISocialWeb::TWITTER:
			$text = mb_ereg_replace("@([a-zA-Z0-9_]+)", "<a href=\"http://twitter.com/\\1\">@\\1</a>", $text);
			return mb_ereg_replace("#([а-яА-Яa-zA-Z0-9_]+)", "<a href=\"http://twitter.com/#!/search/%23\\1\">#\\1</a>", $text);
		default:
			return $text;
		}
	}

	private $replacer;
	private $pipeMLTrie;
	private $HTMLTrie;

	private function __construct(IReplacer $replacer) {
		$this->replacer = $replacer;

		$this->pipeMLTrie = new Trie('#');
		$tags = $this->replacer->getPipeMLTags();
		foreach ($tags as $tag) {
			$this->pipeMLTrie->addstring("$tag#");
			$this->pipeMLTrie->addstring("/$tag#");
		}

		$this->HTMLTrie = new Trie('#');
		$tags = $this->replacer->getHTMLTags();
		foreach ($tags as $tag) {
			$this->HTMLTrie->addstring("$tag#");
			$this->HTMLTrie->addstring("/$tag#");
		}
	}

	/**
	 * This function should  parse attributes string and return
	 * coresponding array.
	 * @param <type> $attr_str
	 * @return <type>
	 */
	private function parseAttributes($attr_str) {
		$result = array();
		$key = '';
		$value = '';
		$quote = false;
		for ($i = 0; $i < strlen($attr_str); ++$i) {
			while ($attr_str[$i] != '=' && $i < strlen($attr_str)) {
				$key .= $attr_str[$i];
				$i++;
			}

			if ($attr_str[$i] == '=') $i++;

			if ($attr_str[$i] == '"') {
				$i++;
				$quote = true;
			}

			while (
				(
					($quote && $attr_str[$i] != '"') ||
					(!$quote && $attr_str[$i] != ' ')
				)
				&& $i < strlen($attr_str)
			) {
				$value .= $attr_str[$i];
				$i++;
			}

			if ($attr_str[$i] == '"') $i++;
			if ($attr_str[$i] == ' ') $i++;

			$result[$key] = $value;
			$key = '';
			$value = '';
			$quote = false;
		}
		return $result;
	}

	private function parsePipeML($source) {

		$root = $this->pipeMLTrie;
		$st = new _Stack("Nya");
		
		$node = null;
		$first = 0;
		$openbr = 0;
		$tagstart = 0;
		$tagend = 0;

		$st->push(new _Context(""));
		$temp = $st->top();

		for($i = 0; $i < strlen($source); $i++) {
			switch($source[$i]) {
				case '[':
					$node = $root;
					$openbr = $i;
					break;
				case ' ':
				case "\n":
				case "\t":
				case "\r":
					if($node != null && $node->move('#') != null && $tagend == $tagstart - 1) {
						$tagend = $i - 1;
					}
					break;
				case ']':
					if($tagend < $tagstart) {
						$tagend = $i - 1;
					}
					if($node != null && $node->move('#') != null) {
						$node = null;
						$temp = $st->top();

						if($first < $openbr) {
							$temp->str .= substr($source, $first, $openbr - $first);
						}

						$first = $i + 1;
						if($source[$tagstart] == '/') {
							if($temp->tagName != substr($source, $tagstart + 1, $tagend - $tagstart)) {
								//throw new Exception("Error occured at $i: tag unmatched");
								break;
							}
							$str = $this->replacer->replace($temp->str,
									$temp->tagName, $temp->attr);
							$st->pop();
							$temp = $st->top();
							$temp->str .= $str;

						} else {
							$attributes = $this->parseAttributes(substr($source, $tagend + 2, $i - $tagend - 2));
							//TODO parse the third param with parseAttributes()
							$st->push(new _Context("", 
									substr($source, $tagstart, $tagend - $tagstart + 1),
									$attributes));
						}
						$tagend = $tagstart - 1;
					} else {
					}
					break;
				default:
					if($node != null) {
						if($node == $root) {
							$tagstart = $i;
							$tagend = $i - 1;
							$node = $node->move($source[$i]);
						} else if ($tagend + 1 == $tagstart) {
							$node = $node->move($source[$i]);
						}
					}
			}
		}

		if($st->_empty()) {
			throw new Exception("Please remove tag </idle> :P");
		}

		$res = "";
		while(!$st->_empty()) {
			$temp = $st->top();
			$res = $temp->str . $res;
			$st->pop();
		}

		return $res . substr($source, $first);
	}

	/**
	 * This function parses html-tags
	 * @param <type> $source string to parse
	 * @return <type> returns string with the corresponding tags applied
	 */
	private function parseHTML($source) {

		$root = $this->HTMLTrie;
		$st = new _Stack("Nya");

		$node = null;
		$first = 0;
		$openbr = -1;
		$tagstart = -1;
		$tagend = -1;

		$st->push(new _Context(""));
		$temp = $st->top();
		$logger = new Logger('../../logs/test.log');
		for($i = 0; $i < strlen($source); $i++) {
			switch($source[$i]) {
				case '<':
					if($openbr >= $first){
						$temp = $st->top();
						if($openbr > $first) {
							$temp->str .= substr($source, $first, $openbr - $first);
						}
						$temp->str .= $this->replacer->escapeSymbol('<');
						$first = $openbr + 1;
					}
					$node = $root;
					$openbr = $i;
					break;
				case ' ':
				case "\n":
				case "\t":
				case "\r":
					if($node != null && $node->move('#') != null && $tagend == $tagstart - 1) {
						$tagend = $i - 1;
					}
					break;
				case '>':
					if($tagend < $tagstart) {
						$tagend = $i - 1;
					}
					if($node != null && $node->move('#') != null) {
						$node = null;
						$temp = $st->top();

						if($first < $openbr) {
							$temp->str .= substr($source, $first, $openbr - $first);
						}

						$first = $i + 1;
						if($source[$tagstart] == '/') {
							if($temp->tagName != substr($source, $tagstart + 1, $tagend - $tagstart)) {
								//throw new Exception("Error occured at $i: tag unmatched");
								//TODO just replace brackets at positions $openbr, $i with empty symbols except this throwing.
							}
							//$logger->info("Apply ".$temp->tagName." to ".$temp->str." with attr " . $temp->attr);
							$str = $this->replacer->replace($temp->str,
									$temp->tagName, $temp->attr);
							$st->pop();
							$temp = $st->top();
							$temp->str .= $str;

						} else {
							$attributes = $this->parseAttributes(substr($source, $tagend + 2, $i - $tagend - 2));
							$st->push(new _Context("",
									substr($source, $tagstart, $tagend - $tagstart + 1),
									$attributes));
							//print_r($attributes);
						}
						$tagend = $tagstart - 1;
					} else {
						$temp = $st->top();
						if($openbr > $first) {
							$temp->str .= substr($source, $first, $openbr - $first);
						}
						if($openbr >= $first) {
							$temp->str .= $this->replacer->escapeSymbol('<');
						}
						$first = $openbr + 1;

						if($i > $first) {
							$temp->str .= substr($source, $first, $i - $first);
						}
						if($i >= $first) {
							$temp->str .= $this->replacer->escapeSymbol('>');
						}
						$first = $i + 1;
					}
					break;
				default:
					if($node != null) {
						if($node == $root) {
							$tagstart = $i;
							$tagend = $i - 1;
							$node = $node->move($source[$i]);
						} else if ($tagend + 1 == $tagstart) {
							$node = $node->move($source[$i]);
						}
					}
			}
		}

		if($st->_empty()) {
			throw new Exception("Please remove tag </idle> :P");
		}

		if($openbr >= $first) {
			$temp = $st->top();
			if($openbr > $first) {
				$temp->str .= substr($source, $first, $openbr - $first);
			}

			$temp->str .= $this->replacer->escapeSymbol('<');
			$first = $openbr + 1;
		}
		$res = "";
		while(!$st->_empty()) {
			$temp = $st->top();
			//$logger->info("Unclosed tag $temp->tagName contains $temp->str");
			$res = $temp->str . $res;
			$st->pop();
		}
		//$logger->info($res.substr($source, $first));
		return $res.substr($source, $first);
	}

	private function replaceNewLine($source) {
		$res = "";
		$first = 0;
		for($i = 0; $i < strlen($source); ++$i) {
			switch($source[$i]) {
				case "\n":
				case "\r":
					$res .= substr($source, $first, $i - $first);
					$res .= $this->replacer->escapeSymbol($source[$i]);
					$first = $i + 1;
					break;
			}
		}
		return $res.substr($source, $first);
	}
}

interface IReplacer {

	/**
	 * 
	 */
	public function getPipeMLTags();

	/**
	 *
	 */
	public function getHTMLTags();

	/**
	 *
	 * @param string $innerText
	 * @param string $tag
	 * @param array $params
	 * @return string
	 */
	public function replace($innerText, $tag, $params);

	/**
	 * @param string $symbol
	 */
	public function escapeSymbol($symbol);
}

class TextReplacer implements IReplacer {

	public function getPipeMLTags() {
		return array();
	}

	public function getHTMLTags() {
		return array();
	}

	public function replace($innerText, $tag, $params) {
		return $innerText;
	}

	public function escapeSymbol($symbol) {
		return $symbol;
	}
}

class HTMLReplacer implements IReplacer {

	protected static $ALLOWED_TAGS = array(
		'a' => array('href'),
		'img' => array('src', 'alt'),
		'b' => array(),
		'i' => array(),
		'small' => array(),
		'table' => array(),
		'thead' => array(),
		'tbody' => array(),
		'tr' => array(),
		'td' => array('colspan')
	);

	protected $allowedTags = array();

	public function __construct($allowedTags = false) {
		if (!$allowedTags) {
			$allowedTags = self::$ALLOWED_TAGS;
		}

		$this->allowedTags = $allowedTags;
	}

	public function getHTMLTags() {
		return array_keys(self::$ALLOWED_TAGS);
	}

	public function getPipeMLTags() {
		return array();
	}

	public function replace($innerText, $tag, $params) {

		$result = "";
		$tag_enabled = array_key_exists($tag, $this->allowedTags);
		if ($tag_enabled) {
			$result .= "<$tag";
			foreach ($params as $key => $value) {
				if (array_contains($this->allowedTags[$tag], $key)) {
					$result .= " $key=\"$value\"";
				}
			}
			$result .= ">";
		}

		$result .= $innerText;

		if ($tag_enabled) {
			$result .= "</$tag>";
		}

		return $result;
	}

	public function escapeSymbol($symbol) {
		if ($symbol == "\n") return "<br />\n";
		if ($symbol == "\r") return "";
		return htmlspecialchars($symbol);
	}
}

class PipeMLReplacer extends HTMLReplacer {

	private static $ALLOWED_PIPEML_TAGS = array(
		'quote' => array('name'),
		'video' => array('id'),
		'photo' => array('id'),
		'post' => array('id'),
	);

	public function getPipeMLTags() {
		return array_keys(self::$ALLOWED_PIPEML_TAGS);
	}

	public function replace($innerText, $tag, $params) {
		if (array_key_exists($tag, self::$ALLOWED_TAGS)) {
			return parent::replace($innerText, $tag, $params);
		}

		if (array_key_exists($tag, self::$ALLOWED_PIPEML_TAGS)) {
			switch ($tag) {
			case 'quote':
				return '<div class="quote"><div class="author">' . 
					$params['name'] . '</div><div class="text">' .
					$innerText .  '</div></div>';
			case 'video':
				try {
					$video = Item::getById($params['id']);
					if ($video instanceof Video) {
						return '<a class="video" href="/media/video/album'.
							$video->getGroupId().'/'.$video->getId().'">'.
							$video->getTitle().'</a>';
					}
				} catch (Exception $e) {
					global $LOG;
					@$LOG->exception($e);
				}
				return "";
			case 'photo':
				try {
					$photo = Item::getById($params['id']);
					if ($photo instanceof Photo) {
						return '<a class="photo" href="/media/photo/album'.$photo->getGroupId().'/'.
							$photo->getId().'"><img src="'.$photo->getUrl(Photo::SIZE_MINI).'" /></a>';
					}
				} catch (Exception $e) {
					global $LOG;
					@$LOG->exception($e);
				}
				return "";
			case 'post':
				try {
					$post = Item::getById($params['id']);
					if ($post instanceof BlogPost) {
						return '<a class="video" href="/live/blog/'.
							$post->getId().'">'.$post->getTitle().'</a>';
					}
				} catch (Exception $e) {
					global $LOG;
					@$LOG->exception($e);
				}
				return "";
			}
		}

		return "Incorrect PipeML usage";
	}
}

class _Context {
	public $str;
	public $tagName;
	public $attr;

	public function  __construct($str, $tag = "idle", $attr = array()) {
		$this->str = $str;
		$this->tagName = $tag;
		$this->attr = $attr;
	}
}

class _Stack {
	public $data;
	private $next;

	public function top() {
		return $this->next->data;
	}

	public function pop() {
		if($this->next != null) {
			$this->next = $this->next->next;
		}
	}

	public function push($data) {
		$t = new _Stack($data);
		$t->next = $this->next;
		$this->next = $t;
	}

	public function _empty() {
		return $this->next == null;
	}

	public function __construct($data) {
		$this->data = $data;
		$this->next = null;
	}
}
?>
