<?php

require_once dirname(__FILE__).'/Competition.php';
require_once dirname(__FILE__).'/../db/TournamentDBClient.php';
require_once dirname(__FILE__).'/../db/CompetitionDBClient.php';

/**
 * @author Artyom Grigoriev aka ortemij
 */
class Tournament {
    private $id;
	private $name;
	private $description;

	// array of all competitions which are in this tournament
    private $competitions = array();
    private $competitionsLoaded = false;

	// unfortunately PHP does not support many constructors
    // if first parameter is zero constructor of new instance in DB is called
    // else this constructor loads data from DB
    public function  __construct($id) {
        if ($id <= 0) {
			throw new Exception("Incorrect id = ?", $id);
        } else {
            $req = TournamentDBClient::selectById($id);
            if ($tournament = mysql_fetch_assoc($req)) {
                $this->id =     $tournament['id'];
                $this->name =   $tournament['name'];
                $this->description =   $tournament['description'];
            } else {
                throw new InvalidArgumentException("There is no tournament with id=$id");
            }
        }
    }

	public static function create($name, $description = "") {
		TournamentDBClient::insert($name, $description);
        return new Tournament(mysql_insert_id());
	}

	/**
	 * updates database entry
	 * should be called to save changes!
	 */
	public function update() {
		TournamentDBClient::update($this);
	}

	public function getId() { return $this->id; }

    public function getName() { return $this->name; }
    public function setName($name) {
        $this->name = $name;
    }

    public function getDescription() { return $this->description; }
    public function setDescription($description) {
        $this->description = $description;
    }

    public function getCompetitions() {
        // if competitions was already loaded return them
        if ($this->competitionsLoaded) return $this->competitions;

        // else load them from database
        $req = CompetitionDBClient::selectByTournamentId($this->getId());
        while ($comp = mysql_fetch_assoc($req)) {
            try {
                $this->competitions[] = Competition::getById($comp['id']);
            } catch (Exception $e) {
                // TODO use error log file
                echo $e->getMessage();
            }
        }

        $this->competitionsLoaded = true;
        return $this->competitions;
    }

    // sometimes we may need to reload competitions
    // this method help us to do it
    public function getCompetitionsWithReload() {
        $this->competitionsLoaded = false;
        return $this->getCompetitions();
    }

    public static function getAll() {
        $result = array();
        $req = TournamentDBClient::selectAll();
        while ($tournament = mysql_fetch_assoc($req)) {
            try {
                $result[] = new Tournament($tournament['id']);
            } catch (Exception $e) {
                // TODO use error log file
                echo $e->getMessage();
            }
        }

        return $result;
    }
	
	public function toArray() {
		return array (
			'id' => $this->getId(),
			'value' => $this->getName()
		);
	}

	public static function getAllToArray() {
		$result = array();
		foreach (Tournament::getAll() as $tournament)
			$result[] = $tournament->toArray();

		return $result;
	}
}
?>
