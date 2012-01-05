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
	}
	
};
