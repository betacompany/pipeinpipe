<?php

require_once dirname(__FILE__) . '/../config/global-properties.php';
require_once dirname(__FILE__) . '/../config/local-properties.php';

function recursive_go ($prefix, $element) {
	if (is_array($element)) {
		foreach ($element as $key => $value) {
			$upper = strtoupper($key);
			$new_prefix = $prefix == '' ? $upper : $prefix . '_' . $upper;
			recursive_go($new_prefix, $value);
		}
	} else {
		define ($prefix, $element);
		//echo $prefix, '=', $element;
	}
}

global $PROPERTIES;
recursive_go('', $PROPERTIES);

?>
