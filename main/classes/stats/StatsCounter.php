<?php
/**
 * Description of StatsCounter
 *
 * @author Nikita Artyushov
 */

require_once dirname(__FILE__) . '/../db/GameDBClient.php';
require_once dirname(__FILE__) . '/../cupms/Game.php';
require_once dirname(__FILE__) . '/../db/StatsDBClient.php';
require_once dirname(__FILE__) . '/../charts/PieChart.php';
require_once dirname(__FILE__) . '/../charts/BarChart.php';

class StatsCounter {

	const CHART_WIDTH = 650;
	const CHART_HEIGHT = 300;

	private static $instance;

	private static $gameCounter = array(
		'5:0' => 0,
		'5:1' => 0,
		'5:2' => 0,
		'5:3' => 0,
		'6:4' => 0,
		'balance' => 0,
	);
	private static $recordGames = array(
		'regular' => array(),
		'play-off' => array()
	);
	private static $maxPersonalGames = array();
	private static $maxPersonalWins = array();
	private static $recordPipeMans = array();

	private function __construct() {

		$iterator = GameDBCLient::getAll();
		while ($iterator->valid()) {
			$row = $iterator->current();
			self::handleGameCounter($row);
			self::handleRecordGames($row);
			self::handlePersonalGames($row);
			self::handlePersonalWins($row);
			self::handleTotalGames($row);
			self::handleWhitewashes($row);
			$iterator->next();
		}

		self::handlePersonalGames(null);
		self::handlePersonalWins(null);
		self::handleTotalGames(null);
		self::handleWhitewashes(null);
		self::handleMaxWinLossPercentage();
		self::handleMaxAverage();
		self::handleMaxCompsWon();
		self::handleMaxCompPerc();
		self::handleMaxComps();
		self::handleMaxPoints();
		self::handleMaxdaysOnTop();
	}

	/**
	 *
	 * @return StatsCounter
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new StatsCounter();
		}
		return self::$instance;
	}

	private static function handleGameCounter($row) {

		if ($row['stage'] == 0) {
			$max = max($row['score1'], $row['score2']);
			$min = min($row['score1'], $row['score2']);
			if ($max > 6) {
				self::$gameCounter['balance']++;
			} elseif ($max == 6) {
				self::$gameCounter['6:4']++;
			} elseif ($min != 5) {
				$s = '5:' . $min;
				self::$gameCounter[$s]++;
			}
		}
	}

	private static function handleRecordGames($row) {

		static $maxRegScore = 0;
		static $maxOffScore = 0;

		if ($row['stage'] == 0) {
			if (max($row['score1'], $row['score2']) == $maxRegScore) {
				self::$recordGames['regular'][] = new Game($row['id']);
			}
			if (max($row['score1'], $row['score2']) > $maxRegScore) {
				self::$recordGames['regular'] = array();
				self::$recordGames['regular'][] = new Game($row['id']);
				$maxRegScore = max($row['score1'], $row['score2']);
			}
		} else {
			if (max($row['score1'], $row['score2']) == $maxOffScore) {
				self::$recordGames['play-off'][] = new Game($row['id']);
			}
			if (max($row['score1'], $row['score2']) > $maxOffScore) {
				self::$recordGames['play-off'] = array();
				self::$recordGames['play-off'][] = new Game($row['id']);
				$maxOffScore = max($row['score1'], $row['score2']);
			}
		}
	}

	private static function handlePersonalGames($row) {

		static $counter = array();

		if ($row == null) {
			$maxValue = 0;
			$maxKey = 0;
			foreach ($counter as $key => $value) {
				if ($value > $maxValue) {
					$maxValue = $value;
					$maxKey = $key;
				}
			}
			
			foreach ($counter as $key => $value) {
				if ($value == $maxValue) {
//					$pmid1;
//					$pmid2;
//					for ($i = 0; $i < strlen($key); $i++) {
//						if ($key[$i] == ':') {
//							$pmid1 = substr($key, 0, $i);
//							$pmid2 = substr($key, $i+1);
//							break;
//						}
//					}
					list ($pmid1, $pmid2) = explode(':', $key, 2);
					$temp = array(
						'pmid1' => $pmid1,
						'pmid2' => $pmid2,
						'gameNum' => $maxValue
					);
					self::$maxPersonalGames[] = $temp;
				}
			}
			return;
		}
		$id1 = max($row['pmid1'], $row['pmid2']);
		$id2 = min($row['pmid1'], $row['pmid2']);
		$s = $id1 . ':' . $id2;
		$counter[$s]++;
	}
	
	private static function handlePersonalWins($row) {
	
		static $counter = array();

		if ($row == null) {
			$maxValue = 0;
			$maxKey = 0;
			foreach ($counter as $key => $value) {
				if ($value > $maxValue) {
					$maxValue = $value;
					$maxKey = $key;
				}
			}
			
			foreach ($counter as $key => $value) {
				if ($value == $maxValue) {
					list ($pmid1, $pmid2) = explode(':', $key, 2);
					$temp = array(
						'pmid1' => $pmid1,
						'pmid2' => $pmid2,
						'gameNum' => $maxValue
					);
					self::$maxPersonalWins[] = $temp;
				}
			}
			return;
		}
		$id1 = max($row['pmid1'], $row['pmid2']);
		$id2 = min($row['pmid1'], $row['pmid2']);
		
		if ($row['score1'] > $row['score2']) {
			$id1 = $row['pmid1'];
			$id2 = $row['pmid2'];
			$s = $id1 . ':' . $id2;
			$counter[$s]++;
		} elseif ($row['score1'] < $row['score2']) {
			$id1 = $row['pmid2'];
			$id2 = $row['pmid1'];
			$s = $id1 . ':' . $id2;
			$counter[$s]++;
		}

	}

	private static function handleTotalGames($row) {

		static $totalCounter = array();
		static $playOffCounter = array();

		if ($row == null) {
			$totalMax = 0;
			$totalResult = array();
			$playOffMax = 0;
			$playOffResult = array();
			foreach ($totalCounter as $key => $value) {
				if ($value > $totalMax) {
					$totalMax = $value;
				}
			}
			foreach ($totalCounter as $key => $value) {
				if ($value == $totalMax) {
					$totalResult[$key] = $value;
				}
			}

			foreach ($playOffCounter as $key => $value) {
				if ($value > $playOffMax) {
					$playOffMax = $value;
				}
			}
			foreach ($playOffCounter as $key => $value) {
				if ($value == $playOffMax) {
					$playOffResult[$key] = $value;
				}
			}
			self::$recordPipeMans['total'] = $totalResult;
			self::$recordPipeMans['play-off'] = $playOffResult;
			return;
		}

		$id1 = $row['pmid1'];
		$id2 = $row['pmid2'];
		settype($id1, "string");
		settype($id2, "string");
		$totalCounter[$id1]++;
		$totalCounter[$id2]++;
		if ($row['stage'] > 0) {
			$playOffCounter[$id1]++;
			$playOffCounter[$id2]++;
		}
	}

	private static function handleWhitewashes($row) {

		static $winCounter = array();
		static $lossCounter = array();

		if ($row == null) {

			$maxWin = 0;
			$maxLoss = 0;
			$winResult = array();
			$lossResult = array();
			foreach ($winCounter as $key => $value) {
				if ($value > $maxWin) {
					$maxWin = $value;
				}
			}
			foreach ($winCounter as $key => $value) {
				if ($value == $maxWin) {
					$winResult[$key] = $maxWin;
				}
			}

			foreach ($lossCounter as $key => $value) {
				if ($value > $maxLoss) {
					$maxLoss = $value;
				}
			}
			foreach ($lossCounter as $key => $value) {
				if ($value == $maxLoss) {
					$lossResult[$key] = $maxLoss;
				}
			}

			self::$recordPipeMans['whitewash-win'] = $winResult;
			self::$recordPipeMans['whitewash-loss'] = $lossResult;
			return;
		}


		$id1 = $row['pmid1'];
		$id2 = $row['pmid2'];
		settype($id1, "string");
		settype($id2, "string");
		if ($row['score1'] == 0 && $row['score2'] != 0 && $row['is_tech'] == '0') {
			$winCounter[$id2]++;
			$lossCounter[$id1]++;
		}
		if ($row['score2'] == 0 && $row['score1'] != 0 && $row['is_tech'] == '0') {
			$lossCounter[$id2]++;
			$winCounter[$id1]++;
		}
	}

	private static function handleMaxWinLossPercentage() {
		$winIterator = StatsDBClient::getMaxWinPerc();
		$lossIterator = StatsDBClient::getMaxLossPerc();
		$array = array();
		while ($winIterator->valid()) {
			$row = $winIterator->current();
			$pmid = $row['id'];
			$value = $row['percentage'];
			settype($pmid, "string");
			$array[$pmid] = round($value);
			$winIterator->next();
		}
		self::$recordPipeMans['max_win'] = $array;
		$array = array();
		while ($lossIterator->valid()) {
			$row = $lossIterator->current();
			$pmid = $row['id'];
			$value = $row['percentage'];
			settype($pmid, "string");
			$array[$pmid] = round($value);
			$lossIterator->next();
		}
		self::$recordPipeMans['max_loss'] = $array;
	}

	private static function handleMaxAverage() {
		$iterator = StatsDBClient::getMaxAve();
		$array = array();
		while ($iterator->valid()) {
			$row = $iterator->current();
			$pmid = $row['pmid'];
			settype($pmid, "string");
			$ave = $row['ave'];
			$array[$pmid] = round($ave, 2);
			$iterator->next();
		}
		self::$recordPipeMans['max_ave'] = $array;
	}

	private static function handleMaxCompsWon() {
		$iterator = StatsDBClient::getMaxCompetitionsWon();
		$array = array();
		while ($iterator->valid()) {
			$row = $iterator->current();
			$pmid = $row['pmid'];
			settype($pmid, "string");
			$value = $row['comp_won'];
			$array[$pmid] = $value;
			$iterator->next();
		}
		self::$recordPipeMans['max_comp_won'] = $array;
	}

	private static function handleMaxCompPerc() {
		$iterator = StatsDBClient::getMaxCompPerc();
		$array = array();
		while ($iterator->valid()) {
			$row = $iterator->current();
			$pmid = $row['pmid'];
			settype($pmid, "string");
			$value = $row['percentage'];
			$array[$pmid] = round($value);
			$iterator->next();
		}
		self::$recordPipeMans['max_comp_perc'] = $array;
	}

	private static function handleMaxComps() {
		$iterator = StatsDBClient::getMaxComps();
		$array = array();
		while ($iterator->valid()) {
			$row = $iterator->current();
			$pmid = $row['pmid'];
			settype($pmid, "string");
			$value = $row['comp_num'];
			$array[$pmid] = $value;
			$iterator->next();
		}
		self::$recordPipeMans['max_comps'] = $array;
	}

	private static function handleMaxPoints() {
		$iterator = StatsDBClient::getMaxPoints();
		$array = array();
		while ($iterator->valid()) {
			$row = $iterator->current();
			$pmid = $row['pmid'];
			settype($pmid, "string");
			$value = $row['points'];
			$array[$pmid] = round($value);
			$iterator->next();
		}
		self::$recordPipeMans['max_points'] = $array;

	}

	private static function handleMaxDaysOnTop() {
		$iterator = StatsDBClient::getMaxDaysOnTop();
		$array = array();
		while ($iterator->valid()) {
			$row = $iterator->current();
			$pmid = $row['pmid'];
			settype($pmid, "string");
			$value = $row['days'];
			$array[$pmid] = $value;
			$iterator->next();
		}
		self::$recordPipeMans['max_days_on_top'] = $array;
	}

	/**
	 * Returns associative array with keys representing score in the form <code>score1:score2.</code>
	 * If the score is by balance then the key is <code>balance</code>
	 * @return array
	 */
    public function getGameCounter() {

		return self::$gameCounter;
	}

	/**
	 * Returns 2-element associative array of games with keys <b>regular</b> and <b>play-off</b>.
	 * Each element contains array of record games. If it has more than one element,
	 * then all the scores are equal in this array.
	 * @return array
	 */
	public function getRecordMatches() {

		return self::$recordGames;
	}

	/**
	 *@return array
	 */
	public function getMaxPersonalGames() {

		return self::$maxPersonalGames;
	}

	/**
	 * @return array
	 */
	public function getMaxPersonalWins() {

		return self::$maxPersonalWins;
	}

	/**
	 * Returns the associative array with keys <b>total, max_ave, max_comp_perc, max_comps, max_comp_won, max_days_on_top,
	 * max_points, max_win, max_loss, play-off, whitewash-win, whitewash-loss</b>.
	 * Each value is an associative array with
	 * key representing pipeman id.
	 * @return array
	 */
	public function getRecordPipeMans() {
		
		return self::$recordPipeMans;
	}

	public function getRecordNames() {
		return array("total", "max_ave", "max_comp_perc", "max_comps", "max_comp_won", "max_days_on_top",
			"max_points", "max_win", "max_loss", "play-off", "whitewash-win", "whitewash-loss");
	}

	public function getPieChart($legendEnabled = false) {

		$pie = new PieChart();
		$labels = array('5:0', '5:1', '5:2', '5:3', '6:4', 'balance');
		$data = array();
		for ($i = 0; $i < 6; $i++) {
			$data[$i] = self::$gameCounter[$labels[$i]];
		}
		$labels[5] = 'бал.';
		$pie->set($data, $labels);
		$colors = array();
		$colors[] = '66b0ca';
		$colors[] = '000000';
		$colors[] = 'f6976c';      /* black-262626  grey-9F9F9F blue-007CA7 light violet-9c63a5 violet-792D86 l.blue-66b0ca  */
		$colors[] = '007CA7';
		$colors[] = '9F9F9F';
		$colors[] = 'f15711';
		return $pie->url(self::CHART_WIDTH, self::CHART_HEIGHT, $colors, $legendEnabled);
	}

	public function getBarChart($legendEnabled = false) {

		$bar = new BarChart();
		$labels = array('5:0', '5:1', '5:2', '5:3', '6:4', 'balance');
		$data = array();

		for ($i = 0; $i < 6; $i++) {
			$data[$i] = self::$gameCounter[$labels[$i]];
		}
		
		$labels[5] = 'бал.';
		$bar->set($data, $labels);
		$colors = array();
		$colors[] = '66b0ca';
		$colors[] = '000000';
		$colors[] = 'f6976c';
		$colors[] = '007CA7';
		$colors[] = '9F9F9F';
		$colors[] = 'f15711';
		return $bar->url(self::CHART_WIDTH, self::CHART_HEIGHT, $colors, $legendEnabled);
	}

	/**
	 * @return array
	 */
	public function getClub69() {

		$iterator = StatsDBClient::getClub69();
		$counter = 0;
		$result = array();

		while ($iterator->valid()) {
			if ($counter >= 5) {
				break;
			}
			$row = $iterator->current();
			if ($row['victories'] < 69) {
				$counter++;
			}
			$result[$row['id']] = $row['victories'];
			$iterator->next();
		}

		return $result;
	}

	public function getCompsWithMaxMatches() {

		$iterator = StatsDBClient::getCompWithMaxMatches();
		$result = array();
		while ($iterator->valid()) {
			$row = $iterator->current();
			$result[$row['competition_id']] = $row['game_num'];
			$iterator->next();
		}
		return $result;
	}

	public function getCompsWithMaxPman() {

		$iterator = StatsDBClient::getCompsWithMaxPman();
		$result = array();
		while ($iterator->valid()) {
			$row = $iterator->current();
			$result[$row['comp_id']] = $row['count'];
			$iterator->next();
		}
		return $result;
	}
}
?>
