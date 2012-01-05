<?php

$prefix = '';
if (issetParam('part')) {
	$split = explode('_', param('part'), 2);
	$prefix = $split[0];
}

$menu_items = array('' => 'Главное', 'sport' => 'Спорт', /*'blogs' => 'Блоги',*/ 'forum' => 'Форум');

?>

	<div id="header">
	<?
	foreach ($menu_items as $menu_item => $title):
		$t = ($menu_item != '') ? $title : '<img class="home" src="/images/home.png"/>';
	?>
		<a href="/<?=$menu_item?>"
		   class="menu_item
		   <? if ($menu_item == $prefix): ?> selected<? endif; ?>"><?=$t?></a>
		<? endforeach; ?>
	</div>

	<div id="subheader">

	</div>