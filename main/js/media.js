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

	initEditing: {
		video: function(videoId) {
			const speed = 'fast';

			var editLink = $('#video_edit_link');
			var currentText = editLink.html();
			var nextText = 'назад';

			var wrapper = $('.item_title_wrapper');
			wrapper.css({
				'height': wrapper.height()
			});
			var title = $('.item_title');

			var titleInputWrapper = $('<div/>').hide()
					.appendTo(wrapper);

			var titleInput = $('<input>', {
				type:'text',
				class:'item_edit_title_input'
			}).appendTo(titleInputWrapper);

			var saveTitleButton = $('<div/>', {
				text: 'Cохранить',
				class: 'button'
			}).click(saveTitle)
					.appendTo(titleInputWrapper);

			var tagCreator = $('.tag_creator');
			var tagsContainer = $('.tags');

			function saveTitle() {
				var newTitle = titleInput.val();
				if (!newTitle) {
					main.showErrorText('Введите название для видеозаписи!');
				} else {
					$.ajax({
						url: '/procs/proc_media.php',
						data: {
							method: 'set_video_title',
							video_id: videoId,
							title: newTitle
						},
						dataType: 'json',
						beforeSend: function() {
							saveTitleButton.unbind('click')
									.addClass('disabled');
							editLink.unbind('click');
							titleInput.attr('disabled', 'disabled')
						},
						success: function(json) {
							if (!json || !json.status || json.status != 'ok') {
								main.showErrorText(json.message ? json.message : 'Не удалось :(');
								console.debug(json);
							} else {
								title.html(newTitle);
								saveTitleButton.click(saveTitle)
										.removeClass('disabled');
								editLink.click(finishEditing);
								titleInput.removeAttr('disabled');
								alert('Сохранено!\nНажмите кнопку \'' + editLink.text() + '\' внизу, чтобы вернуться на страницу просмотра.')
							}
						},
						error: console.debug
					})
				}
			}

			function slideToggle(toHide, toShow, callback) {
				toHide.slideUp(speed, function() {
					toShow.slideDown(speed, callback);
				});
			}

			function toggleEditLink(newActionFn, callback) {
				editLink.slideUp(speed, function() {
					var a = currentText;
					currentText = nextText;
					nextText = a;

					editLink.html(currentText)
							.unbind('click')
							.click(newActionFn)
							.slideDown(speed, callback);
				});
			}

			function startEditing() {
				titleInput.val(title.text());
				toggleEditLink(finishEditing, function() {
					slideToggle(title, titleInputWrapper, function() {
						slideToggle(tagsContainer, tagCreator);
					});
				});
			}

			function finishEditing() {
				tagsContainer.empty();
				var tags = []
				var tagIds = TagCreator.getTagIds(videoId);
				for (var i in tagIds) {
					var id = tagIds[i];
					tags.push({
						'id':id,
						'value':TagCreator.getTagValue(id)
					});
				}
				content.showTags(tags, '/media/video/tag%d', tagsContainer);

				toggleEditLink(startEditing, function() {
					slideToggle(titleInputWrapper, title, function() {
						tagCreator.slideUp(speed);
						tagsContainer.show();
					});
				});
			}

			$(document).ready(function () {
				editLink.click(startEditing);
			});
		}
	},

	remove: {
		item: function(itemId, confirmText, redirect) {
			if (confirm(confirmText)) {
				$.ajax({
					url: '/procs/proc_media.php',
					data: {
						method: 'remove_item',
						item_id: itemId
					},
					dataType: 'json',

					success: function (json) {
						if (!json || !json.status || json.status != 'ok') {
							main.showErrorText('Не удалось :(');
							console.debug(json);
						} else {
							window.location = redirect;
						}
					},
					error: console.debug
				});
			}
		},

		video: function(videoId) {
			media.remove.item(videoId, 'Вы действительно хотите удалить эту видеозапись?', '/media/video');
		}
	},

	upload: {
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

//						var a = {"version":"1.0","encoding":"UTF-8","entry":{"xmlns":"http://www.w3.org/2005/Atom","xmlns$media":"http://search.yahoo.com/mrss/","xmlns$gd":"http://schemas.google.com/g/2005","xmlns$yt":"http://gdata.youtube.com/schemas/2007","gd$etag":"W/\"A0YCSX47eCp7I2A9WhVXFk0.\"","id":{"$t":"tag:youtube.com,2008:video:g7zHKcYzjNQ"},"published":{"$t":"2012-04-16T10:00:00.000Z"},"updated":{"$t":"2012-04-16T20:46:08.000Z"},"category":[{"scheme":"http://schemas.google.com/g/2005#kind","term":"http://gdata.youtube.com/schemas/2007#video"},{"scheme":"http://gdata.youtube.com/schemas/2007/categories.cat","term":"Comedy","label":"Comedy"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"rhettandlink"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"rhett"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"link"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"Good Mythical Morning"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"The Gregory Brothers"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"schmoyo"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"Bed Intruder"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"Taxi Dave"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"Winning"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"Songify This"},{"scheme":"http://gdata.youtube.com/schemas/2007/keywords.cat","term":"auto tune the news"}],"title":{"$t":"Epic Sibling Rivalries with the Gregory Brothers"},"content":{"type":"application/x-shockwave-flash","src":"https://www.youtube.com/v/g7zHKcYzjNQ?version=3&f=videos&app=youtube_gdata"},"link":[{"rel":"alternate","type":"text/html","href":"https://www.youtube.com/watch?v=g7zHKcYzjNQ&feature=youtube_gdata"},{"rel":"http://gdata.youtube.com/schemas/2007#video.responses","type":"application/atom+xml","href":"https://gdata.youtube.com/feeds/api/videos/g7zHKcYzjNQ/responses?v=2"},{"rel":"http://gdata.youtube.com/schemas/2007#video.related","type":"application/atom+xml","href":"https://gdata.youtube.com/feeds/api/videos/g7zHKcYzjNQ/related?v=2"},{"rel":"http://gdata.youtube.com/schemas/2007#mobile","type":"text/html","href":"https://m.youtube.com/details?v=g7zHKcYzjNQ"},{"rel":"self","type":"application/atom+xml","href":"https://gdata.youtube.com/feeds/api/videos/g7zHKcYzjNQ?v=2"}],"author":[{"name":{"$t":"rhettandlink2"},"uri":{"$t":"https://gdata.youtube.com/feeds/api/users/rhettandlink2"},"yt$userId":{"$t":"4PooiX37Pld1T8J5SYT-SQ"}}],"yt$accessControl":[{"action":"comment","permission":"allowed"},{"action":"commentVote","permission":"allowed"},{"action":"videoRespond","permission":"moderated"},{"action":"rate","permission":"allowed"},{"action":"embed","permission":"allowed"},{"action":"list","permission":"allowed"},{"action":"autoPlay","permission":"allowed"},{"action":"syndicate","permission":"allowed"}],"gd$comments":{"gd$feedLink":{"rel":"http://gdata.youtube.com/schemas/2007#comments","href":"https://gdata.youtube.com/feeds/api/videos/g7zHKcYzjNQ/comments?v=2","countHint":379}},"yt$hd":{},"media$group":{"media$category":[{"$t":"Comedy","label":"Comedy","scheme":"http://gdata.youtube.com/schemas/2007/categories.cat"}],"media$content":[{"url":"https://www.youtube.com/v/g7zHKcYzjNQ?version=3&f=videos&app=youtube_gdata","type":"application/x-shockwave-flash","medium":"video","isDefault":"true","expression":"full","duration":737,"yt$format":5},{"url":"rtsp://v4.cache1.c.youtube.com/CiILENy73wIaGQnUjDPGKce8gxMYDSANFEgGUgZ2aWRlb3MM/0/0/0/video.3gp","type":"video/3gpp","medium":"video","expression":"full","duration":737,"yt$format":1},{"url":"rtsp://v1.cache4.c.youtube.com/CiILENy73wIaGQnUjDPGKce8gxMYESARFEgGUgZ2aWRlb3MM/0/0/0/video.3gp","type":"video/3gpp","medium":"video","expression":"full","duration":737,"yt$format":6}],"media$credit":[{"$t":"rhettandlink2","role":"uploader","scheme":"urn:youtube","yt$display":"rhettandlink2","yt$type":"partner"}],"media$description":{"$t":"Special Guests: The Gregory Brothers. Good Mythical Morning Episode 70 Comment below: Share you sibling rivalry stories \r\nWatch the full length performance of The Gregory Brothers \"24/7\" on the Kommunity: http://rhettandlinkommunity.com/profiles/blogs/gregorybrosperform\r\n\r\n**** SUBSCRIBE for daily episodes: http://bit.ly/subrl2 ****\r\n\r\nThis episode of GMM is brought to you by Smule! \r\nhttp://bit.ly/SmuleWebsite \r\n\r\nGMM Jingle/ Smule Songify 2.0 Contest Instructions:\r\n1.  download Songify 2.0 (it's free!): http://itunes.apple.com/us/app/songify/id438735719\r\n2.  download tracks (earn free coins within the app)\r\n3.  write a few lines about GMM\r\n4.  record a few different versions of you TALKING (not singing) the lines\r\n5.  name each one \"GMM Jingle ____\"\r\n6.  email your favorite to us by click SHARE then email: show@rhettandlink.com\r\n* DEADLINE * :  Sunday Night 4/22/12\r\n\r\nThanks to the Gregory Brothers for stopping by! Be sure to subscribe to their channel! \r\nhttp://www.youtube.com/schmoyoho\r\n\r\nWatch the Gregory Brothers Exclusive Performance by  joining the RhettandLinKommunity!\r\nhttp://bit.ly/rlkommunity\r\n\r\nFor information about sponsoring an episode of GMM, emails us at show@rhettandlink.com\r\n\r\nMAIN YOUTUBE CHANNEL: http://youtube.com/rhettandlink\r\n\r\nFACEBOOK: \u202ahttp://bit.ly/rhettandlinkfb\u202c\r\n\r\nTWITTER: \u202ahttp://bit.ly/rltwitter\u202c\r\n\r\nSend us stuff at our P.O. Box\r\nRhett & Link\r\nPO Box 55605, Sherman Oaks, CA 91413\r\n\r\nGood Mythical Morning is available for download on iTunes!\r\nVideo Podcast: http://bit.ly/xuJVPc\r\nAudio Podcast: http://bit.ly/zSewZ6\r\n\r\nJOIN the RhettandLinKommunity!\r\nhttp://bit.ly/rlkommunity\r\n\r\nCREDITS: \r\nCamera, PA, Editing:  Jason Inman\r\nIntro/Outro music: RoyaltyFreeMusicLibrary.com\u2028\u202a\u202ahttp://www.royaltyfreemusiclibrary.com/\u202c\u202c \r\nMicrophone: The Mouse from Blue Microphones: http://www.bluemic.com/mouse/\r\n\r\n------------------\r\nSubscribe if you like what you see!","type":"plain"},"media$keywords":{"$t":"rhettandlink, rhett, link, Good Mythical Morning, The Gregory Brothers, schmoyo, Bed Intruder, Taxi Dave, Winning, Songify This, auto tune the news"},"media$license":{"$t":"youtube","type":"text/html","href":"http://www.youtube.com/t/terms"},"media$player":{"url":"https://www.youtube.com/watch?v=g7zHKcYzjNQ&feature=youtube_gdata_player"},"media$thumbnail":[{"url":"http://i.ytimg.com/vi/g7zHKcYzjNQ/default.jpg","height":90,"width":120,"time":"00:06:08.500","yt$name":"default"},{"url":"http://i.ytimg.com/vi/g7zHKcYzjNQ/mqdefault.jpg","height":180,"width":320,"yt$name":"mqdefault"},{"url":"http://i.ytimg.com/vi/g7zHKcYzjNQ/hqdefault.jpg","height":360,"width":480,"yt$name":"hqdefault"},{"url":"http://i.ytimg.com/vi/g7zHKcYzjNQ/1.jpg","height":90,"width":120,"time":"00:03:04.250","yt$name":"start"},{"url":"http://i.ytimg.com/vi/g7zHKcYzjNQ/2.jpg","height":90,"width":120,"time":"00:06:08.500","yt$name":"middle"},{"url":"http://i.ytimg.com/vi/g7zHKcYzjNQ/3.jpg","height":90,"width":120,"time":"00:09:12.750","yt$name":"end"}],"media$title":{"$t":"Epic Sibling Rivalries with the Gregory Brothers","type":"plain"},"yt$aspectRatio":{"$t":"widescreen"},"yt$duration":{"seconds":"737"},"yt$uploaded":{"$t":"2012-04-13T23:13:44.000Z"},"yt$videoid":{"$t":"g7zHKcYzjNQ"}},"gd$rating":{"average":4.960483,"max":5,"min":1,"numRaters":911,"rel":"http://schemas.google.com/g/2005#overall"},"yt$statistics":{"favoriteCount":"65","viewCount":"2887"},"yt$rating":{"numDislikes":"9","numLikes":"902"}}};
//						handleVideo(a);
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
			photos: {
				init: function(accessToken, vkAuthPopupOptions) {
					function sendApiRequest(method, callback, data) {
						var data1 = data;
						$.ajax({
							url:'https://api.vk.com/method/' + method,
							data:$.extend(data1, {
								access_token: accessToken,
								callback: callback
							}),
							dataType:'jsonp',
							beforeSend: function() {
								vkPhotosEnabled = false;
							}
						});
					}

					var vkPhotosEnabled = true;

					const availablePhotosTitle = $('#vk_available_photos_title');
					const selectedPhotosTitle = $('#vk_selected_photos_title').hide().html('Выбранные фотографии');
					const availablePhotosList = $('#vk_available_photos');
					const selectedPhotosList = $('#vk_selected_photos');

					const options = $('#vk_photos_options');
					const groupSelector = $('#vk_photos_group select');
					const tagCreator = $('#vk_photos_tag_creator');

					const actionBtnBack = $('#vk_photos_controls_back');
					const actionBtnAll = $('#vk_photos_controls_all');
					const actionBtnUpload = $('#vk_photos_controls_upload');

					/**
					 * photoId -> {Object} details
					 * @type map
					 */
					var selectedPhotos = {};
					var availablePhotosCount = 0;
					var selectedPhotosCount = 0;
					var currentAid = 0;

					var albumsData;

					const animationSpeed = "fast";

					const focusOutImageOpacity = 0.9;

					const focusOutTitleBgPadding = '4px'
					const focusInTitleBgPadding = '10px';
					const focusOutTitleBgOpacity = 0.6
					const focusInTitleBgOpacity = 0.8;

					const focusOutTitleBgCss = {
						'padding-top': focusOutTitleBgPadding,
						'padding-bottom': focusOutTitleBgPadding,
						'opacity': focusOutTitleBgOpacity
					};

					const focusInTitleBgCss = {
						'padding-top': focusInTitleBgPadding,
						'padding-bottom': focusInTitleBgPadding,
						'opacity': focusInTitleBgOpacity
					};

					const focusOutTitleCss = {
						'height': '14px'
					};

					function buildAlbum(data) {
						/*
						 aid: "154808970"
						 created: "1332285464"
						 description: "часть фоток у нас с бобом одинаковые, а остальные – не совсем. http://vk.com/album312666_153850082"
						 owner_id: "355679"
						 privacy: 0
						 size: 31
						 thumb_id: "280431237"
						 thumb_src: "http://cs301301.userapi.com/u355679/154808970/m_86454a14.jpg"
						 title: "Барса"
						 updated: "1332511095"
						 */

						var container = $('<div/>',{
							title: data.description
						})
								.addClass('vk_album')
								.addClass('vk_media_item')
								.appendTo(availablePhotosList);

						$('<img/>', {src:data.thumb_src}).appendTo(container);

						var titleBg = $('<div/>')
								.css(focusOutTitleBgCss)
								.addClass('vk_album_title_bg')
								.appendTo(container);

						var title = $('<div/>', {
							text:data.title
						})
								.addClass('vk_album_title')
								.appendTo(titleBg);

						var focusInTitleCss = {
							'height': Math.min(title.height(), container.height())
						};

						title.css(focusOutTitleCss);

						container.hover(function () {
							titleBg.animate(focusInTitleBgCss, animationSpeed);
							title.animate(focusInTitleCss, animationSpeed);
						}, function () {
							titleBg.animate(focusOutTitleBgCss, animationSpeed);
							title.animate(focusOutTitleCss, animationSpeed);
						});

						bindHover(container);

						container.click(function() {
							if (vkPhotosEnabled) {
								currentAid = data.aid;
								sendApiRequest("photos.get", 'showPhotos', {
									uid: data.owner_id,
									aid: data.aid
								})
//								showPhotos({"response":[{"pid":280307174,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_3fc3979e.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_7d73584d.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_2bcd9e3f.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_d29aaf43.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_2561fda1.jpg","width":1280,"height":960,"text":"","created":1332285986},{"pid":280307177,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_695fcc0e.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_d2854859.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_d6fbd1c7.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_a4281d7e.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_dd6672cc.jpg","width":1280,"height":890,"text":"","created":1332286002},{"pid":280307180,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_0bfe84f3.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_e58b5ae6.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_6c436c85.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_f7ced2fb.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_b0aba393.jpg","width":1280,"height":960,"text":"","created":1332286011},{"pid":280307181,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_ee871f87.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_6b687e17.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_637442cf.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_0da5c43a.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_10798667.jpg","width":1280,"height":960,"text":"","created":1332286021},{"pid":280307182,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_c332ee57.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_81b01d48.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_00522950.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_183eb1a7.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_1db1fd6a.jpg","width":768,"height":1024,"text":"","created":1332286026},{"pid":280307183,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_d3502078.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_33d1cb22.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_48c065d1.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_5b4bd8e1.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_ffec0832.jpg","width":1280,"height":960,"text":"","created":1332286035},{"pid":280307185,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_6c35cfda.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_61af0525.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_d5adb9d4.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_fbcf18e9.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_5e754ed6.jpg","width":1280,"height":995,"text":"","created":1332286045},{"pid":280307188,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_36cbd169.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_3e72f509.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_d3bc7d46.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_bad929ae.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_d06c8975.jpg","width":1280,"height":936,"text":"","created":1332286058},{"pid":280307190,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_ebdf5453.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_7f5e2305.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_fc2fdee0.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_8cc5fd70.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_057bc68d.jpg","width":1280,"height":960,"text":"","created":1332286072},{"pid":280307194,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_8de9f6ba.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_b9038798.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_625bf68b.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_8fc676be.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_16853233.jpg","width":1280,"height":960,"text":"","created":1332286086},{"pid":280307197,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_2b8ae660.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_61efef4a.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_14349045.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_72e56b29.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_5fcafa12.jpg","width":1280,"height":960,"text":"","created":1332286098},{"pid":280307200,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_dc4a8911.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_fc21ce84.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_05d292b9.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_e4bf9376.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_d129a640.jpg","width":1280,"height":960,"text":"","created":1332286110},{"pid":280307203,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_86504086.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_c2ac87e3.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_ad757d5e.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_82a8abdd.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_295e6527.jpg","width":1280,"height":944,"text":"","created":1332286119},{"pid":280307204,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_9bc30b89.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_22d8f519.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_9c033038.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_b763b210.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_355e38d3.jpg","width":1280,"height":960,"text":"","created":1332286130},{"pid":280307207,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_8adddbdf.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_999b46d3.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_62a45a74.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_dd32a36c.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_1503679e.jpg","width":1280,"height":960,"text":"","created":1332286146},{"pid":280307209,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_80d26a86.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_1e832b8c.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_5d1bfdb5.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_2cda57b7.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_ded3cc1a.jpg","width":1280,"height":909,"text":"","created":1332286155},{"pid":280307210,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_80dc7740.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_adfcaa39.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_31c57e81.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_9e738dd5.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_5040c622.jpg","width":790,"height":1024,"text":"","created":1332286165},{"pid":280307211,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_a204e805.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_1ba516ab.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_ae38075b.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_c1d6ba73.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_caa2b856.jpg","width":768,"height":1024,"text":"","created":1332286171},{"pid":280307213,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_fa3b42c3.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_628d5017.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_b2fbf26a.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_ee501658.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_3578e32d.jpg","width":1280,"height":960,"text":"","created":1332286184},{"pid":280307214,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_688b58f1.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_a49d7ff3.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_d31a740c.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_78136185.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_d41810d3.jpg","width":1280,"height":960,"text":"","created":1332286192},{"pid":280307216,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_bba5d818.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_dec464b1.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_b1df006d.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_74a41bc3.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_939a6f80.jpg","width":768,"height":1024,"text":"Gerardo,  мы там с ним угорали","created":1332286198},{"pid":280307221,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/m_d56bd5fb.jpg","src_big":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/x_89df4aa9.jpg","src_small":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/s_8cc7ce70.jpg","src_xbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/y_e989095a.jpg","src_xxbig":"http:\/\/cs5171.userapi.com\/u355679\/154808970\/z_06ae51db.jpg","width":1280,"height":871,"text":"","created":1332286220},{"pid":280408383,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/m_2ecbf9ea.jpg","src_big":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/x_19ff3b05.jpg","src_small":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/s_78ecf1ee.jpg","src_xbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/y_785db038.jpg","src_xxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/z_5a776992.jpg","src_xxxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/w_1749eedf.jpg","width":2560,"height":1873,"text":"","created":1332453742},{"pid":280408392,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/m_c900c995.jpg","src_big":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/x_f33a3088.jpg","src_small":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/s_3b86b35f.jpg","src_xbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/y_6cf4497c.jpg","src_xxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/z_2e03bf90.jpg","src_xxxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/w_41d8f588.jpg","width":2560,"height":1857,"text":"","created":1332453777},{"pid":280408409,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/m_8981b076.jpg","src_big":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/x_61c74d7e.jpg","src_small":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/s_4611ceaf.jpg","src_xbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/y_266494e3.jpg","src_xxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/z_6f5f968c.jpg","src_xxxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/w_51ae395b.jpg","width":2560,"height":1920,"text":"","created":1332453833},{"pid":280408418,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/m_7c2bfa4a.jpg","src_big":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/x_0956c333.jpg","src_small":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/s_902cd65a.jpg","src_xbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/y_acfba559.jpg","src_xxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/z_9b4558c7.jpg","src_xxxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/w_980c73f5.jpg","width":1551,"height":2048,"text":"","created":1332453882},{"pid":280427490,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/m_f9309746.jpg","src_big":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/x_9cd9aa49.jpg","src_small":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/s_f264f749.jpg","src_xbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/y_edd16f88.jpg","src_xxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/z_52faa9e1.jpg","src_xxxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/w_57f7b20a.jpg","width":2560,"height":1835,"text":"","created":1332506294},{"pid":280427493,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/m_57a19a4a.jpg","src_big":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/x_f03d48e6.jpg","src_small":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/s_48ea6e17.jpg","src_xbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/y_c80714b5.jpg","src_xxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/z_6832afc6.jpg","src_xxxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/w_b7f8fc21.jpg","width":2560,"height":1920,"text":"","created":1332506297},{"pid":280427495,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/m_a0d80571.jpg","src_big":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/x_b2908c77.jpg","src_small":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/s_a46cbdab.jpg","src_xbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/y_f940eea4.jpg","src_xxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/z_39610912.jpg","src_xxxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/w_7e0be6f5.jpg","width":2560,"height":1781,"text":"","created":1332506299},{"pid":280431235,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/m_269d85d9.jpg","src_big":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/x_ad1e4cd0.jpg","src_small":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/s_282706fd.jpg","src_xbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/y_5065c368.jpg","src_xxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/z_976b5e29.jpg","src_xxxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/w_a6469871.jpg","width":2560,"height":1920,"text":"","created":1332511092},{"pid":280431237,"aid":154808970,"owner_id":355679,"src":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/m_86454a14.jpg","src_big":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/x_d517121d.jpg","src_small":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/s_58ca6bbc.jpg","src_xbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/y_b77022ca.jpg","src_xxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/z_bbb361ba.jpg","src_xxxbig":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/w_9d7ad5ea.jpg","width":2560,"height":1920,"text":"","created":1332511095}]});
							}
						});
					}

					function bindHover(elem) {
						return elem.css({
							'opacity':focusOutImageOpacity
						}).hover(function () {
									$(this).fadeTo(animationSpeed, 1);
								}, function () {
									$(this).fadeTo(animationSpeed, focusOutImageOpacity);
								});
					}

					function buildPhoto(data) {
						/*
						 aid: 154808970
						 created: 1332285986
						 height: 960
						 owner_id: 355679
						 pid: 280307174
						 src: "http://cs5171.userapi.com/u355679/154808970/m_3fc3979e.jpg"
						 src_big: "http://cs5171.userapi.com/u355679/154808970/x_7d73584d.jpg"
						 src_small: "http://cs5171.userapi.com/u355679/154808970/s_2bcd9e3f.jpg"
						 src_xbig: "http://cs5171.userapi.com/u355679/154808970/y_d29aaf43.jpg"
						 src_xxbig: "http://cs5171.userapi.com/u355679/154808970/z_2561fda1.jpg"
						 text: ""
						 width: 1280
						 */

						if (selectedPhotos[data.aid] && selectedPhotos[data.aid][data.pid]) {
							availablePhotosCount --;
							return;
						}

						var container = $('<div/>', {
							title: data.text
						})
								.addClass('vk_photo')
								.addClass('vk_media_item')
								.appendTo(availablePhotosList);

						bindHover(container);

						var containerWidth = container.width();
						var containerHeight = container.height();
						var imgWidth = data.width;
						var imgHeight = data.height;

						var properties = {src: imgWidth > imgHeight ? data.src : data.src_big};

						if (imgWidth / imgHeight >= containerWidth / containerHeight) {
							properties.height = containerHeight;
						} else {
							properties.width = containerWidth;
						}

						$('<img/>', properties).appendTo(container);

						bindPhotoClick(container, data)
					}

					function bindPhotoClick(container, data) {
						container.click(function () {
							if (vkPhotosEnabled) {
								var copy = container.clone()
										.hide()
										.appendTo(selectedPhotosList)
										.click(function() {
											delete selectedPhotos[data.aid][data.pid];
											selectedPhotosCount --;
											if (data.aid == currentAid) {
												availablePhotosCount ++;
												moveLeftAndResize(copy, container);
											} else {
												moveLeftAndResize(copy);
											}
										});

								availablePhotosCount --;
								selectedPhotosCount ++;
								if (!selectedPhotos[data.aid])
									selectedPhotos[data.aid] = {};
								selectedPhotos[data.aid][data.pid] = data;
								moveRightAndResize(container, copy);
							}
						});
					}

					function moveRightAndResize(photo1, photo2) {
						resizePhotosList(50, selectedPhotosList, availablePhotosList, function() {
							selectedPhotosTitle.show();
							togglePhoto(photo1, photo2, function() {
								enableActionUpload();
								showUploadOptions(function() {
									if (availablePhotosCount == 0) {
										availablePhotosTitle.hide();
										disableActionAll();
										resizePhotosList(0, availablePhotosList, selectedPhotosList);
									}
								});
							});
						});
					}

					function moveLeftAndResize(photo1, photo2) {
						resizePhotosList(50, availablePhotosList, selectedPhotosList, function() {
							availablePhotosTitle.show();
							enableActionAll();
							togglePhoto(photo1, photo2, function() {
								if (selectedPhotosCount == 0) {
									selectedPhotosTitle.hide();
									disableActionUpload();
									resizePhotosList(0, selectedPhotosList, availablePhotosList, hideUploadOptions);
								}
								photo1.remove();
							});
						});
					}

					function resizePhotosList(percentage, list, otherList, callback) {
						otherList.animate({width:(100 - percentage) + '%'}, animationSpeed);
						list.animate({width:percentage + '%'}, animationSpeed, null, callback);
					}

					function togglePhoto(photo1, photo2, callback) {
						photo1.unbind('mouseenter mouseleave')
								.fadeOut(animationSpeed, function () {
									photo2 && photo2.fadeTo(animationSpeed, focusOutImageOpacity, function(){
										bindHover(photo2);
										callback && callback();
									});

								});
					}

					function showUploadOptions(callback) {
						options.slideDown(animationSpeed, callback);
					}

					function hideUploadOptions() {
						options.slideUp(animationSpeed);
					}

					function enableActionAll() {
						enableAction(actionBtnAll, actionAll);
					}

					function disableActionAll() {
						disableAction(actionBtnAll);
					}

					function enableActionUpload() {
						enableAction(actionBtnUpload, actionUpload);
					}

					function disableActionUpload() {
						disableAction(actionBtnUpload);
					}

					function disableActionBack() {
						disableAction(actionBtnBack);
					}

					function enableActionBack() {
						enableAction(actionBtnBack, actionBack);
					}

					function enableAction(btn, fn) {
						btn.removeClass('disabled')
								.unbind('click')
								.click(function() {
									vkPhotosEnabled && fn && fn();
								});
					}

					function disableAction(btn) {
						btn.addClass('disabled')
								.unbind('click');
					}

					function actionAll() {
						availablePhotosList.children().each(function () {
							if ($(this).css('display') != 'none')
								$(this).click()
						});
					}

					function actionBack(){
						disableAction(actionBtnBack);
						disableActionAll();
						resizePhotosList(selectedPhotosCount > 0 ? 50 : 100, availablePhotosList, selectedPhotosList, function() {
							showAlbums(albumsData);
						});
					}

					function actionUpload() {
						var photos = [];
						for (var i in selectedPhotos) {
							for (var j in selectedPhotos[i]) {
								var data = selectedPhotos[i][j];
								if (!data.uploaded) {
									photos.push({
										micro: data.src_small,
										mini: data.src,
										middle: data.src_big,
										hq: data.src_xxbig,
										description: data.text
									});
								}
							}
						}

						$.ajax({
							url: '/procs/proc_media_uploader.php',
							type: "POST",
							data: {
								method: 'vk_photos',
								photos: JSON.stringify(photos),
								tags: JSON.stringify(TagCreator.getTagIds()),
								group_id: groupSelector.val()
							},
							dataType: 'json',
							beforeSend: function() {
								vkPhotosEnabled = false;
							},
							success: function(data) {
								if (data && data.status == 'ok') {
									main.showNotification('<p>Загрузка фотографий прошла успешно!</p>' +
											'<p>Вы можете <a href="' + data.redirect + '">перейти</a> к их просмотру.</p>');

									for (var i in selectedPhotos) {
										for (var j in selectedPhotos[i]) {
											selectedPhotos[i][j].uploaded = true;
										}
									}

									selectedPhotosCount = 0;
									disableActionUpload();
									selectedPhotosList.empty();
									selectedPhotosTitle.hide();
									availablePhotosTitle.show();
									if (availablePhotosCount > 0) {
										resizePhotosList(0, selectedPhotosList, availablePhotosList, hideUploadOptions);
									} else {
										actionBack();
									}
								} else {
									main.showErrorText(data && data.message ? data.message : 'Загрузка не удалась! Попробуйте ещё раз!');
									console.debug(data);
								}
								vkPhotosEnabled = true;
							},
							error: function() {
								main.showErrorText('Загрузка не удалась! Попробуйте ещё раз!');
								console.debug(data);
								vkPhotosEnabled = true;
							}
						});
					}

					function show(data, fn, title) {
						availablePhotosList.fadeOut(animationSpeed, function() {
							$(this).children().each(function() {
								$(this).unbind('mouseenter mouseleave')
										.hide();
							});

							$(this).show();

							var response = data.response;
							for (var i in response) {
								fn(response[i]);
							}
							availablePhotosTitle.html(title);
							vkPhotosEnabled = true;
						});
					}

					window.groupAlbums = [];

					window.showAlbums = function(data) {
						if (data.error) {
							main.showNotification('Не удалось загрузить альбомы!');
							vkPhotosEnabled = false;
							var popup = window.open(vkAuthPopupOptions.url, vkAuthPopupOptions.windowName, vkAuthPopupOptions.windowFeatures);

							function checkPopup() {
								if(popup.closed) {
									window.location.reload();
								} else {
									setTimeout(checkPopup, 300);
								}
							}

							checkPopup();
							return;
						}
						for (var i = 0; i < window.groupAlbums.length; ++i) {
							data.response.push(window.groupAlbums[i]);
						}
						show(data, buildAlbum, 'Выберите альбом');
						albumsData = data;
						currentAid = 0;
					};

					window.showPhotos = function(data) {
						show(data, buildPhoto, 'Выберите фотографии');

						enableActionBack();
						availablePhotosCount = data.response.length;
						if (availablePhotosCount > 0) {
							enableActionAll();
						} else {
							availablePhotosList.html('Так вы ж уже загрузили все фотки из этого альбома!');
						}
					};

					var pipeinpipeGroupId = 2075695;

					window.saveAlbums = function (data) {
						window.groupAlbums = data.response;
						sendApiRequest('photos.getAlbums', 'showAlbums', {need_covers:1});
					};

					window.checkMember = function (data) {
						if (data.response == '1') {
							sendApiRequest('photos.getAlbums', 'saveAlbums', {need_covers:1, gid:pipeinpipeGroupId});
						} else {
							sendApiRequest('photos.getAlbums', 'showAlbums', {need_covers:1});
						}
					};

					$(document).ready(function(){
						sendApiRequest('groups.isMember', 'checkMember', {gid: pipeinpipeGroupId});
//						showAlbums({"response":[{"aid":"154808970","thumb_id":"280431237","owner_id":"355679","title":"Барса","description":"часть фоток у нас с бобом одинаковые, а остальные – не совсем. http:\/\/vk.com\/album312666_153850082","created":"1332285464","updated":"1332511095","size":31,"privacy":0,"thumb_src":"http:\/\/cs301301.userapi.com\/u355679\/154808970\/m_86454a14.jpg"},{"aid":"122568522","thumb_id":"163723944","owner_id":"355679","title":"случайные фотки","description":"листики, там, всякие.. натюрморты..","created":"1290898589","updated":"1320425798","size":41,"privacy":0,"thumb_src":"http:\/\/cs517.userapi.com\/u355679\/2443014\/m_8d275227.jpg"},{"aid":"116992054","thumb_id":"179817101","owner_id":"355679","title":"Sverige","description":"Stockholm + a bit of Helsinki\n\nкто хотел найти в этом альбоме просто виды Стокгольма, тому сюда: http:\/\/images.google.com\/images?q=stockholm","created":"1283890658","updated":"1300549452","size":69,"privacy":0,"thumb_src":"http:\/\/cs226.userapi.com\/u355679\/116992054\/m_245ac857.jpg"},{"aid":"28966094","thumb_id":"155546412","owner_id":"355679","title":"Питер","description":"в хорошем качестве\nздесь:\nhttp:\/\/s613.photobucket.com\/albums\/tt218\/kex_guru\/city\/\n\nи здесь:\n(в нескольких постах)\nhttp:\/\/shuvalovip.livejournal.com\/","created":"1211989252","updated":"1308659078","size":94,"privacy":0,"thumb_src":"http:\/\/cs224.userapi.com\/u355679\/28966094\/m_9ab93801.jpg"},{"aid":"6152017","thumb_id":"161257980","owner_id":"355679","title":"Комарово","description":"в хорошем качестве\nтут:\nhttp:\/\/s613.photobucket.com\/albums\/tt218\/kex_guru\/komarovo\/\n\nили тут:\nhttp:\/\/shuvalovip.livejournal.com\/1790.html\nhttp:\/\/shuvalovip.livejournal.com\/1920.html","created":"1194903958","updated":"1309791888","size":76,"privacy":0,"thumb_src":"http:\/\/cs9980.userapi.com\/u355679\/6152017\/m_d05562d6.jpg"},{"aid":"139170314","thumb_id":"275461242","owner_id":"355679","title":"in Berlin","description":"","created":"1310997101","updated":"1326304723","size":43,"privacy":0,"thumb_src":"http:\/\/cs5999.userapi.com\/u355679\/139170314\/m_3f1231c5.jpg"},{"aid":"95254084","thumb_id":"167517172","owner_id":"355679","title":"Нюмтхайон","description":"в хорошем качестве тут:\nhttp:\/\/shuvalovip.livejournal.com\/2979.html\n\nили тут:\nhttp:\/\/s613.photobucket.com\/albums\/tt218\/kex_guru\/numthaion\/","created":"1250240725","updated":"1287599377","size":94,"privacy":0,"thumb_src":"http:\/\/cs10188.userapi.com\/u355679\/95254084\/m_71e29ba6.jpg"},{"aid":"2443014","thumb_id":"206975936","owner_id":"355679","title":"просто","description":"хронологический порядок полностью отсутствует.\n\nсамые, на мой взгляд, хорошие есть в нормальном качестве\nтут портреты: \nhttp:\/\/s613.photobucket.com\/albums\/tt218\/kex_guru\/portraits\/\n\nтут просто всякое макро-и-не-только:\nhttp:\/\/s613.photobucket.com\/albums\/tt218\/kex_guru\/\n\nи тут всякие там листики:\nhttp:\/\/s613.photobucket.com\/albums\/tt218\/kex_guru\/autumn\/","created":"1188323843","updated":"1318789562","size":483,"privacy":0,"thumb_src":"http:\/\/cs10011.userapi.com\/u355679\/2443014\/m_1bcfe232.jpg"},{"aid":"154513363","thumb_id":"281444118","owner_id":"355679","title":"hipster-like","description":"","created":"1331753432","updated":"1334343174","size":7,"privacy":0,"thumb_src":"http:\/\/cs11376.userapi.com\/u355679\/154513363\/m_f73825c9.jpg"},{"aid":"142819209","thumb_id":"267697035","owner_id":"355679","title":"Вот и свадебку сыграли","description":"http:\/\/vkontakte.ru\/video397095_160883034","created":"1315858902","updated":"1320783281","size":121,"privacy":0,"thumb_src":"http:\/\/cs10096.userapi.com\/u355679\/142819209\/m_7728731c.jpg"},{"aid":"133980710","thumb_id":"258670967","owner_id":"355679","title":"у Даши","description":"новоселье + Радин первый др","created":"1304371168","updated":"1312559032","size":18,"privacy":0,"thumb_src":"http:\/\/cs5587.userapi.com\/u355679\/133980710\/m_aa3ddb7e.jpg"},{"aid":"144870026","thumb_id":"269190299","owner_id":"355679","title":"concert","description":"","created":"1318429912","updated":"1318430963","size":11,"privacy":0,"thumb_src":"http:\/\/cs10096.userapi.com\/u355679\/144870026\/m_a90b4a41.jpg"},{"aid":"80327597","thumb_id":"123978973","owner_id":"355679","title":"Abaza parties","description":"22.02.2009\n20.02.2011\n\nв хорошем качестве\nтут:\nhttp:\/\/s613.photobucket.com\/albums\/tt218\/kex_guru\/\n\nили тут:\nhttp:\/\/shuvalovip.livejournal.com\/717.html","created":"1235582930","updated":"1304241627","size":25,"privacy":0,"thumb_src":"http:\/\/cs4152.userapi.com\/u355679\/80329275\/m_5b14efaa.jpg"},{"aid":"96518249","thumb_id":"138642133","owner_id":"355679","title":"Самая организованная нимфейская встреча","description":"когда я отправлял фотки масс-аплоадером, то он мне показал, что в контакте они весят 666 кб!!!","created":"1253739849","updated":"1253740524","size":16,"privacy":0,"thumb_src":"http:\/\/cs1575.userapi.com\/u355679\/96518249\/m_2ff1485b.jpg"},{"aid":"14881506","thumb_id":"94226540","owner_id":"355679","title":"удолбки","description":"нарыл в компе.\nне помню когда это снималось.\nи не мной.","created":"1203104493","updated":"1203104598","size":13,"privacy":1,"thumb_src":"http:\/\/cs159.userapi.com\/u355679\/14881506\/m_9b96406f.jpg"},{"aid":"86142598","thumb_id":"125094086","owner_id":"355679","title":"пайп и жизнь вокруг него","description":"pipeinpipe.info","created":"1237213751","updated":"1310078566","size":76,"privacy":1,"thumb_src":"http:\/\/cs508.userapi.com\/u355679\/86142598\/m_190dc21d.jpg"},{"aid":"64760885","thumb_id":"125490989","owner_id":"355679","title":"Сахнен Open Air","description":"","created":"1230490388","updated":"1246603591","size":22,"privacy":1,"thumb_src":"http:\/\/cs508.userapi.com\/u355679\/64760885\/m_4f428b2f.jpg"},{"aid":"81835077","thumb_id":"124263448","owner_id":"355679","title":"Сахнен пати","description":"одну из партий шопофотить было лень... может потом...\nя только \"автоматический контраст\" сделал и всё...","created":"1236015332","updated":"1267261281","size":42,"privacy":1,"thumb_src":"http:\/\/cs4152.userapi.com\/u355679\/81835077\/m_356fd1ab.jpg"},{"aid":"91917319","thumb_id":"126458812","owner_id":"355679","title":"Толстой пати","description":"","created":"1239030874","updated":"1239292669","size":18,"privacy":1,"thumb_src":"http:\/\/cs584.userapi.com\/u355679\/91917319\/m_96388df2.jpg"},{"aid":"63906536","thumb_id":"120449946","owner_id":"355679","title":"ну ни фига же не умеет!..","description":"у Шигарова\n21.12.08","created":"1230144309","updated":"1230145447","size":18,"privacy":1,"thumb_src":"http:\/\/cs222.userapi.com\/u355679\/63906536\/m_84dc59b8.jpg"},{"aid":"92360627","thumb_id":"127469423","owner_id":"355679","title":"Лёха news","description":"","created":"1240550842","updated":"1263479127","size":18,"privacy":1,"thumb_src":"http:\/\/cs501.userapi.com\/u355679\/92360627\/m_baed6116.jpg"},{"aid":"84729769","thumb_id":"128854071","owner_id":"355679","title":"Мой универчик","description":"(с) Пит","created":"1236791286","updated":"1299606390","size":86,"privacy":1,"thumb_src":"http:\/\/cs4230.userapi.com\/u355679\/84729769\/m_c82493e3.jpg"},{"aid":"92491255","thumb_id":"127795395","owner_id":"355679","title":"Этнофест","description":"","created":"1240991566","updated":"1240992086","size":24,"privacy":1,"thumb_src":"http:\/\/cs855.userapi.com\/u355679\/92491255\/m_7e0b8c87.jpg"},{"aid":"6779939","thumb_id":"81314269","owner_id":"355679","title":"they're coming","description":"нашествие чумных зомбанов","created":"1195676416","updated":"1195851760","size":48,"privacy":1,"thumb_src":"http:\/\/cs53.userapi.com\/u355679\/6779939\/m_20bb9210.jpg"},{"aid":"14885713","thumb_id":"94221522","owner_id":"355679","title":"школа. тяжесть трудовых будней","description":"некоторые портреты, на мой взгляд, особенно удались\n","created":"1203106114","updated":"1234792467","size":37,"privacy":1,"thumb_src":"http:\/\/cs159.userapi.com\/u355679\/14876511\/m_e3c37606.jpg"},{"aid":"84790778","thumb_id":"146981708","owner_id":"355679","title":"fun'n'order","description":"","created":"1236800276","updated":"1320915481","size":147,"privacy":0,"thumb_src":"http:\/\/cs4737.userapi.com\/u355679\/84790778\/m_eed78b84.jpg"},{"aid":"121801819","thumb_id":"241053322","owner_id":"355679","title":"Поука","description":"адские бед-биты и просто прикольные моменты","created":"1289853910","updated":"1301348260","size":31,"privacy":1,"thumb_src":"http:\/\/cs11002.userapi.com\/u355679\/121801819\/m_c0cc192a.jpg"}]});
					});
				}
			},

			video: {

			}
		}
	}
};
