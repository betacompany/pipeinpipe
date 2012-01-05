<?php

require_once dirname(__FILE__) . '/LineSet.php';

/**
 * Description of LineSet
 *
 * @author ortemij
 */
class LineSet {
    private $lines = array();

	public function add($line) {
		$this->lines[] = $line;
	}

	public function getLines() {
		return $this->lines;
	}

	public function isEmpty() {
		return empty ($this->lines);
	}

	public function merge(LineSet $other) {
		foreach ($other->lines as $line) {
			$this->add($line);
		}
	}

	public function section($x) {
		$result = array();
		foreach ($this->lines as $line) {
			$points = $line->getPoints();
			if (isset($points[$x])) {
				$result[] = $points[$x];
			}
		}

		return $result;
	}

	public function split($y) {
		$topSet = new LineSet();
		$bottomSet = new LineSet();
		foreach ($this->lines as $line) {
			$result = $line->split($y);
			$topSet->merge($result['top']);
			$bottomSet->merge($result['bottom']);
		}

		return array (
			'top' => $topSet,
			'bottom' => $bottomSet
		);
	}

	public function splitHorizontal($x) {
		$leftSet = new LineSet();
		$rightSet = new LineSet();

		foreach ($this->getLines() as $line) {
			$result = $line->splitHorizontal($x);
			$leftSet->add($result['left']);
			$rightSet->add($result['right']);
		}

		return array (
			'left' => $leftSet,
			'right' => $rightSet
		);
	}

	/**
	 * y := $k * y + $b
	 * @param $k
	 * @param $b
	 */
	public function linearY($k, $b) {
		foreach ($this->lines as $line) {
			$line->linearY($k, $b);
		}
	}

	public function minimize() {
// TODO разобраться, в чём говно
//		foreach ($this->getLines() as $line) {
//			$line->minimize();
//		}
	}
}
?>
