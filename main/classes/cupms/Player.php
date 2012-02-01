<?php

require_once dirname(__FILE__) . '/../../includes/common.php';
require_once dirname(__FILE__) . '/../../includes/assertion.php';
require_once dirname(__FILE__) . '/../../includes/security.php';

require_once dirname(__FILE__) . '/../db/PlayerDBClient.php';
require_once dirname(__FILE__) . '/../db/ResultCupDBClient.php';
require_once dirname(__FILE__) . '/../db/LeagueDBClient.php';
require_once dirname(__FILE__) . '/../db/GameDBClient.php';
require_once dirname(__FILE__) . '/../db/DBResultIterator.php';

require_once dirname(__FILE__) . '/../cupms/RatingTable.php';

/**
 * @author Innokenty
 */
class Player {

	const SCORE_FIVE = '5';
	const SCORE_SIX = '6';
	const BALANCE = 'balance';

	const FATALITY = 'fatality';
	const TECHNICAL = 'technical';

	const TOTAL = 'total';

	const MALE = 'm';
	const FEMALE = 'f';

	const IMG_SMALL = '_sq';
	const IMG_NORMAL = '';
	const IMG_SMALL_WIDTH = 50;
	const IMG_SMALL_HEIGHT = 50;
	const IMG_NORMAL_WIDTH = 300;
	const IMG_NORMAL_HEIGHT = 400;
	const IMG_FOLDER_URL = '/images/pipemen';

	const PROFILE_URL_PREFIX = '/pm';

    const INFO_KEYS_ID = 'id';
    const INFO_KEYS_NAME = 'name';
    const INFO_KEYS_SURNAME = 'surname';
    const INFO_KEYS_GENDER = 'gender';
    const INFO_KEYS_COUNTRY = 'country';
    const INFO_KEYS_CITY = 'city';
    const INFO_KEYS_EMAIL = 'email';
    const INFO_KEYS_DESCRIPTION = 'description';
    const INFO_KEYS_USER_ID = "userId";
    const INFO_KEYS_USER = 'user';
    const INFO_KEYS_IMAGE_URL = 'image_url';
    const INFO_KEYS_USER_IMAGE_URL = 'user_image_url';
    const INFO_KEYS_DETAIL_NOT_SET = 'detail name is not set';

    const INFO_KEYS_RUSSIAN_NAME = 'Имя';
    const INFO_KEYS_RUSSIAN_SURNAME = 'Фамилия';
    const INFO_KEYS_RUSSIAN_GENDER = 'Пол';
    const INFO_KEYS_RUSSIAN_COUNTRY = 'Страна';
    const INFO_KEYS_RUSSIAN_CITY = 'Город';
    const INFO_KEYS_RUSSIAN_EMAIL = 'E-mail';
    const INFO_KEYS_RUSSIAN_DESCRIPTION = 'Описание';
    const INFO_KEYS_RUSSIAN_USER = 'Привязанный пользователь';
    
	private $id;
	private $name;
	private $surname;
	private $gender;
	private $country;
	private $city;
	private $email;
	private $description;

	private $user;
	private $userLoaded = false;

    /**
	 * contains statistics about all the cups
	 * which our current pipeman has taken part in.
	 * each element of this array is an associative array
	 * with 4 following elements: cup_id, date, points and place.
	 * @var <array>
	 */
	private $cups;
	private $cupsLoaded = false;
	/**
	 * contains statistics about all the leagues
	 * which our current pipeman has taken part in.
	 * each element of this array is an associative array
	 * with 3 following elements: legue_id, points and place.
	 * @var <array>
	 */
	private $leagues = array();
	private $leaguesLoaded = false;
	/**
	 * contains statistics about all the victories of our current pipeman
	 * on group stage or in the cups with no play-offs.
	 *
	 * @var <array>
	 * associative array with the following fields:
	 * TOTAL, SCORE_FIVE, SCORE_SIX, BALANCE, TECHNICAL and FATALITY.
	 */
	private $victoriesRegularity;
	/**
	 * contains statistics about all the victories of our current pipeman
	 * in the play-offs.
	 *
	 * @var <array>
	 * victoriesPlayOffs[TOTAL] shows total number of play-off victories.
	 * victoriesPlayOffs['stage'] shows number of victories on 1/stage level.
	 *
	 */
	private $victoriesPlayOffs;
	/**
	 * contains statistics about all the victories of our current pipeman
	 * in the play-offs due to technical reasons.
	 *
	 * @var <array>
	 * victoriesPlayOffs[TOTAL] shows total number of technical play-off victories.
	 * victoriesPlayOffs['stage'] shows number of technical victories on 1/stage level.
	 *
	 */
	private $victoriesPlayOffsTechnical;
	/**
	 * contains statistics about all the victories of our current pipeman
	 * in the play-offs made with fatality!!
	 *
	 * @var <array>
	 * victoriesPlayOffs[TOTAL] shows total number of fatality play-off victories.
	 * victoriesPlayOffs['stage'] shows number of faatality victories on 1/stage level.
	 *
	 */
	private $victoriesPlayOffsFatality;
	/**
	 * contains statistics about all the defeats of our current pipeman
	 * on group stage or in the cups with no play-offs.
	 *
	 * @var <array>
	 * associative array with the following fields:
	 * TOTAL, SCORE_FIVE, SCORE_SIX, BALANCE, TECHNICAL and FATALITY.
	 */
	private $defeatsRegularity;
	/**
	 * contains statistics about all the defeats of our current pipeman
	 * in the play-offs.
	 *
	 * @var <array>
	 * defeatsPlayOffs[TOTAL] shows total number of play-off defeats.
	 * defeatsPlayOffs['stage'] shows number of defeats on 1/stage level.
	 *
	 */
	private $defeatsPlayOffs;
	/**
	 * contains statistics about all the defeats of our current pipeman
	 * in the play-offs due to technical reasons.
	 *
	 * @var <array>
	 * defeatsPlayOffs[TOTAL] shows total number of technical play-off defeats.
	 * defeatsPlayOffs['stage'] shows number of technical defeats on 1/stage level.
	 *
	 */
	private $defeatsPlayOffsTechnical;
	/**
	 * contains statistics about all the defeats of our current pipeman
	 * in the play-offs made with fatality!!
	 *
	 * @var <array>
	 * defeatsPlayOffs[TOTAL] shows total number of fatality play-off defeats.
	 * defeatsPlayOffs['stage'] shows number of faatality defeats on 1/stage level.
	 *
	 */
	private $defeatsPlayOffsFatality;
	private $gamesLoaded = false;
	/**
	 * @var <array>
	 * the first element shows the number of games
	 * with the most common opponent, and all the rest elements
	 * are ids of theese opponents from the `p_man`.
	 */
	private $commonOpponentIds;
	private $commonOpponentIdsLoaded = false;
	/**
	 * @var <array>
	 * the i-th element is an integer equal to the number of games between
	 * our current pipeman and the pipeman with id = i;
	 */
	private $opponentIds;
	private $opponentIdsLoaded = false;
	/**
	 * @var <array>
	 * the i-th element of this array shows
	 * how many times our current pipeman has defeated
	 * the pipeman with id from the `p_man` equal to i.
	 */
	private $defeatedOpponentIds;
	/**
	 * I have no idea how to call that field!!!!!!!!!!!!
	 * I have no idea how to call that field!!!!!!!!!!!!
	 * I have no idea how to call that field!!!!!!!!!!!!
	 * I have no idea how to call that field!!!!!!!!!!!!
	 * 
	 * @var <array>
	 * the i-th element of this array shows how many times our current pipeman
	 * has lost a match against the pipeman with id from the `p_man` equal to i.
	 */
	private $unluckyOpponentIds;
	private $victoriesAndDefeatsLoaded = false;


	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getSurname() {
		return $this->surname;
	}

	public function setSurname($surname) {
		$this->surname = $surname;
	}

	public function getFullName() {
		return $this->name . ' ' . $this->surname;
	}

	public function getShortName() {
		return $this->surname . ' ' . substring($this->name, 0, 1) . '.';
	}

	public function getGender() {
		return $this->gender;
	}

	public function isMale() {
		return $this->gender == self::MALE;
	}

	public function setGender($gender) {
		$this->gender = $gender;
	}

	public function getCountry() {
		return $this->country;
	}

	public function setCountry($country) {
		$this->country = $country;
	}

	public function getCity() {
		return $this->city;
	}

	public function setCity($city) {
		$this->city = $city;
	}

	public function getEmail() {
		return $this->email;
	}

	public function setEmail($email) {
		$this->email = $email;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		$this->description = $description;
	}

	public function getUser() {
		if (!$this->userLoaded) {
			$this->user = User::getByPmid($this->getId());
			$this->userLoaded = true;
		}
		return $this->user;
	}

	private function init($id, $name, $surname, $gender, $country, $city, $email, $description) {
		$this->id = $id;
		$this->name = $name;
		$this->surname = $surname;
		$this->gender = $gender;
		$this->country = $country;
		$this->city = $city;
		$this->email = $email;
		$this->description = $description;
	}

	private function resetStats($maxStage) {
		$array = array();
		if ($maxStage == 3)
			$maxStage++;
		for ($i = $maxStage; $i >= 1; $i /= 2)
			$array["$i"] = 0;
		$array["3"] = 0;
		$array[self::TOTAL] = 0;

		$this->defeatsPlayOffs = $array;
		$this->defeatsPlayOffsFatality = $array;
		$this->defeatsPlayOffsTechnical = $array;
		$this->victoriesPlayOffs = $array;
		$this->victoriesPlayOffsFatality = $array;
		$this->victoriesPlayOffsTechnical = $array;

		$array = array();
		$array[self::TOTAL] = 0;
		$array[self::SCORE_FIVE] = 0;
		$array[self::SCORE_SIX] = 0;
		$array[self::BALANCE] = 0;
		$array[self::FATALITY] = 0;
		$array[self::TECHNICAL] = 0;

		$this->victoriesRegularity = $array;
		$this->defeatsRegularity = $array;
	}

	private static $players = array();

	private function __construct($id, $row = null) {
		if ($row == null) {
			$row = mysql_fetch_assoc(PlayerDBClient::selectById($id));
			if (!$row) throw new InvalidIdException("Incorrect player id=$id");
		}

		$this->id = $row[self::INFO_KEYS_ID];
		$this->name = $row[self::INFO_KEYS_NAME];
		$this->surname = $row[self::INFO_KEYS_SURNAME];
		$this->gender = $row[self::INFO_KEYS_GENDER];
		$this->country = $row[self::INFO_KEYS_COUNTRY];
		$this->city = $row[self::INFO_KEYS_CITY];
		$this->email = $row[self::INFO_KEYS_EMAIL];
		$this->description = $row[self::INFO_KEYS_DESCRIPTION];
	}

	/**
	 * @param <type> $id
	 * @return Player
	 */
	public static function getById($id) {
		if (!isset($id) || $id <= 0) return null;
		if (isset(self::$players[$id])) return self::$players[$id];
		self::$players[$id] = new Player($id);
		return self::$players[$id];
	}

	public static function getByIds($pmIds) {
		$result = array();
		foreach ($pmIds as $pmid) {
			$result[] = self::getById($pmid);
		}

		return $result;
	}

	public static function getByData($row) {
		$id = $row[self::INFO_KEYS_ID];
		if (isset($id) && isset(self::$players[$id])) return self::$players[$id];

		$player = new Player(null, $row);
		self::$players[$player->getId()] = $player;
		return $player;
	}

	public static function getByIterator(MySQLResultIterator $iterator) {
		$result = array();
		while ($iterator->valid()) {
			$result[] = self::getByData($iterator->current());
			$iterator->next();
		}

		return $result;;
	}

	public static function create($name, $surname, $gender = null, $country = "", $city = "", $email = "", $description = "") {

		if ($gender == null) {
			$db_gender = PlayerDBClient::getGenderByName($name);
			$gender = $db_gender ? $db_gender : self::MALE;
		}

		PlayerDBClient::insert($name, $surname, $gender, $country, $city,
								$email, $description);

		return Player::getById(mysql_insert_id());
	}

	/**
	 * loads all the info-fields from this class to the database
	 */
	public function updateInfo() {
		PlayerDBClient::update($this);
	}

	/**
	 * loads all the info-fields from the database
	 * and re-writes them in this instance of the class.
	 */
	public function loadInfo() {
		$req = PlayerDBClient::selectById($this->id);
		if ($pm = mysql_fetch_assoc($req))
			$this->init(
                $pm[self::INFO_KEYS_ID], 
                $pm[self::INFO_KEYS_NAME], 
                $pm[self::INFO_KEYS_SURNAME],
                $pm[self::INFO_KEYS_GENDER],
                $pm[self::INFO_KEYS_COUNTRY], 
                $pm[self::INFO_KEYS_CITY], 
                $pm[self::INFO_KEYS_EMAIL], 
                $pm[self::INFO_KEYS_DESCRIPTION]
            );
		else
			throw new InvalidArgumentException("There is no pipeman with id=$this->id");
	}

	/**
	 * returns the statistics of all the cups
	 * which our current pipeman has taken part in.
	 * @return <array> each element of the returned array
	 * is also an associative array with 4 following elements:
	 * cup_id, date, points and place.
	 */
	public function getCups() {
		if (!$this->cupsLoaded) {
			$req = ResultCupDBClient::selectCups($this->id);
			$this->cups = array();
			while ($cupResult = mysql_fetch_assoc($req))
				$this->cups[] = $cupResult;
			$this->cupsLoaded = true;
		}
		return $this->cups;
	}

	/**
	 * @return array
	 * [(league_id, place, points), ...]
	 */
	public function getLeaguesInfo() {
		if (!$this->leaguesLoaded) {
			$req = LeagueDBClient::selectLeaguesForPlayer($this->id);
			while ($lg = mysql_fetch_assoc($req)) {
				// preloading current rating if neccessary
				RatingTable::getInstance($lg['league_id']);
				$today = date("Y-m-d");
				$movement = RatingTable::getRatingMovementInterval($today, $today, $lg['league_id'], $this->id);
				$this->leagues[] = array (
					'league_id' => $lg['league_id'],
					'place' => $movement[0]['place'],
					'points' => round($movement[0]['points'], 2)
				);
			}
			$this->leaguesLoaded = true;
		}
		return $this->leagues;
	}

	public function getTrophiesInfo() {
		$trophies = array();
		$iterator = ResultCupDBClient::getCompetitionVictories($this->id);
		while ($iterator->valid()) {
			$data = $iterator->current();
			if (!isset($trophies[$data['league_id']])) {
				$trophies[$data['league_id']] = array();
			}
			$trophies[$data['league_id']][] = Competition::getByData($data);
			$iterator->next();
		}

		return $trophies;
	}

	public function getCompetitionsInfo() {
		$counts = Competition::getPmCount();
		$info = array();
		$iterator = ResultCupDBClient::getCompetitionPlaces($this->id);
		while ($iterator->valid()) {
			$data = $iterator->current();
			$info[] = array (
				'competition' => Competition::getByData($data),
				'place' => ($data['parent_cup_id'] == 0) ? $data['place'] : 0,
				'count' => $counts[$data[self::INFO_KEYS_ID]]
			);
			$iterator->next();
		}

		return $info;
	}

	/**
	 * Returns the statistics about our current pipeman's victories
	 * in regularity or in playoffs depending on the peremeter value.
	 *
	 * @param <boolean>
	 * @return <array>
	 *
	 * If the parameter is 'true' the returned array has the following fields:
	 * TOTAL, SCORE_FIVE, SCORE_SIX, BALANCE, TECHNICAL and FATALITY.
	 *
	 * If the parameter is false, the returned array has the folowing sturcture:
	 * result[0] shows total number of play-off victories.
	 * result[stage] shows number of victories on 1/stage level.
	 */
	public function getVictories($regularity) {
		$this->loadGameStats();
		return $regularity ? $this->victoriesRegularity : $this->victoriesPlayOffs;
	}

	/**
	 * Returns the statistics about our current pipeman's defeats.
	 *
	 * @param <boolean>
	 * @return <array>
	 * 
	 * If the parameter is 'true' the returned array has the following fields:
	 * TOTAL, SCORE_FIVE, SCORE_SIX, BALANCE, TECHNICAL and FATALITY.
	 *
	 * If the parameter is false, the returned array has the folowing sturcture:
	 * result[0] shows total number of play-off defeats.
	 * result[stage] shows number of defeats on 1/stage level.
	 */
	public function getDefeats($regularity) {
		$this->loadGameStats();
		return $regularity ? $this->defeatsRegularity : $this->defeatsPlayOffs;
	}

	/**
	 * fills the both arrays `victories` and both arrays `defeats` from the database.
	 */
	private function loadGameStats() {
		if (!$this->gamesLoaded) {
			$req = GameDBClient::selectGames($this->id);

			$firstLine = true;
			while ($game = mysql_fetch_assoc($req)) {
				if ($firstLine) {
					$this->resetStats($game['stage']);
					$firstLine = false;
				}
				$this->handleGame($game);
			}
			$this->gamesLoaded = true;
		}
	}

	private function handleGame($game) {
		$maxScore = max($game['score1'], $game['score2']);
		$thisPlayerWon = ( $maxScore == $game['score1'] && $game['pmid1'] == $this->id ) ||
						 ( $maxScore == $game['score2'] && $game['pmid2'] == $this->id );
		$regularity = $game['stage'] == 0;

		if ($thisPlayerWon)
			if ($regularity)
				$result = $this->victoriesRegularity;
			else
				$result = $this->victoriesPlayOffs;
		else
			if ($regularity)
				$result = $this->defeatsRegularity;
			else
				$result = $this->defeatsPlayOffs;

		$result[self::TOTAL]++;

		if ($regularity) {
			switch ($game['tech']) {
				case Game::GAME_TYPE_TECHNICAL : $result[self::TECHNICAL]++; break;
				case Game::GAME_TYPE_FATALITY : $result[self::FATALITY]++;	break;
				default :
					switch ($maxScore) {
						case 5 : $result[self::SCORE_FIVE]++;	break;
						case 6 : $result[self::SCORE_SIX]++; break;
						default : $result[self::BALANCE]++;
					}
			}

			if ($thisPlayerWon)
				$this->victoriesRegularity = $result;
			else
				$this->defeatsRegularity = $result;
		} else {
			$stage = $game['stage'];
			$result["$stage"]++;
			if ($thisPlayerWon) {
				$this->victoriesPlayOffs = $result;
				switch ($game['tech']) {
					case Game::GAME_TYPE_TECHNICAL: $this->victoriesPlayOffsTechnical++; break;
					case Game::GAME_TYPE_FATALITY: $this->victoriesPlayOffsFatality++; break;
				}
			} else {
				$this->defeatsPlayOffs = $result;
				switch ($game['tech']) {
					case Game::GAME_TYPE_TECHNICAL: $this->defeatsPlayOffsTechnical++; break;
					case Game::GAME_TYPE_FATALITY: $this->defeatsPlayOffsFatality++;	break;
				}
			}
		}
	}

	public function countGames() {
		if ($this->opponentIdsLoaded)
			return array_sum($this->opponentIds);

		$this->loadGameStats();
		return $this->victoriesRegularity[self::TOTAL] +
				$this->victoriesPlayOffs[self::TOTAL] +
				$this->defeatsRegularity[self::TOTAL] +
				$this->defeatsPlayOffs[self::TOTAL];
	}

	public function countRegularityGames() {
		if (!$this->gamesLoaded)
			$this->loadGameStats();
		return $this->victoriesRegularity[self::TOTAL] +
				$this->defeatsRegularity[self::TOTAL];
	}

	/**
	 * Calculates number of games on 1/$stage play-off round.
	 * If the parameter is not set,
	 * the metod returns total number of play-off games.
	 * @param <int> $stage
	 * @return <int>
	 */
	public function countPlayOffGames($stage = Player::TOTAL) {
		$this->loadGameStats();
		return $this->victoriesPlayOffs["$stage"] +
				$this->defeatsPlayOffs["$stage"];
	}

	public function countVictories() {
		return $this->countVictoriesPlayOffs() + $this->countVictoriesRegularity();
	}

	public function countVictoriesRegularity() {
		$this->loadGameStats();
		return $this->victoriesRegularity[self::TOTAL];
	}

	public function countVictoriesRegularity5() {
		$this->loadGameStats();
		return $this->victoriesRegularity[self::SCORE_FIVE];
	}

	public function countVictoriesRegularity6() {
		$this->loadGameStats();
		return $this->victoriesRegularity[self::SCORE_SIX];
	}

	public function countVictoriesRegularityBalance() {
		$this->loadGameStats();
		return $this->victoriesRegularity[self::BALANCE];
	}

	public function countVictoriesRegularityTechnical() {
		$this->loadGameStats();
		return $this->victoriesRegularity[self::TECHNICAL];
	}

	public function countVictoriesRegularityFatality() {
		$this->loadGameStats();
		return $this->victoriesRegularity[self::FATALITY];
	}

	/**
	 * returns the number of victories on 1/(parameter value) level.
	 * @param int $stage
	 * if no parameter is set or the parameter equals zero
	 * the method returns a total number of play-off victories.
	 * @return int
	 */
	public function countVictoriesPlayOffs($stage = 0) {
		if ($stage < 0)
			throw new InvalidArgumentException("parameter stage in method
                countVictoriesPlayOffs(\$stage) must be >= 0 !!");
		else {
			$this->loadGameStats();
			return $stage > 0 ? $this->victoriesPlayOffs["$stage"] : $this->victoriesPlayOffs[self::TOTAL];
		}
	}

	/**
	 * returns the number of technical victories on 1/(parameter value) level.
	 * @param int $stage
	 * if no parameter is set or the parameter equals zero
	 * the method returns a total number of play-off victories.
	 * @return int
	 */
	public function countVictoriesPlayOffsTechnical($stage = 0) {
		if ($stage < 0)
			throw new InvalidArgumentException("parameter stage in method
                countVictoriesPlayOffs(\$stage) must be >= 0 !!");
		else {
			$this->loadGameStats();
			return $stage > 0 ? $this->victoriesPlayOffsTechnical["$stage"] : $this->victoriesPlayOffsTechnical[self::TOTAL];
		}
	}

	/**
	 * returns the number of Fatality victories on 1/(parameter value) level.
	 * @param int $stage
	 * if no parameter is set or the parameter equals zero
	 * the method returns a total number of play-off victories.
	 * @return int
	 */
	public function countVictoriesPlayOffsFatality($stage = 0) {
		if ($stage < 0)
			throw new InvalidArgumentException("parameter stage in method
                countVictoriesPlayOffs(\$stage) must be >= 0 !!");
		else {
			$this->loadGameStats();
			return $stage > 0 ? $this->victoriesPlayOffsFatality["$stage"] : $this->victoriesPlayOffsFatality[self::TOTAL];
		}
	}

	public function countDefeatsRegularity() {
		$this->loadGameStats();
		return $this->defeatsRegularity[self::TOTAL];
	}

	public function countDefeatsRegularity5() {
		$this->loadGameStats();
		return $this->defeatsRegularity[self::SCORE_FIVE];
	}

	public function countDefeatsRegularity6() {
		$this->loadGameStats();
		return $this->defeatsRegularity[self::SCORE_SIX];
	}

	public function countDefeatsRegularityBalance() {
		$this->loadGameStats();
		return $this->defeatsRegularity[self::BALANCE];
	}

	public function countDefeatsRegularityTechnical() {
		$this->loadGameStats();
		return $this->defeatsRegularity[self::TECHNICAL];
	}

	public function countDefeatsRegularityFatality() {
		$this->loadGameStats();
		return $this->defeatsRegularity[self::FATALITY];
	}

	/**
	 * returns the number of defeats on 1/(parameter value) level.
	 * @param <type> $stage
	 * if no parameter is set or the parameter equals zero
	 * the method returns a total number of play-off defeats.
	 * @return <int>
	 */
	public function countDefeatsPlayOffs($stage = 0) {
		if ($stage < 0)
			throw new InvalidArgumentException("parameter stage in method
                countDefeatsPlayOffs(\$stage) must be >= 0 !!");
		else {
			$this->loadGameStats();
			return $stage > 0 ? $this->defeatsPlayOffs["$stage"] : $this->defeatsPlayOffs[self::TOTAL];
		}
	}

	/**
	 * returns the number of technical defeats on 1/(parameter value) level.
	 * @param int $stage
	 * if no parameter is set or the parameter equals zero
	 * the method returns a total number of play-off defeats.
	 * @return int
	 */
	public function countDefeatsPlayOffsTechnical($stage = 0) {
		if ($stage < 0)
			throw new InvalidArgumentException("parameter stage in method
                countdefeatsPlayOffs(\$stage) must be >= 0 !!");
		else {
			$this->loadGameStats();
			return $stage > 0 ? $this->defeatsPlayOffsTechnical["$stage"] : $this->defeatsPlayOffsTechnical[self::TOTAL];
		}
	}

	/**
	 * returns the number of Fatality defeats on 1/(parameter value) level.
	 * @param int $stage
	 * if no parameter is set or the parameter equals zero
	 * the method returns a total number of play-off defeats.
	 * @return int
	 */
	public function countDefeatsPlayOffsFatality($stage = 0) {
		if ($stage < 0)
			throw new InvalidArgumentException("parameter stage in method
                countdefeatsPlayOffs(\$stage) must be >= 0 !!");
		else {
			$this->loadGameStats();
			return $stage > 0 ? $this->defeatsPlayOffsFatality["$stage"] : $this->defeatsPlayOffsFatality[self::TOTAL];
		}
	}

	/**
	 *
	 * @return <array>
	 * the i-th element is an integer equal to the number of games between
	 * our current pipeman and the pipeman with id = i;

	 */
	public function getOpponentIds() {
		$this->loadOpponentIds();
		return $this->opponentIds;
	}

	private function loadOpponentIds() {
		if (!$this->opponentIdsLoaded) {
			if (!$this->victoriesAndDefeatsLoaded)
				$this->loadVictoriesAndDefeats();

			$maxId = max(count($this->unluckyOpponentIds), count($this->defeatedOpponentIds));
			for ($i = 0; $i <= $maxId; $i++)
				$this->opponentIds[$i] = 0;

			foreach ($this->unluckyOpponentIds as $key => $val)
				$this->opponentIds[$key] += $val;
			foreach ($this->defeatedOpponentIds as $key => $val)
				$this->opponentIds[$key] += $val;

			$this->opponentIdsLoaded = true;
		}
	}

	/**
	 * this method calculates with whom our current pipeman
	 * has played most of all. If there are many of them
	 * the method returns them all.
	 * @return <array> the first element shows the number
	 * of games with the most common opponent, and all the rest elements
	 * are ids of theese opponents from the `p_man`.
	 */
	public function getCommonOpponentIds() {
		if (!$this->commonOpponentIdsLoaded) {
			$this->loadOpponentIds();
			$this->commonOpponentIds = array_most_common_elements($this->opponentIds);
			$this->commonOpponentIdsLoaded = true;
		}
		return $this->commonOpponentIds;
	}

	/**
	 * returns all the pipemen defeated by of our current pipeman,
	 * and the number of theese defeats for each of them.
	 * @return <array>
	 * the i-th element of this array shows how many times our current pipeman
	 * has defeated the pipeman with id from the `p_man` equal to i.
	 * the 0-th element shows the total number of won matches.
	 */
	public function getDefeatedOpponentIds() {
		if (!$this->victoriesAndDefeatsLoaded)
			$this->loadVictoriesAndDefeats();
		return $this->defeatedOpponentIds;
	}

	/**
	 * returns all the pipemen againts whom our current pipeman has lost
	 * a match and the number of such matches for each of them.
	 * @return <array>
	 * the i-th element of this array shows how many times our current pipeman
	 * has lost a match against the pipeman with id from the `p_man` equal to i.
	 * * the 0-th element shows the total number of lost matches.
	 */
	public function getUnluckyOpponentIds() {
		if (!$this->victoriesAndDefeatsLoaded)
			$this->loadVictoriesAndDefeats();
		return $this->unluckyOpponentIds;
	}

	/**
	 * fills the array `defeatedOpponentIds` and `unluckyOpponentIds`.
	 */
	private function loadVictoriesAndDefeats() {
		$this->defeatedOpponentIds = null;
		$this->unluckyOpponentIds = null;

		$req = GameDBClient::selectOpponentsAndScore($this->id);

		$firstLine = true;
		while ($game = mysql_fetch_assoc($req)) {
			if ($firstLine) {
				for ($i = 0; $i <= $game['opp_id']; $i++) {
					$this->defeatedOpponentIds[$i] = 0;
					$this->unluckyOpponentIds[$i] = 0;
				}
				$firstLine = false;
			}
			if ($game['my_score'] > $game['opp_score']) {
				$this->defeatedOpponentIds[$game['opp_id']]++;
				$this->defeatedOpponentIds[0]++;
			} else {
				$this->unluckyOpponentIds[$game['opp_id']]++;
				$this->unluckyOpponentIds[0]++;
			}
		}
		$this->victoriesAndDefeatsLoaded = true;
	}

	public function hasImage($type = self::IMG_NORMAL) {
		return file_exists(
				dirname(__FILE__) . '/../../images/pipemen/' . $this->getId() . $type . '.jpg'
		);
	}

	public function getImageURL($type = self::IMG_NORMAL) {
		if (!$this->hasImage($type)) return self::IMG_FOLDER_URL . '/default' . $type . '.jpg';
		return self::IMG_FOLDER_URL . '/' . $this->getId() . $type . '.jpg';
	}

	public static function existsById($id) {
		return (boolean) mysql_num_rows(PlayerDBClient::selectById($id));
	}

	public function toArray() {
		return array(
			self::INFO_KEYS_ID => $this->getId(),
			'value' => $this->getFullName()
		);
	}

	public function toJSON() {
		$description = str_replace("\r\n", "\\n", $this->getDescription());
		$description = str_replace('"', '\"', $description);

		$user = $this->getUser();
		return json(array(
			self::INFO_KEYS_ID => $this->getId(),
			self::INFO_KEYS_NAME => $this->getName(),
			self::INFO_KEYS_SURNAME => $this->getSurname(),
			self::INFO_KEYS_GENDER => $this->getGender(),
			self::INFO_KEYS_EMAIL => $this->getEmail(),
			self::INFO_KEYS_COUNTRY => $this->getCountry(),
			self::INFO_KEYS_CITY => $this->getCity(),
			self::INFO_KEYS_DESCRIPTION => $this->getDescription(),
			self::INFO_KEYS_USER_ID => $user ? $user->getId() : 0,
			self::INFO_KEYS_IMAGE_URL => $this->getImageURL(),
			self::INFO_KEYS_USER_IMAGE_URL => $user ? $user->getImageURL() : User::getDefaultImageUrl()
		));
	}

	/**
	 * returned array has such format:
	 *
	 * <pre>array (
	 * 		array (
	 * 			'date' => ...,
	 * 			'place' => ...,
	 * 			'points' => ...
	 * 		),
	 * 		...
	 * )</pre>
	 *
	 * and is sorted by 'date'
	 *
	 * @param $leagueId
	 * @return array
	 */
	public function getRatingMovement($leagueId = 1) {
		return RatingTable::getRatingMovement($leagueId, $this->getId());
	}

	public function getRatingMovementInterval($begin, $end, $leagueId = 1) {
		return RatingTable::getRatingMovementInterval($begin, $end, $leagueId, $this->getId());
	}

	public function getBestRank($leagueId = 1) {
		return RatingTable::getBestRank($leagueId, $this->getId());
	}

	public static function getAll() {
		$result = array();
		$req = PlayerDBClient::getAll();
		while ($row = mysql_fetch_assoc($req)) {
			try {
				$result[] = new Player(-1, $row);
			} catch (Exception $e) {
				// TODO use error log file
				echo $e->getMessage();
			}
		}

		return $result;
	}

	public static function countAll() {
		return PlayerDBClient::countAll();
	}

	public function toHTML($fromCupMS = true) {
		$src = ($fromCupMS ? MAIN_URL : '') . $this->getImageURL(self::IMG_SMALL);
		$alt = $this->getFullName();
		$name = $this->getName();
		$surname = $this->getSurname();
		$id = $this->getId();
		$html =
<<< LABEL
<div class='person_to_html'>
	<img src='$src' alt='$alt'/>
	<div>
		<div>$surname</div>
		<div>$name</div>
		<div>
			<div>ID $id</div>
		</div>
	</div>
</div>
LABEL;

		return array_merge(array('html' => $html), $this->toArray());
	}

	public static function getAllToHTML($fromCupMS = true) {
		return array_transform_toHTML(Player::getAll(), $fromCupMS);
	}

	public static function getImageById($pmid = null, $type = self::IMG_NORMAL) {
		if ($pmid == null || !file_exists(dirname(__FILE__) . '/../../images/pipemen/' . $pmid . $type . '.jpg'))
			return self::IMG_FOLDER_URL . '/default' . $type . '.jpg';
		return self::IMG_FOLDER_URL . '/' . $pmid . $type . '.jpg';
	}

	public static function createByUser(User $user) {
		return Player::create(
				$user->getName(),
				$user->getSurname(),
				null,
				$user->getCountryName(),
				$user->getCityName(),
				$user->getEmail()
			);
	}

	public static function getDetailName($str) {
		switch($str) {
			case self::INFO_KEYS_NAME : return self::INFO_KEYS_RUSSIAN_NAME;
			case self::INFO_KEYS_SURNAME : return self::INFO_KEYS_RUSSIAN_SURNAME;
			case self::INFO_KEYS_GENDER :	return self::INFO_KEYS_RUSSIAN_GENDER;
			case self::INFO_KEYS_COUNTRY : return self::INFO_KEYS_RUSSIAN_COUNTRY;
			case self::INFO_KEYS_CITY : return self::INFO_KEYS_RUSSIAN_CITY;
			case self::INFO_KEYS_EMAIL : return self::INFO_KEYS_RUSSIAN_EMAIL;
			case self::INFO_KEYS_DESCRIPTION : return self::INFO_KEYS_RUSSIAN_DESCRIPTION;
			case self::INFO_KEYS_USER : return self::INFO_KEYS_RUSSIAN_USER;
		}
		return self::INFO_KEYS_DETAIL_NOT_SET;
	}
	
	private static function compareLeaguesByInfo($first, $second) {
		if ($first['league_id'] == 1) return 1;
		if ($second['league_id'] == 1) return -1;

		return $first['place'] == $second['place'] ?
			$second['league_id'] - $first['league_id'] :
			$second['place'] - $first['place'];
	}

	public function getTopLeagueInfos() {
		$leagues = $this->getLeaguesInfo();
		usort($leagues, 'Player::compareLeaguesByInfo');
		return $leagues;
	}

	public function getTopLeagueIds() {
		$result = array();
		foreach ($this->getTopLeagueInfos() as $league)
			$result[] = $league['league_id'];
		return $result;
	}

	public function getTopLeagues() {
		return League::getByIds($this->getTopLeagueIds());
	}

	public function getURL() {
		return self::PROFILE_URL_PREFIX . $this->getId();
	}

	public static function getByIdFromArray($pmId, $players) {
		foreach ($players as $player)
			if ($player->getId() == $pmId)
				return $player;
	}

	public static function getURLById($pmid) {
		return self::PROFILE_URL_PREFIX . $pmid;
	}
}
?>
