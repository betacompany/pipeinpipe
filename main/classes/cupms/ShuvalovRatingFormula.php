<?php
/**
 * Formula is, first, funny and, second, created for testing purposes
 * @author Innokenty Shuvalov
 */

require_once dirname(__FILE__).'/RatingFormula.php';

class ShuvalovRatingFormula extends RatingFormula {
	const NAME = 'Шувалов';

	public function getName() {
		return self::NAME;
	}

	public function  __construct() {}

	public function evaluate(RatingTable $ratingTable, array $pmIds) {
        return 69;
    }

	public function getData(RatingTable $ratingTable, array $pmIds) {
		return $this->evaluate($ratingTable, $pmIds);
	}

	public function toJSON(RatingTable $ratingTable, array $pmIds) {
		return json_encode($this->evaluate($ratingTable, $pmIds));
	}
}
?>
