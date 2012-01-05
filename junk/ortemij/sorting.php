<?php
/**
 * @author Artyom Grigoriev
 */

require_once '../../main/classes/utils/IComparable.php';
require_once '../../main/classes/utils/Sorting.php';

class MyComparable implements IComparable {

	private $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function compareTo(IComparable $other) {
		if ($other instanceof MyComparable) {
			return $this->value - $other->value;
		}

		return -1;
	}
}

$array = array();

for ($i = 0; $i < 10; $i++) {
	$array[] = new MyComparable(mt_rand(0, 100));
}

Sorting::qsort($array);

//echo $array[0]->compareTo($array[1]);

echo '<pre>';
print_r($array);
echo '</pre>';

?>
