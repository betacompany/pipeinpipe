<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PieChart
 *
 * @author ortemij
 */
class PieChart {
    private $data = array ();
	private $labels = array ();

	public function set($data, $labels = array(), $zerosEnabled = false) {
		$isLabels = !empty ($labels);
		foreach ($data as $i => $item) {
			if (intval($item) > 0 || $zerosEnabled) {
				$this->data[] = $item / 10;
				if ($isLabels) $this->labels[] = $labels[$i];
			}
		}
	}

	public function url($width, $height, $colors, $legendEnabled = false) {
		$result = 'http://chart.apis.google.com/chart?';

		$tr = dechex(255 - 255 * $transparency / 100);
		$tr = (strlen($tr) == 1) ? "0".$tr : $tr;
		$tr = strtoupper($tr);
		$result .= 'chf=a,s,000000'.$tr;

		$result .= '&cht=p3';
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

		$percentage = array();
		$percentage[0] = '5:0+(';
		$percentage[1] = '5:1+(';
		$percentage[2] = '5:2+(';
		$percentage[3] = '5:3+(';
		$percentage[4] = '6:4+(';
		$percentage[5] = iconv('windows-1251', 'UTF-8', 'бал.+(');
		$sum = 0;
		foreach ($this->data as $value) {
			$sum += $value;
		}
		for ($i = 0; $i < count($this->data); $i++) {
			$percentage[$i] .= round($this->data[$i] / $sum * 100) . '%)';
		}
		if ($legendEnabled) {
			$result .= '&chdl=';
			$i = 0;
			foreach ($percentage as $value) {
				$result .= $value;
				if ($i < count($percentage) - 1) {
					$result .= '|';
				}
				$i++;
			}

			$result .= '&chdls=000000,20';
		}

		$result .= '&chxt=x&chxs=0,000000,17';

		return $result;
	}
}
?>
