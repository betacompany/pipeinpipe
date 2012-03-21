<?php
/**
 * @author Nikita
 */

function appendTableRow(&$resultString, $label, $value1, $value2) {

	if ($value1 + $value2 <= 0) {
		return;
	}
	$perc1 = round($value1 / ($value1 + $value2) * 100);
	$perc2 = round($value2 / ($value1 + $value2) * 100);
	
	$resultString .= <<<LABEL
		<tr>
			<td>$value1</td>
			<td>
				<div class="strip_wrapper">
					<table class="strip">
						<tbody>
							<tr>
								<td style="width: $perc1%; height: 20px; background: #007ca7"></td>
								<td style="width: $perc2%; height: 20px; background: #8FBC13"></td>
							</tr>
						</tbody>
					</table>
					<div class="strip_label">
						$label
					</div>
				</div>
			</td>
			<td>$value2</td>
		</tr>
LABEL;
}

function show_player_comparator($data) {

	$pm1 = Player::getById($data['pmid1']);
	$pm2 = Player::getById($data['pmid2']);
	$url1 = $pm1->getURL();
	$url2 = $pm2->getURL();
	$imURL1 = $pm1->getImageURL();
	$imURL2 = $pm2->getImageURL();

	$name1 = $pm1->getFullName();
	$name2 = $pm2->getFullName();

//upper table with images
	$result = <<<LABEL
<div style="padding-left: 10px;">
	<table class="comparator_head">
		<thead>
			<th>
				<a href="$url1">$name1</a>
			</th>
			<th>
				<a href="$url2">$name2</a>
			</th>
		</thead>
		<tbody>
			<tr>
				<td>
					<img src="$imURL1"/>
				</td>
				<td>
					<img src="$imURL2"/>
				</td>
			</tr>
		</tbody>
	</table>
	
LABEL;

//=====================таблица личных встреч=====================
	
	$irv1 = $data['games_inter_stat']['regular']['v1']['total'];
	$irv2 = $data['games_inter_stat']['regular']['v2']['total'];
	$ipv1 = $data['games_inter_stat']['playoff']['v1']['total'];
	$ipv2 = $data['games_inter_stat']['playoff']['v2']['total'];
	$iv1 = $irv1 + $ipv1;
	$iv2 = $irv2 + $ipv2;

	if ($iv1 + $iv2 > 0) {
		$result .= <<<LABEL
		<table class="comparator">
			<thead>
				<th colspan="3">Личные встречи</th>
			</thead>
			<tbody>
LABEL;

		$irv1_5 = $data['games_inter_stat']['regular']['v1']['five'];
		$irv2_5 = $data['games_inter_stat']['regular']['v2']['five'];
		$irv1_6 = $data['games_inter_stat']['regular']['v1']['six'];
		$irv2_6 = $data['games_inter_stat']['regular']['v2']['six'];
		$irv1_b = $data['games_inter_stat']['regular']['v1']['balance'];
		$irv2_b = $data['games_inter_stat']['regular']['v2']['balance'];

		appendTableRow($result, "победы (всего)", $iv1, $iv2);
		appendTableRow($result, "победы в регулярке", $irv1, $irv2);
		appendTableRow($result, "победы со счётом 5", $irv1_5, $irv2_5);
		appendTableRow($result, "победы со счётом 6", $irv1_6, $irv2_6);
		appendTableRow($result, "победы по балансу", $irv1_b, $irv2_b);
		appendTableRow($result, "победы в плей-офф", $ipv1, $ipv2);

		$result .= <<<LABEL
			</tbody>
		</table>
LABEL;
	}
//=======конец таблицы личных встреч====================


//==============таблица общей статистики================

	$g1 = $data['games_total_stat']['first']['count'];
	$g2 = $data['games_total_stat']['second']['count'];

	if ($g1 + $g2 > 0) {
		$result .= <<<LABEL
			<table class="comparator">
				<thead>
					<th colspan="3">Общая статистика</th>
				</thead>
				<tbody>
LABEL;

		$v1 = $data['games_total_stat']['first']['v']['count'];
		$v2 = $data['games_total_stat']['second']['v']['count'];
		$rv1 = $data['games_total_stat']['first']['v']['regular']['count'];
		$rv2 = $data['games_total_stat']['second']['v']['regular']['count'];
		$rv1_5 = $data['games_total_stat']['first']['v']['regular']['five'];
		$rv2_5 = $data['games_total_stat']['second']['v']['regular']['five'];
		$rv1_6 = $data['games_total_stat']['first']['v']['regular']['six'];
		$rv2_6 = $data['games_total_stat']['second']['v']['regular']['six'];
		$rv1_b = $data['games_total_stat']['first']['v']['regular']['balance'];
		$rv2_b = $data['games_total_stat']['second']['v']['regular']['balance'];
		$rv1_t = $data['games_total_stat']['first']['v']['regular']['technical'];
		$rv2_t = $data['games_total_stat']['second']['v']['regular']['technical'];
		$rv1_f = $data['games_total_stat']['first']['v']['regular']['fatality'];
		$rv2_f = $data['games_total_stat']['second']['v']['regular']['fatality'];
		$pv1 = $data['games_total_stat']['first']['v']['playoff']['count'];
		$pv2 = $data['games_total_stat']['second']['v']['playoff']['count'];
		$pv1_f = $data['games_total_stat']['first']['v']['playoff']['fatality'];
		$pv2_f = $data['games_total_stat']['second']['v']['playoff']['fatality'];
		$pv1_t = $data['games_total_stat']['first']['v']['playoff']['technical'];
		$pv2_t = $data['games_total_stat']['second']['v']['playoff']['technical'];

		$d1 = $data['games_total_stat']['first']['d']['count'];
		$d2 = $data['games_total_stat']['second']['d']['count'];
		$rd1 = $data['games_total_stat']['first']['d']['regular']['count'];
		$rd2 = $data['games_total_stat']['second']['d']['regular']['count'];
		$rd1_5 = $data['games_total_stat']['first']['d']['regular']['five'];
		$rd2_5 = $data['games_total_stat']['second']['d']['regular']['five'];
		$rd1_6 = $data['games_total_stat']['first']['d']['regular']['six'];
		$rd2_6 = $data['games_total_stat']['second']['d']['regular']['six'];
		$rd1_b = $data['games_total_stat']['first']['d']['regular']['balance'];
		$rd2_b = $data['games_total_stat']['second']['d']['regular']['balance'];
		$rd1_t = $data['games_total_stat']['first']['d']['regular']['technical'];
		$rd2_t = $data['games_total_stat']['second']['d']['regular']['technical'];
		$rd1_f = $data['games_total_stat']['first']['d']['regular']['fatality'];
		$rd2_f = $data['games_total_stat']['second']['d']['regular']['fatality'];
		$pd1 = $data['games_total_stat']['first']['d']['playoff']['count'];
		$pd2 = $data['games_total_stat']['second']['d']['playoff']['count'];
		$pd1_f = $data['games_total_stat']['first']['d']['playoff']['fatality'];
		$pd2_f = $data['games_total_stat']['second']['d']['playoff']['fatality'];
		$pd1_t = $data['games_total_stat']['first']['d']['playoff']['technical'];
		$pd2_t = $data['games_total_stat']['second']['d']['playoff']['technical'];

		$rg1 = $rv1 + $rd1;
		$rg2 = $rv2 + $rd2;
		$pg1 = $pv1 + $pd1;
		$pg2 = $pv2 + $pd2;

		appendTableRow($result, "Игры (всего)", $g1, $g2);
		appendTableRow($result, "Победы (всего)", $v1, $v2);
		appendTableRow($result, "Поражения (всего)", $d1, $d2);
		appendTableRow($result, "Игры в регулярке", $rg1, $rg2);
		appendTableRow($result, "Победы в регулярке", $rv1, $rv2);
		appendTableRow($result, "Победы со счётом 5", $rv1_5, $rv2_5);
		appendTableRow($result, "Победы со счётом 6", $rv1_6, $rv2_6);
		appendTableRow($result, "Победы по балансу", $rv1_b, $rv2_b);
		appendTableRow($result, "Технические победы", $rv1_t, $rv2_t);
		appendTableRow($result, "Победы по фаталити", $rv1_f, $rv2_f);
		appendTableRow($result, "Поражения со счётом 5", $rd1_5, $rd2_5);
		appendTableRow($result, "Поражения со счётом 6", $rd1_6, $rd2_6);
		appendTableRow($result, "Поражения по балансу", $rd1_b, $rd2_b);
		appendTableRow($result, "Технические поражения", $rd1_t, $rd2_t);
		appendTableRow($result, "Поражения по фаталити", $rd1_f, $rd2_f);
		appendTableRow($result, "Игры в плей-офф", $pg1, $pg2);
		appendTableRow($result, "Победы в плей-офф", $pv1, $pv2);
		appendTableRow($result, "Поражения в плей-офф", $pd1, $pd2);
		$result .= <<<LABEL
				</tbody>
			</table>
LABEL;
    }

    $result .= <<<LABEL
            <table class="comparator">
    <thead>
    <th colspan="3">Движение по рейтингу</th>
    </thead>
        <tbody id="comparison_chart">
LABEL;

    $result .= "<script type=\"text/javascript\" src=\"https://www.google.com/jsapi\"></script>\n";
    $result .= "<script type=\"text/javascript\">\n";

    // Load the Visualization API and the piechart package.
    $result .= "google.load('visualization', '1.0', {'packages':['corechart']});\n";

    // Set a callback to run when the Google Visualization API is loaded.
    $result .= "google.setOnLoadCallback(drawChart);\n";

        // Callback that creates and populates a data table,
    // instantiates the pie chart, passes in the data and
    // draws it.
    $result .= "function drawChart() {\n";

    // Create the data table.
    $result .= "var data = new google.visualization.DataTable();\n";

            $chartData = $data['movement'];
            $pm1 = Player::getById($data['pmid1']);
            $pm2 = Player::getById($data['pmid2']);
            $numberOfDates = max($data['movement'][1], $data['movement'][2]);

    $result .= "var players = ['" . $pm1->getFullName() . "', '" . $pm2->getFullName() . "'];\n";
    $result .= "data.addColumn('date', 'День');\n";
    $result .= "for (var i = 0; i < players.length; i++) {\n
        data.addColumn('number', players[i]);\n
    }\n";

    $result .= "data.addRows(" . $numberOfDates . ");\n";
            foreach($chartData as $number => $playerMovement){
                foreach ($playerMovement as $movement) {
                    list($year, $month, $day) = explode("-", $movement['date']);
                    $points = $movement['points'];
                    $result .= "data.setCell(new Date($year, $month, $day), $number, $points);\n";
                }
            }

    $result .= "var dataView = new google.visualization.DataView(data);\n";
    $result .= "dataView.setColumns([{calc: function(data, row) { return data.getFormattedValue(row, 0); }, type:'string'}, 1]);\n";

    // Chart options may be found on http://code.google.com/intl/ru-RU/apis/chart/interactive/docs/gallery/areachart.html
    $result .= "var options = {\n
        'title':'Движение по WPR',\n
        'legend': \"none\",\n
        'chartArea': {left: 69, width: 666},\n
        'focusTarget': 'category',\n
        'legend.position': 'right',\n
        'hAxis': {\n
            'format': 'd MMM y',\n
            'textPosition': 'out',\n
            'title': \"Дата\",\n
            'slantedText': false,\n
            'gridlines.count': 8,\n
            'maxAlternation': 2\n
         },\n
        'vAxis': {\n
            'gridlines.count': 8\n
         },\n
        'width': 750,\n
        'height': 300\n
    };\n";

        $result .= "var chart = new google.visualization.AreaChart(document.getElementById('comparison_chart'));\n";
    $result .= "chart.draw(data, options);}\n";
$result .= "</script>\n";

    $result .= <<<LABEL
		    </tbody>
        </table>
LABEL;

	return $result;
}
?>
