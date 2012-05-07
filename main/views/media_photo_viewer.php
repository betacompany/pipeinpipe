<?php

require_once dirname(__FILE__) . '/media_view.php';

global $photos;
global $itemId;
global $user;

$index = 0;
$photo = $photos[0];
if ($itemId) {
	for ($i = 0, $n = count($photos); $i < $n; $i++) {
		$ph = $photos[$i];
		if ($ph->getId() == $itemId) {
			$index = $i;
			$photo = $ph;
			break;
		}
	}
}

if ($user) {
	$photo->viewedBy($user);
}

?>

<div id="media_container">
	<div class="item_wrapper">
		<div class="item_left">
			<div class="tools">
				<div id="photo_title" class="title"><?=$photo->getTitle()?></div>
				<div class="evaluation"></div>
				<script type="text/javascript">
					$$(function () {
						content.showEvaluation(
							$('.evaluation'),
						<?=$photo->getId()?>,
						<?=$photo->getEvaluation()?>,
						<?=($uid != 0 && !$photo->isActedBy($user, Action::EVALUATION) && Action::isActive(Action::EVALUATION, $photo) ? 'true' : 'false')?>

						);
					});
				</script>
			</div>
		</div>
	</div>
	<div id="photo_viewer">
		<div class="photo_wrap" id="prev_photo"><div></div></div>
		<div class="photo_wrap" id="main_photo"></div>
		<div class="photo_wrap" id="next_photo"><div></div></div>
	</div>
	<div id="photo_bar">
		<div class="item_wrapper">
			<div class="item_left">
				<div id="photo_comments" class="comments">
					<?
					show_block_comments($user, $photo);
					?>

				</div>
			</div>
		</div>
	</div>
	<div id="thumbs" class="item_right">
		<?
		media_show_preview_photos_list($photo);
		?>

	</div>
</div>

<script type="text/javascript">
	$$(function () {
		photo.init(<?=json($photos)?>, <?=$index?>);
	});
</script>
