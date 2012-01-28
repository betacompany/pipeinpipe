<?php
   
/**
 * calculates the biggest number of appereances in the given array
 * among all the elements of this array.
 * @param array $values array of positive numbers or zeros
 * @return <array of ints> first element shows the number of appereances,
 * and all the other are values, which are reaching this number of appereances.
 */
function array_most_common_elements($values) {
	$appereances = array_appereances($values);

	$maxValue = max($appereances);

	$result = array();
	$result[] = $maxValue;
	foreach ($appereances as $key => $val) {
		if($maxValue == $val)
			$result[] = $key;
	}

	return $result;
}

/**
 * calculates the number of appereances in the given array
 * for each element of this array.
 * @param <type> $values array of positive numbers or zeros
 * @return array the i-th element of the returned array is equal
 * to the number of apereances of i in the parameter-array.
 */
function array_appereances($values) {

	$appereances = array();

    for($i = 0; $i <= max($values); $i++)
        $appereances[] = 0;

    foreach ($values as $key => $val)
        $appereances[$val]++;

    return $appereances;
}

/**
 * @param array $array
 * @param mixed $value
 * @return boolean
 */
function array_contains($array, $value) {
    foreach ($array as $element) {
        if ($element == $value) return true;
    }

    return false;
}

/**
 * Encodes $var into JSON standart
 * Supported types of $var:
 * - string (or some other objects with defined __toString() method)
 * - array (list)
 * - array (associative)
 * and all possible inclusions of them.
 * 
 * @param mixed $var
 * @return string
 */
function json($var) {
	if (!is_array($var)) {
		$var = str_replace("\r\n", "\\n", $var);
		$var = str_replace("\n", "\\n", $var);
		$var = str_replace('"', '\"', $var);
		if (is_bool($var)) {
			return $var ? 'true' : 'false';
		}
		return '"' . $var . '"';
	}

	if (isset($var[0])) {
		$result = '[';
		foreach ($var as $i => $item) {
			$result .= json($item);
			if ($i < count($var) - 1) $result .= ',';
		}
		$result .= ']';
		return $result;
	}

	$i = 0;
	$result = '{';
	foreach ($var as $key => $value) {
		$result .= '"' . $key . '":';
		$result .= json($value);
		if ($i < count($var) - 1) $result .= ',';
		$i++;
	}
	$result .= '}';

	return $result;
}

function xml($var) {
	if (!is_array($var)) {
		return $var;
	}

	if (isset($var[0])) {
		$result = "<array>\n";
		foreach ($var as $item) {
			$result .= "<element>" . xml($item) . "</element>";
		}
		$result .= "</array>\n";
		return $result;
	}

	$i = 0;
	$result = "<assoc>\n";
	foreach ($var as $key => $value) {
		$result .= '<' . $key . '>';
		$result .= xml($value);
		$result .= '</' . $key . '>';
		$i++;
	}
	$result .= "</assoc>\n";

	return $result;
}

function swap(&$x, &$y) {
	$t = $y;
	$y = $x;
	$x = $t;
}


function array_transform_toHTML($array, $fromCupMS = true) {
	$result = array();
	foreach ($array as $value) {
		$result[] = $value->toHTML($fromCupMS);
	}
	return $result;
}

function array_diff_value($array, $arrayToExclude) {
	$result = array();
	foreach ($array as $value) {
		if (!array_contains($arrayToExclude, $value)) {
			$result[] = $value;
		}
	}
	return $result;
}

/**
 * Remove recursively $path and all of its contents
 * @param string $path
 */
function file_remove($path, $verbose = false) {
	if (is_file($path)) {
		@unlink($path);
		if ($verbose) echo $path . " removed\n";
	} elseif (is_dir($path)) {
		$folderPath = $path;
		if ($path[count($path) - 1] == "/") {
			$folderPath .= "*";
		} else {
			$folderPath .= "/*";
		}

		$scan = glob($folderPath);
		if ($scan) {
			foreach ($scan as $newPath) {
				file_remove($newPath, $verbose);
			}
		}
		@rmdir($path);
		if ($verbose) echo $path . " dir removed\n";
	}
}

function param($param) {
	return $_REQUEST[$param];
}

function intparam($param) {
	return intval($_REQUEST[$param]);
}

function textparam($param) {
	return string_convert($_REQUEST[$param]);
}

function issetParam($param) {
	return isset ($_REQUEST[$param]);
}

function datetoint($base, $date) {
	$baseint = strtotime($base) / (3600 * 24);
	$dateint = strtotime($date) / (3600 * 24);
	return intval($dateint - $baseint);
}

function substring($str, $start, $length) {
	return mb_substr($str, $start, $length, "UTF-8");
}

function strlength($str) {
	return mb_strlen($str, "UTF-8");
}

function string_convert($str) {
	//return iconv('UTF-8', 'windows-1251', $str);
	return $str;
}

function string_short($str, $length, $maxLength, $suffix = '...') {
	if ($length > $maxLength) return $str;
	if (strlength($str) <= $maxLength) return $str;
	for ($i = $maxLength; $i >= 0; $i--) {
		if ($str[$i] == ' ' || $str[$i] == "\n" || $str[$i] == "\t") break;
	}

	if ($i < $length / 2) return substring($str, 0, $length) . $suffix;

	return substring($str, 0, $i) . $suffix;
}

function string_insert_spaces($str) {
	$result = '';
	for ($i = 0; $i < strlen($str) - 1; $i++) {
		$result .= $str[$i] . ' ';
	}
	$result .= $str[strlen($str) - 1];
	return $result;
}

function string_split_into_lines($string, $max_line_length = 30, $min_word_length_to_hyphenate = 10) {
	$result = array();
	$words = explode(' ', $string);
	$currentLine = '';
	foreach($words as $word) {
		if (strlength($word) > $min_word_length_to_hyphenate && strlength($word) > $max_line_length) {
			$result[] = $currentLine;
			$result[] = $word;
			$currentLine = '';
		} else if (strlength($currentLine) + strlength($word) > $max_line_length) {
			if (strlength($word) >= $min_word_length_to_hyphenate) {
				$hiphenated = hyphenate($word, $max_line_length - strlength($currentLine));
				$result[] = $currentLine . ($hiphenated[0] == '' ? '' : $hiphenated[0] . '-');
				$currentLine = $hiphenated[1];
			} else {
				$result[] = $currentLine;
				$currentLine = $word;
			}
		} else {
			$currentLine .= $word;
		}
		$currentLine .= ' ';
	}

	$result[] = $currentLine;

	return $result;
}

function hyphenate($word, $max_prefix_length) {
	$vowels = array('у', 'е', 'ы', 'а', 'о', 'э', 'я', 'и', 'ю', 'У', 'Е', 'Ы', 'А', 'О', 'Э', 'Я', 'И', 'Ю');

	$jotAdded = false;// a flag that means that we added й to the vowels set
	for ($i = $max_prefix_length - 1; $i >= 1; $i--) {
		$char = substring($word, $i, 1);
		$next = substring($word, $i + 1, 1);
		$afterOne = substring($word, $i + 2, 1);

		if (in_array($char, $vowels)) {
			if ($next == 'й') {
				$vowels[] = 'й';
				$jotAdded = true;
				continue;
			}

			if (in_array($next, $vowels) || in_array($afterOne, $vowels)) {
				return array(substring($word, 0, $i + 1), substring($word, $i + 1, strlength($word)));
			} else if (!in_array($next, $vowels) && !in_array($afterOne, $vowels)) {
				if ($i + 2 <= $max_prefix_length)
					return array(substring($word, 0, $i + 2), substring($word, $i + 2, strlength($word)));
			}
		}
		$jotAdded = false;
	}
	return array('', $word);

}

function redirect_back($anchor = false, $exit = true) {
	Header('Location: ' . $_SERVER['HTTP_REFERER'] . ($anchor ? '#'.$anchor : ''));
	if ($exit) exit(0);
}
?>
