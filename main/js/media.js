/**
 * @author Artyom Grigoriev
 */

var media = {

	loadItems: function (groupId, page) {
		$.ajax({
			url: '/procs/proc_media.php',
			data: {
				method: 'load_items',
				from: (page - 1) * 20,
				limit: 20,
				group_id: groupId
			},

			success: function (html) {
				$('.preview').html(html);
				try {
					make_grid();
					$('.photo_preview > div > img').load(make_position);
				} catch (e) {}
			}
		});
	},

	_slideShowEnabled: false,
	_slideShowItems: {},
	_slideShowItemsLoaded: false,
	_slideshowHeight: 0,
	_slideshowWidth: 0,

	setSlideShowItems: function (a) {
		this._slideShowItems = a;
		for (var key in this._slideShowItems) {
			var item = this._slideShowItems[key];
			$('#slideshow_bar > div').append(
				$('<img/>')
					.attr({
						'src': item.preview,
						'id': 'slideshow_bar_' + key
					})
					.click(function () {
						var tokens = $(this).attr('id').split('_');
						media.loadSlideShowPhoto(tokens[2]);
					})
			);
		}

		$('#slideshow_bar > div')
			.width(getKeys(this._slideShowItems).length * 80)
			.draggable({
				axis: 'x',
				cursor: 'x-resize',
				drag: function(e, ui) {}
			});
			
		this._slideShowItemsLoaded = true;
	},

	getSlideShowItems: function () {
		return this._slideShowItems;
	},
	
	enableSlideShow: function (albumId, itemId) {
		if (this._slideShowEnabled) return;
		if ($.browser.msie && $.browser.version < '7.0') {
			alert('Too old browser!');
			return;
		}
		
		$('#slideshow').fadeIn();
		$('#slideshow').width(window.innerWidth);
		$('#slideshow').height(window.innerHeight);
		$('body').css('overflow', 'hidden');

		var h = this._slideshowHeight = window.innerHeight - 80;
		var w = this._slideshowWidth = window.innerWidth - 10;

		if (!this._slideShowItemsLoaded) {
			$.ajax({
				url: '/procs/proc_media.php',
				data: {
					method: 'load_slideshow',
					size: w + 'x' + h,
					group_id: albumId
				},
				dataType: 'json',

				success: function (json) {
					media.setSlideShowItems(json);
					media.startSlideShow(itemId);
				}
			});
		} else {
			media.startSlideShow(itemId);
		}

		this._slideShowEnabled = true;
	},

	getCurrentItemId: function () {
		var attr_id = $('#slideshow_content .current').attr('id').split('_');
		return attr_id[2];
	},

	disableSlideShow: function () {
		if (!this._slideShowEnabled) return;
		var itemId = this.getCurrentItemId();

		$('#slideshow').fadeOut();
		$('#slideshow_content *').removeClass('current').hide();
		$('#slideshow_bar *').removeClass('selected');
		$('body').css('overflow', 'auto');
		this._slideShowEnabled = false;
		this.onClose(itemId);
	},

	loadSlideShowPhoto: function (itemId) {
		if (ge('slideshow_img_' + itemId) == undefined) {
			var url = this._slideShowItems[itemId].full;
			loading(ge('slideshow_content'), true);
			$('#slideshow_content').append(
				$('<img/>')
					.attr({
						src: url,
						id: 'slideshow_img_' + itemId
					})
					.css({
						'max-height': this._slideshowHeight,
						'max-width': this._slideshowWidth,
						'cursor': 'pointer'
					})
					.hide()
					.load(function () {
						loading(ge('slideshow_content'), false);
						$('#slideshow_content > img.current').fadeOut(function () {
							$(this).removeClass('current');
							$('#slideshow_img_' + itemId).fadeIn().addClass('current');
							$('#slideshow_bar *').removeClass('selected');
							$('#slideshow_bar_' + itemId).addClass('selected');
							media.moveSlideShowBar();
						});
					})
					.click(
						function () {
							media.loadSlideShowPhotoNext(itemId);
						}
					)
			);
		} else {
			var open = function () {
				$(this).removeClass('current');
				$('#slideshow_img_' + itemId)
					.fadeIn()
					.addClass('current')
					.css({
						'max-height': media._slideshowHeight,
						'max-width': media._slideshowWidth
					});
				$('#slideshow_bar *').removeClass('selected');
				$('#slideshow_bar_' + itemId).addClass('selected');
				media.moveSlideShowBar();
			};

			$('#slideshow_content > img.current').fadeOut(open);
			if ($('#slideshow_content > img.current').length == 0) open();
		}
	},

	loadSlideShowPhotoNext: function (itemId) {
		var ids = getKeys(this._slideShowItems);
		for (var i = 0; i < ids.length; i++) {
			if (ids[i] == itemId) break;
		}

		if (++i < ids.length) {
			this.loadSlideShowPhoto(ids[i]);
		} else if (ids.length > 0) {
			this.loadSlideShowPhoto(ids[0]);
		}
	},

	moveSlideShowBar: function () {
		var x = $('#slideshow_bar .selected').offset().left,
			c = (window.innerWidth - $('#slideshow_bar .selected').outerWidth()) / 2;
		$('#slideshow_bar > div').animate({
			left: '-=' + (x - c)
		});
	},

	startSlideShow: function (itemId) {
		var items = getKeys(this._slideShowItems);
		itemId = (itemId != undefined) ? ''+itemId : items[0];
		debug(itemId);
		this.loadSlideShowPhoto(itemId);
		this.preloadItemId = itemId;
		this.preload();
		$('#slideshow_img_' + itemId).addClass('current');
		$('#slideshow_bar_' + itemId).addClass('selected');
	},

	slideShowKey: function (event) {
		debug(event);
		if (event.keyCode == 27) {
			this.disableSlideShow();
		}
	},

	clearSlideShow: function () {
		this._slideShowItems = {};
		this._slideShowItemsLoaded = false;
		this._slideShowEnabled = false;
		$('#slideshow_content').html('');
		$('#slideshow_bar > div').html('');
	},

	onClose: function (itemId) {},

	preloadItemId: false,
	preload: function () {
		if (!this.preloadItemId) return;
		if (!this._slideShowEnabled) return;
		if (ge('slideshow_item_' + this.preloadItemId) == undefined) {
			var itemId = this.preloadItemId;
			var url = this._slideShowItems[itemId].full;
			$('#slideshow_content').append(
				$('<img/>')
					.attr({
						src: url,
						id: 'slideshow_img_' + itemId
					})
					.css({
						'max-height': this._slideshowHeight,
						'max-width': this._slideshowWidth,
						'cursor': 'pointer'
					})
					.hide()
					.load(function () {
						debug('[slideshow preloader] Item ' + itemId + ' loaded.');
						var items = media.getSlideShowItems();
						var ids = getKeys(items);
						var curItemId = media.getCurrentItemId();
						for (var i = 0; i < ids.length; i++) {
							if (ids[i] == curItemId) break;
						}

						if (i >= ids.length) return;

						var go = true;
						for (var j = 1; j < ids.length && go; j++) {
							for (var x = 0; x <= 1; x++) {
								var dir = (x == 0) ? j : -j;
								if (ids[i + dir] != undefined) {
									if (ge('slideshow_img_' + ids[i + dir]) == undefined) {
										media.preloadItemId = ids[i + dir];
										media.preload();
										debug('[slideshow preloader] Preloader called with id ' + ids[i + dir]);
										go = false;
										break;
									}
								}
							}
						}
					})
					.click(
						function () {
							media.loadSlideShowPhotoNext(itemId);
						}
					)
			);
		}
	},

	youtube: {
		init: function() {
			const youtubeApiUrl = "https://gdata.youtube.com/feeds/api/videos/";
			const youtubeApiParams = {
				v: 2,
				alt: "json-in-script",
				format: 5,
				callback: "handleVideo"
			};
			const youtubeApiThumbnailId = "hqdefault";

			const formSelector = "#video_uploader > form";
			const uploadBtnId = "video_upload_btn";
			const titleInputId = "video_title";
			const linkInputId = "video_link";
			const videoPreviewImgId = "video_preview";

			const defaultLinkInputValue = 'http://www.youtube.com/watch?v=';
			const linkRegex = /(http\:\/\/|)(www.|)(youtube\.com)\/(v\/|watch\?v\=)((\w|\-){7,}).*/;

			var titleInput = $('#' + titleInputId);
			var linkInput = $('#' + linkInputId);
			var uploadBtn = $('#' + uploadBtnId);
			var videoPreviewImg = $('#' + videoPreviewImgId);

			var videoLoaded;
			var videoLoadedUrl;

			videoPreviewImg.hide();
			titleInput.attr('disabled', 'disabled')
					.addClass('disabled');
			linkInput.val(defaultLinkInputValue);
			bindFocusInFadingValue(linkInput, defaultLinkInputValue);
			bindFocusOutFadingValue(linkInput, defaultLinkInputValue);

			linkInput.bind('keyup', function() {
				var link = linkInput.val();
				if (linkRegex.test(link)) {
					$.ajax({
						url: youtubeApiUrl + RegExp.$5,
						data: youtubeApiParams,
						success: function(data) {
							eval(data);
						}
					});

//					var a = {"version":"1.0","encoding":"UTF-8","entry":{"xmlns":"http://www.w3.org/2005/Atom","xmlns$media":"http://search.yahoo.com/mrss/","xmlns$gd":"http://schemas.google.com/g/2005","xmlns$yt":"http://gdata.youtube.com/schemas/2007","gd$etag":"W/\"A0YCSX47eCp7I2A9WhVXFk0.\"","id":{"$t":"tag:youtube.com,2008:video:g7zHKcYzjNQ"},"published":{"$t":"2012-04-16T10:00:00.000Z"},"updated":{"$t":"2012-04-16T20:46:08.000Z"},"category":[{"scheme":"http://schemas.google.com/g/2005#kind","term":"http://gdata.youtube.com/schemas/2007#video"},{"scheme":"http://gdata.youtube.com/schemas/2007/categories.cat","term":"Comedy","label":"Comedy"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"rhettandlink"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"rhett"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"link"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"Good Mythical Morning"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"The Gregory Brothers"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"schmoyo"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"Bed Intruder"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"Taxi Dave"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"Winning"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"Songify This"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"auto tune the news"}],"title":{"$t":"Epic Sibling Rivalries with the Gregory Brothers"},"content":{"type":"application/x-shockwave-flash","src":"https://www.youtube.com/v/g7zHKcYzjNQ?version=3&f=videos&app=youtube_gdata"},"link":[{"rel":"alternate","type":"text/html","href":"https://www.youtube.com/watch?v=g7zHKcYzjNQ&feature=youtube_gdata"},{"rel":"http://gdata.youtube.com/schemas/2007#video.responses","type":"application/atom+xml","href":"https://gdata.youtube.com/feeds/api/videos/g7zHKcYzjNQ/responses?v=2"},{"rel":"http://gdata.youtube.com/schemas/2007#video.related","type":"application/atom+xml","href":"https://gdata.youtube.com/feeds/api/videos/g7zHKcYzjNQ/related?v=2"},{"rel":"http://gdata.youtube.com/schemas/2007#mobile","type":"text/html","href":"https://m.youtube.com/details?v=g7zHKcYzjNQ"},{"rel":"self","type":"application/atom+xml","href":"https://gdata.youtube.com/feeds/api/videos/g7zHKcYzjNQ?v=2"}],"author":[{"name":{"$t":"rhettandlink2"},"uri":{"$t":"https://gdata.youtube.com/feeds/api/users/rhettandlink2"},"yt$userId":{"$t":"4PooiX37Pld1T8J5SYT-SQ"}}],"yt$accessControl":[{"action":"comment","permission":"allowed"},{"action":"commentVote","permission":"allowed"},{"action":"videoRespond","permission":"moderated"},{"action":"rate","permission":"allowed"},{"action":"embed","permission":"allowed"},{"action":"list","permission":"allowed"},{"action":"autoPlay","permission":"allowed"},{"action":"syndicate","permission":"allowed"}],"gd$comments":{"gd$feedLink":{"rel":"http://gdata.youtube.com/schemas/2007#comments","href":"https://gdata.youtube.com/feeds/api/videos/g7zHKcYzjNQ/comments?v=2","countHint":379}},"yt$hd":{},"media$group":{"media$category":[{"$t":"Comedy","label":"Comedy","scheme":"http://gdata.youtube.com/schemas/2007/categories.cat"}],"media$content":[{"url":"https://www.youtube.com/v/g7zHKcYzjNQ?version=3&f=videos&app=youtube_gdata","type":"application/x-shockwave-flash","medium":"video","isDefault":"true","expression":"full","duration":737,"yt$format":5},{"url":"rtsp://v4.cache1.c.youtube.com/CiILENy73wIaGQnUjDPGKce8gxMYDSANFEgGUgZ2aWRlb3MM/0/0/0/video.3gp","type":"video/3gpp","medium":"video","expression":"full","duration":737,"yt$format":1},{"url":"rtsp://v1.cache4.c.youtube.com/CiILENy73wIaGQnUjDPGKce8gxMYESARFEgGUgZ2aWRlb3MM/0/0/0/video.3gp","type":"video/3gpp","medium":"video","expression":"full","duration":737,"yt$format":6}],"media$credit":[{"$t":"rhettandlink2","role":"uploader","scheme":"urn:youtube","yt$display":"rhettandlink2","yt$type":"partner"}],"media$description":{"$t":"Special Guests: The Gregory Brothers. Good Mythical Morning Episode 70 Comment below: Share you sibling rivalry stories \r\nWatch the full length performance of The Gregory Brothers \"24/7\" on the Kommunity: http://rhettandlinkommunity.com/profiles/blogs/gregorybrosperform\r\n\r\n**** SUBSCRIBE for daily episodes: http://bit.ly/subrl2 ****\r\n\r\nThis episode of GMM is brought to you by Smule! \r\nhttp://bit.ly/SmuleWebsite \r\n\r\nGMM Jingle/ Smule Songify 2.0 Contest Instructions:\r\n1.  download Songify 2.0 (it's free!): http://itunes.apple.com/us/app/songify/id438735719\r\n2.  download tracks (earn free coins within the app)\r\n3.  write a few lines about GMM\r\n4.  record a few different versions of you TALKING (not singing) the lines\r\n5.  name each one \"GMM Jingle ____\"\r\n6.  email your favorite to us by click SHARE then email: show@rhettandlink.com\r\n* DEADLINE * :  Sunday Night 4/22/12\r\n\r\nThanks to the Gregory Brothers for stopping by! Be sure to subscribe to their channel! \r\nhttp://www.youtube.com/schmoyoho\r\n\r\nWatch the Gregory Brothers Exclusive Performance by  joining the RhettandLinKommunity!\r\nhttp://bit.ly/rlkommunity\r\n\r\nFor information about sponsoring an episode of GMM, emails us at show@rhettandlink.com\r\n\r\nMAIN YOUTUBE CHANNEL: http://youtube.com/rhettandlink\r\n\r\nFACEBOOK: \u202ahttp://bit.ly/rhettandlinkfb\u202c\r\n\r\nTWITTER: \u202ahttp://bit.ly/rltwitter\u202c\r\n\r\nSend us stuff at our P.O. Box\r\nRhett & Link\r\nPO Box 55605, Sherman Oaks, CA 91413\r\n\r\nGood Mythical Morning is available for download on iTunes!\r\nVideo Podcast: http://bit.ly/xuJVPc\r\nAudio Podcast: http://bit.ly/zSewZ6\r\n\r\nJOIN the RhettandLinKommunity!\r\nhttp://bit.ly/rlkommunity\r\n\r\nCREDITS: \r\nCamera, PA, Editing:  Jason Inman\r\nIntro/Outro music: RoyaltyFreeMusicLibrary.com\u2028\u202a\u202ahttp://www.royaltyfreemusiclibrary.com/\u202c\u202c \r\nMicrophone: The Mouse from Blue Microphones: http://www.bluemic.com/mouse/\r\n\r\n------------------\r\nSubscribe if you like what you see!","type":"plain"},"media$keywords":{"$t":"rhettandlink, rhett, link, Good Mythical Morning, The Gregory Brothers, schmoyo, Bed Intruder, Taxi Dave, Winning, Songify This, auto tune the news"},"media$license":{"$t":"youtube","type":"text/html","href":"http://www.youtube.com/t/terms"},"media$player":{"url":"https://www.youtube.com/watch?v=g7zHKcYzjNQ&feature=youtube_gdata_player"},"media$thumbnail":[{"url":"http://i.ytimg.com/vi/g7zHKcYzjNQ/default.jpg","height":90,"width":120,"time":"00:06:08.500","yt$name":"default"},{"url":"http://i.ytimg.com/vi/g7zHKcYzjNQ/mqdefault.jpg","height":180,"width":320,"yt$name":"mqdefault"},{"url":"http://i.ytimg.com/vi/g7zHKcYzjNQ/hqdefault.jpg","height":360,"width":480,"yt$name":"hqdefault"},{"url":"http://i.ytimg.com/vi/g7zHKcYzjNQ/1.jpg","height":90,"width":120,"time":"00:03:04.250","yt$name":"start"},{"url":"http://i.ytimg.com/vi/g7zHKcYzjNQ/2.jpg","height":90,"width":120,"time":"00:06:08.500","yt$name":"middle"},{"url":"http://i.ytimg.com/vi/g7zHKcYzjNQ/3.jpg","height":90,"width":120,"time":"00:09:12.750","yt$name":"end"}],"media$title":{"$t":"Epic Sibling Rivalries with the Gregory Brothers","type":"plain"},"yt$aspectRatio":{"$t":"widescreen"},"yt$duration":{"seconds":"737"},"yt$uploaded":{"$t":"2012-04-13T23:13:44.000Z"},"yt$videoid":{"$t":"g7zHKcYzjNQ"}},"gd$rating":{"average":4.960483,"max":5,"min":1,"numRaters":911,"rel":"http://schemas.google.com/g/2005#overall"},"yt$statistics":{"favoriteCount":"65","viewCount":"2887"},"yt$rating":{"numDislikes":"9","numLikes":"902"}}};
//					handleVideo(a);
				}
			});

			function handleVideo(youtubeApiResponse) {
				var entry = youtubeApiResponse.entry;

				videoLoaded = true;
				videoLoadedUrl = entry.content.src;

				var title = entry.title.$t;
				titleInput.val(title);
				titleInput.removeAttr('disabled')
						.removeClass('disabled');
				enableSubmitBtn();
				bindFocusOutFadingValue(titleInput, title);

				var thumbnails = entry.media$group.media$thumbnail;
				for (var i in thumbnails) {
					if (thumbnails[i].yt$name == youtubeApiThumbnailId) {
						videoPreviewImg.attr("src", thumbnails[i].url)
								.slideDown("fast");
						break;
					}
				}
			}

			function enableSubmitBtn() {
				uploadBtn.removeClass('disabled')
						.click(function () {
							linkInput.val(videoLoadedUrl);
							$(formSelector).submit();
						});
			}

			function disableSubmitBtn() {
				uploadBtn.addClass('disabled')
						.click(function() {});
			}

			function bindFocusInFadingValue(input, defaultValue) {
				input.unbind('focusin')
						.focusin(function () {
					if ($(this).val() == defaultValue) {
						$(this).val('');
					}
				});
			}

			function bindFocusOutFadingValue(input, defaultValue) {
				input.unbind('focusout')
						.focusout(function () {
					if ($(this).val() === '') {
						$(this).val(defaultValue);
					}
				});
			}
		}
	},

	vk: {

	}
	
};
