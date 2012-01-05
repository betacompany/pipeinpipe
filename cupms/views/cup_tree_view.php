<?php

require_once dirname(__FILE__).'/../includes/config.php';

require_once dirname(__FILE__).'/../../'.MAINSITE.'/classes/cupms/Cup.php';

function draw_cup(Cup $cup){
	$childCups = $cup->getChildren();
	$cup_id = $cup->getId();
?>
<li id="cup_<?=$cup_id?>" class="<?=(empty($childCups) ? 'leaf' : 'unfolded')?> cup">
	<div
<?
    if (!$cup->isFinished()) {
?>
            onclick="javascript:
                cup.editName(this, <?=$cup_id?>, {
                    nameEditable: <?=$cup->getParentCupId() ? 'true' : 'false'?>,
                    subCupsMultEditable: <?=!empty($childCups) ? 'true' : 'false'?>,
                    cupMultEditable: <?=!$cup->getParentCupId() ? 'true' : 'false'?>
                })"
<?
    }
?>
    >
		<div class="cup_name">
<?
	echo $cup->getName()
?>
		</div>
		<div class="cup_mult"><?=$cup->getMultiplier()?></div>
		<div id="cup_<?=$cup_id?>_image"></div>
	</div>
<?
	if(!$cup->isFinished()) {
		$isTopLevelCup = $cup->getParentCupId() == 0;
?>
	<script type="text/javascript">
		var сontainerToAppend = 'cup_<?=$cup_id?>_image',
			сontainerToHover = $('#' + сontainerToAppend).parent().parent(),
			
		deleteImage = new FadingImage({
			CSSClass: 'fading_image',
			onclick: function(e) {
				cup.remove(<?=$cup_id?>, <?= $isTopLevelCup ? 'true' : 'false'?>);
		<?if ($isTopLevelCup) {?>
				competition.loadStructure(<?=$cup->getCompetitionId()?>);
		<?}?>
				e.stopPropagation();
			}
		}).appendTo(сontainerToAppend, сontainerToHover),

		createImage = new FadingImage({
			src: '../images/list_add.png',
			CSSClass: 'fading_image',
			onclick: function(e) {
				new AddCupPanel({
					compId: <?=$cup->getCompetitionId()?>,
					parentCupId: <?=$cup_id?>,
					container: 'content_menu'
				}).slideDown();
				e.stopPropagation();
			}
		}).appendTo(сontainerToAppend, сontainerToHover);
	</script>
<?
	}
?>
	<div class="clear"></div>
<?
	if (!empty($childCups)){
?>
	<ul>
<?
		foreach ($childCups as $child)
			draw_cup($child);
?>
	</ul>
<?
	}
?>
</li>
<?php
}
?>
