<?php

require_once dirname(__FILE__).'/Player.php';
require_once dirname(__FILE__).'/CupFactory.php';

require_once dirname(__FILE__).'/../db/GameDBClient.php';
require_once dirname(__FILE__).'/../db/ResultTableDBClient.php';

require_once dirname(__FILE__).'/../exceptions/cupms_exception_set.php';

define('DEFAULT_NAME', 'Турнир');
define('DEFAULT_TYPE', 'CUP_TYPE_UNDEFINED');
define('DEFAULT_STATUS', 'CUP_STATUS_BEFORE');

define('CUP_TYPE_UNDEFINED', 'undefined');
define('CUP_TYPE_ONELAP', 'one-lap');
define('CUP_TYPE_TWOLAPS', 'two-laps');
define('CUP_TYPE_PLAYOFF', 'playoff');

/**
 * Abstract implementation of cup
 * For concrete implementations see its children
 * @author ortemij
 */
abstract class Cup {

	const TYPE_PLAYOFF = 'playoff';
	const TYPE_ONE_LAP = 'one-lap';
	const TYPE_TWO_LAPS = 'two-laps';
	const TYPE_UNDEFINED = 'undefined';

	const DEFAULT_NAME = 'Турнир';

	private $id;
	private $competitionId;
	private $parentCupId;
	private $name;
	private $type;
	private $mult;

	private $competition;
	private $competitionLoaded = false;

	private $parentCup;
	private $parentCupLoaded = false;

	private $children = array();
	private $childrenLoaded = false;

	protected $players = array(); // array of players
	protected $playerIndexes = array(); // array of player indexes in $players array
	protected $playersLoaded = false;

	/**
	 * constructs instance of Cup by existing in DB data
	 * throws InvalidArgumentException
	 * @param int $id
	 */
	public final function __construct($id) {
		assertPositive('Cup does not support construction. Please use CupFactory::create()', $id);
        
		$req = CupDBClient::selectById($id);
		if ($c = mysql_fetch_assoc($req)) {
			$this->id =             $c['id'];
			$this->competitionId =  $c['competition_id'];
			$this->parentCupId =    $c['parent_cup_id'];
			$this->name =           $c['name'];
			$this->type =           $c['type'];
			$this->mult =		$c['multiplier'];
		} else {
			throw new InvalidArgumentException("There is no cup with id=$id");
		}
	}

	public final function getCompetition() {
        if ($this->competitionLoaded) return $this->competition;
		if ($this->competitionId == 0) return null;

		try {
			$this->competition = Competition::getById($this->competitionId);
		} catch (Exception $e) {
			global $LOG;
			@$LOG->exception($e);
			$this->competition = null;
		}

		$this->competitionLoaded = true;

		return $this->competition;
	}

	public final function getCompetitionId() {
		return $this->competitionId;
	}

	public final function getId() {
		return $this->id;
	}

	public final function getName() {
		if (empty($this->name)) return self::DEFAULT_NAME;
		return $this->name;
	}

	public final function getType() {
		if (empty($this->type)) return self::DEFAULT_NAME;
		return $this->type;
	}

	public final function getParentCup() {
		if ($this->parentCupLoaded) return $this->parentCup;
		if ($this->parentCupId == 0) return null;

		try {
			$this->parentCup = CupFactory::getCupById($this->parentCupId);
		} catch (Exception $e) {
			global $LOG;
			@$LOG->exception($e);
			$this->parentCup = null;
		}

		$this->parentCupLoaded = true;
		return $this->parentCup;
	}

	public final function getParentCupId() {
		return $this->parentCupId;
	}

	public final function getChildren() {
		if ($this->childrenLoaded) return $this->children;

		$this->children = array();
		$req = CupDBClient::selectChildrenFor($this->id);
		while ($c = mysql_fetch_assoc($req)) {
			$this->children[] = CupFactory::getCupById($c['id']);
		}

		$this->childrenLoaded = true;
		return $this->children;
	}

	public final function isFinished() {
		return $this->getCompetition()->isFinished();
	}

	public final function isRegular() {
		return ($this->type == self::TYPE_ONE_LAP || $this->type == self::TYPE_TWO_LAPS);
	}

	public final function isPlayOff() {
		return $this->type == self::TYPE_PLAYOFF;
	}

	public final function isUndefined() {
		return $this->type == self::TYPE_UNDEFINED;
	}
	
	private final function safeUpdateDetail(&$detail, $newData, $update = true) {
		$copy = $detail;
		$detail = $newData;
		if ($update) {
			$result = CupDBClient::update($this);
			if (!$result) {
				$detail = $copy;
				return false;
			}
		}
		return true;
	}

	/**
	* @param string $name New name value
	* @param boolean $update It must be false in case you don't want to update database
	* @return boolean If database query was successful
	*/
	public final function setName($name, $update = true) {
		return $this->safeUpdateDetail($this->name, $name, $update);
	}
	
	/**
	* @param int $multiplier New multiplier value
	* @param boolean $update It must be false in case you don't want to update database
	* @return boolean If both database query and recalculating rating were successful
	*/
	public final function setMultiplier($multiplier, $update = true) {
		assertCupMult($multiplier);
		return $this->safeUpdateDetail($this->mult, $multiplier, $update);
	}
	
	public final function setMultiplierForChildren($multiplier) {
		$result = true;
		foreach ($this->getChildren() as $childCup) {
			if (!$childCup->setMultiplier($multiplier)) {
				$result = false;
			}
		}
		return $result;
	}

	public final function update() {
		CupDBClient::update($this);
	}

	/**
	 * return array of players in this cup
	 * @return Player[]
	 */
	public abstract function getPlayers();

	public final function getPlayersHard() {
		$this->players = array();
		$req = GameDBClient::selectPlayersForCup($this->getId());
		while ($p = mysql_fetch_assoc($req)) {
			try {
				//FIXME use getById(null, $row) function
				$this->players[] = Player::getByData($p);
				$this->playerIndexes[$p['id']] = count($this->players) - 1;
			} catch (Exception $e) {
				global $LOG;
				@$LOG->exception($e);
			}
		}

		$this->playersLoaded = true;

		return $this->players;
	}

	/**
	 * return array of Player::toArray() results for every player of this cup
	 */
	public final function getPlayersToArray() {
		$players = $this->getPlayers();
		$result = array();
		foreach($players as $player) {
			$result[] = $player->toArray();
		}

		return $result;
	}

	public final function getPmids() {
		if ($this->pmidsLoaded)
			return $this->pmids;

		$this->pmids = array();
		foreach ($this->getPlayers() as $player)
			$this->pmids[] = $player->getId();
		$this->pmidsLoaded = true;
		return $this->pmids;
	}

	public final function getPlayerIndex($pmid) {
		return $this->playerIndexes[$pmid];
	}

	public function removePlayer($pmid) {
		assertPipeman($pmid);
		$this->getPlayers();

		if (!array_key_exists($pmid, $this->playerIndexes)) {
			throw new Exception('There is no player with ID='.$pmid.' in cup with ID='.$this->getId());
		}

		$playerIndex = $this->playerIndexes[$pmid];
		$resultArray = array();
		foreach ($this->players as $index => $player) {
			if ($index != $playerIndex) {
				$resultArray[] = $player;
			}
		}
		$this->players = $resultArray;
		
		GameDBClient::removeAllWith($pmid, $this->getId());

		return true;
	}

	/**
	 * removes all data concerning to this cup from db
	 * but does not remove the loaded data!
	 *
	 * @return boolean
	 * 'true' if all data was successfully removed,
	 * and 'false' if we are fucked and only part of the operation was successful!
	 */
	public function remove() {
		$result = true;
		foreach($this->getChildren() as $cup)
			if (!$cup->remove())
				$result = false;

		if ($result && ! CupDBClient::deleteById($this->getId()))
			$result = false;
		if ($result && ! GameDBClient::deleteByCupId($this->getId()))
			$result = false;
		if ($result && ! ResultTableDBClient::deleteByCupId($this->getId()))
			$result = false;
		if ($result && ! ResultCupDBClient::deleteByCupId($this->getId()))
			$result = false;

		return $result;
	}

	/**
	 * Returns multiplier for coefficient evaluating concerning to
	 * resolution of the XXIII Conference of betacompany.
	 * Default values (if negative value defined in DB) are:
	 * 6 for one or two laps cup and 2 for play-off.
	 * @return double
	 */
	public final function getMultiplier() {
		if ($this->mult < 0) {
			switch ($this->type) {
			case Cup::TYPE_ONE_LAP:
			case Cup::TYPE_TWO_LAPS:
				return 6;
			case Cup::TYPE_PLAYOFF:
				return 2;
			}
		}

		return $this->mult;
	}

	/**
	 * adds player with saving data in DB
	 */
	public abstract function addPlayer($pmid);

	/**
	 * return true iff cup with such id exists in DB
	 * @param int $id
	 * @return boolean
	 */
	public static final function existsById($id) {
		return (boolean) mysql_num_rows(CupDBClient::selectById($id));
	}

	/**
	 * return true if this type exists in enum specified in `p_cup` table in DB
	 * @param string $type
	 * @return boolean
	 */
	public static final function isCorrectType($type) {
		// FIXME make dependency on DB
		return ($type == Cup::TYPE_ONE_LAP ||
		        $type == Cup::TYPE_TWO_LAPS ||
		        $type == Cup::TYPE_PLAYOFF ||
		        $type == Cup::TYPE_UNDEFINED);
	}

	public static function isCorrectName($comp_id, $name) {
		return (boolean) mysql_num_rows(CupDBClient::selectByName($comp_id, $name)) > 0 ? false : true;
	}

	public abstract function getVictor();

	public final function toArray() {
		return array(
			'id' => $this->getId(),
			'value' => $this->getName()
		);
	}

    public final function hasGames() {
        $req = GameDBClient::selectCountGamesForCup($this->getId());
        return mysql_result($req, 0, 0) > 0;
    }
}
?>
