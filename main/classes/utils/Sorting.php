<?php
/**
 * @author Artyom Grigoriev
 */
class Sorting {

	/**
	 * Quick Sort in ascendive order according to IComparable::compareTo
	 * @author Nikolay Malkovsky
	 * @param array $array of IComparable
	 * @param int $left [optional]
	 * @param int $right [optional]
	 */
	public static function qsort(&$array, $left = null, $right = null) {
		if (!count($array)) {
			throw new Exception('Array is empty');
		}

		if (count($array) && !($array[0] instanceof IComparable)) {
			throw new Exception('Array items sholud implament IComparable');
		}

		if ($left === null) {
			$count = count($array);
			return self::qsort($array, 0, $count - 1);
		}

		if ($left >= $right) return;
		$p = $left; $q = $right;
        $m = mt_rand($p, $q);
		$x = $array[$m];

		do {
			while ($array[$p]->compareTo($x) < 0) $p++;
			while ($array[$q]->compareTo($x) > 0) $q--;
			if ($p <= $q) {
				$c = $array[$p];
				$array[$p] = $array[$q];
				$array[$q] = $c;
				$p++; $q--;
			}
		} while ($p <= $q);

		if ($left < $q) self::qsort($array, $left, $q);
		if ($p < $right) self::qsort($array, $p, $right);
	}
}
?>
