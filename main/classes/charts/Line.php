<?php

require_once dirname(__FILE__) . '/LineSet.php';

/**
 * Description of Line
 *
 * @author ortemij
 */
class Line {

	const EPS = 0.0001;

    private $color = '000000';
	private $width = 1;
	private $points = array();

	public function getColor() { return $this->color; }
	public function setColor($color) {
		$this->color = $color;
	}

	public function getWidth() { return $this->width; }
	public function setWidth($width) {
		$this->width = $width;
	}

	public function copyProperties(Line $other) {
		$this->width = $other->width;
		$this->color = $other->color;
	}

	public function addPoint($x, $y) {
		$this->points[$x] = $y;
		ksort($this->points);
	}

	public function isEmpty() {
		return empty ($this->points);
	}

	public function isConstant() {
		$values = array_values($this->points);
		$value = $values[0];
		foreach ($values as $y) {
			if ($y != $value) return false;
		}

		return true;
	}

	public function getPoints() {
		return $this->points;
	}

	public function getMinValue() {
		return min(array_values($this->points));
	}

	public function getMaxValue() {
		return max(array_values($this->points));
	}

	public function subLine($fromX, $toX) {
		$result = new Line();
		$result->setColor($this->color);
		foreach ($this->points as $x => $y) {
			if ($x >= $fromX && $x <= $toX) {
				$result->addPoint($x, $y);
			}
		}
		
		return $result;
	}

	/**
	 * y := $k * y + $b
	 * @param $k
	 * @param $b
	 */
	public function linearY($k, $b) {
		foreach ($this->points as $x => $y) {
			$this->points[$x] = $k * $y + $b;
		}
	}

	public function split($splitY) {
		$topSet = new LineSet();
		$bottomSet = new LineSet();
		$topLine = new Line();
		$bottomLine = new Line();
		$topLine->copyProperties($this);
		$bottomLine->copyProperties($this);


		$prevLine = null;
		$prevX = null;
		$prevY = null;
		foreach ($this->points as $x => $y) {
			if ($prevLine == null) {
				if ($y <= $splitY) {
					$bottomLine->addPoint($x, $y);
					$prevLine = $bottomLine;
				} else {
					$topLine->addPoint($x, $y);
					$prevLine = $topLine;
				}

				$prevX = $x;
				$prevY = $y;

				continue;
			}

			if ($y <= $splitY) {
				if ($prevY <= $splitY) {
					$bottomLine->addPoint($x, $y);
				} else {
					//$middleX = round($prevX + ($prevY - $splitY) * ($x - $prevX) / ($prevY - $y));
					$middleX = ($prevX + ($prevY - $splitY) * ($x - $prevX) / ($prevY - $y)) . '';
					$topLine->addPoint($middleX, $splitY);
					$topSet->add($topLine);
					$topLine = new Line();
					$topLine->copyProperties($this);

					$bottomLine->addPoint($middleX, $splitY);
					$bottomLine->addPoint($x, $y);
				}
			} else {
				if ($prevY > $splitY) {
					$topLine->addPoint($x, $y);
				} else {
					//$middleX = round($prevX + ($prevY - $splitY) * ($x - $prevX) / ($prevY - $y));
					$middleX = ($prevX + ($prevY - $splitY) * ($x - $prevX) / ($prevY - $y)) . '';
					$bottomLine->addPoint($middleX, $splitY);
					$bottomSet->add($bottomLine);
					$bottomLine = new Line();
					$bottomLine->copyProperties($this);

					$topLine->addPoint($middleX, $splitY);
					$topLine->addPoint($x, $y);
				}
			}

			$prevX = $x;
			$prevY = $y;

		}

		if (!$topLine->isEmpty()) $topSet->add($topLine);
		if (!$bottomLine->isEmpty()) $bottomSet->add($bottomLine);

		return array (
			'top' => $topSet,
			'bottom' => $bottomSet
		);
	}

	public function splitHorizontal($splitX) {
		$lineLeft = new Line();
		$lineRight = new Line();
		$lineLeft->copyProperties($this);
		$lineRight->copyProperties($this);

		$prevX = null;
		$prevY = null;
		foreach ($this->points as $x => $y) {
			if ($x < $splitX) {
				$lineLeft->addPoint($x, $y);
			} elseif ($x > $splitX) {
				if ($prevX != null && $prevX <= $splitX) {
					$lineLeft->addPoint($prevX, $prevY);
					$lineRight->addPoint($prevX, $prevY);
				}

				$lineRight->addPoint($x, $y);
			} else {
				$lineLeft->addPoint($x, $y);
				$lineRight->addPoint($x, $y);
			}

			$prevX = $x;
			$prevY = $y;
		}

		return array (
			'left' => $lineLeft,
			'right' => $lineRight
		);
	}

	public function getAbscisses() {
		return array_keys($this->points);
	}

	public function minimize() {
		if (count($this->points) < 3) return;

		$result = array();

		$x = $this->getAbscisses();
		$kprev = ($this->points[$x[1]] - $this->points[$x[0]]) / ($x[1] - $x[0]);

		$result[$x[0]] = $this->points[$x[0]];
		$result[$x[count($x) - 1]] = $this->points[$x[count($x) - 1]];

		for ($j = 2; $j < count($x) - 1; $j++) {
			$xcurr = $x[$j];
			$xprev = $x[$j - 1];
			$kcurr = ($this->points[$xcurr] - $this->points[$xprev]) / ($xcurr - $xprev);
			if (abs($kcurr - $kprev) > self::EPS) {
				$result[$xprev] = $this->points[$xprev];
			}

			//echo $j, ": ", $kcurr, "\n";

			$kprev = $kcurr;
		}

		$this->points = $result;
	}
}
?>
