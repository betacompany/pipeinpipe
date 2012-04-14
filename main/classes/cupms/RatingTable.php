<?php

require_once dirname(__FILE__) . '/RatingFormula.php';
require_once dirname(__FILE__) . '/League.php';

require_once dirname(__FILE__) . '/../db/RatingDBClient.php';

require_once dirname(__FILE__) . '/../utils/BijectiveMap.php';

/**
 * Description of RatingTable
 *
 * @author Artyom Grigoriev
 */
class RatingTable {

	private $leagueId;
	private $league;
	private $date;
	private $map = array();
	private $data = array();

	/*
	 * array (
	 *     [leagueId] => array (
	 *         [date] => RatingTable
	 *     )
	 */
	private static $rating;

	private function __construct($leagueId, $date) {
		assertLeague($leagueId);
		assertDate($date);

		$this->leagueId = $leagueId;
		$this->league = League::getById($leagueId);
		$this->date = $date;

		$req = RatingDBClient::select($leagueId, $date);
		if (mysql_num_rows($req)) {
			while ($row = mysql_fetch_assoc($req)) {
				$this->map[$row['pmid']] = $row['points'];
				$this->data[] = array(
					'pmid' => $row['pmid'],
					// TODO мне ужасно не нравится, что это делается здесь!!!
					// это нужно стопудово отсюда убрать прямо в жопу.
					// что image, что url
					'url' => Player::getURLById($row['pmid']),
					'image' => Player::getImageById($row['pmid'], Player::IMG_SMALL),
					'name' => $row['name'],
					'surname' => $row['surname'],
					'points' => $row['points']
				);
			}
		} else {
			$data = self::evaluateData($leagueId, $date);
			$this->map = $data['map'];
			$this->data = $data['data'];
		}
	}

	public function getPmids() {
		return array_keys($this->map);
	}

	public function getScoreByPmid($pmid) {
		return $this->map[$pmid];
	}

	public function getData() {
		return $this->data;
	}

	public function evaluateCoefficient(array $pmIds) {
		$this->league->getFormula()->evaluate($this, $pmIds);
	}

	/**
	 * @static
	 * @param int $leagueId
	 * @return RatingTable
	 */
	public static function getInstance($leagueId = 1) {
		$date = date('Y-m-d');
		return self::getInstanceByDate($leagueId, $date);
	}

	/**
	 * @static
	 * @param int $leagueId
	 * @param string $date
	 * @return RatingTable
	 */
	public static function getInstanceByDate($leagueId, $date) {
		assertLeague($leagueId);
		assertDate($date);

		if (isset(self::$rating[$leagueId][$date]))
			return self::$rating[$leagueId][$date];

		self::$rating[$leagueId][$date] = new RatingTable($leagueId, $date);
		return self::$rating[$leagueId][$date];
	}

	/**
	 * Evaluates and returns rating table data of such league in such date
	 * @param int $leagueId
	 * @param string $date
	 * @return array
	 */
	public static function evaluateData($leagueId, $date) {
		$data = array(
			'map' => array(),
			'data' => array()
		);

		$req = CompetitionDBClient::selectBefore($date, $leagueId);
		while ($row = mysql_fetch_assoc($req)) {
			try {
				$competition = Competition::getById($row['id']);
				foreach ($competition->getCupsList() as $cup) {
					// FIXME remove hard suffix
					foreach ($cup->getPlayers() as $pm) {
						$pmid = $pm->getId();
						$r = ResultCupDBClient::select($cup->getId(), $pmid);
						if ($result = mysql_fetch_assoc($r)) {
							$delta = $result['points'] / $competition->getCoefDivider($date);
							$name = $pm->getFullName();

							if (!array_key_exists($pmid, $data['map'])) {
								$data['map'][$pmid] = $delta;
								$data['data'][] = array(
									'points' => $delta,
									'pmid' => $pmid,
									'name' => $name
								);
								continue;
							}

							foreach ($data['data'] as $index => $d) {
								if ($d['pmid'] == $pmid) {
									$prev = $d['points'];
									$data['data'][$index] = array(
										'points' => $prev + $delta,
										'pmid' => $pmid,
										'name' => $name
									);
									$data['map'][$pmid] += $delta;
									break;
								}
							}
						}
					}
				}
			} catch (Exception $e) {
				// TODO use error log file
				echo $e->getTraceAsString();
			}
		}

		array_multisort($data['data'], SORT_DESC);

		foreach ($data['data'] as $index => $pair) {
			$place = $index + 1;
			$pmid = $pair['pmid'];
			$points = $pair['points'];
			RatingDBClient::insert($leagueId, $date, $pmid, $points, $place);
		}

		return $data;
	}

	/**
	 * removes for future evaluated values from `p_rating`
	 */
	public static function removeFuture($date, $leagueId = 0) {
		return RatingDBClient::removeFuture($date, $leagueId);
	}

	public static function getRatingMovement($leagueId, $pmid) {
		$result = array();
		$req = RatingDBClient::selectByPmid($leagueId, $pmid);
		while ($row = mysql_fetch_assoc($req)) {
			$result[] = array(
				'date' => $row['date'],
				'place' => $row['rating_place'],
				'points' => $row['points']
			);
		}

		return $result;
	}

	public static function getRatingMovementInterval($begin, $end, $leagueId, $pmid) {
		$result = array();
		$req = RatingDBClient::selectByPmidInterval($begin, $end, $leagueId, $pmid);
		while ($row = mysql_fetch_assoc($req)) {
			$result[] = array(
                'date' => $row['date'],
				'place' => $row['rating_place'],
				'points' => $row['points']
			);
		}

		return $result;
	}

	public static function getBestRank($leagueId, $pmid) {
		return RatingDBClient::getBestRank($leagueId, $pmid);
	}

}

?>
