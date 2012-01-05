<?php
require_once dirname(__FILE__).'/GrigorievRatingFormula.php';
require_once dirname(__FILE__).'/ShuvalovRatingFormula.php';

class RatingFormula {
	private static $instance = null;
	public static final function getInstance() {
		if (self::$instance == null) {
			self::$instance = new RatingFormula();
		}
		return self::$instance;
	}

	private $formulae = array();

	private function __construct() {
		$this->formulae = array(
			ShuvalovRatingFormula::getName() => new ShuvalovRatingFormula(),
			GrigorievRatingFormula::getName() => new GrigorievRatingFormula()
			
		);
	}

	const NAME = 'This is an Astract Formula!!';

	public function getName() {
		return self::NAME;
	}

	public final function getFormulaByName($name) {
		return $this->formulae[$name];
	}

	public final function getAllToArray() {
		$result = array();
		foreach ($this->formulae as $i => $formula) {
			$result[] = array(
				'id' => $i,
				'value' => $formula->getName()
			);
		}
		return $result;
	}

	public function evaluate(RatingTable $ratingTable, array $pmIds) {
		return 0;
    }
	public function getData(RatingTable $ratingTable, array $pmIds) {
		return 0;
	}
	public function toJSON(RatingTable $ratingTable, array $pmIds) {
		return 0;
	}
}
?>
