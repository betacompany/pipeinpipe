<?php

require_once dirname(__FILE__) . '/../includes/error.php';
require_once dirname(__FILE__) . '/../includes/assertion.php';
require_once dirname(__FILE__) . '/../includes/common.php';

require_once dirname(__FILE__) . '/../classes/charts/Chart.php';

function distance($r, $g, $b, $r1, $g1, $b1) {
	return sqrt(($r - $r1) * ($r - $r1) + ($g - $g1) * ($g - $g1) + ($b - $b1) * ($b - $b1));
}

if (!isset($_REQUEST['method'])) {
	echo json_encode(array(
		'status' => 'failed',
		'reason' => 'method not specified'
	));
}

try {
	switch ($_REQUEST['method']) {
	case 'rating_all':
		assertIsset($_REQUEST['delta_past']);
		assertIsset($_REQUEST['date']);
		assertIsset($_REQUEST['delta_future']);
		assertIsset($_REQUEST['league_id']);

		$leftWidth = null;
		$rightWidth = null;

		if (isset($_REQUEST['left_width'])) {
			$leftWidth = intval($_REQUEST['left_width']);
		}

		if (isset($_REQUEST['right_width'])) {
			$leftWidth = intval($_REQUEST['right_width']);
		}


		$leagueId = intval($_REQUEST['league_id']);
		$date = $_REQUEST['date'];

		assertDate($date);

		$begin = date('Y-m-d', strtotime($date . " ".$_REQUEST['delta_past']." day"));
		$end = date('Y-m-d', strtotime($date . " +".$_REQUEST['delta_future']." day"));

		assertDate($begin);
		assertDate($end);

		$set = new LineSet();

		$prevR = 0; $prevG = 0; $prevB = 0;

		$ratingTable = RatingTable::getInstanceByDate($leagueId, $date);
		foreach ($ratingTable->getPmids() as $pmid) {
			$movement = RatingTable::getRatingMovementInterval($begin, $end, $leagueId, $pmid);
			$line = new Line();
			foreach ($movement as $step) {
				$x = 2 * datetoint($begin, $step['date']);
				$line->addPoint($x, -$step['place']);
			}

			$r = rand(0, 192);
			$g = rand(0, 192);
			$b = rand(0, 192);
			$i = 0;
			while (distance($r, $g, $b, $prevR, $prevG, $prevB) < 100 && $i < 20) {
				$r = rand(0, 192);
				$g = rand(0, 192);
				$b = rand(0, 192);
				$i++;
			}

			$prevR = $r; $prevG = $g; $prevB = $b;

			$r = dechex($r);
			$g = dechex($g);
			$b = dechex($b);
			$r = (strlen($r) == 1) ? "0".strtoupper($r) : strtoupper($r);
			$g = (strlen($g) == 1) ? "0".strtoupper($g) : strtoupper($g);
			$b = (strlen($b) == 1) ? "0".strtoupper($b) : strtoupper($b);
			
			$line->setColor("$r$g$b");
			if (!$line->isConstant()) {
				$line->setWidth(5);
			}

			$set->add($line);
		}

		$set->linearY(1, -0.5);

		$splittedSet = $set->splitHorizontal(2 * datetoint($begin, $date));
		$leftSet = $splittedSet['left'];
		$rightSet = $splittedSet['right'];

		//echo "<pre>";

		echo "[";

		$resLeft = $leftSet->split(0);
		$resRight = $rightSet->split(0);

		$leftSet = $resLeft['bottom'];
		$rightSet = $resRight['bottom'];

		for ($y = -5; !($leftSet->isEmpty() && $rightSet->isEmpty()); $y -= 5) {
			$chartLeft = new Chart();
			$chartRight = new Chart();

			$chartLeft->setXs(2 * datetoint($begin, $begin), 2 * datetoint($begin, $date));
			$chartRight->setXs(2 * datetoint($begin, $date), 2 * datetoint($begin, $end));

			$resLeft = $leftSet->split($y);
			$resRight = $rightSet->split($y);

			$chartLeft->setLineSet($resLeft['top']);
			$chartRight->setLineSet($resRight['top']);

			$leftSet = $resLeft['bottom'];
			$rightSet = $resRight['bottom'];

//			$leftHeight = 52 * ($chartLeft->countLinesAt(2 * datetoint($begin, $date)));
//			$rightHeight = 52 * ($chartRight->countLinesAt(2 * datetoint($begin, $date)));

			$leftHeight = 52 * ($chartLeft->countLines());
			$rightHeight = 52 * ($chartRight->countLines());

//			echo '(' . $chartRight->getRangeHeight() . ' ' . $chartRight->countLinesAt(2 * datetoint($begin, $date)) . ')';

			if ($y != -5) echo ',';

			echo json_encode(array (
				'left' => array (
					'url' => $chartLeft->url($leftWidth, $leftWidth == null ? null : $leftHeight, 40),
					'height' => $leftHeight,
					'setted' => ($leftWidth != null)
				),
				'right' => array (
					'url' => $chartRight->url($rightWidth, $rightWidth == null ? null : $rightHeight, 40),
					'height' => $rightHeight,
					'setted' => ($rightWidth != null)
				)
			));

//			echo '<div style="background-image: url('.
//					$chart->url($_REQUEST['left_width'], 52 * ($chart->countLines()), 20).
//					'); width: 300px; height: '.(52 * ($chart->countLines())).'px; background-position: -2px 0px; background-repeat: no-repeat; overflow: hidden;"></div>';
		}
		
		echo "]";

		//echo "</pre>";

		break;
	}
} catch (Exception $e) {
	echo_json_exception($e);
	exit(0);
}

?>