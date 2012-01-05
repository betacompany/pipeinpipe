var forum = {
	sending: false,
	loading: false,
	editing: {},
	popups: {},

	loadMsgsSuccess: function (topic_id, data) {
		// TODO prove if data contains error report
		$('#topic_' + topic_id).html(data);
		forum.loading = false;
	},

	loadMsgsBeforeSend: function () {
		forum.loading = true;
		for (var key in forum.popups) {
			if (forum.hasOwnProperty(key) && forum.popups[key]) {
				forum.popups[key].hide();
			}
		}
	},

	loadTopicsSuccess: function (part_id, data) {
		// TODO prove if data contains error report
		$('#part_' + part_id).html(data);
		forum.loading.remove();
		forum.loading = false;
	},

	loadTopicsBeforeSend: function () {
		forum.loading = loading('#body', true, undefined, 50);
	},

	loadMsgs: function (topic_id, from, limit) {
		$.ajax({
			url: '/procs/proc_forum.php',
			data: {
				method: 'load_messages',
				topic_id: topic_id,
				from: from,
				limit: limit
			},

			beforeSend: forum.loadMsgsBeforeSend,
			success: function (data) {
				forum.loadMsgsSuccess(topic_id, data);
			},
			error: showError
		});
	},

	loadLastMsgs: function (topic_id, success) {
		$.ajax({
			url: '/procs/proc_forum.php',
			data: {
				method: 'load_last_messages',
				topic_id: topic_id
			},

			beforeSend: forum.loadMsgsBeforeSend,
			success: function (data) {
				forum.loadMsgsSuccess(topic_id, data);
				if (success != undefined) success();
			},
			error: showError
		});
	},

	loadLastOne: function (topic_id, success) {
		$.ajax({
			url: '/procs/proc_forum.php',
			data: {
				method: 'load_last_one',
				topic_id: topic_id
			},

			success: function (xml) {
				var data = $(xml).find('result').html();
				var count = $(xml).find('count').html();

				$('#topic_'+topic_id+' > .title').animate({
					backgroundColor: COLOR.FIRST
				});
				$('#topic_'+topic_id+' > .title a').animate({
					color: '#fff'
				});
				$('#quick_'+topic_id).html(data);

				$('#topic_'+topic_id).find('.count').html(count);
				$('#topic_'+topic_id).removeClass('new');
				$('#topic_'+topic_id).find('.appendix').fadeOut();
				
				if (success != undefined) success();
			},
			error: showError
		});
	},

	loadTopics: function (part_id, from, limit) {
		$.ajax({
			url: '/procs/proc_forum.php',
			data: {
				method: 'load_topics',
				part_id: part_id,
				from: from,
				limit: limit
			},

			beforeSend: forum.loadTopicsBeforeSend,
			success: function (data) {
				forum.loadTopicsSuccess(part_id, data);
			},
			error: showError
		});
	},

	loadLastTopics: function (part_id) {
		$.ajax({
			url: '/procs/proc_forum.php',
			data: {
				method: 'load_last_topics',
				part_id: part_id
			},

			beforeSend: forum.loadTopicsBeforeSend,
			success: function (data) {
				forum.loadTopicsSuccess(part_id, data);
			},
			error: showError
		});
	},

	loadTopicsTop: function (part_id) {
		$.ajax({
			url: '/procs/proc_forum.php',
			data: {
				method: 'load_topics_top',
				part_id: part_id
			},

			beforeSend: forum.loadTopicsBeforeSend,
			success: function (data) {
				forum.loadTopicsSuccess(part_id, data);
			},
			error: showError
		});
	},

	sendMsg: function (topic_id) {
		if (forum.sending) return false;

		if ($('#textarea_field').val() == '') {
			return showErrorText('Сообщение не может быть пустым');
		}


		$.ajax({
			url: '/procs/proc_forum.php',
			data: {
				method: 'add_message',
				topic_id: topic_id,
				html: $('#textarea_field').val()
			},
			dataType: 'json',
			type: 'POST',

			beforeSend: function () {
				forum.sending = true;
			},

			success: function (json) {
				forum.sending = false;
				
				if (json != null && json.status == 'ok') {
					forum.loadLastMsgs(topic_id, function () {
						$('#textarea_field').val('');
					});
					
					return;
				}

				showErrorJSON(json);
			},

			error: showError
		});

		return true;

	},

	sendQuickMsg: function (topic_id) {
		if (forum.sending == true) return false;

		if ($('#quick_'+topic_id+'_textarea').val() == '') {
			return showErrorText('Сообщение не может быть пустым!');
		}

		$.ajax({
			url: '/procs/proc_forum.php',
			data: {
				method: 'add_message',
				topic_id: topic_id,
				html: $('#quick_'+topic_id+'_textarea').val()
			},
			dataType: 'json',
			type: 'POST',

			beforeSend: function () {
				forum.sending = true;
			},

			success: function (json) {
				forum.sending = false;

				if (json != null && json.status == 'ok') {
					forum.loadLastOne(topic_id, function () {
						$('#quick_'+topic_id+'_textarea').val('');
						$('#topic_'+topic_id).delay(5000).queue(function () {
							if ($('#topic_'+topic_id+' > .title').hasClass('opened')) {
								forum.toggleQuick(topic_id);
							}
						});
					});
					return;
				}

				showErrorJSON(json);
			},

			error: showError
		});

		return true;
	},

	editMsg: function (msgId) {
		if (this.editing[msgId] != undefined) {
			if (this.editing[msgId]) {
				$('#msg_'+msgId)
					.find('.edit')
					.fadeOut('fast', function () {
						$('#msg_'+msgId)
							.find('.text')
							.fadeIn('fast');
					});

				this.editing[msgId] = false;
				return;
			}
		}

		$('#msg_'+msgId)
			.find('.text')
			.fadeOut('fast', function () {
				$('#msg_'+msgId)
					.find('.edit')
					.fadeIn('fast');
			});

		this.editing[msgId] = true;
	},

	removeMsg: function (msgId) {
		var l;
		if (confirm('Вы уверены, что хотите удалить это сообщение?')) {
			$.ajax({
				url: '/procs/proc_forum.php',
				data: {
					method: 'remove_message',
					msg_id: msgId
				},
				dataType: 'json',

				beforeSend: function () {
					l = loading(ge('msg_'+msgId), true);
				},

				success: function (json) {
					l.hide();
					if (json != null && json.status != undefined) {
						if (json.status != 'ok') {
							return false;
						}

						$('#msg_'+msgId).fadeOut();
					}

					return false;
				}
			});
		}
	},

	saveMsg: function (msgId) {
		var text = $('#msg_'+msgId).find('textarea').val(), l;
		$.ajax({
			url: '/procs/proc_forum.php',
			data: {
				method: 'edit_message',
				msg_id: msgId,
				text: text
			},
			dataType: 'json',
			type: 'POST',

			beforeSend: function () {
				l = loading(ge('msg_'+msgId), true);
				//$('#msg_'+msgId).find('.button').slideUp();
			},

			success: function (json) {
				l.hide();
				if (json != null && json.status != undefined) {
					if (json.status != 'ok') {
						return false;
					}

					$('#msg_'+msgId).find('.edit > textarea').val(json.msg.src);
					$('#msg_'+msgId).find('.text').html(json.msg.html);
					$('#msg_'+msgId).find('.edit').fadeOut(
						function () {
							$('#msg_'+msgId).find('.text').fadeIn();
							forum.editing[msgId] = false;
						}
					);
				}

				return false;
			}
		});
	},

	actMsg: function (type, msgId) {
		$.ajax({
			url: '/procs/proc_forum.php',
			data: {
				method: 'act',
				action: type,
				msg_id: msgId
			},
			dataType: 'json',

			beforeSend: function () {
				debug('before [act: ' + type + ', ' + msgId + ']');
			},

			success: function (json) {
				debug('success [' + json.status + ']');
				if (json.status == 'ok') {
					$('#msg_' + msgId + ' .' + type).html(json.count).addClass('count').addClass('yours');
					forum.popups[type+'_'+msgId] = undefined;
				}
			},

			error: showError
		});
	},

	citeMsg: function (msgId) {
		var txt = $('#msg_' + msgId + ' textarea').html(),
			authorName = ($('#msg_' + msgId + ' .author').html().split(' '))[0];
		$('#textarea_field').val(
			$('#textarea_field').val() +
			'[quote name=' + authorName + ']' + txt + '[/quote]'
		);
	},

	showActions: function (type, msgId) {
		if (this.popups[type+'_'+msgId] != undefined) {
			//debug('show_' + msgId);
			this.popups[type+'_'+msgId].show();
			return;
		} else {
			$.ajax({
				url: '/procs/proc_forum.php',
				data: {
					method: 'load_actions',
					msg_id: msgId,
					action: type
				},

				success: function (data) {
					var position = $('#msg_' + msgId).find('.' + type).offset();
					forum.popups[type+'_'+msgId] = new PopUp({
						x: position.left + 10, y: position.top - 3,
						id: 'popup_' + type + '_' + msgId,
						html: data
					});
					forum.popups[type+'_'+msgId].show();
				}
			});
		}
	},

	hideActions: function (type, msgId) {
		if (this.popups[type+'_'+msgId] != undefined) {
			//debug('hide_' + msgId);
			this.popups[type+'_'+msgId].hide();
			//debug(this.popups[type+'_'+msgId]);
		}
	},

	toggleQuick: function (topic_id) {
		if ($('#topic_'+topic_id+' > .title').hasClass('opened')) {
			$('#topic_'+topic_id+' > .body').slideUp();
			$('#topic_'+topic_id+' > .title').removeClass('opened');
			return;
		}

		$('#topic_'+topic_id+' > .body').slideDown();
		$('#topic_'+topic_id+' > .title').addClass('opened');
		$('#quick_'+topic_id+'_textarea').focus();
	},

	togglePart: function (part_id) {
		if ($('#part_'+part_id+' > .title').hasClass('opened')) {
			$('#part_'+part_id+' > .body').slideUp();
			$('#part_'+part_id+' > .title').removeClass('opened');
			slideBlock.removeOpenedPart(part_id);
			return;
		}

		$('#part_'+part_id+' > .body').slideDown();
		$('#part_'+part_id+' > .title').addClass('opened');
		slideBlock.addOpenedPart(part_id);
	},

	openPart: function (part_id) {
		if ($('#part_'+part_id+' > .title').hasClass('opened')) return;
		$('#part_'+part_id+' > .body').slideDown();
		$('#part_'+part_id+' > .title').addClass('opened');
	},

	newTopic: function () {
		$('#topic_new').slideDown();
		$('#topic_new_href').fadeOut();
		$('#topic_new_input').focus();
	},

	createTopic: function (part_id) {
		if (forum.sending == true) return false;

		if ($('#topic_new_input').val() == '') {
			return showErrorText('Название топика не может быть пустым!');
		}

		$.ajax({
			url: '/procs/proc_forum.php',
			data: {
				method: 'create_topic',
				part_id: part_id,
				value: $('#topic_new_input').val()
			},
			dataType: 'json',

			beforeSend: function () {
				forum.sending = true;
			},

			success: function (json) {
				forum.sending = false;

				if (json.status == 'ok') {
					window.location = '/forum/part'+part_id+'/topic'+json.topic_id;
					return;
				}

				showErrorJSON(json);
			},

			error: showError
		});

		return true;
	},

	ctrlEnterHandler: function (event, handler) {
		if (event.ctrlKey && (event.keyCode == 10 || event.keyCode == 13)) {
			handler();
		}
	},

	enterHandler: function (event, handler) {
		if (event.keyCode == 10 || event.keyCode == 13) {
			handler();
		}
	},

	loadStats: function (id) {
		var l;
		$.ajax({
			url: '/procs/proc_forum.php',
			data: {
				method: 'load_stats',
				type: id
			},

			beforeSend: function () {
				l = loading(ge('stats_body'), true);
			},

			success: function (html) {
				$('#stats_body').html(html);
				l.fadeOut().remove();
			}
		});
	}
};

$(document).ready(function () {
	var path = document.URL;
	var a = path.split('#');
	if (a.length > 1) {
		var address = a[0];
		var l = address.split('topic');
		var anchor = a[1];
		var b = anchor.split('/');
		if (b.length == 2) {
			if (l.length > 1) {
				var topic_id = l[1];
				if (b[0] == 'page') {
					forum.loadMsgs(topic_id, (b[1] - 1) * 10, 10);
				}
			} else {
				l = address.split('part');
				if (l.length > 1) {
					var part_id = l[1];
					if (b[0] == 'page') {
						if (b[1] == 'top') {
							forum.loadTopicsTop(part_id);
						} else {
							forum.loadTopics(part_id, (b[1] - 1) * 10, 10);
						}
					}
				}
			}
		}
	}

//	$('#menu').mouseover(function () {
//		$('#forum_topic > .title').fadeOut();
//	});
//
//	$('#menu').mouseout(function () {
//		$('#forum_topic > .title').fadeIn();
//	});
});