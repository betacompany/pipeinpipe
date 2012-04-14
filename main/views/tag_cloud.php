<?php
/**
 * @author Innokenty Shuvalov
 *         ishuvalov@pipeinpipe.info
 *         vk.com/innocent
 */

/**
 * @param int $width width of the canvas in pixels
 * @param int $height height of the canvas in pixels
 * @param null $tags all tags are loaded by default
 */
function tag_cloud_show($width = 300, $height = 300, $tags = null) {
?>
<div id="tag_cloud_container">
    <canvas id="tag_cloud" width="<?=$width?>" height="<?=$height?>">
<?
    $tags = Tag::getAllByType(Item::BLOG_POST, true);
    $max = Tag::$max;
    foreach ($tags as $tag) {
        $id = $tag->getId();
        $value = $tag->getValue();
        $fontSize = 1 + round(($tag->getCount() / $max) * 20) / 10;
?>
    <a href="#tag=<?=$id?>" style="font-size: <?=$fontSize?>em" onclick="javascript: life.showTag(<?=$id?>);">
<?
        echo $value;
?>
    </a>
<?
    }
?>
    </canvas>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        if(!$('#tag_cloud').tagcanvas({
            minBrightness: 0,
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
//            textHeight: 14,
        })) {
            // TagCanvas failed to load
            $('#tag_cloud_container').hide();
        }
    });
</script>
<?
}
?>
