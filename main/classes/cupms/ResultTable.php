<?php

require_once dirname(__FILE__).'/../../includes/assertion.php';

require_once dirname(__FILE__).'/Player.php';
require_once dirname(__FILE__).'/Game.php';
require_once dirname(__FILE__).'/Cup.php';
require_once dirname(__FILE__).'/CupFactory.php';

require_once dirname(__FILE__).'/../db/ResultTableDBClient.php';

require_once dirname(__FILE__).'/../utils/IComparable.php';
require_once dirname(__FILE__).'/../utils/Sorting.php';


/**
 * Represents row with all information about statistics of defined player in defined cup
 * @author Artyom Grigoriev
 */
class ResultTable implements IComparable {

	const WIN5 = 6;
	const WIN6 = 5;
	const WINB = 4;
	const LOSE5 = 0;
	const LOSE6 = 1;
	const LOSEB = 2;

    private $id;
	private $pmid;
	private $cupId;
    private $place;
	private $games;
    private $points;
	private $win5;
	private $win6;
	private $winb;
	private $lose5;
	private $lose6;
	private $loseb;
	
	private $player;
	private $playerLoaded = false;

	public function  __construct($pmid, $cupId, $create = false) {
		assertTrue('Invalid pmid='.$pmid, Player::existsById($pmid));
		assertTrue('Invalid cupId='.$cupId, Cup::existsById($cupId));

		$req = ResultTableDBClient::select($pmid, $cupId);
		if ($result = mysql_fetch_assoc($req)) {
            if ($create) {
                throw new Exception("Results for pmid=$pmid and cupId=$cupId already exists");
            }

			$this->id =		$result['id'];
			$this->pmid =	$result['pmid'];
            $this->cupId =  $result['cup_id'];
			$this->games =	$result['games'];
			$this->points = $result['points'];
			$this->win5 =	$result['win5'];
			$this->win6 =	$result['win6'];
			$this->winb =	$result['winb'];
			$this->lose5 =	$result['lose5'];
			$this->lose6 =	$result['lose6'];
			$this->loseb =	$result['loseb'];
		} else {
            if (!$create) {
                throw new InvalidArgumentException("There is no results for pmid=$pmid and cupId=$cupId");
            }

            $this->pmid =   $pmid;
            $this->cupId =  $cupId;
		}
	}

	public function update() {
		ResultTableDBClient::update($this);
	}

    public function store() {
        ResultTableDBClient::insert($this);
        $this->id = mysql_insert_id();
    }

    public function getId() {
		return $this->id;
	}

	public function getPmid() {
		return $this->pmid;
	}

    public function getPlace() {
        return $this->place;
    }

    public function setPlace($place) {
        $this->place = $place;
    }

    public function getGames() {
		return $this->games;
	}

	public function getPoints() {
		return $this->points;
	}

	public function getCupId() {
		return $this->cupId;
	}

	public function getWin5() {
		return $this->win5;
	}

	public function getWin6() {
		return $this->win6;
	}

	public function getWinb() {
		return $this->winb;
	}

	public function getWin() {
		return $this->win5 + $this->win6 + $this->winb;
	}

	public function getLose5() {
		return $this->lose5;
	}

	public function getLose6() {
		return $this->lose6;
	}

	public function getLoseb() {
		return $this->loseb;
	}

	public function getLose() {
		return $this->lose5 + $this->lose6 + $this->loseb;
	}

    /**
     * return average count of points per game
     * @return double
     */
    public function getAverage() {
       return ($this->games > 0) ? $this->points / $this->games : 0;
    }

	public function getPlayer() {
		if ($this->playerLoaded) return $this->player;
		$this->player = Player::getById($this->pmid);
		$this->playerLoaded = true;
		return $this->player;
	}

    public function recalculate() {
		$this->games =  Game::countGamesFor($this->pmid, $this->cupId);
        $this->win5 =   Game::countWin5For($this->pmid, $this->cupId);
        $this->win6 =   Game::countWin6For($this->pmid, $this->cupId);
        $this->winb =   Game::countWinbFor($this->pmid, $this->cupId);
        $this->lose5 =  Game::countLose5For($this->pmid, $this->cupId);
        $this->lose6 =  Game::countLose6For($this->pmid, $this->cupId);
        $this->loseb =  Game::countLosebFor($this->pmid, $this->cupId);
        $this->points = $this->win5 * self::WIN5 +
                        $this->win6 * self::WIN6 +
                        $this->winb * self::WINB +
                        $this->lose5 * self::LOSE5 +
                        $this->lose6 * self::LOSE6 +
                        $this->loseb * self::LOSEB;
	}

    /**
     * Special function for not generated results.
	 * Note that it uses hard
	 * @param $cupId
	 * @return ResultTable[]
     */
    public static function generate($cupId) {
        $cup = CupFactory::getCupById($cupId);
        // FIXME
		$players = $cup->getPlayersHard();
        $results = array();
        foreach ($players as $player) {
            $result = new ResultTable($player->getId(), $cupId, true);
            $result->recalculate();
            $results[] = $result;
        }

        self::sort($results);

        return $results;
    }

	/**
	 * Generates statistics for cup and stores it in database.
	 * Only for not generated results!!!
	 * @param $cupId
	 * @return ResultTable[]
	 */
    public static function generateAndStore($cupId) {
        $results = self::generate($cupId);

        foreach ($results as $result) {
            $result->store();
        }

        return $results;
    }

    /**
     * sets place property according to this sorting using compareTo
     * think about sorting the array by places...
	 * Assuming -1 as "less" and 1 as "more"
     * @param ResultTable[] $array
	 * @return ResultTable[] sorted $array
     */
    public static function sort(&$array) {
		Sorting::qsort($array);
        foreach ($array as $place=>$table) {
            $table->setPlace($place + 1);
        }

		return $array;
    }

    /**
     * Returns statistics for $cup as ordered by place array
     * @param Cup $cup
     * @return ResultTable[]
     */
    public static function getForCup($cup) {
        $count = count($cup->getPlayers());
        $result = array();
        for ($place = 1; $place <= $count; $place++) {
            $req = ResultTableDBClient::selectPmidByPlaceInCup($place, $cup->getId());
            if ($pm = mysql_fetch_assoc($req)) {
                $result[] = new ResultTable($pm['pmid'], $cup->getId());
            }
        }

        return $result;
    }

	/**
	 * Return array of statistics rows
	 * @param Cup $cup
	 * @return ResultTable[]
	 */
    public static function getForCupLite(Cup $cup) {
        $result = array();
        $players = $cup->getPlayers();
        foreach ($players as $player) {
            $result[] = new ResultTable($player->getId(), $cup->getId());
        }

        return $result;
    }

	/**
	 * Recalculates the whole results table for the cup
	 * @param Cup $cup
	 * @param array $pmids [optional] array of pmid whos tables are to recalculate
	 * @return ResultTable[]
	 */
	public static function recalculateForCup(Cup $cup, $pmids = array()) {
		$stats = self::getForCup($cup);
		$all = (count($pmids) == 0);
		foreach ($stats as $stat) {
			if ($all || array_contains($pmids, $stat->getPmid())) {
				$stat->recalculate();
			}
		}

		self::sort($stats);
		foreach ($stats as $stat) {
			$stat->update();
		}

		return $stats;
	}

    public function compareTo(IComparable $other) {
		if (!($other instanceof ResultTable)) {
			throw new Exception('Unable to compare');
		}

        if ($this->getCupId() != $other->getCupId()) {
            throw new InvalidArgumentException('Unable to compare results of different cups');
        }

        if ($this->getPmid() == $other->getPmid()) {
            return 0;
        }

        $priority = array(
            'Points', '-Games', 'Win', 'Win5', 'Win6', 'Winb',
            'Lose', 'Loseb', 'Lose6', 'Lose5'
        );

        foreach ($priority as $attr) {
            $negate = false;
            if (preg_match('/^-/', $attr)) {
                $attr = substr($attr, 1);
                $negate = true;
            }

            $thisValue =    call_user_method('get' . $attr, &$this);
            $otherValue =   call_user_method('get' . $attr, &$other);

            $cmpResult = ($thisValue == $otherValue) ? 0 :
                            (($thisValue < $otherValue) ? 1 : -1);

            if ($negate) $cmpResult *= -1;

            if ($cmpResult != 0) return $cmpResult;
        }

        // this code may be reached only if all properties listed above are equal

        $gamesTO =  Game::getByPmidsAndCup(
                        $this->getPmid(),
                        $other->getPmid(),
                        $this->getCupId()
                    );

        $gamesOT =  Game::getByPmidsAndCup(
                        $other->getPmid(),
                        $this->getPmid(),
                        $this->getCupId()
                    );

        
        $scoreThis = 0;
        $scoreOther = 0;
        $shotsThis = 0;
        $shotsOther = 0;
        
        foreach ($gamesTO as $game) {
            $shotsThis += $game->getScore1();
            $shotsOther += $game->getScore2();
            if ($game->getScore1() > $game->getScore2()) {
                switch ($game->getScore1()) {
                case 5:
                    $scoreThis += self::WIN5;
                    $scoreOther += self::LOSE5;
                    break;
                case 6:
                    $scoreThis += self::WIN6;
                    $scoreOther += self::LOSE6;
                    break;
                default:
                    if ($game->getScore1() > 6) {
                        $scoreThis += self::WINB;
                        $scoreOther += self::LOSEB;
                    }
                }
            } elseif ($game->getScore1() < $game->getScore2()) {
                switch ($game->getScore2()) {
                case 5:
                    $scoreThis += self::LOSE5;
                    $scoreOther += self::WIN5;
                    break;
                case 6:
                    $scoreThis += self::LOSE6;
                    $scoreOther += self::WIN6;
                    break;
                default:
                    if ($game->getScore1() > 6) {
                        $scoreThis += self::LOSEB;
                        $scoreOther += self::WINB;
                    }
                }
            }
        }

        foreach ($gamesOT as $game) {
            $shotsOther += $game->getScore1();
            $shotsThis += $game->getScore2();
            if ($game->getScore1() < $game->getScore2()) {
                switch ($game->getScore2()) {
                case 5:
                    $scoreThis += self::WIN5;
                    $scoreOther += self::LOSE5;
                    break;
                case 6:
                    $scoreThis += self::WIN6;
                    $scoreOther += self::LOSE6;
                    break;
                default:
                    if ($game->getScore1() > 6) {
                        $scoreThis += self::WINB;
                        $scoreOther += self::LOSEB;
                    }
                }
            } elseif ($game->getScore1() > $game->getScore2()) {
                switch ($game->getScore1()) {
                case 5:
                    $scoreThis += self::LOSE5;
                    $scoreOther += self::WIN5;
                    break;
                case 6:
                    $scoreThis += self::LOSE6;
                    $scoreOther += self::WIN6;
                    break;
                default:
                    if ($game->getScore1() > 6) {
                        $scoreThis += self::LOSEB;
                        $scoreOther += self::WINB;
                    }
                }
            }
        }

        if ($scoreThis != $scoreOther) {
            return ($scoreThis < $scoreOther) ? 1 : -1;
        }

        if ($shotsThis != $shotsOther) {
            return ($shotsThis < $shotsOther) ? 1 : -1;
        }

        // otherwise
        // system is impotent
        return 0;
    }
}
?>
