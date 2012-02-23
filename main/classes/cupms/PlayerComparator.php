<?php

require_once dirname(__FILE__).'/../../includes/mysql.php';
require_once dirname(__FILE__).'/Player.php';
require_once dirname(__FILE__).'/../db/GameDBClient.php';

/**
 * @author Innokenty
 */

class PlayerComparator {

    const FIRST_PLAYER = true;
    const SECOND_PLAYER = false;

    const SCORE_FIVE = Player::SCORE_FIVE;
    const SCORE_SIX = Player::SCORE_SIX;
    const BALANCE = Player::BALANCE;

    const NORMAL_VICTORY = 'normal';
    const FATALITY = Player::FATALITY;
    const TECHNICAL = Player::TECHNICAL;

    const TOTAL = Player::TOTAL;

    private $pm1;
    private $pm2;

    private $pmId1;
    private $pmId2;

    private $dataLoaded = false;

    private $draws = array();

	private $firstRegularity = array();
    private $firstPlayOffs = array();
    private $firstPlayOffsFatality = array();
    private $firstPlayOffsTechnical = array();
    
    private $secondRegularity = array();
    private $secondPlayOffs = array();
    private $secondPlayOffsFatality = array();
    private $secondPlayOffsTechnical = array();

	private $stages = array();
	private $cups = array();


    public function __construct(Player $player1, Player $player2) {
        $this->pm1 = $player1;
        $this->pm2 = $player2;
        $this->pmId1 = $player1->getId();
        $this->pmId2 = $player2->getId();
    }

    private function init($maxStage = 0) {
        if ($maxStage == 3)
            $maxStage = 4;

        $array[PlayerComparator::TOTAL] = 0;
        $array[PlayerComparator::SCORE_FIVE] = 0;
        $array[PlayerComparator::SCORE_SIX] = 0;
        $array[PlayerComparator::BALANCE] = 0;
        $array[PlayerComparator::FATALITY] = 0;
        $array[PlayerComparator::TECHNICAL] = 0;

        $this->firstRegularity = $array;
        $this->secondRegularity = $array;

        for ($i = $maxStage; $i >= 1; $i /= 2) {
            $arrayPlayOff["$i"] = 0;
        }
        $arrayPlayOff["3"] = 0;

        $this->stages = $arrayPlayOff;
        $this->stages["0"] = 0;

        $arrayPlayOff[PlayerComparator::TOTAL] = 0;

        $this->firstPlayOffs = $arrayPlayOff;
        $this->firstPlayOffsFatality = $arrayPlayOff;
        $this->firstPlayOffsTechnical = $arrayPlayOff;
        $this->secondPlayOffs = $arrayPlayOff;
        $this->secondPlayOffsFatality = $arrayPlayOff;
        $this->secondPlayOffsTechnical = $arrayPlayOff;

        $this->draws = $arrayPlayOff;
        $this->draws["0"] = 0;
    }

    private function getTwoPlayersMovement($pmId1, $pmId2){
        $today = date("Y-m-d");
        $start = "2007-10-23";
        return array(
            1 => RatingTable::getRatingMovementInterval($start, $today, 1, $pmId1),
            2 => RatingTable::getRatingMovementInterval($start, $today, 1, $pmId2),
        );
    }

    public function loadGames($reload = false) {
        if ($reload || !$this->dataLoaded) {
            $req = GameDBClient::selectGamesBetween($this->pmId1, $this->pmId2);
            
            $firstLine = true;
            while ($game = mysql_fetch_assoc($req)) {
                if ($firstLine) {
					$this->init($game['stage']);
                    $firstLine = false;
                }
				$this->handleGame($game);
			}
            
            $this->dataLoaded = true;
        }
    }

    private function handleGame($game) {
		$stage = $game['stage'];
		$this->stages["$stage"] ++;
		
		if (!array_contains($this->cups, $game['cup_id']))
			$this->cups[] = $game['cup_id'];

        if ($game['score1'] == $game['score2']) {
			 $this->draws["$stage"] ++;
			 $this->draws[PlayerComparator::TOTAL] ++;
		} else {
			$isRegularity = $stage == 0;
			$maxScore = max($game['score1'], $game['score2']);
			$firstPlayerWon = ($maxScore == $game['score1'] && $game['pmid1'] == $this->pmId1) ||
							 ($maxScore == $game['score2'] && $game['pmid2'] == $this->pmId1);
			if ($firstPlayerWon)
				if ($isRegularity)
					$result = $this->firstRegularity;
				else
					$result = $this->firstPlayOffs;
			else
				if ($isRegularity)
					$result = $this->secondRegularity;
				else
					$result = $this->secondPlayOffs;

			$result[PlayerComparator::TOTAL] ++;

			if ($isRegularity) {
				switch ($game['tech']) {
					case Game::GAME_TYPE_TECHNICAL : $result[PlayerComparator::TECHNICAL] ++; break;
					case Game::GAME_TYPE_FATALITY : $result[PlayerComparator::FATALITY] ++;  break;
					default :
						switch ($maxScore) {
							case 5 : $result[PlayerComparator::SCORE_FIVE] ++; break;
							case 6 : $result[PlayerComparator::SCORE_SIX] ++; break;
							default : $result[PlayerComparator::BALANCE] ++;
						}
				}
				if($firstPlayerWon)
					$this->firstRegularity = $result;
				else
					$this->secondRegularity = $result;
			} else {
				$result["$stage"] ++;

				if($firstPlayerWon) {
					$this->firstPlayOffs = $result;
					switch ($game['tech']) {
						case Game::GAME_TYPE_TECHNICAL:
							$this->firstPlayOffsTechnical[PlayerComparator::TOTAL] ++;
							$this->firstPlayOffsTechnical["$stage"] ++;
							break;
						case Game::GAME_TYPE_FATALITY:
							$this->firstPlayOffsFatality[PlayerComparator::TOTAL] ++;
							$this->firstPlayOffsFatality["$stage"] ++;
							break;
					}
				} else {
					$this->secondPlayOffs = $result;
					switch ($game['tech']) {
						case Game::GAME_TYPE_TECHNICAL:
							$this->secondPlayOffsTechnical[PlayerComparator::TOTAL] ++;
							$this->secondPlayOffsTechnical["$stage"] ++;
							break;
						case Game::GAME_TYPE_FATALITY:
							$this->secondPlayOffsFatality[PlayerComparator::TOTAL] ++;
							$this->secondPlayOffsFatality["$stage"] ++;
							break;
					}
				}
			}
		}
    }


    /**
	 * @return array an associative array of pairs
	 * "stage" => "number of games on that stage",
	 * "0" => "number of regularity games"
	 */
	public function getStages($reload = false) {
		$this->loadGames($reload);
		return $this->stages;
	}

	/*
	 * @return array array of ids of cups both players took part in
	 */
	public function getCups($reload = false) {
		$this->loadGames($reload);
		return $this->cups;
	}


	public function countGames($reload = false) {
        $this->loadGames($reload);
        return $this->firstPlayOffs[PlayerComparator::TOTAL] +
				$this->firstRegularity[PlayerComparator::TOTAL] +
				$this->secondPlayOffs[PlayerComparator::TOTAL] +
				$this->secondRegularity[PlayerComparator::TOTAL] +
				$this->draws[PlayerComparator::TOTAL];
    }

    public function countRegularityGames($reload = false) {
        $this->loadGames($reload);
        return  $this->firstRegularity[PlayerComparator::TOTAL] +
                $this->secondRegularity[PlayerComparator::TOTAL] +
				$this->draws["0"];
    }

    /**
     * Calculates number of games on 1/$stage play-off round
     * @param <int> $stage
     * If the parameter is not set or equals zero
     * the metod returns total number of play-off games.
	 * Use getStages() to find all the available values for the parameter `$stage`
     * @return <int>
     */
    public function countPlayOffGames($stage = 0, $reload = false) {
        if ($stage < 0)
            throw new InvalidArgumentException("parameter \$stage in method
                countPlayOffGames(\$stage) must be >= 0 !!");
        else {
            $this->loadGames($reload);
            if ($stage != PlayerComparator::TOTAL)
				$stage = "$stage";
			return  $this->firstPlayOffs[$stage] +
					$this->secondPlayOffs[$stage] +
					$this->draws[$stage];
		}
    }

	/**
	 * @param $stage int if $stage is not set or equals the constant TOTAL
	 * the method returns the total number of draws between players.
	 * Otherweise it returns the number of draws on 1/$stage level
	 * Use getStages() to find all the available values for the parameter
	 * @return int
	 */
	public function countDraws($stage = PlayerComparator::TOTAL, $reload = false) {
		if ($stage < 0)
            throw new InvalidArgumentException("parameter \$stage in method
                countPlayOffGames(\$stage) must be >= 0 !!");
        else {
            $this->loadGames($reload);
			if ($stage != PlayerComparator::TOTAL)
				$stage = "$stage";
			return $this->draws[$stage];
		}
	}

	/**
     * @param boolean $player
     * use the constants FIRST_PLAYER or SECOND_PLAYER
     * @param String $typeOfVictory
     * unnecessary parameter. set it only if you want to count some sort
     * of victories instead of the whole number of them.
	 * Use constants TOTAL (default) SCORE_FIVE, SCORE_SIX,
	 * BALANCE, FATALITY or TECHNICAL for this parameter!
     * @return int
     */
    public function countRegularityVictories($player, $typeOfVictory = PlayerComparator::TOTAL, $reload = false) {
        if ($typeOfVictory != PlayerComparator::SCORE_FIVE &&
            $typeOfVictory != PlayerComparator::SCORE_SIX &&
            $typeOfVictory != PlayerComparator::BALANCE &&
            $typeOfVictory != PlayerComparator::TOTAL &&
            $typeOfVictory != PlayerComparator::FATALITY &&
            $typeOfVictory != PlayerComparator::TECHNICAL &&
            $player != PlayerComparator::FIRST_PLAYER &&
			$player != PlayerComparator::SECOND_PLAYER) {
            
			$message = "PlayerComparator::countRegularityVictories(\$player, \$typeOfVictory) 
				Usage: \$player and \$typeOfVictory must be constants of this class";
			throw new InvalidArgumentException($message);
		} else {
			$this->loadGames($reload);
            return $player ? $this->firstRegularity[$typeOfVictory] : $this->secondRegularity[$typeOfVictory];
        }
    }

    /**
     * You better use constants of this class as parameters!
     * @param boolean $player
	 * use the constants FIRST_PLAYER or SECOND_PLAYER for this parameter
     * @param int $stage
     * unnecessary parameter. set it only if you want to count victories
     * on a 1/$stage level instead of the whole amount of them.
     * Use getStages() to find all the available values for the parameter `$stage`
     * @param String $typeOfVictory
     * unnecessary parameter. default value  - constant TOTAL.
	 * set constant TECHNICAL if you want to count
     * technical victories or FATALITY for victories by fatality.
     * @return <type>
     */
    public function countPlayOffVictories($player, 
											$stage = PlayerComparator::TOTAL,
											$typeOfVictory = PlayerComparator::NORMAL_VICTORY,
											$reload = false) {
        if ($typeOfVictory != PlayerComparator::NORMAL_VICTORY &&
            $typeOfVictory != PlayerComparator::FATALITY &&
            $typeOfVictory != PlayerComparator::TECHNICAL &&
            $player != PlayerComparator::FIRST_PLAYER &&
			$player != PlayerComparator::SECOND_PLAYER) {
            
			$message = "PlayerComparator::countPlayOffVictories(\$player, \$typeOfVictory)
					Usage: \$player and \$typeOfVictory must be constants of this class";
			throw new InvalidArgumentException($message);

		} else {
			$this->loadGames($reload);
			if ($stage != PlayerComparator::TOTAL)
				$stage = "$stage";
            switch ($typeOfVictory) {
                case PlayerComparator::FATALITY : 
					return $player ? $this->firstPlayOffsFatality[$stage] : $this->secondPlayOffsFatality[$stage];
                    break;
                case PlayerComparator::TECHNICAL : 
					return $player ? $this->firstPlayOffsTechnical[$stage] : $this->secondPlayOffsTechnical[$stage];
                    break;
                default : 
					return $player ? $this->firstPlayOffs[$stage] : $this->secondPlayOffs[$stage];
            }
        }
    }

	public function getFirstPlayer() {
		return $this->pm1;
	}
	public function getSecondPlayer() {
		return $this->pm2;
	}
	public function getFirstPlayerId() {
		return $this->pmId1;
	}
	public function getSecondPlayerId() {
		return $this->pmId2;
	}
	public function getPlayer(int $which) {
		if ($which == 1)
			return $this->pm1;
		else
			return $this->pm2;
	}
	public function getPlayerId(int $which) {
		if ($which == 1)
			return $this->pmId1;
		else
			return $this->pmId2;
	}

//	public function toJSON() {
//		$json = '{';
//		$json .= '"pmid1": "' . $this->getFirstPlayerId() . '",';
//		$json .= '"pmid2": "' . $this->getSecondPlayerId() . '",';
//		$json .= '"pm_name_first": "' . $this->getFirstPlayer()->getName() . '",';
//		$json .= '"pm_name_second": "' . $this->getSecondPlayer()->getName() . '",';
//		$json .= '"games": "' . $this->countGames() . '",';
//		$json .= '"regularity_games": "' . $this->countRegularityGames() . '",';
//		$json .= '"regularity_victories_first": "' . $this->countRegularityVictories(PlayerComparator::FIRST_PLAYER) . '",';
//		$json .= '"regularity_victories_second": "' . $this->countRegularityVictories(PlayerComparator::SECOND_PLAYER) . '",';
//		$json .= '"playoff_games": "' . $this->countPlayOffGames() . '",';
//		$json .= '"playoff_victories_first": "' . $this->countPlayOffVictories(PlayerComparator::FIRST_PLAYER) . '",';
//		$json .= '"playoff_victories_second": "' . $this->countPlayOffVictories(PlayerComparator::SECOND_PLAYER) . '",';
//		$json .= '}';
//
//		return $json;
//	}

	public function toArray() {
		return array (
			'pmid1' => $this->pmId1,
			'pmid2' => $this->pmId2,

			'games_inter_stat' => array (
				'count' => $this->countGames(),

				'regular' => array (
					'count' => $this->countRegularityGames(),

					'v1' => array (
						'total' => $this->countRegularityVictories(PlayerComparator::FIRST_PLAYER),
						'five' => $this->countRegularityVictories(PlayerComparator::FIRST_PLAYER, PlayerComparator::SCORE_FIVE),
						'six' => $this->countRegularityVictories(PlayerComparator::FIRST_PLAYER, PlayerComparator::SCORE_SIX),
						'balance' => $this->countRegularityVictories(PlayerComparator::FIRST_PLAYER, PlayerComparator::BALANCE)
					),

					'v2' => array (
						'total' => $this->countRegularityVictories(PlayerComparator::SECOND_PLAYER),
						'five' => $this->countRegularityVictories(PlayerComparator::SECOND_PLAYER, PlayerComparator::SCORE_FIVE),
						'six' => $this->countRegularityVictories(PlayerComparator::SECOND_PLAYER, PlayerComparator::SCORE_SIX),
						'balance' => $this->countRegularityVictories(PlayerComparator::SECOND_PLAYER, PlayerComparator::BALANCE)
					),
				),

				'playoff' => array (
					'count' => $this->countPlayOffGames(),

					'v1' => array (
						'total' => $this->countPlayOffVictories(PlayerComparator::FIRST_PLAYER)
					),

					'v2' => array (
						'total' => $this->countPlayOffVictories(PlayerComparator::SECOND_PLAYER)
					)
				)
			),

			'games_total_stat' => array (
				'first' => array (
					'count' => $this->pm1->countGames(),

					'v' => array (
						'count' => $this->pm1->countVictoriesPlayOffs() + $this->pm1->countVictoriesRegularity(),
						'regular' => array (
							'count' => $this->pm1->countVictoriesRegularity(),
							'five' => $this->pm1->countVictoriesRegularity5(),
							'six' => $this->pm1->countVictoriesRegularity6(),
							'balance' => $this->pm1->countVictoriesRegularityBalance(),
							'fatality' => $this->pm1->countVictoriesRegularityFatality(),
							'technical' => $this->pm1->countVictoriesRegularityTechnical()
						),
						'playoff' => array (
							'count' => $this->pm1->countVictoriesPlayOffs(),
							// TODO stages!!!!!!!!!
							'fatality' => $this->pm1->countVictoriesPlayOffsFatality(),
							'technical' => $this->pm1->countVictoriesPlayOffsTechnical()
						)
					),

					'd' => array (
						'count' => $this->pm1->countDefeatsPlayOffs() + $this->pm1->countDefeatsRegularity(),
						'regular' => array (
							'count' => $this->pm1->countDefeatsRegularity(),
							'five' => $this->pm1->countDefeatsRegularity5(),
							'six' => $this->pm1->countDefeatsRegularity6(),
							'balance' => $this->pm1->countDefeatsRegularityBalance(),
							'fatality' => $this->pm1->countDefeatsRegularityFatality(),
							'technical' => $this->pm1->countDefeatsRegularityTechnical()
						),
						'playoff' => array (
							'count' => $this->pm1->countDefeatsPlayOffs(),
							// TODO stages!!!!!!!!!
							'fatality' => $this->pm1->countDefeatsPlayOffsFatality(),
							'technical' => $this->pm1->countDefeatsPlayOffsTechnical()
						)
					)
				),

				'second' => array (
					'count' => $this->pm2->countGames(),

					'v' => array (
						'count' => $this->pm2->countVictoriesPlayOffs() + $this->pm2->countVictoriesRegularity(),
						'regular' => array (
							'count' => $this->pm2->countVictoriesRegularity(),
							'five' => $this->pm2->countVictoriesRegularity5(),
							'six' => $this->pm2->countVictoriesRegularity6(),
							'balance' => $this->pm2->countVictoriesRegularityBalance(),
							'fatality' => $this->pm2->countVictoriesRegularityFatality(),
							'technical' => $this->pm2->countVictoriesRegularityTechnical()
						),
						'playoff' => array (
							'count' => $this->pm2->countVictoriesPlayOffs(),
							// TODO stages!!!!!!!!!
							'fatality' => $this->pm2->countVictoriesPlayOffsFatality(),
							'technical' => $this->pm2->countVictoriesPlayOffsTechnical()
						)
					),

					'd' => array (
						'count' => $this->pm2->countDefeatsPlayOffs() + $this->pm2->countDefeatsRegularity(),
						'regular' => array (
							'count' => $this->pm2->countDefeatsRegularity(),
							'five' => $this->pm2->countDefeatsRegularity5(),
							'six' => $this->pm2->countDefeatsRegularity6(),
							'balance' => $this->pm2->countDefeatsRegularityBalance(),
							'fatality' => $this->pm2->countDefeatsRegularityFatality(),
							'technical' => $this->pm2->countDefeatsRegularityTechnical()
						),
						'playoff' => array (
							'count' => $this->pm2->countDefeatsPlayOffs(),
							// TODO stages!!!!!!!!!
							'fatality' => $this->pm2->countDefeatsPlayOffsFatality(),
							'technical' => $this->pm2->countDefeatsPlayOffsTechnical()
						)
					)
				)
			),
            'movement' => $this->getTwoPlayersMovement($this->pmId1, $this->pmId2)
		);
	}

	public function toJSON() {
		return json_encode($this->toArray());
	}

	public function toHTML($handler) {
		return call_user_func($handler, $this->toArray());
	}
}

?>