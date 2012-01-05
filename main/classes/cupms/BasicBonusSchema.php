<?php
/**
 * Represents basic usual bonus schema for cups without bronze final
 * @author ortemij
 */
class BasicBonusSchema implements IRatingBonusSchema {
	public function getBonus($place) {
		switch ($place) {
		case 1:
			return 2.0;
		case 2:
			return 1.5;
		case 3:
			return 1.125;
		case 5:
			return 0.75;
		case 9:
			return 0.5;
		case 17:
			return 0.25;
		case 33:
			return 0.125;
		case 65:
			return 0.0625;
		default:
			return 0;
		}
	}
}
?>
