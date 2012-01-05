<?php
/**
 * Description of Competition
 *
 * @author Andrew Solozobov
 * @author Innokenty Shuavlov
 */

require_once dirname(__FILE__).'/CupFactory.php';
require_once dirname(__FILE__).'/Tournament.php';
require_once dirname(__FILE__).'/RatingTable.php';
require_once dirname(__FILE__).'/RatingManager.php';

require_once dirname(__FILE__).'/../db/CompetitionDBClient.php';
require_once dirname(__FILE__).'/../db/CupDBClient.php';

require_once dirname(__FILE__).'/../user/User.php';

require_once dirname(__FILE__).'/../utils/IComparable.php';
require_once dirname(__FILE__).'/../utils/Sorting.php';

require_once dirname(__FILE__).'/../../includes/common.php';

class Competition implements IComparable {

	const STATUS_DISABLED = 'disabled';
	const STATUS_REGISTERING = 'registering';
	const STATUS_RUNNING = 'running';
	const STATUS_FINISHED = 'finished';

	private $id;
	private $leagueId;
	private $tournamentId;
	private $name;
	private $date;
	private $coef;
	private $description;
	private $status;

	private $dateToInt;
	private $dateToIntLoaded = false;

	private $mainCup;
	private $mainCupLoaded = false;

	private $mainCupId;
	private $mainCupIdLoaded = false;

	private $tournament;
	private $tournamentLoaded = false;

	private $cupsList = array();
	private $cupsListLoaded = false;

	private $players = array();
	private $playersLoaded = false;

	private $pmids = array();
	private $pmidsLoaded = false;

	private $ratingManager = null;

	private static $competitions = array();

	private static $pmCount = array();
	private static $pmCountLoaded = false;

	private function __construct($id, $data = null) {
		if ($data == null) {
			$req = CompetitionDBClient::selectById($id);
			$data = mysql_fetch_assoc($req);
			if (!$data) {
				throw new InvalidIdException("Invalid competition id=$id");
			}
		}

		$this->id = $data['id'];
		$this->leagueId = $data['league_id'];
		$this->tournamentId = $data['tournament_id'];
		$this->name = $data['name'];
		$this->date = $data['date'];
		$this->coef = $data['coef'];
		$this->description = $data['description'];
		$this->status = $data['status'];
	}

	public static function getById($id) {
		if (!isset($id) || $id <= 0) return null;
		if (isset(self::$competitions[$id])) return self::$competitions[$id];
		self::$competitions[$id] = new Competition($id);
		return self::$competitions[$id];
	}

	public static function getByIds($compIds) {
		$result = array();
		foreach ($compIds as $compId) {
			$result[] = self::getById($compId);
		}

		return $result;
	}

	public static function getByData($data) {
		$id = $data['id'];
		if (isset(self::$competitions[$id])) return self::$competitions[$id];
		self::$competitions[$id] = new Competition(-1, $data);
		return self::$competitions[$id];
	}

	private static function getByIterator(DBResultIterator $iterator) {
		$result = array();
        while ($iterator->valid()) {
            $result[] = self::getByData($iterator->current());
			$iterator->next();
        }
		return $result;
	}

	public static function create($leagueId = 0, $tournamentId = 0, $name = "",
	                            $description = "", $date = "0000-00-00", $coef = 0,
								$status = self::STATUS_DISABLED) {

		CompetitionDBClient::insert($leagueId, $tournamentId, $name, $date, $coef, $description, $status);

		$temp = mysql_insert_id();
		if($temp <= 0) {
			throw new Exception("Unable to create new record in database");
		}
		return self::getById($temp);
	}

    public static function existsById($id) {
			return CompetitionDBClient::existsById($id);
    }

	public function getId() {
		return $this->id;
	}

	public function getLeagueId() {
		return $this->leagueId;
	}

	public function getLeague() {
		try {
			return League::getById($this->getLeagueId());
		} catch (Exception $e) {
			global $LOG;
			@$LOG->exception($e);
			return null;
		}
	}

	public function getTournamentId() {
		return $this->tournamentId;
	}

	public function getName() {
		return $this->name;
	}

	public function getDate() {
		return $this->date == null ? '0000-00-00' : $this->date;
	}

	public function getDateToInt() {
		if ($this->dateToIntLoaded) return $this->dateToInt;
		$this->dateToInt = datetoint('2007-10-23', $this->getDate());
		$this->dateToIntLoaded = true;
		return $this->dateToInt;
	}

	public function getCoef($date = '') {
		if ($date == '') return $this->coef;

		$ratingManager = $this->getRatingManager();
		return $ratingManager->getCoef($date);
	}

	public function getCoefDivider($date) {
		$ratingManager = $this->getRatingManager();
		return $ratingManager->getCoefDivider($date);
	}

    public function getDescription() {
		return $this->description;
	}

    public function getTournament() {
		if ($this->tournamentLoaded) return $this->tournament;

		// some competitions could have `tournament_id`=0, it means that they
		// are not bounded to any tournament.
		if ($this->tournamentId == 0) {
			$this->tournamentLoaded = true;
			$this->tournament = null;
			return $this->tournament;
		}

		$this->tournament = null;
		try {
			$this->tournament = new Tournament($this->tournamentId);
			$this->tournamentLoaded = true;
		} catch (Exception $e) {
			global $LOG;
			@$LOG->exception($e);
		}

		return $this->tournament;
    }

    public function getMainCupId() {
		if ($this->mainCupIdLoaded) return $this->mainCupId;

		$req = CupDBClient::selectZeroParentById($this->id);

		if ($req == false){
			return false;
		}

		if ($c = mysql_fetch_assoc($req)) {
			$this->mainCupId = $c['id'];
		} else {
			$this->mainCupId = 0;
		}

		$this->mainCupIdLoaded = true;
		return $this->mainCupId;
	}

	public function hasMainCup() {
		return ($this->getMainCup() != null);
	}

    public function getMainCup() {
		if ($this->mainCupLoaded) return $this->mainCup;

		$this->mainCup = null;
		if ($this->getMainCupId() != 0) {
			try {
				$this->mainCup = CupFactory::getCupById($this->getMainCupId());
				$this->mainCupLoaded = true;
			} catch (Exception $e) {
				global $LOG;
				@$LOG->exception($e);
			}
		}

		return $this->mainCup;
    }

	public function getCupsList() {
		if ($this->cupsListLoaded) return $this->cupsList;

		$this->cupsList = array();
		$req = CupDBClient::selectByCompetitionId($this->getId());
		while ($cup = mysql_fetch_assoc($req)) {
			try {
				$this->cupsList[] = CupFactory::getCupById($cup['id']);
			} catch (Exception $e) {
				global $LOG;
				@$LOG->exception($e);
			}
		}

		$this->cupsListLoaded = true;
		return $this->cupsList;
	}

	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return RatingManager
	 */
	public function getRatingManager() {
		return ($this->ratingManager == null) ?
				$this->ratingManager = new RatingManager($this) :
				$this->ratingManager;
	}

	/**
	 * @param int $leagueId New league id value
	 * @param boolean $update It must be false in case you don't want to update database
	 * @return booleanif database query was successful
	 */
	public function setLeagueId($leagueId, $update = true) {
		assertPositive('LeagueId must be positive', $leagueId);
		$copy = $this->leagueId;
		$this->leagueId = $leagueId;
		if ($update){
			$result = CompetitionDBClient::update($this);
			if (!$result){
				$this->leagueId = $copy;
				return false;
			}
		}
		return true;
	}

	/**
	 * @param int $tournamentId New tournament id value
	 * @param boolean $update It must be false in case you don't want to update database
	 * @return boolean if database query was successful
	 */
	public function setTournamentId($tournamentId, $update = true) {
		assertPositive('TournamentId must be positive', $tournamentId);
		$copy = $this->tournamentId;
		$this->tournamentId = $tournamentId;
		if ($update){
			$result = CompetitionDBClient::update($this);
			if (!$result){
				$this->tournamentId = $copy;
				return false;
			}
		}
		return true;
	}

	/**
	 * @param string $name New name value
	 * @param boolean $update It must be false in case you don't want to update database
	 * @return boolean if database query was successful
	 */
	public function setName($name, $update = true) {
		$copy = $this->name;
		$this->name = $name;
		if ($update){
			$result = CompetitionDBClient::update($this);
			if (!$result){
				$this->name = $copy;
				return false;
			}
		}
		return true;
	}

	/**
	 * @param string $season New season value yyyy
	 * @param boolean $update It must be false in case you don't want to update database
	 * @return boolean if database query was successful
	 */
	public function setSeason($season, $update = true) {
		$copy = $this->season;
		$this->season = $season;
		if ($update){
			$result = CompetitionDBClient::update($this);
			if (!$result){
				$this->season = $copy;
				return false;
			}
		}
		return true;
	}

	/**
	 * @param string $date New date value yyyy-mm-dd
	 * @param boolean $update It must be false in case you don't want to update database
	 * @return boolean If database query was successful
	 */
	public function setDate($date, $update = true) {
		assertDate($date);

		$copy = $this->date;
		$this->date = $date;
		if ($update){
			$result = CompetitionDBClient::update($this);
			if (!$result){
				$this->date = $copy;
				return false;
			}
		}
		return true;
	}

	/**
	 * @param double $coef New coefficient value
	 * @param boolean $update It must be false in case you don't want to update database
	 * @return boolean If database query was successful
	 */
	public function setCoef($coef, $update = true) {
		assertNotNegative("Coefficient value must be positive", $coef);
		$copy = $this->coef;
		$this->coef = $coef;
		if ($update){
			$result = CompetitionDBClient::update($this);
			if (!$result){
				$this->coef = $copy;
				return false;
			}
		}
		return true;
	}

	/**
	 * @param string $description New description value
	 * @param boolean $update It must be false in case you don't want to update database
	 * @return boolean If database query was successful
	 */
	public function setDescription($description, $update = true) {
		$copy = $this->description;
		$this->description = $description;
		if ($update){
			$result = CompetitionDBClient::update($this);
			if (!$result){
				$this->description = $copy;
				return false;
			}
		}
		return true;
	}

	public function setStatus($status, $update = true) {
		if (!($status == Competition::STATUS_DISABLED ||
			  $status == Competition::STATUS_FINISHED ||
			  $status == Competition::STATUS_REGISTERING ||
		      $status == Competition::STATUS_RUNNING)) {
			throw new InvalidArgumentException('Invalid status value');
		}

		$copy = $this->status;
		$this->status = $status;
		if ($update){
			$result = CompetitionDBClient::update($this);
			if (!$result){
				$this->status = $copy;
				return false;
			}
		}
		return true;
	}

	public function startRegistering() {
		if ($this->isEnabled()) {
			throw new InvalidStatusException();
		}

		$this->setStatus(self::STATUS_REGISTERING);
	}

	public function start() {
		if ($this->getMainCup() == null) {
			throw new NullCupException();
		}

		if (!$this->isBefore()) {
			throw new InvalidStatusException();
		}

		$this->setStatus(self::STATUS_RUNNING);
	}

	public function finish($date = null) {
		if ($this->getMainCup() == null) {
			throw new NullCupException();
		}

		if (!$this->isRunning()) {
			throw new InvalidStatusException();
		}


		if ($date != null) {
			assertDate($date);
			$this->setDate($date);
		} else {
			assertDate($this->getDate());
		}

		// method name says all about it =)
		RatingTable::removeFuture($this->getDate(), $this->getLeagueId());

		// calculating increments for rating
		$ratingManager = $this->getRatingManager();
		$ratingManager->onFinish($this->getDate());

		// setting status finished
		$this->setStatus(self::STATUS_FINISHED);
	}

	public function restart() {
		if ($this->getMainCup() == null) {
			throw new NullCupException();
		}

		if (!$this->isFinished()) {
			throw new InvalidStatusException();
		}

		// method name says all about it =)
		RatingTable::removeFuture($this->getDate(), $this->getLeagueId());

		// removing increments for rating
		$ratingManager = $this->getRatingManager();
		$ratingManager->onReStart();

		// setting status running
		$this->setStatus(self::STATUS_RUNNING);
	}

	public function update() {
		CompetitionDBClient::update($this);
	}

	public function isDisabled() {
		return $this->status == self::STATUS_DISABLED;
	}

	public function isEnabled() {
		return $this->status != self::STATUS_DISABLED;
	}

	public function isBefore() {
		return $this->status == self::STATUS_DISABLED ||
			   $this->status == self::STATUS_REGISTERING;
	}

	public function isRegistering() {
		return $this->status == self::STATUS_REGISTERING;
	}

	public function isRunning() {
		return $this->status == self::STATUS_RUNNING;
	}

	public function isFinished() {
		return $this->status == self::STATUS_FINISHED;
	}

	public function getPlayers() {
		if ($this->playersLoaded) {
			return $this->players;
		}

		$this->players = array();
		foreach ($this->getPlayerIds() as $id) {
			try {
				$this->players[] = Player::getById($id);
			} catch(Exception $e) {
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
		$result = array();
		foreach($this->getPlayers() as $player) {
			$result[] = $player->toArray();
		}
		return $result;
	}

	public final function getPlayerIds() {
		if ($this->pmidsLoaded)
			return $this->pmids;

		$this->pmids = array();
		foreach ($this->getCupsList() as $cup) {
//			why it doesn't work?!?!
//			$this->pmids = array_merge($this->pmids, $cup->getPmids());
			foreach ($cup->getPmids() as $pmid) {
				if (!array_contains($this->pmids, $pmid)) {
					$this->pmids[] = $pmid;
				}
			}
		}
		$this->pmidsLoaded = true;
		return $this->pmids;
	}

	public final function countPlayers() {
		return count($this->getPlayerIds());
	}

	/**
	 * removes all data concerning to this competition from db
	 * but does not remove the loaded data!
	 */
	public function remove() {
		$result = true;
		foreach ($this->getCupsList() as $cup) {
			if (! $cup->remove()) {
				$result = false;
			}
		}

		if ($result) {
			if (! CompetitionDBClient::deleteById($this->getId())) {
				$result = false;
			}
		}

		return $result;
	}

	const IMAGE_NORMAL = '';
	const IMAGE_SMALL = '_small';

	public function hasImage($type = self::IMAGE_NORMAL) {
		return file_exists(dirname(__FILE__) . '/../../images/competitions/' . $this->getId() . $type . '.jpg');
	}

	public function getImageURL($type = self::IMAGE_NORMAL) {
		return $this->hasImage($type) ?
			   '/images/competitions/' . $this->getId() . $type . '.jpg':
			   '/images/competitions/default' . $type . '.jpg';
	}
	
	public function getStatusImageURL() {
		if ($this->isRunning()) return '/images/competitions/running.png';
		elseif ($this->isRegistering()) return '/images/competitions/registering.png';
		return '';
	}

	public function getVictor() {
		return $this->hasMainCup() ? $this->getMainCup()->getVictor() : null;
	}

	/**
	 * @return string
	 */
	public function getURL() {
		return "/sport/league/" . $this->leagueId . "/competition/" . $this->id;
	}

	/**
	 *
	 * @param <type> $cupId
	 * @return Competition
	 */
	public static function getByCupId($cupId) {
		$competitionId = CupDBClient::getCompetitionIdFor($cupId);
		return self::getById($competitionId);
	}

	public static function getByLeagueId($leagueId) {
		return self::getByIterator(CompetitionDBClient::selectByLeagueId($leagueId));
	}

	public static function getByLeagueIdChronologically($leagueId) {
		$iterator = CompetitionDBClient::getByLeagueIdChronologically($leagueId);
		return self::getByIterator($iterator);
	}

	public static function isSuchName($name) {
		$result = self::getByIterator(CompetitionDBClient::selectByName($name)); //чтобы были, раз уж мы их загрузили всё равно.

		return $result.length > 0;
	}

	public static function getAll() {
		return self::getByIterator(CompetitionDBClient::selectAll());
	}

	public static function countAll() {
		return CompetitionDBClient::countAll();
	}

	public static function getPmCount() {
		if (self::$pmCountLoaded)
			return self::$pmCount;

		$iterator = CompetitionDBClient::countPipemen();
		while ($iterator->valid()) {
			$current = $iterator->current();
			self::$pmCount[$current['comp_id']] = $current['count'];
			$iterator->next();
		}

		self::$pmCountLoaded = true;
		return self::$pmCount;
	}

	public function register($uid, $pmid) {
		return CompetitionDBClient::insertRegistration($this->getId(), $uid, $pmid);
	}

	public function unregister($uid) {
		return CompetitionDBClient::deleteRegistration($this->getId(), $uid);
	}

	public function getRegisteredUsers() {
		$iterator = CompetitionDBClient::selectRegisteredUsers($this->getId());
		$result = array();
		while ($iterator->valid()) {
			$result[] = User::getByData($iterator->current());
			$iterator->next();
		}
		return $result;
	}

	public function getRegisteredArray() {
		$iterator = CompetitionDBClient::selectRegistered($this->getId());
		$result = array();
		while ($iterator->valid()) {
			$data = $iterator->current();
			$result[] = array(
				'user' => array('id' => $data['uid'], 'name' => $data['u_name'], 'surname' => $data['u_surname']),
				'player' => array('id' => $data['pmid'], 'name' => $data['p_name'], 'surname' => $data['p_surname'])
			);
			$iterator->next();
		}
		return $result;
	}

	public function compareTo(IComparable $other) {
		if (!($other instanceof Competition)) {
			global $LOG;
			@$LOG->pizdets('Non-competition in competitions\' array');
			return 1;
		}
		return $this->getDateToInt() - $other->getDateToInt();
	}

	public static function sortByDate(&$competitions, $ascending = true) {
		Sorting::qsort($competitions);
		if(!$ascending) {
			$competitions = array_reverse($competitions);
		}
	}

	public static function getByIdFromArray($compId, $competitions) {
		foreach ($competitions as $comp)
			if ($comp->getId() == $compId)
				return $comp;
	}
}
?>
