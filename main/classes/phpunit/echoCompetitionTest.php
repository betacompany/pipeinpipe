<?php

define ('ITERATIONS', 5);
define ('OBTAINING_TEST', true);
define ('CREATIONAL_TEST', true);

require_once dirname(__FILE__).'/../cupms/Competition.php';
require_once dirname(__FILE__).'/../db/CompetitionDBClient.php';

function writeObjInfo($object){
	//here is the test of functionalities
	echo '<pre>';
	echo "\nLegueId: ".$object->getLeagueId();
	echo "\nId: ".$object->getId();
	echo "\nName: ".$object->getName();
	echo "\nTournamentId: ".$object->getTournamentId();
	echo "\nDate: ".$object->getDate();
	//echo "\nSeason: ".$object->getSeason();
	echo "\nCoef: ".$object->getCoef();
	echo "\nDescription: ".$object->getDescription();
	echo "\nMainCupId: ".$object->getMainCupId();
	echo "\nMainCup: ";
	print_r($object->getMainCup());
	echo "\nCompetitionObject: ";
	print_r($object);
}

if (OBTAINING_TEST){
	//competition obtaining test
	echo "\n========================================================================================";
	echo "\n=====================================Obtaining Test=====================================";
	echo "\n========================================================================================";

	for ($i=0; $i<ITERATIONS; $i++){
		$id = 0;
		while (!CompetitionDBClient::existsById($id = rand(1,100))){
		}
		$time_begin = microtime(true);

		$competitionObj = Competition::getById($id);
		writeObjInfo($competitionObj);

		$time_end = microtime(true);

		echo "==================================";
		echo "\nTesting time: ".($time_end - $time_begin).' sec';
		echo '</pre>';
	}
}

if(CREATIONAL_TEST){
	//competition creational test:
	echo "\n=========================================================================================";
	echo "\n=====================================Creational Test=====================================";
	echo "\n=========================================================================================";

	$createdObjectIds = array();
	try{
		for ($i=0; $i<ITERATIONS; $i++){
			$time_begin = microtime(true);

			$competitionObj = Competition::getById(0, rand(1, 10), rand(1, 10), 'test', 'test', '2000-01-01', rand(10, 1000), "test");
			$createdObjectIds[$i] = $competitionObj->getId();
			writeObjInfo($competitionObj);

			$time_end = microtime(true);

			echo "==================================";
			echo "\nTesting time: ".($time_end - $time_begin).' sec';
			echo '</pre>';
		}
	}
	catch (Exception $e) {
		echo $e->getMessage();
	}
	foreach ($createdObjectIds as $objId){
		CompetitionDBClient::removeById($objId);
	}
}

?>
