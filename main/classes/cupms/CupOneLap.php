<?php

require_once dirname(__FILE__).'/Cup.php';
require_once dirname(__FILE__).'/Player.php';

require_once dirname(__FILE__).'/../../includes/mysql.php';

/**
 * Description of CupOneLap
 *
 * @author Artyom Grigoriev aka ortemij
 */
class CupOneLap extends Cup {

    private $gameGrid = array();
    private $gameGridLoaded = false;

    private $resultTable;
    private $resultTableLoaded = false;

    public function getGameGrid($reload = false) {
        if ($this->gameGridLoaded && !$reload) return $this->gameGrid;

        $players = $this->getPlayers();
        foreach ($players as $player1) {
			$pmid1 = $player1->getId();
			$index1 = $this->getPlayerIndex($pmid1);
			$this->gameGrid[$index1] = array();

            foreach ($players as $player2) {
                $pmid2 = $player2->getId();
                $index2 = $this->getPlayerIndex($pmid2);

                if ($index1 < $index2) {
                    $game = Game::getByPmidsAndCup($pmid1, $pmid2, $this->getId());
                    $this->gameGrid[$index1][$index2] = $game;
					if ($game == null) {
						$game = Game::getByPmidsAndCup($pmid2, $pmid1, $this->getId());
						if ($game != null) {
							$game->swapPrevGames();
							$game->update();
							$this->gameGrid[$index1][$index2] = $game;
						}
					}
				}
            }
        }

        $this->gameGridLoaded = true;
        
        return $this->gameGrid;
    }

	public function createGame($pmid1, $pmid2) {
		$game = Game::getByPmidsAndCup($pmid1, $pmid2, $this->getId());
		if ($game != null) {
			throw new Exception('There is already such game! It\'s id='.$game->getId());
		}

		$game = Game::create($this->getId(), 0, 0, $pmid1, $pmid2);
		return $game;
	}

    /**
     *
     * @return array of ResultTable
     */
    public function getResultTable($reload = false) {
        if ($this->resultTableLoaded && !$reload) return $this->resultTable;

        $this->resultTable = ResultTable::getForCup($this);
        $this->resultTableLoaded = true;

        return $this->resultTable;
    }

    /**
     * generates results for this cup,
     * puts them into resultTable property,
     * return them
     * @return array of ResultTable
     */
    public function generateResultTable() {
        $this->resultTable = ResultTable::generate($this->getId());
        $this->resultTableLoaded = true;
        return $this->resultTable;
    }

    /**
     * stores results in database
     */
    public function storeResultTable() {
        foreach ($this->resultTable as $result) {
            $result->store();
        }
    }

	public function getPlayers() {
		if ($this->playersLoaded) return $this->players;

        $this->players = array();
		$req = ResultTableDBClient::selectPmidsForCup($this->getId());
		while ($p = mysql_fetch_assoc($req)) {
			try {
				$this->players[] = Player::getById($p['pmid']);
				$this->playerIndexes[$p['pmid']] = count($this->players) - 1;
			} catch (Exception $e) {
				global $LOG;
				@$LOG->exception($e);
			}
		}

		$this->playersLoaded = true;
        return $this->players;
	}

	public function addPlayer($pmid) {
		return ResultTableDBClient::insertPmidInCup($pmid, $this->getId());
	}

	public function removePlayer($pmid) {
		$t = parent::removePlayer($pmid);
		$z = ResultTableDBClient::deletePlayer($this->getId(), $pmid);
		ResultTable::recalculateForCup($this);

		return $t && $z;
	}

	public final function getVictor() {
		$sortedRT = ResultTable::sort($this->getResultTable());
		return $sortedRT[0]->getPlayer();
	}
}
?>
