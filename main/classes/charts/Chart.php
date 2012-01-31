<?php

require_once dirname(__FILE__) . '/LineSet.php';
require_once dirname(__FILE__) . '/Line.php';
require_once dirname(__FILE__) . '/../utils/Cache.php';

/**
 * Description of Chart
 *
 * @author ortemij
 */
class Chart {
	
    private $lineSet;
	private $minX = null;
	private $maxX = null;
	private $minY = null;
	private $maxY = null;

	public function setXs($minX, $maxX) {
		$this->minX = $minX;
		$this->maxX = $maxX;
	}

	public function setYs($minY, $maxY) {
		$this->minY = $minY;
		$this->maxY = $maxY;
	}

	public function setLineSet(LineSet $lineSet) {
		$this->lineSet = $lineSet;
	}

	public function countLinesLight() {
		return count($this->lineSet->getLines());
	}

	public function countLinesAt($x) {
		$counter = 0;
		foreach ($this->lineSet->getLines() as $line) {
			$points = $line->getPoints();
			if (isset($points[$x])) $counter++;
		}

		return $counter;
	}

	public function countLines() {
		$result = array();
		foreach ($this->lineSet->getLines() as $line) {
			foreach ($line->getPoints() as $y) {
				// FIXME very small case
				if (round($y) != $y) {
					$result[$y . ''] = true;
				}
			}
		}

		//print_r($result);

		return count(array_keys($result));
	}

	public function getRangeHeight() {
		$min = INF;
		$max = -INF;
		foreach ($this->lineSet->getLines() as $line) {
			$lmin = $line->getMinValue();
			$lmax = $line->getMaxValue();
			if ($lmin < $min) $min = $lmin;
			if ($lmax > $max) $max = $lmax;
		}

		return ($min == INF && $max == -INF) ? 0 : ceil($max - $min);
	}


	public function url($width, $height, $transparency = 0) {


//		$this->lineSet->minimize();
//
//		$lines = $this->lineSet->getLines();
//
//		$post_data = array();
//		$result = 'http://chart.apis.google.com/chart?';
//
//		$tr = dechex(255 - 255 * $transparency / 100);
//		$tr = (strlen($tr) == 1) ? "0".$tr : $tr;
//		$tr = strtoupper($tr);
//		$result .= 'chf=a,s,000000'.$tr;
//		$post_data['chf'] = 'a,s,000000'.$tr;
//
//		$result .= '&chco=';
//		$post_data['chco'] = '';
//		foreach ($lines as $index => $line) {
//			$result .= $line->getColor();
//			$post_data['chco'] .= $line->getColor();
//			if ($index < count($lines) - 1) {
//				$result .= ',';
//				$post_data['chco'] .= ',';
//			}
//		}
//
//		$minX = INF;
//		$maxX = -INF;
//		$minY = INF;
//		$maxY = -INF;
//
//		$result .= '&chd=t:';
//		$post_data['chd'] = 't:';
//		foreach ($lines as $i => $line) {
//			$points = $line->getPoints();
//
//			$j = 0;
//			foreach ($points as $x => $y) {
//				if ($x < $minX) $minX = $x;
//				if ($x > $maxX) $maxX = $x;
//
//				$result .= sprintf("%.2f", $x);
//				$post_data['chd'] .= sprintf("%.2f", $x);
//				if ($j < count($points) - 1) {
//					$result .= ',';
//					$post_data['chd'] .= ',';
//				}
//				$j++;
//			}
//
//			$result .= '|';
//			$post_data['chd'] .= '|';
//
//			$j = 0;
//			foreach ($points as $y) {
//				if ($y < $minY) $minY = $y;
//				if ($y > $maxY) $maxY = $y;
//
//				$result .= sprintf("%.2f", $y);
//				$post_data['chd'] .= sprintf("%.2f", $y);
//				if ($j < count($points) - 1) {
//					$result .= ',';
//					$post_data['chd'] .= ',';
//				}
//				$j++;
//			}
//
//
//			if ($i < count($lines) - 1) {
//				$result .= '|';
//				$post_data['chd'] .= '|';
//			}
//		}
//
//		$minX2 = ($this->minX == null) ? floor($minX) : $this->minX;
//		$maxX2 = ($this->maxX == null) ? ceil($maxX) : $this->maxX;
//		$minY2 = ($this->minY == null) ? floor($minY) : $this->minY;
//		$maxY2 = ($this->maxY == null) ? ceil($maxY) : $this->maxY;
//
//		$result .= '&chds=';
//		$post_data['chds'] = '';
//		foreach ($lines as $i => $line) {
//			$result .= "$minX2,$maxX2,$minY2,$maxY2";
//			$post_data['chds'] .= "$minX2,$maxX2,$minY2,$maxY2";
//			if ($i < count($lines) - 1) {
//				$result .= ',';
//				$post_data['chds'] .= ',';
//			}
//		}
//
//		$result .= '&chls=';
//		$post_data['chls'] = '';
//		foreach ($lines as $i => $line) {
//			$result .= $line->getWidth();
//			$post_data['chls'] .= $line->getWidth();
//			if ($i < count($lines) - 1) {
//				$result .= '|';
//				$post_data['chls'] .= '|';
//			}
//		}
//
//		if ($width == null && $height == null) {
//			$result .= '&cht=lxy&chs=';
//			$post_data['cht'] = 'lxy';
//			$post_data['chs'] = '';
//		} else {
//			$result .= '&chs='.$width.'x'.$height.'&cht=lxy';
//			$post_data['chs'] = $width.'x'.$height;
//			$post_data['cht'] = 'lxy';
//		}
//
//		if (strlen($result) < 1024) {
//			return $result;
//		} else {
//			$cache = new Cache('charts');
//			$hash = $cache->put($result, serialize($post_data));
//			return '/procs/proc_charts_proxy.php?hash='.$hash.'&chs=' . $post_data['chs'];
//		}
	}
}
?>
