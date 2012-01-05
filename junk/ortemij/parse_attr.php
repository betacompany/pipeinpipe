<?php
/**
 * @author Artyom Grigoriev
 */

$attr_str = 'atata="dsds" ffff=10 aasdasd=dadas dasdas="sdas asdas"';
$result = array();
$key = '';
$value = '';
$quote = false;
for ($i = 0; $i < strlen($attr_str); ++$i) {
	while ($attr_str[$i] != '=' && $i < strlen($attr_str)) {
		$key .= $attr_str[$i];
		$i++;
	}

	if ($attr_str[$i] == '=') $i++;

	if ($attr_str[$i] == '"') {
		$i++;
		$quote = true;
	}

	while (
		(
			($quote && $attr_str[$i] != '"') ||
			(!$quote && $attr_str[$i] != ' ')
		)
		&& $i < strlen($attr_str)
	) {
		$value .= $attr_str[$i];
		$i++;
	}

	if ($attr_str[$i] == '"') $i++;
	if ($attr_str[$i] == ' ') $i++;

	$result[$key] = $value;
	$key = '';
	$value = '';
	$quote = false;
}

echo '<pre>';
echo $attr_str. "\n\n";
print_r($result);
echo '</pre>';

?>
