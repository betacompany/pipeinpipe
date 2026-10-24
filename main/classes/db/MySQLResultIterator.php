<?php

require_once dirname(__FILE__) . '/DBResultIterator.php';
require_once dirname(__FILE__) . '/../../includes/mysql.php';

/**
 * Description of DBResultIterator
 *
 * @author ortemij
 */
// #[\ReturnTypeWillChange] keeps PHP 8.1+ from asking for return types on the
// Iterator methods; PHP 5 reads the line as a comment.
class MySQLResultIterator implements DBResultIterator {
	private $resource;
	private $results = array();
	private $index = 0;
	private $count = 0;

	public function  __construct($resource) {
		$this->resource = $resource;
		$this->count = $resource ? db_num_rows($this->resource) : 0;
	}

	#[\ReturnTypeWillChange]
	public function current() {
		if (isset($this->results[$this->index])) return $this->results[$this->index];

		$this->results[$this->index] = db_fetch_assoc($this->resource);
		return $this->results[$this->index];
	}

	#[\ReturnTypeWillChange]
	public function key() {
		return $this->index;
	}

	#[\ReturnTypeWillChange]
	public function next() {
		$this->index++;
	}

	#[\ReturnTypeWillChange]
	public function rewind() {
		$this->index = 0;
	}

	#[\ReturnTypeWillChange]
	public function valid() {
		return $this->index < $this->count;
	}

	public function getResults() {
		if (count($this->results) != $this->count) $this->all();
		return $this->results;
	}

	private function all() {
		while ($result = db_fetch_assoc($this->resource)) {
			$this->results[] = $result;
		}
	}
}
?>
