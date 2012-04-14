<?php

require_once dirname(__FILE__) . '/../classes/charts/PieChart.php';

/**
 * Handles input array in format described in PlayerComparator
 * Very complicated function. Needs hard refactoring!
 * @param array $a
 * @return string
 */
function show_player_comparator($a) {
	$pm1 = Player::getById($a['pmid1']);
	$pm2 = Player::getById($a['pmid2']);
	$url1 = $pm1->getURL();
	$url2 = $pm2->getURL();
	$imURL1 = $pm1->getImageURL();
	$imURL2 = $pm2->getImageURL();

	$f1 = $pm1->getFullName();
	$f2 = $pm2->getFullName();

	$irv1 = $a['games_inter_stat']['regular']['v1']['total'];
	$irv2 = $a['games_inter_stat']['regular']['v2']['total'];
	$irv1_5 = $a['games_inter_stat']['regular']['v1']['five'];
	$irv2_5 = $a['games_inter_stat']['regular']['v2']['five'];
	$irv1_6 = $a['games_inter_stat']['regular']['v1']['six'];
	$irv2_6 = $a['games_inter_stat']['regular']['v2']['six'];
	$irv1_b = $a['games_inter_stat']['regular']['v1']['balance'];
	$irv2_b = $a['games_inter_stat']['regular']['v2']['balance'];
	$ipv1 = $a['games_inter_stat']['playoff']['v1']['total'];
	$ipv2 = $a['games_inter_stat']['playoff']['v2']['total'];
	$iv1 = $irv1 + $ipv1;
	$iv2 = $irv2 + $ipv2;

	$result = <<<LABEL
<div style="padding-left: 10px;">
	<table class="comparator_head">
		<thead>
			<th>
				<a href="$url1">$f1</a>
			</th>
			<th>
				<a href="$url2">$f2</a>
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

	if ($iv1 + $iv2 > 0) {
		$result .= <<<LABEL
	<table class="comparator">
		<thead>
			<th colspan="3">Личные встречи</th>
		</thead>
		<tbody>
			<tr>
				<td>$iv1</td>
				<td>победы (всего)</td>
				<td>$iv2</td>
			</tr>
LABEL;

		if ($irv1 + $irv2 > 0) {
			$result .= <<<LABEL
			<tr>
				<td>$irv1</td>
				<td>победы в регулярке</td>
				<td>$irv2</td>
			</tr>
LABEL;

			if ($irv1_5 + $irv2_5 > 0) {
				$result .= <<<LABEL
			<tr>
				<td>$irv1_5</td>
				<td>победы со счётом 5</td>
				<td>$irv2_5</td>
			</tr>
LABEL;
			}

			if ($irv1_6 + $irv2_6 > 0) {
				$result .= <<<LABEL
			<tr>
				<td>$irv1_6</td>
				<td>победы со счётом 6</td>
				<td>$irv2_6</td>
			</tr>
LABEL;
			}

			if ($irv1_b + $irv2_b > 0) {
				$result .= <<<LABEL
			<tr>
				<td>$irv1_b</td>
				<td>победы по балансу</td>
				<td>$irv2_b</td>
			</tr>
LABEL;
			}
		}

		if ($ipv1 + $ipv2 > 0) {
			$result .= <<<LABEL
			<tr>
				<td>$ipv1</td>
				<td>победы в плей-офф</td>
				<td>$ipv2</td>
			</tr>
LABEL;
		}



	$result .= <<< LABEL
		</tbody>
	</table>
LABEL;
	}

	$g1 = $a['games_total_stat']['first']['count'];
	$g2 = $a['games_total_stat']['second']['count'];

	$v1 = $a['games_total_stat']['first']['v']['count'];
	$v2 = $a['games_total_stat']['second']['v']['count'];
	$rv1 = $a['games_total_stat']['first']['v']['regular']['count'];
	$rv2 = $a['games_total_stat']['second']['v']['regular']['count'];
	$rv1_5 = $a['games_total_stat']['first']['v']['regular']['five'];
	$rv2_5 = $a['games_total_stat']['second']['v']['regular']['five'];
	$rv1_6 = $a['games_total_stat']['first']['v']['regular']['six'];
	$rv2_6 = $a['games_total_stat']['second']['v']['regular']['six'];
	$rv1_b = $a['games_total_stat']['first']['v']['regular']['balance'];
	$rv2_b = $a['games_total_stat']['second']['v']['regular']['balance'];
	$rv1_t = $a['games_total_stat']['first']['v']['regular']['technical'];
	$rv2_t = $a['games_total_stat']['second']['v']['regular']['technical'];
	$rv1_f = $a['games_total_stat']['first']['v']['regular']['fatality'];
	$rv2_f = $a['games_total_stat']['second']['v']['regular']['fatality'];
	$pv1 = $a['games_total_stat']['first']['v']['playoff']['count'];
	$pv2 = $a['games_total_stat']['second']['v']['playoff']['count'];
	$pv1_f = $a['games_total_stat']['first']['v']['playoff']['fatality'];
	$pv2_f = $a['games_total_stat']['second']['v']['playoff']['fatality'];
	$pv1_t = $a['games_total_stat']['first']['v']['playoff']['technical'];
	$pv2_t = $a['games_total_stat']['second']['v']['playoff']['technical'];

	$d1 = $a['games_total_stat']['first']['d']['count'];
	$d2 = $a['games_total_stat']['second']['d']['count'];
	$rd1 = $a['games_total_stat']['first']['d']['regular']['count'];
	$rd2 = $a['games_total_stat']['second']['d']['regular']['count'];
	$rd1_5 = $a['games_total_stat']['first']['d']['regular']['five'];
	$rd2_5 = $a['games_total_stat']['second']['d']['regular']['five'];
	$rd1_6 = $a['games_total_stat']['first']['d']['regular']['six'];
	$rd2_6 = $a['games_total_stat']['second']['d']['regular']['six'];
	$rd1_b = $a['games_total_stat']['first']['d']['regular']['balance'];
	$rd2_b = $a['games_total_stat']['second']['d']['regular']['balance'];
	$rd1_t = $a['games_total_stat']['first']['d']['regular']['technical'];
	$rd2_t = $a['games_total_stat']['second']['d']['regular']['technical'];
	$rd1_f = $a['games_total_stat']['first']['d']['regular']['fatality'];
	$rd2_f = $a['games_total_stat']['second']['d']['regular']['fatality'];
	$pd1 = $a['games_total_stat']['first']['d']['playoff']['count'];
	$pd2 = $a['games_total_stat']['second']['d']['playoff']['count'];
	$pd1_f = $a['games_total_stat']['first']['d']['playoff']['fatality'];
	$pd2_f = $a['games_total_stat']['second']['d']['playoff']['fatality'];
	$pd1_t = $a['games_total_stat']['first']['d']['playoff']['technical'];
	$pd2_t = $a['games_total_stat']['second']['d']['playoff']['technical'];

	$rg1 = $rv1 + $rd1;
	$rg2 = $rv2 + $rd2;
	$pg1 = $pv1 + $pd1;
	$pg2 = $pv2 + $pd2;

	if ($g1 + $g2 > 0) {
		$result .= <<< LABEL
	<table class="comparator">
		<thead>
			<th colspan="3">Общая статистика</th>
		</thead>
		<tbody>
			<tr>
				<td>$g1</td>
				<td>Игры (всего)</td>
				<td>$g2</td>
			</tr>
			<tr>
				<td>$v1</td>
				<td>Победы (всего)</td>
				<td>$v2</td>
			</tr>
			<tr>
				<td>$d1</td>
				<td>Поражения (всего)</td>
				<td>$d2</td>
			</tr>

			<tr>
				<td>$rg1</td>
				<td>Игры в регулярке</td>
				<td>$rg2</td>
			</tr>
			<tr>
				<td>$rv1</td>
				<td>Победы в регулярке</td>
				<td>$rv2</td>
			</tr>
			<tr>
				<td>$rv1_5</td>
				<td>Победы со счётом 5</td>
				<td>$rv2_5</td>
			</tr>
			<tr>
				<td>$rv1_6</td>
				<td>Победы со счётом 6</td>
				<td>$rv2_6</td>
			</tr>
			<tr>
				<td>$rv1_b</td>
				<td>Победы по балансу</td>
				<td>$rv2_b</td>
			</tr>
			<tr>
				<td>$rv1_t</td>
				<td>Технические победы</td>
				<td>$rv2_t</td>
			</tr>
			<tr>
				<td>$rv1_f</td>
				<td>Победы по фаталити</td>
				<td>$rv2_f</td>
			</tr>

			<tr>
				<td>$rd1</td>
				<td>Поражения в регулярке</td>
				<td>$rd2</td>
			</tr>
			<tr>
				<td>$rd1_5</td>
				<td>Поражения со счётом 5</td>
				<td>$rd2_5</td>
			</tr>
			<tr>
				<td>$rd1_6</td>
				<td>Поражения со счётом 6</td>
				<td>$rd2_6</td>
			</tr>
			<tr>
				<td>$rd1_b</td>
				<td>Поражения по балансу</td>
				<td>$rd2_b</td>
			</tr>
			<tr>
				<td>$rd1_t</td>
				<td>Технические поражения</td>
				<td>$rd2_t</td>
			</tr>
			<tr>
				<td>$rd1_f</td>
				<td>Поражения по фаталити</td>
				<td>$rd2_f</td>
			</tr>

			<tr>
				<td>$pg1</td>
				<td>Игры в плей-офф</td>
				<td>$pg2</td>
			</tr>
			<tr>
				<td>$pv1</td>
				<td>Победы в плей-офф</td>
				<td>$pv2</td>
			</tr>
			<tr>
				<td>$pd1</td>
				<td>Поражения в плей-офф</td>
				<td>$pd2</td>
			</tr>

		</tbody>
	</table>
LABEL;

	}

	$result .= <<< LABEL
</div>
LABEL;

	$pie = new PieChart();
	$pie->set(
		array ($rv1_5, $rv1_6, $rv1_b, $rv1_t, $rv1_f),
		array ('со счётом 5', 'со счётом 6', 'по балансу', 'технически', 'по фаталити')
	);
	$url = $pie->url(300, 100, '007ca7');

	$result .= <<< LABEL
	<img src="$url" />

LABEL;

	return $result;
}

?>
