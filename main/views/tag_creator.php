<?php
/**
 * @author Innokenty Shuvalov
 *         ishuvalov@pipeinpipe.info
 *         vk.com/innocent
 */

function tag_creator_show($item = null, $width = 300) {
    $addedTags = array();
    if ($item) {
        foreach ($item->getTags() as $tag) {
            $addedTags[] = array('id' => $tag->getId(), 'value' => $tag->getValue());
        }
        $addedTags = json($addedTags);
    }
    $itemId = $item ? $item->getId() : 0;
?>
<script type="text/javascript" src="/js/tag_creator.js"></script>
<link rel="stylesheet" type="text/css" href="/css/tag_creator.css"/>

<div class="tag_creator" data-item-id="<?=$itemId?>" data-width="<?=$width?>">
    <script type="text/javascript">
        TagCreator.setTags(<?=$addedTags?>, <?=$itemId?>);
    </script>
    <div class="tag_creator_added_tags_container">
        <div class="tag_creator_added_tags"></div>
        <div class="clear"></div>
    </div>
    <div class="tag_creator_new_tag_selector_container">
        <div class="tag_creator_new_tag_selector"></div>
        <div class="tag_creator_btn_create button">Добавить</div>
        <div class="clear"></div>
    </div>
</div>
<?
}
?>
