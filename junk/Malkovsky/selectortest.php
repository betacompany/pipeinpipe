<?php
/**
 * @author Artyom Grigoriev
 */

require_once dirname(__FILE__) . '/../../main/includes/config-local.php';

?>
<html>
	<head>
		<script type="text/javascript" src="<?=MAIN_SITE_URL?>/js/jquery-1.5.1.min.js"></script>
		<script type="text/javascript" src="<?=MAIN_SITE_URL?>/js/lib-structures.js"></script>
		<script type="text/javascript" src="<?=MAIN_SITE_URL?>/js/ui-controls.js"></script>
		<link rel="stylesheet" href="<?=MAIN_SITE_URL?>/css/ui-controls.css" type="text/css" />
	</head>
	<body>
		<div id="selector_wrapper">
			<script type="text/javascript">
				var selector = new DynamicSelector({
					content: [
						{id: 1, value: 'Пётр Смирнов', html: 'Пётр Смирнов (1)'},
						{id: 2, value: 'Пётр Смирнов', html: 'Пётр Смирнов (2)'},
						{id: 3, value: 'Пётр Смирнов', html: 'Пётр Смирнов (3)'},
						{id: 4, value: 'Пётр Смирнов', html: 'Пётр Смирнов (4)'},
					]
				});
				selector.appendTo($('#selector_wrapper'));
			</script>
		</div>
	</body>
</html>