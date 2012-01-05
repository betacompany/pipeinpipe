<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../../main/classes/content/Parser.php';

//echo Parser::parse("[quote]I said: [quote]asdasdad[/quote].[/quote]");

?>

<html>
	<body>
		<tt>
<?
$parsed = '';
$source = isset($_REQUEST['source']) ? $_REQUEST['source'] : '';
try {
	define ('START', microtime(true));
	
	switch ($_REQUEST['parser']) {
	case 'strict':
		$parsed = Parser::parseStrict($source);
		break;
	case 'source':
		$parsed = Parser::parseSource($source);
		break;
	case 'description':
		$parsed = Parser::parseDescription($source);
		break;
	}

	printf('<br/>Time: %.4fs', microtime(true) - START);
} catch (Exception $e) {
	echo $e->getMessage();
}
?>

		</tt>
		<form method="post" action="<?=$_SERVER['SCRIPT_NAME']?>">
			parsed:<br />
			<textarea cols="100" rows="6" name="parsed"><?=$parsed?></textarea>
			<br />
			source:<br />
			<textarea cols="100" rows="6" name="source"><?=$source?></textarea>
			<br />
			parser:
			<select name="parser">
				<option value="strict" <?if ($_REQUEST['parser'] == 'strict'): ?>selected="selected"<?endif;?>>Strict Parser</option>
				<option value="source" <?if ($_REQUEST['parser'] == 'source'): ?>selected="selected"<?endif;?>>Source Parser</option>
				<option value="description" <?if ($_REQUEST['parser'] == 'description'): ?>selected="selected"<?endif;?>>Description Parser</option>
			</select>
			<input type="submit" value="Парсить!" />
		</form>
	</body>
</html>
