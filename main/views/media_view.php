<?php

require_once dirname(__FILE__) . '/../includes/date.php';
require_once dirname(__FILE__) . '/blocks.php';

function media_show_preview_items($uid, Group $group, $from, $limit) {
	$items = $group->getItems($from, $limit);
	$count = $group->countItems();
	$page = $from / $limit + 1;
	$id = $group->getId();

	if ($count > 20) {
?>

		<div class="paging_wrapper">
<?
	show_paging_bar($count, $limit, $page, "media.loadItems($id, %d);");
?>

		</div>
<?
	}
?>

		<div class="body">
<?
	foreach ($items as $item) {
		if ($item instanceof Photo) {
			media_show_preview_photo($uid, $item);
		} elseif ($item instanceof Video) {
			media_show_preview_video($uid, $item);
		} else {
			global $LOG;
			$LOG->warn('Album ' + $group->getId() + ' contains not only photos or videos!');
		}
	}
?>

		</div>
<?
	if ($count > 20) {
?>

		<div class="paging_wrapper">
<?
	show_paging_bar($count, $limit, $page, "media.loadItems($id, %d);");
?>

		</div>
<?
	}
}

function media_show_preview_items_array($uid, $items) {
?>

		<div class="body">
<?
	foreach ($items as $item) {
		if ($item instanceof Photo) {
			media_show_preview_photo($uid, $item);
		} elseif ($item instanceof Video) {
			media_show_preview_video($uid, $item);
		} 
	}
?>

		</div>
<?
}


function media_show_preview_photo($uid, Photo $photo) {
?>

			<div class="photo_preview">				
				<div>
					<div class="zoom" onclick="javascript: media.enableSlideShow(<?=$photo->getGroupId()?>, <?=$photo->getId()?>);"></div>
					<a href="/media/photo/album<?=$photo->getGroupId()?>/<?=$photo->getId()?>"><img class="ph" src="<?=$photo->getUrl(Photo::SIZE_MINI)?>" alt="<?=$photo->getTitle()?>" /></a>				
				</div>				
			</div>
<?
}

function media_show_preview_video($uid, Video $video) {
	$author = $video->getUser();
?>

			<div class="video_preview">
				<div class="left">
					<a href="/media/video/album<?=$video->getGroupId()?>/<?=$video->getId()?>" title="<?=$video->getTitle()?>">
						<div class="<?=$video->getType()?>" style="background-image: url('<?=$video->getPreviewUrl()?>');"><div></div></div>
					</a>
				</div>
				<div class="right_container">
					<div class="right_content">
						<div class="title">
							<a href="/media/video/album<?=$video->getGroupId()?>/<?=$video->getId()?>"><?=$video->getTitle()?></a>
						</div>
<?
if ($author != null) {
?>

						<div class="author">
							<a href="/id<?=$author->getId()?>"><?=$author->getFullName()?></a>
						</div>
<?
}
?>

					</div>
				</div>
			</div>
<?
}

function media_show_preview_photos_list(Photo $photo, $items = false) {
	if (!$items) {
		$items = $photo->getGroup()->getItems();
	}
?>

				<div>
<?
	foreach ($items as $item) {
		if ($item instanceof Photo) {
			$t = ($item->getId() == $photo->getId()) ? ' class="selected"' : '';
?>

					<a id="thumb_<?=$item->getId()?>" href="/media/photo/album<?=$item->getGroupId()?>/<?=$item->getId()?>"><img<?=$t?> src="<?=$item->getPreviewUrl()?>" alt="<?=$item->getTitle()?>" /></a>
<?
		}
	}
?>

				</div>
<?
}

function media_show_photo($uid, Photo $photo) {
?>

		<div class="item_wrapper">
			<div class="item_left">
				<div>
					<img class="ph" src="<?=$photo->getUrl(Photo::SIZE_MIDDLE)?>" alt="<?=$photo->getTitle()?>" />
					<div class="title"><?=$photo->getTitle()?></div>
					<div class="author"><a href="/id<?=$photo->getUID()?>"><?=$photo->getUser()->getFullName()?></a></div>
				</div>
				<div class="tools">
					<div class="zoom" style="display: block;" onclick="javascript: media.enableSlideShow(<?=$photo->getGroupId()?>, <?=$photo->getId()?>);"></div>
					<div class="evaluation"></div>
					<script type="text/javascript">
						$$(function () {
							content.showEvaluation(
								$('.evaluation'),
								<?=$photo->getId()?>,
								<?=$photo->getEvaluation()?>,
								<?=($uid != 0 && !$photo->isActedBy(User::getById($uid), Action::EVALUATION) && Action::isActive(Action::EVALUATION, $photo) ? 'true' : 'false')?>

							);
						});
					</script>
				</div>
			</div>
			<div class="item_right">
<?
	media_show_preview_photos_list($photo);
?>

			</div>
			<div class="comments">
<?
	show_block_comments(User::getById($uid), $photo);
?>

			</div>
		</div>

		<script type="text/javascript">
			$$(function () {
				$('.item_right > div').draggable({
					axis:'x',
					cursor:'e-resize',
					drag:function (e, ui) {
					}
				});

				$(document).ready(function () {
					var x = $('.item_right img.selected').offset().left,
						w = $('.item_right img.selected').outerWidth(),
						ww = $('.item_right').innerWidth(),
						d = $('body').innerWidth() - ww;
					d /= 2;
					$('.item_right > div').animate({
						left:'-=' + (x - ww / 2 + w / 2 - d)
					});
				});

				media.onClose = function (itemId) {
					window.location = '' + itemId;
				};
			});
		</script>
<?
}

function media_show_video($uid, Video $video) {
?>

		<div class="item_wrapper">
			<div class="item_left">
				<div>
<?
    if ($video->isVideoCode()) {
        echo $video->getSource();
    } else {
        media_show_youtube_video($video->getSource());
    }
?>
				</div>
			</div>
			<div class="comments">
<?
	show_block_comments(User::getById($uid), $video);
?>

			</div>
		</div>
<?
}

function media_show_youtube_video($videoId, $width = 480, $height = 385) {
?>
<div id="ytapiplayer">
    You need Flash player 8+ and JavaScript enabled to view this video.
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var params = { allowScriptAccess: "always" };
        var atts = {
            id: "ytplayer",
            allowfullscreen: true
        };
        swfobject.embedSWF (
            "http://www.youtube.com/v/<?=$videoId?>?enablejsapi=1&playerapiid=ytplayer",
            "ytapiplayer", "<?=$width?>", "<?=$height?>", "8", null, null, params, atts
        );

        window.onYouTubePlayerReady = function (playerId) {
            var ytplayer = document.getElementById(playerId);
            ytplayer.playVideo();
        }
    });
</script>
<?
}

function media_slideshow_block() {
?>
	<script type="text/javascript">
		$$(function () {
			$(window).keyup(function () {
				media.slideShowKey(event);
			});

			$(window).resize(function () {
				media.disableSlideShow();
			});
		});
	</script>

	<div id="slideshow">
		<div id="slideshow_close" onclick="javascript: media.disableSlideShow();"></div>
		<center id="slideshow_content"></center>
		<div id="slideshow_bar" class="item_right"><div></div></div>
	</div>
<?
}

function media_script_album() {
?>

		<script type="text/javascript">
			$$(function () {
				var make_position = function () {
					var h = $(this).outerHeight(),
						w = $(this).outerWidth(),
						t = $(this).parents('.photo_preview').innerHeight() / 2 - h / 2,
						l = $(this).parents('.photo_preview').innerWidth() / 2 - w / 2;

					$(this).parent().parent().animate({top:t, left:l});
				};

				var make_grid = function () {
					if (window.innerWidth > 1200) {
						var wp = Math.floor(100 / Math.floor(0.8 * window.innerWidth / 200));
						$('.photo_preview').css('width', wp + '%');
						$('.video_preview').css('width', '33%');
					} else {
						$('.photo_preview').css('width', '25%');
					}
				};

				$('img.ph').load(make_position);
				$(document).ready(make_grid);

				$(window).resize(function () {
					make_grid();
					$('img.ph').each(make_position);
				});
			});
		</script>
<?
}

?>

