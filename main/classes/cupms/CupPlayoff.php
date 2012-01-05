<?php

require_once dirname(__FILE__) . '/Cup.php';
require_once dirname(__FILE__) . '/Game.php';

/**
 * Description of CupPlayoff
 * Model representation of cup played in olympic (play-off) system.
 * There is final game and usually bronze final game on the top of game grid.
 * @author Artyom Grigoriev aka ortemij
 */
class CupPlayoff extends Cup {

	private $finalGame;
	private $finalGameLoaded = false;
	private $bronzeGame;
	private $bronzeGameLoaded = false;

	public function getFinalGame() {
		if ($this->finalGameLoaded)
			return $this->finalGame;

		$this->finalGame = Game::getFinalFor($this->getId());
		$this->finalGameLoaded = true;
		return $this->finalGame;
	}

	public function getBronzeGame() {
		if ($this->bronzeGameLoaded)
			return $this->bronzeGame;

		$this->bronzeGame = Game::getBronzeFor($this->getId());
		$this->bronzeGameLoaded = true;
		return $this->bronzeGame;
	}

	public function getMaxStage() {
		return GameDBClient::getMaxStageFor($this->getId());
	}

	public function getPlayers() {
		if ($this->playersLoaded)
			return $this->players;

		$iterator = null;
		// if cup is playoff then we have some problems
		if ($this->getCompetition()->isFinished()) {
			// if cup is finished then its results are hosted in special table
			$iterator = ResultCupDBClient::selectPlayersForCup($this->getId());
		} else {
			// otherwise we should to request `p_game` table and get players from there
			$iterator = GameDBClient::selectPlayersForCup($this->getId());
		}

		while ($iterator && $iterator->valid()) {
			$p = $iterator->current();
			$this->players[] = Player::getByData($p);
			$this->playerIndexes[$p['id']] = count($this->players) - 1;
			$iterator->next();
		}

		$this->playersLoaded = true;
		return $this->players;
	}

	public function addPlayer($pmid) {
		throw new Exception('Unsupported method: addPlayer() in CupPlayoff');
	}

	public function getVictor() {
		return $this->getFinalGame()->getVictor();
	}

	public function getGamesByStage($stage) {
		return Game::getByStageAndCup($stage, $this);
	}
}

?>
