<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BarChart
 *
 * @author Nikita
 */
class BarChart {

	private $data = array();
	private $labels = array();

	public function set($data, $labels = array(), $zerosEnabled = false) {
		$isLabels = !empty ($labels);
		foreach ($data as $i => $item) {
			if (intval($item) > 0 || $zerosEnabled) {
				$this->data[] = $item;
				if ($isLabels) $this->labels[] = $labels[$i];
			}
		}
	}

	public function url($width, $height, $colors, $legendEnabled = false) {
		$result = 'http://chart.apis.google.com/chart?';

		$result .= 'cht=bvs';
		$result .= '&chd=t:';
		foreach ($this->data as $i => $item) {
			$result .= intval($item);
			if ($i < count($this->data) - 1) $result .= ',';
		}

		$result .= '&chl=';
		if (!empty($this->labels)) {
			foreach ($this->labels as $i => $label) {
				$result .= iconv('windows-1251', 'UTF-8', $label);
				if ($i < count($this->labels) - 1) $result .= '|';
			}
		}

		$result .= '&chco=';
		if (is_array($colors)) {
			$i = 0;
			foreach ($colors as $color) {
				$result .= strtoupper($color);
				if ($i < count($colors) - 1) $result .= '|';
				$i++;
			}
		} else {
			$result .= strtoupper($colors);
		}

		if ($width != null) {
			$result .= '&chs=' . $width . 'x' .$height;
		} else {
			$result .= '&chs=';
		}
		
		$maxValue = 0;
		foreach ($this->data as $value) {
			if ($value > $maxValue) {
				$maxValue = $value;
			}
		}
		$maxValue += 10;
		while (++$maxValue % 50 != 0) {}

		$result .= '&chxr=1,0,' . $maxValue . ',50' . '&chds=0,' . $maxValue . '&chbh=a,20&chxt=x,y';

		if ($legendEnabled) {
			$result .= '&chdl=';
			if (!empty($this->labels)) {
				foreach ($this->labels as $i => $label) {
					$label .= '+(' . $this->data[$i] . ')';
					$result .= iconv('windows-1251', 'UTF-8', $label);
					if ($i < count($this->labels) - 1) $result .= '|';
				}
			}

			$result .= '&chdls=000000,20';
		}

		$spaceWidth = 2500 / $maxValue - 0.00001;

		$result .= '&chxs=0,000000,17|1,000000,17&chg=0,' . $spaceWidth . ',1,0';

		return $result;
	}
}
?>
