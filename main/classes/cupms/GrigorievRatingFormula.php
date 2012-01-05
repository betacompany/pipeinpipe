<?php
/**
 * Description of GrigorievRatingFormula
 * Evaluates competition coefficient using Grigoriev's formula.
 * @author Artyom Grigoriev
 */

require_once dirname(__FILE__).'/RatingFormula.php';

class GrigorievRatingFormula extends RatingFormula {
	const NAME = 'Григорьев';
	const NAME_EN = 'Grigoriev';

	private $data = array();
	private $dataEvaluated = false;
	private $json = array();
	private $jsonEvaluated = false;

	public function getName() {
		return self::NAME;
	}

	public function getNameEn() {
		return self::NAME_EN;
	}

	public function  __construct() {}

	/**
     * NB! If some players in $pmIds do not exists in $ratingTable they are
     * evaluated as players with 0 points but not used in counting average
     * value!
     * 
     * @param RatingTable $ratingTable
     * @param array $pmIds
     * @return real
     */
    public function evaluate(RatingTable $ratingTable, array $pmIds) {
        if (empty($pmIds)) return 0;

        $sumPoints = 0;
        $count = 0;
        foreach ($ratingTable->getData() as $row) {
            $sumPoints += $row['points'];
            $count++;
        }

        $avgPoints = ($count > 0) ? $sumPoints / $count : 0;
        $countTop = 0;
        $countBottom = 0;
        $sumWeight = 0;

		$this->dataEvaluated = true;

		$this->data['formula'] = array (
			'name' => $this->getName(),
			'name_en' => $this->getNameEn()
		);

        foreach ($ratingTable->getData() as $row) {
            if (array_contains($pmIds, $row['pmid'])) {
                if ($row['points'] >= $avgPoints) {
                    $countTop++;
                } else {
                    $countBottom++;
                }

                $dSumWeight = ($avgPoints > 0) ? $row['points'] / $avgPoints : 0;
				$sumWeight += $dSumWeight;
				$this->data['weights'][] = array (
					'pmid' => $row['pmid'],
					'url' => Player::getURLById($row['pmid']),
					'weight' => round($dSumWeight * 100) / 100
				);
            }
        }

        $countBottom += count($pmIds) - ($countTop + $countBottom);

        $ratio = $countTop / ($countTop + $countBottom);
        $x = 2 * $ratio - 1;
        $exponent = - ($x * $x) / 2;
        $multiplicator = exp($exponent);

		$result = 10 * $multiplicator * $sumWeight;

		$this->data['data'] = array (
			'avg_points' => round($avgPoints * 10) / 10,
			'count_top' => $countTop,
			'count_bottom' => $countBottom,
			'ratio' => round($ratio * 10) / 10,
			'x' => round($x * 10) / 10,
			'exp' => round($exponent * 100) / 100,
			'mult' => round($multiplicator * 100) / 100,
			'result' => round($result * 100) / 100
		);

        return $result;
    }

	/**
	 * please be sure if you launch this method after evaluate() with some other data
	 * all results of evaluate() are to be cached, you must recall evaluate() before
	 *
	 * @param RatingTable $ratingTable
	 * @param array $pmIds
	 */
	public function getData(RatingTable $ratingTable, array $pmIds) {
		if ($this->dataEvaluated) return $this->data;
		$this->evaluate($ratingTable, $pmIds);
		$this->dataEvaluated = true;
		return $this->data;
	}

	/**
	 * JSON
	 */
	public function toJSON(RatingTable $ratingTable, array $pmIds) {
		if ($this->jsonEvaluated) return $this->json;
		$this->json = json($this->getData($ratingTable, $pmIds));
		$this->jsonEvaluated = true;
		return $this->json;
	}
}
?>
