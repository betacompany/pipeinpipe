<?php

function compile($source) {
	$url = 'http://closure-compiler.appspot.com/compile';
	$post_data = array (
		'js_code' => $source,
		'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
		'output_format' => 'text',
		'output_info' => 'compiled_code'
	);

	$context = stream_context_create(
		array(
			'http' => array(
				'method' => 'POST',
				'content' => http_build_query($post_data)
			)
		)
	);

	$handle = fopen($url, 'r', false, $context);
	return fgets($handle);
}

if (!file_exists('../../js_compiled')) {
	mkdir('../../js_compiled');
}

echo '<pre>';
foreach (glob('../../js/*.js') as $js) {
	if (preg_match('/jquery/', $js)) continue;
	$compiled_code = compile(file_get_contents($js));
	$jjs = basename($js);
	$fp = fopen('../../js_compiled/' . $jjs, 'w');
	fwrite($fp, $compiled_code);
	fclose($fp);
	echo "$jjs compiled and saved\n";
	flush();
}
echo '</pre>';

?>
