<?php
	/**
	*
	* @author Bob Smirnov
	*/
	require_once dirname(__FILE__).'/../../includes/mysql.php';
	require_once dirname(__FILE__) . '/../../classes/cupms/RatingTable.php';

	/**
	 * Adds one day to parameter date and returns it in an integer format
	 * @param int $date
	 * @return int
	 */
	function addDayToDate($date){

		$dateArray = getdate($date);
		$days = $dateArray['mday'];
		$months = $dateArray['mon'];
		$years = $dateArray['year'];
		return mktime(0, 0, 0, $months, $days + 1, $years);

	}

	$startDate = mktime(0, 0, 0, 10, 23, 2007);
	$checkDate = $startDate;
	$now = time();
	$log = new Logger("../../rating.log");

	try{
			$query = "SELECT DISTINCT date FROM p_rating ORDER BY date";
			$result = mysql_query($query);
			$rows = mysql_num_rows($result);
			while ($checkDate < $now){
				$row = mysql_fetch_assoc($result);
				$date_elements  = explode("-", $row['date']);	//converting recieved date into int
				$dbDate = mktime(0,0,0,$date_elements[1], $date_elements[2], $date_elements[0]);
				while($dbDate != $checkDate) {
					foreach (League::getAll() as $league) {
						$leagueId = $league->getId();
						try{
							RatingTable::evaluateData($leagueId, date("Y-m-d", $checkDate));
							$str = "[" . date('Y-m-d', $checkDate) . "] " . "Rating table of " . $league->getName() . " league added to database\n";
							$log->info($str);
							echo $str, "<br/>\n";
							flush();
						} catch (Exception $e) {}
					}
					$checkDate = addDayToDate($checkDate);
					flush();
				}
				$checkDate = addDayToDate($checkDate);
				}
			} catch(Exception $e) {
		$log->error("Error occured: " . $e->getTraceAsString());
		flush();
	}
?>