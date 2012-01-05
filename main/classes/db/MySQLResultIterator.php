<?php

require_once dirname(__FILE__) . '/DBResultIterator.php';
require_once dirname(__FILE__) . '/../../includes/mysql.php';

/**
 * Description of DBResultIterator
 *
 * @author ortemij
 */
class MySQLResultIterator implements DBResultIterator {
	private $resource;
	private $results = array();
	private $index = 0;
	private $count = 0;

	public function  __construct($resource) {
		$this->resource = $resource;
		$this->count = $resource ? mysql_num_rows($this->resource) : 0;
	}

	public function current() {
		if (isset($this->results[$this->index])) return $this->results[$this->index];

		$this->results[$this->index] = mysql_fetch_assoc($this->resource);
		return $this->results[$this->index];
	}

	public function key() {
		return $this->index;
	}

	public function next() {
		$this->index++;
	}

	public function rewind() {
		$this->index = 0;
	}

	public function valid() {
		return $this->index < $this->count;
	}

	public function getResults() {
		if (count($this->results) != $this->count) $this->all();
		return $this->results;
	}

	private function all() {
		while ($result = mysql_fetch_assoc($this->resource)) {
			$this->results[] = $result;
		}
	}
}
?>
