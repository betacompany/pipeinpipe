<?php
require_once dirname(__FILE__).'/../db/LeagueDBClient.php';

require_once dirname(__FILE__).'/RatingFormula.php';
require_once dirname(__FILE__).'/Competition.php';

require_once dirname(__FILE__).'/../utils/IComparable.php';
require_once dirname(__FILE__).'/../utils/Sorting.php';
/**
 * @author Alexander Knop
 * @author Artyom Grigoriev
 * @author Malkovsky Nikolay
 * @author Innokenty Shuavlov
 */
class League implements IComparable {
	const MAIN_LEAGUE_ID = 1;

	private $id;
    private $name;
    private $description;
    private $competitions = array();
    private $competitionsLoaded = false;
    private $competitionsInProgress = array();
    private $competitionsInProgressLoaded = false;
    private $competitionsChronologicallyLoaded = false;
    private $competitionsChronologically = array();
    private $formula;

	private $players = array();
	private $playersLoaded = false;

	private static $leagues = array();

	/**
	 * Constructs new instance of League by existing in DB data
	 * @author Malkovsky Nikolay
	 * @throws InvalidArgumentException
	 * @param $id, meaningless if the second param is given
	 * @param $data Constructor will get information from this param,
	 * if it is null db query will be performed.
	 */
    private function  __construct($id, $data = null) {
		if($data == null) {
			$req = LeagueDBClient::selectById($id);
			if (!($data = mysql_fetch_assoc($req))) {
				throw new InvalidArgumentException("There is no league with id=$id");
			}
		}
		
		$this->id =				$data['id'];
		$this->name =			$data['name'];
		$this->description =	$data['description'];
		$this->formula = RatingFormula::getInstance()->getFormulaByName($data['formula']);
    }

	public static function getById($id) {
		if (!isset($id) || $id <= 0) return null;
		if (isset(self::$leagues[$id])) return self::$leagues[$id];
		self::$leagues[$id] = new League($id);
		return self::$leagues[$id];
	}

	public static function getByIds($ids) {
		//it's faster than loading each one of them separately
		if (count(array_intersect(self::getLoadedLeagueIds(), $ids)) > 0)
			League::getAll();

		$result = array();
		foreach ($ids as $leagueId) {
			$result[] = self::getById($leagueId);
		}

		return $result;
	}

	/*
	 * method is not in use yet. delete this description if you start using it!
	 */
	public static function getByData($data) {
		$id = $data['id'];
		if (isset(self::$leagues[$id])) return self::$leagues[$id];
		self::$leagues[$id] = new League(-1, $data);
		return self::$leagues[$id];
	}

	public static function getByIterator(DBResultIterator $iterator) {
		$result = array();
        while ($iterator->valid()) {
            $result[] = self::getByData($iterator->current());
			$iterator->next();
        }

		return $result;
	}

	private static function getLoadedLeagueIds() {
		$ids = array();
		foreach (self::$leagues as $league)
			$ids[] = $league->getId();
		return $ids;
	}

	/**
	 * Inserts new row in database and return created instance of league
	 * @throws Exception
	 * @param string $name
	 * @param string $description
	 * @return League
	 */
	public static function create($name, $description, $formula) {
		LeagueDBClient::insert($name, $description, $formula);
		$temp = mysql_insert_id();
		if ($temp <= 0) {
			// TODO make more complicated exceptions
			throw new Exception("Unable to create new record in database");
		}
		return League::getById($temp);
	}

	public static function existsById($id){
		return LeagueDBClient::existsById($id);
	}

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getFormula() {
        return $this->formula;
    }

	public function setFormula($formula) {
		if (!($formula instanceof RatingFormula))
			$formula = RatingFormula::getInstance()->getFormulaByName($formula);

		if (!LeagueDBClient::setFormula($this->getId(), $formula)) {
			return false;
		}
		$this->formula = $formula;
		return true;
	}

	public function setName($name) {
    	if (!LeagueDBClient::setName($this->getId(), $name)){
			return false;
		}
		$this->name = $name;
		return true;
	}

    public function getDescription() {
        return $this->description;
    }

	public function setDescription($description) {
    	if (!LeagueDBClient::setDescr($this->getId(), $description)){
			return false;
		}
		$this->description = $description;
		return true;
	}

    public function getCompetitions($chronologically = false) {
		if ($chronologically) return $this->getCompetitionsChronologically();
        if ($this->competitionsLoaded) return $this->competitions;

        $this->competitions = Competition::getByLeagueId($this->getId());

        $this->competitionsLoaded = true;
        return $this->competitions;
    }
	
	public function getCompetitionsChronologically() {
        if ($this->competitionsChronologicallyLoaded) return $this->competitionsChronologically;

        $this->competitionsChronologically = Competition::getByLeagueIdChronologically($this->getId());

		//FIXME может, здесь стоит их в массив массив competitions тоже записать?
		//там же не важно, в каком они порядке..
        $this->competitionsChronologicallyLoaded = true;
        return $this->competitionsChronologically;
    }


	public function getCompetitionsInProgress() {
		if ($this->competitionsInProgressLoaded)
			return $this->competitionsInProgress;

		foreach($this->getCompetitions() as $comp) {
			if ($comp->isRunning() || $comp->isRegistering())
				$this->competitionsInProgress[] = $comp;
		}

		$this->competitionsInProgressLoaded = true;
		return $this->competitionsInProgress;
	}

	// sometimes we may need to reload competitions
    // this method help us to do it
    public function getCompetitionsWithReload() {
        $this->competitionsLoaded = false;
        return $this->getCompetitions();
    }

    /**
     * return all players engaged in competitions of this league
     * @return array of Player
     */
    public function getPlayers() {
		if ($this->playersLoaded) return $this->players;
		$this->players = Player::getByIterator(LeagueDBClient::selectPlayers($this->getId()));
		$this->playersLoaded = true;
		return $this->players;
    }

	public function getAdmins() {
		$req = UserPermissionDBClient::selectByLeague($this->getId());
		$admins = array();
		while ($u = mysql_fetch_assoc($req)) {
			try {
				$admins[] = User::getById($u['uid']);
			} catch (InvalidArgumentException $e) {
				// TODO use error log file
				echo $e->getMessage();
			}
		}

		return $admins;
	}

	/**
	 * return all existing in DB leagues ordered by id
	 * @author Malkovsky Nikolay
	 * @return League[]
	 */
	public static function getAll() {
		return League::getByIterator(LeagueDBClient::selectAll());
	}

	public static function isSuchName($name) {
		$req = LeagueDBClient::selectByName($name);
		return (mysql_num_rows($req) > 0);
	}

	const IMAGE_NORMAL = '.jpg';
	const IMAGE_SMALL = '_small.jpg';

    public function hasImage($type = self::IMAGE_NORMAL) {
		return file_exists(dirname(__FILE__) . '/../../images/leagues/' . $this->getId() . $type);
	}

	public function getImageURL($type = self::IMAGE_NORMAL) {
		return $this->hasImage($type) ?
			   '/images/leagues/' . $this->getId() . $type :
			   '/images/leagues/default' . $type;
	}

	public function compareTo(IComparable $other) {
		if (!($other instanceof League) || $this->getId() == 1)
			return 1;
		else if ($other->getId() == 1)
			return -1;
		
		$count1 = count($this->getPlayers());
		$count2 = count($other->getPlayers());

		if ($count1 == $count2)
			return $other->getId() - $this->getId();

		return $count1 - $count2;
	}
	
	public static function getTopLeagues(User $user = null, $forceAll = true, $forceIncludeWPR = true) {
		if ($user !== null)
			return $user->getLeagues($forceAll, $forceIncludeWPR);

		$leagues = self::getAll();
//		Sorting::qsort($leagues);
		return $leagues;
	}

	public static function getAllIds() {
		$result = array();
		foreach (self::getAll() as $league) {
			$result[] = $league->getId();
		}
		return $result;
	}

	public static function countAll() {
		return LeagueDBClient::countAll();
	}
}
?>
