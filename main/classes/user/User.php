<?php

require_once dirname(__FILE__).'/../cupms/League.php';
require_once dirname(__FILE__).'/../cupms/Competition.php';
require_once dirname(__FILE__).'/../cupms/Player.php';
//require_once dirname(__FILE__).'/../cupms/Cup.php';
//require_once dirname(__FILE__).'/../cupms/CupFactory.php';

require_once dirname(__FILE__) . '/../content/Connection.php';

require_once dirname(__FILE__).'/../db/UserDBClient.php';
require_once dirname(__FILE__).'/../db/UserDataDBClient.php';
require_once dirname(__FILE__).'/../db/UserPermissionDBClient.php';
require_once dirname(__FILE__).'/../db/LeagueDBClient.php';

/**
 * Description of User
 *
 * @author Artyom Grigoriev
 */
class User {

	const KEY_VKID = 'vkid';
	const KEY_PMID = 'pmid';
	const KEY_LOGIN = 'login';
	const KEY_EMAIL = 'email';
	const KEY_PASSHASH = 'passhash';
	const KEY_TEMP_PASSHASH = 'temppasshash';
	const KEY_SKYPE = 'skype';
	const KEY_ICQ = 'icq';
	const KEY_BIRTHDAY = 'birthday';
	const KEY_COUNTRY = 'country';
	const KEY_CITY = 'city';
	const KEY_PHOTO = 'photo';

	const FIELD_NAME = 'name';
	const FIELD_SURNAME = 'surname';

	const IMAGE_NORMAL = '.jpg';
	const IMAGE_SQUARE = '_sq.jpg';
	const IMAGE_SQUARE_SMALL = '_sq_small.jpg';
    const IMAGES_DIR = '/images/users/';

    private $id;
    private $name;
    private $surname;

    private $data = array();
    private $dataLoaded = false;

    private $TA = false;  //totalAdmin
    private $LA = array();//boolean array of user league administrative ability
    private $CA = array();//boolean array of user competition administrative ability
    private $permissionsLoaded = false;

	private static $KEYS = array(
		self::FIELD_NAME, self::FIELD_SURNAME,
		self::KEY_BIRTHDAY, self::KEY_EMAIL, self::KEY_ICQ, self::KEY_LOGIN,
		self::KEY_PASSHASH, self::KEY_PMID, self::KEY_SKYPE, self::KEY_VKID,
		self::KEY_CITY, self::KEY_COUNTRY, self::KEY_PHOTO,
		self::KEY_TEMP_PASSHASH
	);
	public static final function getKeys() {
		return self::$KEYS;
	}

	private static $CONTACT_KEYS = array(
		self::KEY_ICQ, self::KEY_SKYPE, self::KEY_VKID
	);
	public static final function getContactKeys() {
		return self::$CONTACT_KEYS;
	}

	private static $EDITABLE_KEYS = array(
		self::KEY_BIRTHDAY, 
		self::KEY_EMAIL, self::KEY_CITY, self::KEY_COUNTRY,
		self::KEY_ICQ, self::KEY_SKYPE, self::KEY_VKID
	);
	public static final function getEditableKeys() {
		return self::$EDITABLE_KEYS;
	}

	private static $users = array();

    private function  __construct($uid, $data = null) {
		if ($data == null) {
			$req = UserDBClient::selectById($uid);
			if ($data = mysql_fetch_assoc($req)) {

			} else {
				throw new InvalidArgumentException('There is no user with id='.$uid);
			}
		}

		$this->id = $data['id'];
		$this->name = $data['name'];
		$this->surname = $data['surname'];
    }

	public static function getById($id) {
		if ($id <= 0) return null;
		if (isset (self::$users[$id])) return self::$users[$id];
		self::$users[$id] = new User($id);
		return self::$users[$id];
	}

	public static function getByData($data) {
		if (isset (self::$users[$data['id']])) return self::$users[$data['id']];
		self::$users[$data['id']] = new User(-1, $data);
		return self::$users[$data['id']];
	}

	public static function getByKey($key, $value) {
		$users = array();
		$iterator = UserDBClient::getByKey($key, $value);
		while ($iterator->valid()) {
			$data = $iterator->current();
			$users[] = new User(-1, $data);
			$iterator->next();
		}

		return $users;
	}

	public static function getAll() {
		$req = UserDBClient::selectAll();
		$result = array();
		while ($data = mysql_fetch_assoc($req)) {
			$result[] = User::getByData($data);
		}
		return $result;
	}

	/**
	 *
	 * @param string $name
	 * @param string $surname
	 * @return User
	 */
	public static function create($name, $surname) {
		$id = UserDBClient::insert($name, $surname);
		return User::getById($id);
	}

    public function toArray() {
		return array(
			'id' => $this->getId(),
			'value' => $this->getFullName()
		);
	}

	public function isTotalAdmin(){
        $this->loadPermissions();
        return $this->TA;
    }

    public function isLeagueAdmin($leagueId = 0) {
        if ($leagueId == 0) return count($this->LA) > 0;
		assertLeague($leagueId);
        $this->loadPermissions();
        if (!array_key_exists($leagueId, $this->LA)) return false;
        return $this->LA[$leagueId];
    }

	public function isCompetitionAdmin($competitionId = 0) {
		if ($competitionId == 0) return count($this->CA) > 0;
		assertCompetition($competitionId);
		$this->loadPermissions();
		if (!array_key_exists($competitionId, $this->CA)) return false;
		return $this->CA[$competitionId];
	}

	/**
	 * Method returns if user is able to admin any competition in such league
	 * @param <type> $leagueId
	 * @return <type> boolean
	 * @author Andrew Solozobov
	 */
	public function isLeagueCompetitionAdmin($leagueId){
		assertLeague($leagueId);

		if (array_contains($this->LA, $leagueId))
			return true;

		foreach (array_keys($this->CA) as $compId){
			$c = Competition::getById($compId);
			if ($c->getLeagueId() == $leagueId){
				return true;
			}
		}
		return false;
	}

    public function getAdministratedLeaguesList() {
		return $this->LA;
    }

    public function getCompetitionList() {
		return $this->CA;
    }

    public function uid() {
		return $this->id;
    }

    public function getId() {
		return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getSurname() {
        return $this->surname;
    }

    public function getFullName() {
        return $this->name . ' ' . $this->surname;
    }

	public function toJSON() {
		$result = '{';
		$result .= '"id": ' . $this->getId() . ',';
		$result .= '"name": "' . $this->getName() . '",';
		$result .= '"surname": "' . $this->getSurname() . '"';
		$result .= '}';
		return $result;
	}

    public function get($key) {
        $this->loadData();
        if (!isset($this->data[$key])) return false;
		return $this->data[$key];
    }

    public function keys() {
        $this->loadData();
        return array_keys($this->data);
    }

    public function put($key, $value) {
		if (!array_contains(self::$KEYS, $key))
			throw new Exception('Incorrect key');

        if (isset($this->data[$key])) {
            $this->data[$key] = $value;
            return UserDataDBClient::update($this->uid(), $key, $value);
        } else {
            $this->data[$key] = $value;
            return UserDataDBClient::insert($this->uid(), $key, $value);
        }
    }

	public function deleteByKey($key) {
		if (!array_contains(self::$KEYS, $key))
			throw new Exception('Incorrect key');

        if (isset($this->data[$key])) {
            $this->data[$key] = null;
            return UserDataDBClient::delete($this->uid(), $key);
        }
    }

	public function hasImage($type = self::IMAGE_NORMAL) {
		$photo = $this->getImagePrefix();
		return file_exists(dirname(__FILE__) . '/../../images/users/' . $photo . $type);
	}

	public function getImageURL($type = self::IMAGE_NORMAL) {
		return $this->hasImage($type) ?
				sprintf(self::IMAGES_DIR .'%s%s', $this->getImagePrefix(), $type) :
                self::getDefaultImageUrl($type);
	}

    public static function getDefaultImageUrl($type = self::IMAGE_NORMAL) {
        return sprintf(self::IMAGES_DIR . 'default' . '%s', $type);
    }

    public function makeTA() {
		return UserPermissionDBClient::insert($this->getId(), 'TA', 0);
	}

	public function makeLA($leagueId) {
		return UserPermissionDBClient::insert($this->getId(), 'LA', $leagueId);
	}

	public function makeCA($competitionId) {
		return UserPermissionDBClient::insert($this->getId(), 'CA', $competitionId);
	}

//	public function deleteTA() {
//		return UserPermissionDBClient::delete($this->getId(), 'TA', 0);
//	}

	public function deleteLA($leagueId) {
		return UserPermissionDBClient::delete($this->getId(), 'LA', $leagueId);
	}

	public function deleteCA($competitionId) {
		return UserPermissionDBClient::delete($this->getId(), 'CA', $competitionId);
	}

	/**
	 * Returns 0 if it does not exist
	 * @return int
	 */
	public function getPmid() {
		return intval($this->get(self::KEY_PMID));
	}

	public function getPlayer() {
		return $this->getPmid() ? Player::getById($this->getPmid()) : null;
	}

	public function getVkid() {
		return intval($this->get(self::KEY_VKID));
	}

	public function getEmail() {
		return $this->get(self::KEY_EMAIL);
	}

	public function getCountryName() {
		return $this->get(self::KEY_COUNTRY);
	}

	public function getCityName() {
		return $this->get(self::KEY_CITY);
	}

	/**
	 * Says if this user has permission to make given action
	 *
	 * $target: <ul>
	 *		<li>Player instance</li>
	 *		<li>League instance</li>
	 *		<li>Competition instance</li>
	 *		<li>Blog instance</li>
	 *		<li>string: 'player', 'league', 'competition' iff $type == 'add'!</li>
	 *			</ul>
	 * $type: 'add', 'edit', 'remove',
	 *			('start', 'stop', 'restart' for defined Competition)
	 *			('add_competition' for defined League)
	 *			('add_post' for defined Blog)
	 *
	 * @param Player|League|Competition|Blog|string $target
	 * @param string $type
	 * @return boolean
	 */
    public function hasPermission($target, $type) {
		if (is_string($target)) {
			switch ($target) {
			case 'total_admin':
				switch ($type) {
				case 'add':
					if ($this->isTotalAdmin()) return true;
				case 'delete':
				default:
					return false;
				}
			case 'player':
				switch ($type) {
				case 'add':
				case 'edit':
				case 'get_data':
				case 'view_editing_page':
					if ($this->isTotalAdmin()) return true;
					if ($this->isLeagueAdmin()) return true;
					if ($this->isCompetitionAdmin()) return true;
				default :
					return false;
				}
			case 'tournament':
				switch ($type) {
				case 'add':
					if ($this->isTotalAdmin()) return true;
					if ($this->isLeagueAdmin()) return true;
					if ($this->isCompetitionAdmin()) return true;
				default :
					return false;
				}
			case 'league':
				switch ($type) {
				case 'add':
					if ($this->isTotalAdmin()) return true;
				default:
					return false;
				}
			case 'competition':
				switch ($type) {
				case 'add':
					if ($this->isTotalAdmin()) return true;
					if ($this->isLeagueAdmin()) return true;
				default:
					return false;
				}
			default:
				return false;
			}
		}

        if ($target instanceof Player) {
            switch ($type) {
            case 'remove':
                if ($this->isTotalAdmin()) return true;
                return false;
            case 'edit':
                if ($this->isTotalAdmin()) return true;
                $info = $target->getLeagues();
                foreach ($info as $row) {
                    if ($this->isLeagueAdmin($row['league_id'])) return true;
                }
                return false;
            default:
                return false;
            }
        }

        if ($target instanceof League) {
            switch ($type) {
            case 'remove':
            case 'delete_admin':
			    if ($this->isTotalAdmin()) return true;
                return false;
            case 'edit':
            case 'add_admin':
			case 'add_competition':
			    if ($this->isTotalAdmin() || $this->isLeagueAdmin($target->getId())) return true;
                return false;
			default:
                return false;
            }
        }

        if ($target instanceof Competition) {
            switch ($type) {
            case 'remove':
            case 'restart':
			case 'delete_admin':
			case 'set_coef':
			    if ($this->isTotalAdmin()) return true;
                if ($this->isLeagueAdmin($target->getLeagueId())) return true;
                return false;
            case 'edit':
			case 'start':
			case 'start_registering':
			case 'stop':
			case 'add_admin':
				if ($this->isTotalAdmin()) return true;
				if ($this->isLeagueAdmin($target->getLeagueId())) return true;
				if ($this->isCompetitionAdmin($target->getId())) return true;
				return false;
			default:
                return false;
            }
        }

		if ($target instanceof Blog) {
			switch ($type) {
			case 'add':
			case 'remove':
				return $this->isTotalAdmin();
			case 'add_post':
			case 'edit':
				if ($this->isTotalAdmin()) return true;
			
				$holders = Connection::getHoldersFor($target);
				foreach ($holders as $holder) {
					if ($holder instanceof League)
						if ($this->hasPermission($holder, 'edit'))
							return true;

					if ($holder instanceof Competition)
						if ($this->hasPermission($holder, 'edit'))
							return true;

					if ($holder instanceof User)
						if ($holder->getId() == $this->getId())
							return true;
				}

				return false;
			}
		}

        return false;
    }

    public function getPermissions() {
        $result = array();
		if ($this->isTotalAdmin()) {
			$result[] = array(
				"status" => "TA",
				"target_id" => 0
			);
		}

        foreach ($this->LA as $leagueId => $permission) {
            if ($permission) {
                $result[] = array(
                    "status" => "LA",
                    "target_id" => $leagueId
                );
            }
        }

        foreach ($this->CA as $competitionId => $permission) {
            if ($permission) {
                $result[] = array(
                    "status" => "CA",
                    "target_id" => $competitionId
                );
            }
        }

        return $result;
    }

	public function getBlogs() {
		if ($this->isTotalAdmin()) {
			return Group::getAllByType(Group::BLOG);
		}

		$blogs = array();
		$blogIds = array();

		$userBlogs = Connection::getTypifiedContentGroupsFor($this, Group::BLOG);
		foreach ($userBlogs as $blog) {
			$blogIds[$blog->getId()] = true;
			$blogs[] = $blog;
		}

		$competitions = $this->getCompetitions();
		foreach ($competitions as $competition) {
			$competitionBlogs = Connection::getTypifiedContentGroupsFor($competition, Group::BLOG);
			foreach ($competitionBlogs as $blog) {
				if (!isset($blogIds[$blog->getId()])) {
					$blogIds[$blog->getId()] = true;
					$blogs[] = $blog;
				}
			}
		}

		$leagues = $this->getAdministratedLeagues();
		foreach ($leagues as $league) {
			$leagueBlogs = Connection::getTypifiedContentGroupsFor($league, Group::BLOG);
			foreach ($leagueBlogs as $blog) {
				if (!isset($blogIds[$blog->getId()])) {
					$blogIds[$blog->getId()] = true;
					$blogs[] = $blog;
				}
			}
		}

		return $blogs;
	}

	public function getCompetitions() {
		// TODO performance
		$result = array();
		$compIds = array();
		foreach ($this->getCompetitionList() as $compId => $value) {
			$result[] = Competition::getById($compId);
			$compIds[$compId] = true;
		}

		foreach ($this->getAdministratedLeagues() as $league) {
			$competitions = $league->getCompetitions();
			foreach ($competitions as $competition) {
				if (!isset($compIds[$competition->getId()])) {
					$result[] = $competition;
					$compIds[$competition->getId()] = true;
				}
			}
		}
		
		return $result;
	}

	public function getAdministratedLeaguesIds() {
		$ids = array();
		foreach ($this->getAdministratedLeaguesList() as $leagueId => $value) {
			$ids[] = $leagueId;
		}
		return $ids;
	}

	public function getAdministratedLeagues() {
		return League::getByIds($this->LA);
	}

	public static function existsById($id) {
		return mysql_num_rows(UserDBClient::selectById($id)) > 0;
	}

	public static function getByPmid($pmid) {
		$id = UserDataDBClient::getUIDByPmid($pmid);
		$user = null;
		try {
			$user = User::getById($id);
		} catch (Exception $e) {
			// TODO use error log file
		}

		return $user;
	}

	public static function getNearTo($userData) {
		$users = array();
		$iterator = UserDBClient::getNearTo($userData);
		while ($iterator->valid()) {
			$data = $iterator->current();
			try {
				$users[] = User::getById($data['uid']);
			} catch (Exception $e) {
				global $LOG;
				@$LOG->exception($e);
			}
			$iterator->next();
		}

		return $users;
	}

	private function getImagePrefix() {
		$photo = $this->get(self::KEY_PHOTO);
		if (!$photo)
			$photo = $this->id;
		return $photo;
	}

    private function loadData() {
        if ($this->dataLoaded) return;

        $reqd = UserDataDBClient::selectByUID($this->uid());
        while ($d = mysql_fetch_assoc($reqd)) {
            $this->data[$d['key']] = $d['value'];
        }

		$this->dataLoaded = true;
    }

    private function loadPermissions() {
        if ($this->permissionsLoaded) return;

        $reqp = UserPermissionDBClient::selectByUID($this->uid());
        while ($p = mysql_fetch_assoc($reqp)) {
            switch ($p['status']) {
            case 'TA': // total admin
                $this->TA = true;
                break;
            case 'LA': // league admin
                $this->LA[$p['target_id']] = true;
                break;
            case 'CA': // competition admin
                $this->CA[$p['target_id']] = true;
                break;
            }
        }
    }

	public function toHTML($fromCupMS = true) {
		$src = ($fromCupMS ? MAIN_URL : '') . $this->getImageURL(self::IMAGE_SQUARE);
		$alt = $this->getFullName();
		$name = $this->getName();
		$surname = $this->getSurname();
		$id = $this->getId();

		$pmid = $this->getPmid() ? '<div>Pipeman ID ' . $this->getPmid() . '</div>' : '';

		$html =
<<< LABEL
<div class="person_to_html">
	<img src="$src" alt="$alt"/>
	<div>
		<div>$surname</div>
		<div>$name</div>
		<div>
			<div>ID $id</div>
			$pmid
			<div class="clear"></div>
		</div>
	</div>
</div>
LABEL;

		return array_merge(array('html' => $html), $this->toArray());
	}

	public static function getAllToHTML($fromCupMS = true) {
		return array_transform_toHTML(User::getAll(), $fromCupMS);
	}

	public function getLeagues($forceAll = false, $forceIncludeWPR = true) {
		$player = $this->getPlayer();
		if ($player === null)
			return League::getTopLeagues(null, $forceAll, $forceIncludeWPR);

		$playerLeagueIds = $player->getTopLeagueIds();
		if ($forceAll) {
			$allLeagueIds = League::getAllIds();
			
			//FUCK! why doesn't array_merge() work on them!??!?!?!?!??!?

			foreach ($allLeagueIds as $id)
				if (!array_contains($playerLeagueIds, $id))
					$playerLeagueIds[] = $id;
			
		}

		if (array_contains($playerLeagueIds, League::MAIN_LEAGUE_ID) || $forceAll || $forceIncludeWPR) {
			$result = array(1);
			foreach ($playerLeagueIds as $id)
				if ($id != 1)
					$result[] = $id;
			$playerLeagueIds = $result;
		}

		return League::getByIds($playerLeagueIds);
	}

	public function getURL() {
		return '/id' . $this->getId();
	}

	public function addFavourite($title, $target) {
		return UserDBClient::insertFavourite($this->getId(), $target, $title);
	}

	public function getFavouritesAll() {
		$result = array();
		$iterator = UserDBClient::getFavourites($this->getId());
		while ($iterator->valid()) {
			$data = $iterator->current();
			$result[ $data['target'] ] = $data['title'];
			$iterator->next();
		}
		return $result;
	}

	public function getFavouritesBySubTarget($subTarget) {
		$result = array();
		$iterator = UserDBClient::getFavourites($this->getId(), "%$subTarget%");
		while ($iterator->valid()) {
			$data = $iterator->current();
			$result[ $data['target'] ] = $data['title'];
			$iterator->next();
		}
		return $result;
	}

	public function checkFavourite($target) {
		return UserDBClient::checkFavourite($this->getId(), $target);
	}

	public function removeFavourite($target) {
		return UserDBClient::deleteFavourite($this->getId(), $target);
	}
}
?>
