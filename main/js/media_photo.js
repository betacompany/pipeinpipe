
var photo = {

	_prevImg: {},
	_mainImg: {},
	_nextImg: {},

	_photos: [],
	_index: -1,

	init: function (photos, index) {
		if (index === undefined) {
			index = 0;
		}

		this._photos = photos;
		this._index = index;

		var current = photos[index];
		this._mainImg = $("<img src=\""+current.main_url+"\"/>");
		$('#main_photo').append(this._mainImg);

		if (photos.length > index + 1) {
			var next = photos[index + 1];
			this._nextImg = $("<img src=\""+next.main_url+"\"/>");
			$('#next_photo > div').append(this._nextImg);
		} else {
			this._nextImg = $("<img/>").hide();
			$('#next_photo > div').append(this._nextImg);
		}

		if (index > 0) {
			var prev = photos[index - 1];
			this._prevImg = $("<img src=\""+prev.main_url+"\"/>");
			$('#prev_photo > div').append(this._prevImg);
		} else {
			this._prevImg = $("<img/>").hide();
			$('#prev_photo > div').append(this._prevImg);
		}

		$('#prev_photo')
			.mouseenter(function () {
				debug('A');
				$(this).find('img').animate({
					'right': 0,
					'opacity': 1
				});
			})
			.mouseleave(function () {
				debug('B');
				$(this).find('img').animate({
					'right': '50%',
					'opacity': 0.3
				});
			})
			.click(photo.prev);

		$('#next_photo')
			.mouseenter(function () {
				debug('A');
				$(this).find('img').animate({
					'left': 0,
					'opacity': 1
				});
			})
			.mouseleave(function () {
				debug('B');
				$(this).find('img').animate({
					'left': '50%',
					'opacity': 0.3
				});
			})
			.click(photo.next);


		$('#main_photo img').click(photo.next);
	},

	next: function () {
		if (photo._index < photo._photos.length - 1) {
			photo._set(photo._index + 1);
		}
	},

	prev: function () {
		if (photo._index > 0) {
			photo._set(photo._index - 1);
		}
	},

	_set: function (i) {
		debug('[photo] ' + i + ' as main');
		photo._setPrev(i - 1);
		photo._setMain(i);
		photo._setNext(i + 1);
	},

	_setPrev: function (i) {
		if (i < 0) {
			photo._prevImg.hide();
			return;
		}
		var ph = photo._photos[i];
		photo._slide(photo._prevImg, ph.main_url, 'fast');
	},

	_setMain: function (i) {
		var ph = photo._photos[i];
		photo._index = i;
		photo._slide(photo._mainImg, ph.main_url);

		$('#photo_title').html(ph.title);
		$('#thumbs img').removeClass('selected');
		$('#thumb_' + ph.id + ' img').addClass('selected');

		var tgs = $('#photo_tags .tags').html('');
		for (var j = 0; j < ph.tags.length; ++j) {
			tgs.append(
				$('<a href="/media/photo/tag'+ph.tags[j].id+'"/>')
					.append(
						$('<div/>')
							.addClass('tag')
							.html(ph.tags[j].value)
					)
			);
		}

		content.loadInitialComments(ph.id, $('#photo_comments'));
		content.loadEvaluation($('.tools .evaluation'), ph.id);
		content.markAsViewed('item', ph.id, function () {});

		history.pushState && history.pushState({}, ph.title, '/media/photo/album' + ph.album_id + '/' + ph.id);
	},

	_setNext: function (i) {
		if (i >= photo._photos.length) {
			photo._nextImg.hide();
			return;
		}
		var ph = photo._photos[i];
		photo._slide(photo._nextImg, ph.main_url, 'fast');
	},

	_slide: function (jq, src, speed) {
		speed = speed ? speed : 'slow';
		jq.fadeOut('fast', function () {
			$(this).attr('src', src).fadeIn(speed);
		});
	}
};