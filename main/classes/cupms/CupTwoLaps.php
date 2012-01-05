<?php

require_once dirname(__FILE__).'/CupOneLap.php';
require_once dirname(__FILE__).'/../../includes/mysql.php';

/**
 * Description of CupTwoLaps
 * Model representation of cup with two laps.
 * In this cup every player has two games with his opponent.
 * One game when he is first player, other when he is second.
 * @author Artyom Grigoriev aka ortemij
 */
class CupTwoLaps extends CupOneLap {
    /**
     * @return array of ResultTable
     */
    public function getGameGrid() {
        if ($this->gameGridLoaded) return $this->gameGrid;

        $players = $this->getPlayers();
        foreach ($players as $player1) {
            foreach ($players as $player2) {
                $pmid1 = $player1->getId();
                $pmid2 = $player2->getId();
                $index1 = $this->getPlayerIndex($pmid1);
                $index2 = $this->getPlayerIndex($pmid2);

				if ($index1 != $index2) {
                    $game = Game::getByPmidsAndCup($pmid1, $pmid2, $this->getId());
                    $this->gameGrid[$index1][$index2] = $game;
                }
            }
        }

        $this->gameGridLoaded = true;

        return $this->gameGrid;
    }
}
?>
