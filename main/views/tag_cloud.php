<?php
/**
 * @author Innokenty Shuvalov
 *         ishuvalov@pipeinpipe.info
 *         vk.com/innocent
 */

/**
 * @param int $height height of the canvas in pixels
 */
function tag_cloud_show($tags, $height = 300) {
	global $tagCloudEnabled;
	$tagCloudEnabled = true;
?>
<div id="tag_cloud_container">
    <canvas id="tag_cloud" height="<?=$height?>">
<?
    $max = Tag::$max;
    foreach ($tags as $tag) {
        $id = $tag->getId();
        $value = $tag->getValue();
        $fontSize = 1 + round(($tag->getCount() / $max) * 20) / 10;
		if (mb_strlen($value) > 0) {
?>
		<a href="#tag=<?=$id?>" style="font-size: <?=$fontSize?>em" onclick="javascript: life.showTag(<?=$id?>);"><?=$value?></a>
<?
		}
   }
?>
    </canvas>
</div>
<script type="text/javascript">
    const tagCloudSelector = '#tag_cloud';

    var initCloud = function() {
        if(!$(tagCloudSelector)
            .attr('width', $('.body_container').innerWidth())
            .tagcanvas({
                minBrightness: 0,
                maxSpeed: 0.03,
                initial: [0.3, 0.2],
                decel: 0.98,
                textColour: null,
                outlineThickness: 2,
                outlineColour: '#c7dce3',
                outlineMethod: 'block',
                outlineOffset: 3,
                frontSelect: true,
                reverse: true,
                weight: true,
                depth: 0.9,
                stretchX: 4//,
//                textHeight: 14,
        })) {
            // TagCanvas failed to load
            $('#tag_cloud_container').hide();
        }
    };

	$$(function () {
		initCloud();
		$(window).resize(initCloud);
	});
</script>
<?
}
?>
