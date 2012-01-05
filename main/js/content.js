/**
 * @author Artyom Grigoriev
 */
var content = {

	loadingComments: '',
	sending: false,

	loadComments: function (itemId, page) {
		var limit = 5,
			from = (page - 1) * limit;

		$.ajax({
			url: '/procs/proc_content.php',
			data: {
				method: 'get_comments',
				from: from,
				limit: limit,
				item_id: itemId
			},

			beforeSend: function () {
				content.loadingComments = itemId + '_' + page;
			},

			success: function (html) {
				if (content.loadingComments != itemId + '_' + page) return;
				$('#comments_' + itemId).find('.comments_content').html(html);
				content.loadingComments = '';
			}
		});
	},

	sendComment: function (itemId) {
		if (content.sending) return;
		var jca = $('#comments_' + itemId),
			text = jca.find('textarea').val();

		if (text == '') return;

		$.ajax({
			url: '/procs/proc_content.php',
			data: {
				method: 'add_comment',
				text: text,
				item_id: itemId
			},
			dataType: 'json',

			beforeSend: function () {
				content.sending = true;
			},

			success: function (json) {
				if (json.status != 'ok') {
					return;
				}

				jca.find('textarea').val('');
				content.loadComments(itemId, Math.ceil(json.count / 5));
				content.sending = false;
			}
		});
	},

	ctrlEnterHandler: function (event, handler) {
		if (event.ctrlKey && (event.keyCode == 10 || event.keyCode == 13)) {
			handler();
		}
	},

	showEvaluation: function (container, itemId, evaluation, possibility) {
		var i, j;

		if (!possibility) {
			container.attr({
				title: 'Оценка: ' + Math.round(evaluation * 100) / 100
			});
			evaluation = Math.round(evaluation);

			for (i = 0; i < evaluation; i++) {
				container.append(
					$('<div/>').addClass('star_solid')
				);
			}

			for (j = i; j < 5; j++) {
				container.append(
					$('<div/>').addClass('star_empty')
				);
			}
		} else {
			for (j = 0; j < 5; j++) {
				container.append(
					$('<div/>')
						.addClass('star_empty')
						.attr({
							id: 'value_' + (j+1)
						})
						.css({
							'cursor': 'pointer'
						})
						.mouseover(function () {
							var ids = $(this).attr('id').split('_');
							var index = ids[1];
							container.children('div').each(function (i, o) {
								if (i < index) {
									$(o).removeClass('star_empty').addClass('star_solid');
								}
							});
						})
						.mouseout(function () {
							container.children('div').removeClass('star_solid').addClass('star_empty');
						})
						.click(function () {
							var ids = $(this).attr('id').split('_');
							var index = ids[1];
							content.evaluateItem(itemId, index);
						})
				);
			}
		}
	},

	evaluateItem: function (itemId, value) {
		$.ajax({
			url: '/procs/proc_content.php',
			data: {
				method: 'evaluate',
				item_id: itemId,
				value: value
			},
			dataType: 'json',

			success: function (json) {
				if (json.status != 'ok') return false;
				$('.evaluation').html('');
				content.showEvaluation($('.evaluation'), itemId, json.value, false);
			}
		});
	},

	markAsViewed: function (targetType, targetId, onSuccess) {
		$.ajax({
			url: '/procs/proc_content.php',
			data: {
				method: 'mark_as_viewed',
				tagret_type: targetType,
				target_id: targetId
			},
			dataType: 'json',

			success: function (json) {
				return (json.status != 'ok' && onSuccess != undefined) ? onSuccess(targetType, targetId) : false;
			}
		});
	},

	updateFreshness: function (type, count) {
		if (ge(type+'_new') != undefined) {
			if (count > 0) {
				$('#'+type+'_new')
					.html('+' + count)
					.show();
			} else {
				$('#'+type+'_new')
					.html('')
					.hide();
			}
		} else {
			if (count > 0) {
				$('#menu_'+type).append(
					$('<div/>')
						.attr('id', type+'_new')
						.html('+' + count)
						.css('display', 'block')
				);
			} else {
				$('#menu_'+type).append(
					$('<div/>')
						.attr('id', type+'_new')
						.html('')
				);
			}
		}
	},

	checkFreshness: function () {
		if (ge('user_bar') == undefined) return;
		$.ajax({
			url: '/procs/proc_fresh.php',
			data: {
				t: tm()
			},
			dataType: 'json',
			success: function (json) {
				if (json.status != 'ok') return false;
				content.updateFreshness('forum', json.forum);
				content.updateFreshness('life', json.life);
				var pause = json.process_time > 100 ? 60000 : 10000;
				setTimeout(content.checkFreshness, pause);
				return true;
			}
		});
	},

	cutString: function (str, size, maxSize, suffix) {
		suffix = suffix || '...';
		var length = str.length;
		if (size > maxSize) return str;
		if (length <= maxSize) return str;
		var i = maxSize;
		for (; i >= 0; i--) {
			var c = str.charAt(i);
			if (c == '\t' || c == '\n' || c == ' ') break;
		}

		if (i < size / 2) return str.substr(0, size) + suffix;

		return str.substr(0, i) + suffix;
	},

	initBugReport: false,
	reportBug: function () {
		if (!content.initBugReport) {
			$('<div/>')
				.attr('id', 'bug_report')
				.hide()
				.appendTo($('#layout').parent())
				.append(
					$('<textarea/>')
						.attr('id', 'bug_text')
				)
				.append(
					$('<div/>')
						.html('Оставить отзыв')
						.addClass('button')
						.one('click', content.sendBug)
				);
			$('body').keypress(function (ev) {
				debug(ev.which);
				if (ev.which == 27) {
					content.cancelBug();
				}
			});
			content.initBugReport = true;
		}

		$('#layout').fadeOut(function () {
			$('#bug_report').fadeIn();
		});
	},

	cancelBug: function () {
		$('#bug_report').fadeOut(function () {
			$('#layout').fadeIn();
		});
	},

	sendBug: function () {
		var text = $('#bug_text').val(),
			page = window.location.pathname;
		$.ajax({
			url: '/procs/proc_content.php',
			data: {
				method: 'report_bug',
				text: text,
				page: page
			},
			dataType: 'json',

			success: function (json) {
				if (json && json.status && json.status == 'ok') {
					$('#bug_report').html('<center>OK</center>');
					window.location = json.topic;
				} else {
					$('#bug_report').html('<center>ERROR</center>');
				}
				content.cancelBug();
			}
		});
	}

};

$(function () {
	content.checkFreshness();
});
