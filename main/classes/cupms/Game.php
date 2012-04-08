<?php

require_once dirname(__FILE__).'/../../includes/assertion.php';
require_once dirname(__FILE__).'/../../includes/common.php';

require_once dirname(__FILE__).'/../db/GameDBClient.php';

/**
 * Description of Game
 *
 * @author Malkovsky Nikolay
 * @author Artyom Grigoriev
 * @author Innokenty Shuvalov
 */
class Game {
    private $id;
    private $cupId;
    private $cup;
    private $cupLoaded = false;
    private $stage;
    private $tour;
    private $time;
    private $type;

    private $pmids;
    private $players;
    private $scores;

    const GAME_TYPE_COMMON = 'NORMAL';
    const GAME_TYPE_TECHNICAL = 'TECHNICAL';
    const GAME_TYPE_FATALITY = 'FATALITY';
    const GAME_TYPE_DRAW = 'DRAW';

    public static $GAME_TYPES = array(
        self::GAME_TYPE_COMMON => '0',
        self::GAME_TYPE_TECHNICAL => 't',
        self::GAME_TYPE_FATALITY => 'f',
        self::GAME_TYPE_DRAW => 'd'
    );

    /**
     * @var int like param $which must be 1, 2 or 0 in case of tie
     */
    private $victorIndex;
    
    /**
     * @var int -1 in case of tie
     */
    private $victorId;
    
    /**
     * @var Player null in case of tie
     */
    private $victor;

    /**
     * @var int like param $which must be 1, 2 or 0 in case of tie
     */
    private $looserIndex;
    
    /**
     * @var int -1 in case of tie
     */
    private $looserId;
    
    /**
     * @var Player null in case of tie
     */
    private $looser;

    private $prevGameIds = array();
	private $prevGames = array();
	private $prevGameLoaded = array(false, false);

	private $parentGameId;
	private $parentGameIdLoaded = false;
	private $parentGame;
	private $parentGameLoaded = false;

	private $isLeft;

	private $trace = array();
	private $traceLoaded = array();

    public function  __construct($id, $data = null) {
		if($id <= 0) {
            throw new InvalidArgumentException("Id=$id is incorrect value");
        } else  {
            $c = $data;
			if ($data == null) {
				$req = GameDBClient::selectById($id);
				$c = mysql_fetch_assoc($req);
			}
			if ($c) {
                $this->id =             $c['id'];
                $this->cupId =          $c['cup_id'];
                $this->stage =          $c['stage'];
                $this->tour =           $c['tour'];
                $this->pmids[1] =       $c['pmid1'];
                $this->pmids[2] =       $c['pmid2'];
                $this->scores[1] =      $c['score1'];
                $this->scores[2] =      $c['score2'];
                $this->prevGameIds[1] = $c['prev_game_id1'];
                $this->prevGameIds[2] = $c['prev_game_id2'];
                $this->time =           $c['time'];
                $this->type =			$c['is_tech'];
            } else {
                throw new InvalidArgumentException("There is no game with id=$id");
            }
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getCupId() {
        return $this->cupId;
    }

    public function getCup() {
		if ($this->cupLoaded)
			return $this->cup;
		$this->cup = CupFactory::getCupById($this->getCupId());
		$this->cupLoaded = true;
		return $this->cup;
    }

    public function setCupId($cup_id) {
        if($cup_id <= 0) {
            throw new InvalidArgumentException("cup_id=$cup_id is incorrect value");
        } else {
            $this->cupId = $cup_id;
        }
    }

    public function getStage() {
        return $this->stage;
    }

    public function setStage($stage) {
        if($stage < 0) {
            throw new InvalidArgumentException("stage=$stage is incorrect value");
        } else {
            $this->stage = $stage;
        }
    }

    public function getTour() {
        return $this->tour;
    }

    public function setTour($tour) {
        if($tour < 0) {
            throw new InvalidArgumentException("tour=$tour is incorrect value");
        } else {
            $this->tour = $tour;
        }
    }

    public function getPmid($which) {
        return $this->pmids[$which];
    }

    public function setPmid($pmid, $which) {
        if($pmid <= 0) {
            throw new InvalidArgumentException("Id=$pmid is incorrect value");
        } else {
            $this->pmids[$which] = $pmid;
        }
    }

    public function getPmid1() {
        return $this->getPmid(1);
    }
    
    public function setPmid1($pmid) {
        return $this->setPmid($pmid, 1);
    }
    
    public function getPmid2() {
        return $this->getPmid(2);
    }
    
    public function setPmid2($pmid) {
        return $this->setPmid($pmid, 2);
    }

    public function getScore($which) {
        $this->checkWhich($which, 'getScore');
        return $this->scores[$which];
    }

    public function setScore($which, $score) {
        $this->checkWhich($which, 'setScore');
        if ($score < 0) {
            throw new InvalidArgumentException("Score must not be less than zero");
        } else {
            $this->scores[$which] = $score;
        }
    }

    public function getScore1() {
        return $this->getScore(1);
    }

    public function setScore1($score1) {
        $this->setScore(1, $score1);
    }

    public function getScore2() {
        return $this->getScore(2);
    }

    public function setScore2($score2) {
        $this->setScore(2, $score2);
    }

    public function calculateVictorAndLooserIndices() {
        if ($this->victorIndex === null) {
            if ($this->getScore(1) > $this->getScore(2)) {
                $this->victorIndex = 1;
                $this->looserIndex = 2;
            } elseif ($this->getScore(1) < $this->getScore(2)) {
                $this->victorIndex = 2;
                $this->looserIndex = 1;
            } else {
                $this->victorIndex = 0;
                $this->looserIndex = 0;
            }
        }
    }

    /**
     * sets class instance's field which shows which player is a winner, and returns it
     * @return int victorIndex. like param $which is 1, 2 or 0 in case of tie
     */
    public function getVictorIndex() {
        $this->calculateVictorAndLooserIndices();
        return $this->victorIndex;
    }

    public function getVictorId() {
        if ($this->victorId === null) {
            $i = $this->getVictorIndex();
            if ($i) {
                $this->victorId = $this->getPmid($i);
            } else {
                $this->victorId = 0;
            }
        }
        return $this->victorId;
	}

    public function getVictor() {
        if ($this->victor === null) {
            $i = $this->getVictorIndex();
            if ($i) {
                $this->victor = $this->getPlayer($i);
            }
        }
        return $this->victor;
    }

    /**
     * sets class instance's field which shows which player is a looser, and returns it
     * @return int looserIndex
     */
    public function getLooserIndex() {
        $this->calculateVictorAndLooserIndices();
        return $this->looserIndex;
    }

    public function getLooserId() {
        if ($this->looserId === null) {
            $i = $this->getLooserIndex();
            if ($i) {
                $this->looserId = $this->getPmid($i);
            } else {
                $this->looserId = 0;
            }
        }
        return $this->looserId;
	}

    public function getLooser() {
        if ($this->looser === null) {
            $i = $this->getLooserIndex();
            if ($i) {
                $this->looser = $this->getPlayer($i);
            }
        }
        return $this->looser;
    }

    public function getTime() {
        return $this->time;
    }

    public function setTime($time) {
        $this->time = $time;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        if (!array_contains(self::$GAME_TYPES, $type)) {
            throw new InvalidArgumentException(self::getInvalidGameTypeMessage());
        }
        $this->type = $type;
    }

    public static function getInvalidGameTypeMessage() {
        $message = "The Game::type field must be one of these:\n";
        $i = 0;
        $length = count(self::$GAME_TYPES);
        foreach (self::$GAME_TYPES as $defaultGameType) {
            $message .= $defaultGameType . (++$i < $length ? ',' : '');
        }
        return $message;
    }

    public function getPrevGameId1() {
        return $this->getPrevGameId(1);
    }

    public function getPrevGameId2() {
        return $this->getPrevGameId(2);
    }
	
	/**
	 * @param $which must be 1 or 2
	 */
    public function getPrevGameId($which) {
		$this->checkWhich($which, 'getPrevGameId');
        return $this->prevGameIds[$which];
    }

    public function setPrevGameIds($prevId1, $prevId2) {
        $this->prevGameIds[1] = $prevId1;
        $this->prevGameIds[2] = $prevId2;
    }

    private function loadPlayers() {
        if (!$this->players) {
            foreach (array(1, 2) as $which) {
                $this->players[$which] = Player::getById($this->getPmid($which));
            }
        }
    }

	public function getPlayer($which) {
        $this->checkWhich($which, 'getPlayer');
        $this->loadPlayers();
		return $this->players[$which];
	}

	public function getPlayer1() {
		return $this->getPlayer(1);
	}

	public function getPlayer2() {
        return $this->getPlayer(2);
	}

    public function getPlayers() {
        $this->loadPlayers();
        return $this->players;
    }

	/**
	 * @param $which must be 1 or 2
     * @return Game
     */
    public function getPrevGame($which) {
        $this->checkWhich($which, 'Game::getPrevGame');
        if ($this->prevGameLoaded[$which])
			return $this->prevGames[$which];

		$this->loadPrevGame($which);
		return $this->prevGames[$which];
    }

    private function loadPrevGame($which) {
		if (!$this->prevGameLoaded[$which]) {
            $prevGameId = $this->getPrevGameId($which);
            if ($prevGameId > 0) {
                $this->prevGames[$which] = new Game($prevGameId);
            }
			$this->prevGameLoaded[$which] = true;
		}
	}
	
	private function loadPrevGames() {
		foreach (array(1, 2) as $which) {
			$this->loadPrevGame($which);
		}
	}

	/**
     * @return Game
     */
    public function getPrevGame1() {
        return $this->getPrevGame(1);
    }

    /**
     * @return Game
     */
    public function getPrevGame2() {
        return $this->getPrevGame(2);
    }

    /**
     * @return Game[]
     */
    public function getPrevGames() {
		$this->loadPrevGames();
        return $this->prevGames;
    }

	/**
	 * @param $which must be 1 or 2
	 */
	public function setPrevGameId($which, $id) {
		$this->checkWhich($which, 'Game::setPrevGameId');
		$this->prevGameIds[$which] = $id;
		$this->prevGameLoaded[$which] = false;
	}

	public function setPrevGameId1($id) {
		$this->setPrevGameId(1, $id);
	}

	public function setPrevGameId2($id) {
		$this->setPrevGameId(2, $id);
	}

	/**
	 * @param $which must be 1 or 2
	 */
	public function setPrevGame($which, Game $game) {
		$this->checkWhich($which, 'setPrevGame');
		$this->prevGames[$which] = $game;
		$this->prevGameIds[$which] = $game->getId();
		$this->prevGameLoaded[$which] = true;
	}

	public function setPrevGame1($id) {
		$this->setPrevGame(1, $id);
	}

	public function setPrevGame2($id) {
		$this->setPrevGame(2, $id);
	}

	public function getParentGameId() {
		if ($this->parentGameIdLoaded) return $this->parentGameId;

		if ($this->isRegular()) {
			throw new Exception('This is not play-off game!');
		}

		if ($this->getStage() == 1 || $this->getStage() == 3) {
			$this->parentGameId = 0;
			$this->parentGame = null;
			$this->parentGameIdLoaded = true;
			$this->parentGameLoaded = true;
			$this->isLeft = false;
			return $this->parentGameId;
		}

		$result = GameDBClient::getParentGameIdFor($this->getId());
		$this->parentGameId = $result['parent_id'];
		$this->isLeft = $result['is_left'];
		$this->parentGameIdLoaded = true;
		return $this->parentGameId;
	}

	public function getParentGame() {
		if ($this->parentGameLoaded) return $this->parentGame;

		$id = $this->getParentGameId();
		if (!$id) {
			$this->parentGame = null;
			$this->parentGameLoaded = true;
			return $this->parentGame;
		}

		$this->parentGame = new Game($id);
		$this->parentGameIdLoaded = true;
		return $this->parentGame;
	}

	/**
	 * returns true iff this game is prev1 for its parent
	 * throws Exception if game is in regularity
	 */
	public function isLeft() {
		if ($this->isRegular()) {
			throw new Exception('This is not play-off game!');
		}

		if ($this->parentGameIdLoaded) return $this->isLeft;
		
		$this->getParentGameId();
		return $this->isLeft();
	}

	public function isRegular() {
		return $this->getStage() == 0;
	}

	public function swapPrevGames() {
		swap($this->pmids[1], $this->pmids[2]);
		swap($this->prevGameIds[1], $this->prevGameIds[2]);
		swap($this->scores[1], $this->scores[2]);
		$this->prevGameLoaded = array(false, false);
	}

	/**
	 * updates db data concerning to this game
	 */
    public function update() {
        return GameDBClient::update($this);
    }

    /**
     * @return instance of Game with `cup_id`=$cupId and `stage`=1
     * @return null if there is no final game for such cup in DB
     * @param <type> $cupId
     */
    public static function getFinalFor($cupId) {
        $req = GameDBClient::selectFinalFor($cupId);
		$result = null;
		if ($g = mysql_fetch_assoc($req)) {
			try {
				$result = new Game($g['id']);
			} catch (InvalidArgumentException $e) {
				$result = null;
			}
		}

		return $result;
    }

    /**
     * @return instance of Game with `cup_id`=$cupId and `stage`=3
     * @return null if there is no bronze game for such cup in DB
     * @param <type> $cupId
     */
    public static function getBronzeFor($cupId) {
        $req = GameDBClient::selectBronzeFor($cupId);
		$result = null;
        if ($g = mysql_fetch_assoc($req)) {
            try {
                $result = new Game($g['id']);
			} catch (InvalidArgumentException $e) {
				$result = null;
			}
		}

		return $result;
    }

    public static function getByPmidsAndCup($pmid1, $pmid2, $cupId) {
        $req = GameDBClient::selectByPmidsAndCup($pmid1, $pmid2, $cupId);
		$result = array();
        if ($g = mysql_fetch_assoc($req)) {
            try {
                $result = new Game($g['id']);
            } catch (Exception $e) {
                $result = null;
            }
        }

		return $result;
    }

	public static function countGamesFor($pmid, $cupId) {
        $pmid = intval($pmid);
        $cupId = intval($cupId);

        $req = GameDBClient::selectCountGamesFor($pmid, $cupId);
        return mysql_result($req, 0, 0);
    }

    public static function countWin5For($pmid, $cupId) {
        $pmid = intval($pmid);
        $cupId = intval($cupId);

        $req = GameDBClient::selectCountWin5For($pmid, $cupId);
        return mysql_result($req, 0, 0);
    }

    public static function countWin6For($pmid, $cupId) {
        $pmid = intval($pmid);
        $cupId = intval($cupId);

        $req = GameDBClient::selectCountWin6For($pmid, $cupId);
        return mysql_result($req, 0, 0);
    }

    public static function countWinbFor($pmid, $cupId) {
        $pmid = intval($pmid);
        $cupId = intval($cupId);

        $req = GameDBClient::selectCountWinbFor($pmid, $cupId);
        return mysql_result($req, 0, 0);
    }

    public static function countLose5For($pmid, $cupId) {
        $pmid = intval($pmid);
        $cupId = intval($cupId);

        $req = GameDBClient::selectCountLose5For($pmid, $cupId);
        return mysql_result($req, 0, 0);
    }

    public static function countLose6For($pmid, $cupId) {
        $pmid = intval($pmid);
        $cupId = intval($cupId);

        $req = GameDBClient::selectCountLose6For($pmid, $cupId);
        return mysql_result($req, 0, 0);
    }

    public static function countLosebFor($pmid, $cupId) {
        $pmid = intval($pmid);
        $cupId = intval($cupId);

        $req = GameDBClient::selectCountLosebFor($pmid, $cupId);
        return mysql_result($req, 0, 0);
    }


	public static function countAll() {
		return GameDBClient::countAll();
	}

	/**
	 * Creates a new instance of game in database
	 * @return New Game instance associated with created database record.
	 */
	public static function create($cupId, $stage, $tour = 0, $pmid1 = 0, $pmid2 = 0, $score1 = 0, $score2 = 0,
			$time = null, $isTech = false) {
		
		if (!$time) {
			$time = date('Y-m-d H:i:s');
		}
		GameDBClient::insert($cupId, $stage, $tour, $pmid1, $pmid2, $score1,
					$score2, $time, $isTech);
		
		if ($stage == 3) {
			$cup = CupFactory::getCupById($cupId);
			if (!($cup instanceof CupPlayoff)) {
				throw new InvalidCupTypeException("expected CupPlayoff, found {$cup->getType()}");
			}
			$final = $cup->getFinal();
			
		}
	
		return new Game(mysql_insert_id());
	}

	public static function getByStageAndCup($stage, CupPlayoff $playoff) {
		$cup_id = $playoff->getId();
		$iterator = GameDBClient::getByStageAndCupId($stage, $cup_id);
		$games = array();
		while ($iterator->valid()) {
			$data = $iterator->current();
			$games[] = new Game($data['id'], $data);
			$iterator->next();
		}
		return $games;
	}

	public function toJSON() {
		return json(array(
			'status' => 'ok',
			'game_id' => $this->getId(),
			'pmid1' => $this->getPmid(1),
			'pmid2' => $this->getPmid(2),
			'name1' => $this->getPlayer(1) ? $this->getPlayer(1)->getShortName() : '',
			'name2' => $this->getPlayer(2) ? $this->getPlayer(2)->getShortName() : '',
			'score1' => $this->getScoreOrType(1),
			'score2' => $this->getScoreOrType(2),
            'type' => $this->getType()
		));
	}

    public function getScoreOrType($which) {
        $this->checkWhich($which, 'getScoreOrType');

        if (!$this->getType() || $this->getLooserIndex() == $which) {
            return $this->getScore($which);
        }
        return $this->getType();
    }

	private function getPrevGameForPlayer($pmid) {
		foreach ($this->getPrevGames() as $game) {
			if ($this->isPrevGameForPlayer($game, $pmid))
				return $game;
		}
	}
	
	private function isPrevGameForPlayer($game, $pmid) {
		if ($game != null && $pmid > 0 && ($game->getPmid1() == $pmid || $game->getPmid2() == $pmid))
			return true;
		return false;
	}

	/**
	 * @param $which must be 1 or 2
	 */
	public function tracePrevGames($which = null) {
		if (!$which)
			return array_merge($this->tracePrevGames(1), $this->tracePrevGames(2));

		$this->checkWhich($which, 'tracePrevGames');
		if ($this->traceLoaded[$which])
			return $this->trace[$which];
		
		$pmid = $which == 1 ? $this->getPmid1() : $this->getPmid2();
		$this->trace[$which] = array();
		$prev = $this;

		while ($prev != null) {
			$this->trace[$which][] = $prev;
			$prev = $prev->getPrevGameForPlayer($pmid);
		}
		$this->traceLoaded[$which] = true;

		return $this->trace[$which];
	}

	public function tracePrevGames1() {
		return $this->tracePrevGames(1);
	}

	public function tracePrevGames2() {
		return $this->tracePrevGames(2);
	}

	public function tracePrevGamesToString() {
		$result = '';
		$i = 0;
		$count = count($this->tracePrevGames());

		foreach ($this->tracePrevGames() as $game) {
			$result .= $game->getId();
			if ($i < ($count - 1))
				$result .= ',';
			$i++;
		}

		return $result;
	}

	public function tracePrevGameIdsToArray() {
		$result = array();
		foreach ($this->tracePrevGames() as $game) {
			$result[] = $game->getId();
		}

		return $result;
	}

    private function checkWhich($which, $methodName) {
        if ($which != 1 && $which != 2) {
            throw new InvalidArgumentException("parameter \$which in method
				Game::$methodName must be either 1 or 2, not {$which}");
        }
    }
}
?>
