<?php

define('DATE_MONTH_NAME_FULL', 0);
define('DATE_MONTH_NAME_FIRST_LETTER_UPPER', 1);
define('DATE_MONTH_NAME_FULL_GENITIVE', 2);
define('DATE_MONTH_NAME_SHORT', 3);

function date_month_name($month, $mode = DATE_MONTH_NAME_FULL) {
	$month_name = array();
	$month_name[1] = array('январь', 'Январь', 'января', 'янв');
	$month_name[2] = array('февраль', 'Февраль', 'февраля', 'фев');
	$month_name[3] = array('март', 'Март', 'марта', 'мар');
	$month_name[4] = array('апрель', 'Апрель', 'апреля', 'апр');
	$month_name[5] = array('май', 'Май', 'мая', 'май');
	$month_name[6] = array('июнь', 'Июнь', 'июня', 'июн');
	$month_name[7] = array('июль', 'Июль', 'июля', 'июл');
	$month_name[8] = array('август', 'Август', 'августа', 'авг');
	$month_name[9] = array('сентябрь', 'Сентябрь', 'сентября', 'сен');
	$month_name[10] = array('октябрь', 'Октябрь', 'октября', 'окт');
	$month_name[11] = array('ноябрь', 'Ноябрь', 'ноября', 'ноя');
	$month_name[12] = array('декабрь', 'Декабрь', 'декабря', 'дек');

	return $month_name[$month][$mode];
}

define('DATE_LOCAL_FULL', 0);
define('DATE_LOCAL_SHORT', 1);
define('DATE_LOCAL_FULL_DATE', 2);
define('DATE_LOCAL_SUPER_SHORT', 3);

function date_local($timestamp, $mode = DATE_LOCAL_FULL) {
	if ($timestamp == 0) return "незнамокогда";

	$d = date('d', $timestamp);
	$m = date('n', $timestamp);
	$y = date('Y', $timestamp);

	switch ($mode) {
	case DATE_LOCAL_SHORT:
		$date = $d . ' ' . date_month_name($m, DATE_MONTH_NAME_SHORT) . ' ' . $y;
		$time = date('H:i:s', $timestamp);
		return "$date $time";
	case DATE_LOCAL_FULL_DATE:
		return $d . ' ' . date_month_name($m, DATE_MONTH_NAME_FULL_GENITIVE) . ' ' . $y . ' года';
	case DATE_LOCAL_SUPER_SHORT:
		if (date('Y-n-d') == "$y-$m-$d") {
			return date('H:i', $timestamp);
		}
		if (date('Y') == $y) {
			return $d . ' ' . date_month_name($m, DATE_MONTH_NAME_SHORT);
		}
		return date_month_name($m, DATE_MONTH_NAME_SHORT) . ' ' . $y;
	case DATE_LOCAL_FULL:
	default:
		$date = $d . ' ' . date_month_name($m, DATE_MONTH_NAME_FULL_GENITIVE) . ' ' . $y;
		$time = date('H:i:s', $timestamp);
		return "$date года в $time";
	}

}

/**
 *
 * @param string $ymd YYYY-MM-DD
 */
function date_local_ymd($ymd) {
	$time = strtotime($ymd);
	return date("d ", $time) .
		   date_month_name(date("n", $time), DATE_MONTH_NAME_FULL_GENITIVE) .
		   date(" Y", $time) . " года";
}

?>