<?php

require_once dirname(__FILE__).'/../../includes/mysql.php';
require_once dirname(__FILE__).'/../../includes/assertion.php';

require_once dirname(__FILE__).'/Cup.php';
require_once dirname(__FILE__).'/CupOneLap.php';
require_once dirname(__FILE__).'/CupPlayoff.php';
require_once dirname(__FILE__).'/CupTwoLaps.php';
require_once dirname(__FILE__).'/Competition.php';

require_once dirname(__FILE__).'/../db/CupDBClient.php';

/**
 * This class implements some methods concerning to work with Cup and its children
 * @author Artyom Grigoriev aka ortemij
 */
class CupFactory {

	/**
	 * throws InvalidArgumentException
	 * @param int $id
	 * @return Cup
	 */
    public static function getCupById($id) {
		assertPositive('Invalid value of parameter $id: ', $id);
		
		$req = CupDBClient::selectTypeFor($id);
		try {
			if ($c = mysql_fetch_assoc($req)) {
				switch ($c['type']) {
				case Cup::TYPE_PLAYOFF:
					$cup = new CupPlayOff($id);
					break;
				case Cup::TYPE_ONE_LAP:
					$cup = new CupOneLap($id);
					break;
				case Cup::TYPE_TWO_LAPS:
					$cup = new CupTwoLaps($id);
					break;
				default:
					$cup = null;
				}
			}
		} catch (Exception $e) {
			global $LOG;
			@$LOG->exception($e);
		}

		return $cup;
    }

	/**
	 * creates new cup in database
	 * throws InvalidArgumentException
	 * @param int $competitionId
	 * @param int $parentCupId
	 * @param string $name
	 * @param string $type
	 * @return Cup
	 */
    public static function create($competitionId, $parentCupId, $name, $type = 'undefined') {
		assertPositive('Unable to create cup with $competitionId=', $competitionId);
		assertTrue('Unable to create cup with $parentCupId='.$parentCupId, $parentCupId >= 0);
		assertTrue('There is no competition with id='.$competitionId, Competition::existsById($competitionId));
		if ($parentCupId != 0)
			assertTrue('There is no cup with id='.$parentCupId, Cup::existsById($parentCupId));
		assertTrue('Wrong type parameter. It can be `undefined`, `playoff`, `one-lap` or `two-laps`', Cup::isCorrectType($type));

        CupDBClient::insert($competitionId, $parentCupId, $name, $type);

		return self::getCupById(mysql_insert_id());
    }

    public static function getAllRegular() {
        $result = array();
        $req = CupDBClient::selectAllRegular();
        while ($c = mysql_fetch_assoc($req)) {
            switch ($c['type']) {
            case Cup::TYPE_ONE_LAP:
                $result[] = new CupOneLap($c['id']);
                break;
            case Cup::TYPE_TWO_LAPS:
                $result[] = new CupTwoLaps($c['id']);
                break;
            }
        }

        return $result;
    }
}
?>
