<?php

require_once dirname(__FILE__) . '/Competition.php';
require_once dirname(__FILE__) . '/Cup.php';
require_once dirname(__FILE__) . '/CupOneLap.php';
require_once dirname(__FILE__) . '/CupTwoLaps.php';

require_once dirname(__FILE__) . '/IRatingBonusSchema.php';
require_once dirname(__FILE__) . '/BasicBonusSchema.php';
require_once dirname(__FILE__) . '/BasicBronzeBonusSchema.php';

/**
 * 
 * @author Artyom Grigoriev
 */
class RatingManager {

	private $competition;

	public function  __construct(Competition $competition) {
		$this->competition = $competition;
	}

	public function onFinish($date) {
		$competition = $this->competition;
		$cup = $competition->getMainCup();
		$coef = $competition->getCoef();
		$this->finishCup($cup, $coef, $date);
	}

	public function onReStart() {
		$competition = $this->competition;
		$cup = $competition->getMainCup();
		$this->reStartCup($cup);
	}

	public function getCoef($date) {
		if (!$this->competition->isFinished() || $date < $this->competition->getDate()) return 0;
		return $this->competition->getCoef() / $this->getCoefDivider($date);
	}

	public function getCoefDivider($date) {
		if (!$this->competition->isFinished()) return 1;
		if ($date < $this->competition->getDate()) return 1;

		list ($yC, $mC, $dC) = explode('-', $this->competition->getDate(), 3);
		list ($yD, $mD, $dD) = explode('-', $date, 3);

		$yC = intval($yC);
		$mC = intval($mC);
		$dC = intval($dC);
		$yD = intval($yD);
		$mD = intval($mD);
		$dD = intval($dD);

		$result = ($yD - $yC) * 4;
		$result += ($mD - $mC) * 4 / 12;
		$result += ($dD - $dC) * 4 / 365;
		$result = ($result < 1) ? 1 : $result;

		return $result;
	}

	private function finishGame(IRatingBonusSchema $schema, Game $game, $coefN, $date) {
		$stage = $game->getStage();
		$cupId = $game->getCupId();

		switch ($stage) {
		case 0: break;
		case 1:
			ResultCupDBClient::refresh(
				$game->getVictorId(),
				$cupId,
				$date,
				$coefN * $schema->getBonus(1),
				1
			);

			ResultCupDBClient::refresh(
				$game->getLooserId(),
				$cupId,
				$date,
				$coefN * $schema->getBonus(2),
				2
			);
			break;

		case 2:
			if ($schema instanceof BasicBronzeBonusSchema) break;

			$points = $coefN * $schema->getBonus(3);
			ResultCupDBClient::refresh($game->getLooserId(), $cupId, $date, $points, 3);
			break;

		case 3:
			ResultCupDBClient::refresh(
				$game->getVictorId(),
				$cupId,
				$date,
				$coefN * $schema->getBonus(3),
				3
			);

			ResultCupDBClient::refresh(
				$game->getLooserId(),
				$cupId,
				$date,
				$coefN * $schema->getBonus(4),
				4
			);
			break;

		default:
			$place = $stage + 1;
			$points = $coefN * $schema->getBonus($place);
			ResultCupDBClient::refresh(
				$game->getLooserId(),
				$cupId,
				$date,
				$points,
				$place
			);
		}

		$prev1 = $game->getPrevGame1();
		$prev2 = $game->getPrevGame2();

		if ($prev1 != null) $this->finishGame($schema, $prev1, $coefN, $date);
		if ($prev2 != null) $this->finishGame($schema, $prev2, $coefN, $date);
	}

	private function finishCup(Cup $cup, $coef, $date) {
		if ($cup instanceof CupOneLap) {
			$table = $cup->getResultTable();
			foreach ($table as $row) {
				$pmid = $row->getPmid();
				$cupId = $cup->getId();
				$avgn = $row->getAverage() / 6;
				$mult = $cup->getMultiplier();
				$points = $mult * $avgn * $coef;
				$place = $row->getPlace();
				ResultCupDBClient::insert($pmid, $cupId, $date, $points, $place);
			}
		} elseif ($cup instanceof CupPlayoff) {
			$coefN = $coef * $cup->getMultiplier() / 2;
			if ($cup->getFinalGame() !== null) {
				if ($cup->getBronzeGame() != null) {
					$bonusSchema = new BasicBronzeBonusSchema();
					$this->finishGame($bonusSchema, $cup->getFinalGame(), $coefN, $date);
					$this->finishGame($bonusSchema, $cup->getBronzeGame(), $coefN, $date);
				} else {
					$bonusSchema = new BasicBonusSchema();
					$this->finishGame($bonusSchema, $cup->getFinalGame(), $coefN, $date);
				}
			}
		}

		foreach ($cup->getChildren() as $child) {
			$this->finishCup($child, $coef, $date);
		}
	}

	private function reStartCup(Cup $cup) {
		if ($cup instanceof CupOneLap) {
			ResultCupDBClient::deleteByCupId($cup->getId());
		} elseif ($cup instanceof CupPlayoff) {
			ResultCupDBClient::rollBack($cup->getId());
		}

		foreach ($cup->getChildren() as $child) {
			$this->reStartCup($child);
		}
	}
}
?>
