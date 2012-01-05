var rating = {
	ratingContainer: null,
	__state: '',
	__pmids_all: new Array(),
	__pmids: {
		length: 0
	},
	__info_loaded: {},
	__movement: null,
	__width: $('body').innerWidth(),
	waiting: '',

	showMovement: function (json) {
		$('#rating_left_content').children().remove();
		$('#rating_right_content').children().remove();

		for (var i = 0; i < json.length; i++) {
			var obj = json[i],
				leftWidth = $('#rating_left_content').width() + 3,
				rightWidth = $('#rating_right_content').width() + 3,
				leftURL = obj.left.setted ? obj.left.url : obj.left.url + leftWidth + 'x' + (obj.left.height + 2),
				rightURL = obj.right.setted ? obj.right.url : obj.right.url + rightWidth + 'x' + (obj.right.height + 2);

			$('<div/>').css({
				'backgroundImage': 'url(' + leftURL + ')',
				'width': leftWidth - 3,
				'height': obj.left.height + 'px',
				'backgroundRepeat': 'no-repeat',
				'backgroundPosition': '-2px -1px',
				'margin': 0,
				'padding': 0
			}).appendTo($('#rating_left_content'));

			$('<div/>').css({
				'backgroundImage': 'url(' + rightURL + ')',
				'width': rightWidth - 3,
				'height': obj.right.height + 'px',
				'backgroundRepeat': 'no-repeat',
				'backgroundPosition': '-2px -1px',
				'margin': 0,
				'padding': 0
			}).appendTo($('#rating_right_content'));
		}
	},

	show: function (jObject) {
		var offset = $('#rating').offset().top,
			scroll = $(document).scrollTop(),
			top = (scroll - offset < 0) ? 0 : scroll - offset;

		top -= 5;

		$('#rating_compare').fadeOut(200, function () {
			$('#rating_compare').html('');
			jObject.appendTo($('#rating_compare'));
			$('#rating_compare').css({
				'top': top + 'px'
			});
			$('#rating_compare').fadeIn(200);
		});
	},

	loadMovement: function () {
		if (rating.__movement != null) {
			rating.showMovement(rating.__movement);
			return;
		}

		var l1, l2;
		api.request({
			handler: 'charts',
			method: 'rating_all',
			data: {
				delta_past: -5,
				date: date,
				delta_future: 5,
				league_id: leagueId
			},
			beforeSend: function () {
				l1 = loading(ge('rating_left'), true, undefined, 100);
				l2 = loading(ge('rating_right'), true, undefined, 100);
			},
			success: function (json) {
				l1.remove();
				l2.remove();
				rating.__movement = json;
				rating.showMovement(json);
			},
			error: function () {
				alert('Error!!');
			}
		});
	},

	load: function (success) {
		var l;
		api.request({
			handler: 'sport_rating',
			method: 'get_rating',
			data: {
				date: date,
				league_id: leagueId
			},
			beforeSend: function () {
				l = loading(ge('rating_container'), true, undefined, 50);
			},
			success: function (json) {
				l.remove();
				if (json.status == 'failed') return;
				rating.init(json, success);
				rating.loadMovement();
			}
		});
	},

	init: function (jsonData, callback) {
		rating.ratingContainer = $('#rating');
		rating.ratingContainer.children().remove();

		for (var i = 0; i < jsonData.length; i++) {
			var obj = jsonData[i],
				item = ce('li'),
				checkbox = new CheckBox({
					id: obj.pmid,
					onCheck: function (c, id) {
						rating.checkBoxHandler(c, id);
					}
				});

			var points = Math.round(obj.points * 10) / 10,
				rounded = Math.round(obj.points);

			item.innerHTML =  '<div class="place">' + (i+1) + '</div>';
			item.innerHTML += '<div class="box"><a href="' + obj.url +
				'"><img alt="' + obj.name + ' ' + obj.surname +'" src="'+
				obj.photo + '" /><div class="content"><div>' +
				obj.surname + '</div><div>' + obj.name + '</div></div></a><div class="points">'+
				(points == rounded ? points + '.0' : points) +'</div>'+
				'<div class="clear"></div></div>';

			item.id = obj.pmid;
			rating.__pmids_all.push(obj.pmid);
			rating.ratingContainer.append(item);

			if (i >= 99) {
				$('#'+obj.pmid+' .place').addClass('supersmall');
			} else if (i >= 9) {
				$('#'+obj.pmid+' .place').addClass('small');
			}

			checkbox.appendTo($('#'+obj.pmid+' .box'));
		}

		$('#rating_container').height(jsonData.length * 52 + 50);

//		$(document).scroll(rating.scrollHandler);
//		$(window).resize(rating.scrollHandler);
//		rating.scrollHandler();

		$(window).resize(function () {
			if (rating.__width == $('body').innerWidth()) return;
			$('#rating_left_content').children().remove();
			$('#rating_right_content').children().remove();
			rating.state2(rating.state(), function () {
				if (rating.state() == 'initial') {
					rating.loadMovement();
					rating.__width = $('body').innerWidth()
				}
			});
		});

		if (callback != undefined) callback();

		//if (console != undefined) console.debug('rating.init() finished');
	},

	left: function (value, callback) {
		if (value == undefined) return rating.ratingContainer.css('left');
		rating.ratingContainer.css('marginLeft', 0);
		return rating.ratingContainer.animate({"left": value + 'px'}, 1000, 'linear', callback);
	},

	state: function (state, callback) {
		if (state == undefined) {
			return rating.__state;
		}

		if (state == rating.state()) {
			if (callback != undefined) callback();
			return true;
		}

		return rating.state2(state, callback);
	},

	state2: function (state, callback) {
		var outerWidth = $('#rating_container').width();
		var innerWidth = rating.ratingContainer.children().width();

		if (state == 'initial') {
			$('#rating_compare').html('');
			$('#rating_compare').fadeOut(200, function () {
				$('#rating_left, #rating_right').width((outerWidth - innerWidth) / 2);
				$('#rating_right').css('right', 0);
				rating.left((outerWidth - innerWidth) / 2, function () {
					$('#rating_left').show();
					$('#rating_left_content, #rating_right_content').fadeIn(200, function () {
						rating.__state = state;
						if (callback != undefined) callback();
					});
				});

				var left = ($('body').width() - $('#rating_selector').width()) / 2;
				$('#rating_selector').fadeIn(200).animate({
					"left": left
				}, 500);
			});

			return true;
		}

		var left = outerWidth > 1000 ? outerWidth / 10 : 50;

		if (state == 'compare') {
			if (rating.__state == 'initial') {
				$('#rating_left_content, #rating_right_content').fadeOut(200, function () {
					rating.left(left, function () {
						$('#rating_right').width(outerWidth - innerWidth - 2 * left);
						$('#rating_right').css('right', left+'px');
						$('#rating_left').hide();
						rating.__state = state;
						if (callback != undefined) callback();
					});
				});
			} else if (rating.__state == 'evaluate') {
				$('#rating_compare').html('');
				rating.__state = state;
				if (callback != undefined) callback();
			}

			$('#rating_selector').animate({
				"left": left
			}, 500);

			return true;
		}

		if (state == 'evaluate') {
			if (rating.__state == 'initial') {
				$('#rating_left_content, #rating_right_content').fadeOut(200, function () {
					rating.left(left, function () {
						$('#rating_right').width(outerWidth - innerWidth - 2 * left);
						$('#rating_right').css('right', left+'px');
						$('#rating_left').hide();
						rating.__state = state;
						if (callback != undefined) callback();
					});
				});
			} else if (rating.__state == 'compare') {
				$('#rating_compare').html('');
				rating.__state = state;
				if (callback != undefined) callback();
			}

			$('#rating_selector').animate({
				"left": left
			}, 500);

			return true;
		}

		return false;
	},

	checkBoxHandler: function (checked, pmid) {
		if (checked) {
			$('#' + pmid).addClass('selected');
			rating.__pmids[pmid.toString()] = true;
			rating.__pmids.length++;
		} else {
			$('#' + pmid).removeClass('selected');
			rating.__pmids[pmid.toString()] = false;
			rating.__pmids.length--;
		}

		if (rating.__pmids.length == 0) {
			rating.state('initial');
		} else if (rating.__pmids.length == 1) {

		} else if (rating.__pmids.length == 2) {
			rating.state('compare', function () {
				var pmid1 = 0, pmid2 = 0;
				for (var key in rating.__pmids) {
					if (key != 'length') {
						if (rating.__pmids[key]) {
							if (pmid1 == 0) {
								pmid1 = key;
							} else {
								pmid2 = key;
								break;
							}
						}
					}
				}

				var l;

				api.request({
					handler: 'sport_player_comparator',
					method: 'compare',
					data: {
						pmid1: pmid1,
						pmid2: pmid2
					},
					dataType: 'html',
					beforeSend: function () {
						l = loading(ge('rating_right'), true, undefined, 100);
						rating.waiting = 'compare_'+pmid1+'_'+pmid2;
					},
					success: function (html) {
						l.remove();
						if (rating.waiting == 'compare_'+pmid1+'_'+pmid2) {
							rating.show($(html));
							rating.waiting = '';
						}
					}
				});
			});
		} else {
			var pmids = new Array();
			for (var key in rating.__pmids) {
				if (rating.__pmids[key] && key != 'length')
					pmids.push(key);
			}
			rating.state('evaluate', function () {
				var l;
				api.request({
					handler: 'sport_rating',
					method: 'evaluate_coef',
					data: {
						league_id: leagueId,
						pmids: pmids.toString(),
						date: date
					},
					beforeSend: function () {
						l = loading(ge('rating_right'), true, undefined, 100);
						rating.waiting = 'eval_' + pmids.toString();
					},
					success: function (json) {
						l.remove();
						var jContainer = $('<div/>').css({
							"marginLeft" : "10px"
						});

						$('<h2/>').addClass('other')
								  .html('Рассчёт коэффициента турнира:')
								  .appendTo(jContainer);

						if (json.formula.name_en == 'Grigoriev') {
							var ul = $('<ul/>');
							ul.append($('<li/>').html('Формула <a href="/sport/rating/formula/Grigoriev">Григорьева</a>. Результат: <span class="result">' + json.data.result + '</span>'));
							ul.append($('<li/>').html('Среднее: ' + json.data.avg_points));
							var ul2 = $('<ul/>');
							ul.append($('<li/>').html('Веса: ').append(ul2));
							var lit = $('<li/>');
							for (var i = 0; i < json.weights.length; i++) {
								var obj = json.weights[i],
									name = $('#' + obj.pmid + ' a > div:first-child').html(),
									surname = $('#' + obj.pmid + ' a > div:last-child').html(),
									isF = lit.html().length == 0,
									jA = $('<a/>').html(name + '&nbsp;' + surname)
												  .attr({
													  'href': obj.url,
													  'target': '_blank'
												  });
								var html = isF ? '' : ', ';
								lit.html(lit.html() + html);
								lit.append(jA);
								html = '&nbsp;(' + obj.weight + ')';
								lit.html(lit.html() + html);
							}
							ul2.append(lit);
							ul2.append($('<li/>').html('Представителей &laquo;верха&raquo;: <b>' + json.data.count_top + '</b>'));
							ul2.append($('<li/>').html('Представителей &laquo;низа&raquo;: <b>' + json.data.count_bottom + '</b>'));
							ul2.append($('<li/>').html('Всего участников: <b>' + (parseInt(json.data.count_top, 10) + parseInt(json.data.count_bottom, 10)) + '</b>'));

							ul.append($('<li/>').html('Отношение: ' + json.data.ratio));
							ul.append($('<li/>').html('В пересчёте на [-1; 1]: ' + json.data.x));
							ul.append($('<li/>').html('Показатель экспоненты: ' + json.data.exp));
							ul.append($('<li/>').html('Множитель: ' + json.data.mult));

							ul.appendTo(jContainer);
						} else {
							$('<div/>').html('Неизвестная формула')
									   .appendTo(jContainer);
						}

						if (rating.waiting == 'eval_' + pmids.toString()) {
							rating.show(jContainer);
							rating.waiting = '';
						}
					}
				});
			});
		}
	}
};
