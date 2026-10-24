<?php
/**
 * Description of BijectiveMap
 *
 * @author ortemij
 */
// #[\ReturnTypeWillChange] keeps PHP 8.1+ from asking for return types on the
// Iterator methods; PHP 5 reads the line as a comment.
class BijectiveMap implements IteratorAggregate {
	private $direct = array();
	private $reversed = array();

	const KEY_SUFFIX = '_k';
	const VALUE_SUFFIX = '_v';

	public function put($key, $value) {
		unset ($this->reversed[$this->direct[$key] . self::VALUE_SUFFIX]);
		$this->direct[$key . self::KEY_SUFFIX] = $value;
		$this->reversed[$value . self::VALUE_SUFFIX] = $key;
	}

	public function containsKey($key) {
		return isset($this->direct[$key . self::KEY_SUFFIX]);
	}

	public function containsValue($value) {
		return isset($this->reversed[$value . self::VALUE_SUFFIX]);
	}
	
    public function getValue($key) {
		return $this->direct[$key . self::KEY_SUFFIX];
	}

	public function getKey($value) {
		return $this->reversed[$value . self::VALUE_SUFFIX];
	}

	public function getKeysArray() {
		return array_values($this->reversed);
	}

	public function getValuesArray() {
		return array_values($this->direct);
	}

	#[\ReturnTypeWillChange]
	public function getIterator() {
		return new BijectiveMapIterator($this);
	}
}

class BijectiveMapIterator implements Iterator {
	private $map;
	private $keys = array();
	private $values = array();
	private $index = 0;

	public function  __construct(BijectiveMap $map) {
		$this->map = clone $map;
		$this->keys = $this->map->getKeysArray();
		$this->values = rsort($this->map->getValuesArray());
	}

	/**
	 *
	 * @return array ('key' => ..., 'value' => ...)
	 */
	#[\ReturnTypeWillChange]
	public function current() {
		$pair = array (
			'key' => $this->map->getKey($this->values[$index]),
			'value' => $this->values[$index]
		);

		return $pair;
	}

	#[\ReturnTypeWillChange]
	public function key() {
		throw new BadMethodCallException('Method BijectiveMapIterator::key() is unsupported');
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
		return $this->index < count($this->values);
	}
}
?>
