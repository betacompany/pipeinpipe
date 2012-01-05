<?php

/**
 * @author Artyom Grigoriev
 */
interface IComparable {

	/**
	 * @return int
	 * something negative if $this is less than $other
	 * 0 if they are equal
	 * something positive if $this is greater than $other
	 */
    public function compareTo(IComparable $other);
}
?>
