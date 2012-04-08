<?php

require_once dirname(__FILE__) . '/Line.php';

/**
 * User: ortemij
 * Date: 08.04.12
 * Time: 23:06
 */
class VkontakteLineChart {

	private $lines = array();
	private $name;

	function __construct($name) {
		$this->name = $name;
	}


	public function addLine($name, $color, Line $line) {
		$lineData = array();
		$lineData['c'] = intval($color, 16);
		$lineData['f'] = 1;

		$data = array();
		foreach ($line->getPoints() as $x => $y) {
			$data[] = array(intval($x), intval($y));
		}
		$lineData['d'] = $data;
		$lineData['name'] = $name;

		$this->lines[] = $lineData;
	}

	public function toHTML($maxX = 1e100) {

		$name = $this->name;

		$result = <<< HTML
<embed type="application/x-shockwave-flash" id="$name" name="$name" quality="high" flashvars="
HTML;

		$flashVars = "div_id=$name&graphdata=";

		$arr = "[";
		$firstLine = true;
		foreach ($this->lines as $line) {
			if (!$firstLine) {
				$arr .= ",";
			} else {
				$firstLine = false;
			}
			$arr .= "{\"c\":{$line['c']},\"f\":1,\"name\":\"{$line['name']}\",\"d\":[";
			$first = true;
			foreach ($line['d'] as $d) {
				if (!$first) {
					$arr .= ",";
				} else {
					$first = false;
				}
				$pp = ($d[0] > $maxX) ? "-" : "";
				$arr .= "[{$d[0]},{$d[1]},\"$pp\"]";
			}
			$arr .= "]}";
		}
		$arr .= "]";

		$flashVars .= urlencode($arr);

		$flashVars .= "&lang.data_empty=Нет данных&lang.dateFormats.day_fullmon={day} {month}&lang.dateFormats.day_fullmon_year={day} {month} {year}&lang.dateFormats.day_fullmon_year_hour={day} {dayMonth} {year}, {hour}:00&lang.dateFormats.day_fullmon_year_hour_min={day} {dayMonth} {year}, {hour}:{min}&lang.dateFormats.day_mon={day} {month}&lang.dayMonths=Января,Февраля,Марта,Апреля,Мая,Июня,Июля,Августа,Сентября,Октября,Ноября,Декабря&lang.error_loading=Ошибка при загрузке&lang.loading=Загрузка...&lang.months=Январь,Февраль,Март,Апрель,Май,Июнь,Июль,Август,Сентябрь,Октябрь,Ноябрь,Декабрь&lang.no_data=Отсутствуют входные данные&lang.select_graphs=фильтр&multiple=0";

		$result .= htmlspecialchars($flashVars);

		$result .= <<< HTML2
" allowscriptaccess="always" wmode="opaque" src="http://vk.com/swf/graph.swf" height="400px" width="100%"/>
HTML2;

		return $result;
	}
}
