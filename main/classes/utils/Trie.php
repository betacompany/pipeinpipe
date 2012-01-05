<?php
/**
 * @author Malkovsky Nikolay
 * Implements Trie data structure.
 */

/**
 * Trie is a good data structure for storing strings. Also known as prefix tree.
 */
class Trie {
	public $char;
	private $children;
	
	public function draw() {
		echo $this->char;
		?> <li> <?

		for($var = $this->children->first->next; $var !== $this->children->first; $var = $var->next) {
		}

		for($var = $this->children->first->next; $var !== $this->children->first; $var = $var->next) {
			?> <ul> <?
			$var->data->draw();
			?> </ul> <?
		}
		?> </li> <?
	}

	public function  __construct($c) {
		$this->char = $c;
		$this->children = new _List();
	}

	public function contains($c) {
		return $this->children->contains($c);
	}

	public function move($c) {
		for($var = $this->children->first->next; $var !== $this->children->first; $var = $var->next) {
			if($var->data->char == $c) {
				return $var->data;
			}
		}
		return null;
	}

	public function forcemove($c) {
		for($var = $this->children->first->next; $var !== $this->children->first; $var = $var->next) {
			if($var->data->char == $c) {
				return $var->data;
			}
		}
		$t = new Trie($c);
		$this->children->add($t);
		return $t;
	}

	public function addstring($str) {
		$temp = $this;
		$len = strlen($str);
		for($var = 0; $var < $len; ++$var) {
			$temp = $temp->forcemove($str[$var]);
		}
	}
}


class _List {
	public $first;

	public function __construct() {
		$this->first = new _Node(null);
		$this->first->next = $this->first;
		$this->first->prev = $this->first;
	}

	public function add($data) {
		$t = new _Node($data);
		$t->next = $this->first->next;
		$t->prev = $this->first;
		$this->first->next = $t;
		$t->next->prev = $t;
	}

	public function contains($data) {
		for($var = $this->first->next; $var !== $this->first; $var = $var->next) {
			if($var->data == $data) {
				return true;
			}
		}
		return false;
	}

	public function remove($data) {
		for($var = $this->first->next; $var !== $this->first; $var = $var->next) {
			if($var->data == $data) {
				$var->next->prev = $var->prev;
				$var->prev->next = $var->next;
			}
		}
	}
}

class _Node {
	public $next;
	public $prev;
	public $data;

	public function __construct($d) {
		$this->data = $d;
	}
}
?>
