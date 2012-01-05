/**
 * @author Artyom Grigoriev
 * @author Nikolay Malkovsky
 * @author Innokenty Shuvalov
 */
var newCupPanel;

var competition = {
	editName: function (o) {
		var jObject = $(o);
		var jContainer = jObject.parent();
		var previousValue = jObject.text();

		jObject.hide();

		var jEditDiv = $('<div/>').appendTo(jContainer);

		var jInput = $('<input/>', {
			type: 'text',
			value: previousValue
		}).appendTo(jEditDiv)
		.focus();

		var jButton = $('<input/>', {
			type: 'button',
			value: 'OK'
		}).appendTo(jEditDiv);

		var jCancelButton = $('<input/>', {
			type: 'button',
			value: 'Cancel'
		}).appendTo(jEditDiv);

		var hideFields = function (newValue) {
			jButton.hide();
			jCancelButton.hide();
			jInput.fadeOut(
				"slow",
				function () {
					jObject.fadeIn();
					jEditDiv.remove();
					if (newValue != undefined) {
						reloadMenu();
						jObject.html(newValue);
						$('#content_header').html(newValue);
					}
				}
			);
		}

		jButton.click(function () {
			if (previousValue != jInput.val()) {
				$.ajax({
					url: 'proc_competition.php',
					data: {
						method: 'set_name',
						comp_id: currentTargetId,
						name: jInput.val()
					},

					beforeSend: function () {
						jInput.addClass('loading');
						jButton.addClass('disabled');
						jCancelButton.addClass('disabled');
					},

					success: function (data) {
						jInput.removeClass('loading');
						jInput.addClass('loaded');
						jButton.removeClass('disabled');
						jCancelButton.removeClass('disabled');
						hideFields(data);
					},

					error: errorInAjax
				});
			} else {
				jInput.addClass('loaded');
				hideFields();
			}
		});

		jCancelButton.click(function() {
			hideFields();
		});
	},

	showTournamentSelector: function () {
		$('#tournament_name').slideUp('fast', function () {
			$('#tournament_selector_panel').slideDown('fast');
		});
	},

	hideTournamentSelector: function () {
		$('#tournament_selector_panel').slideUp('fast', function () {
			$('#tournament_name').slideDown('fast');
		});
	},

	editTournament: function (tour_id) {
		//alert('tournamentSelector.val(): ' + tournamentSelector.val());
		$.ajax({
			url: 'proc_competition.php',
			data: {
				method: 'set_tournament',
				comp_id: currentTargetId,
				tour_id: tour_id
			},

			dataType: 'json',

			beforeSend: function () {
				tournamentSelector.disable();
			},

			success: function(data) {
				tournamentSelector.enable();
				if (data.status == 'failed') {
					showErrorJSON(data);
				} else {
					$('#tournament_name').html(data.tour_name);
					competition.hideTournamentSelector();
				}
			}
		});
	},

	createTournament: function(compId, tourName) {
		if (tourName == '') {
			alert('Имя новой серии турниров не должно быть пустым!');
		} else {
			$.ajax({
				url: 'proc_competition.php',
				data: {
					method: 'add_tournament',
					comp_id: compId,
					tour_name: tourName
				},
				dataType: 'json',
				beforeSend: function() {
					tournamentSelector.disable();
				},
				success: function(data) {
					tournamentSelector.enable();
					if (data.status == 'failed') {
						showErrorJSON(data);
					} else {
						$('#tournament_name').html(data.tour_name);
						competition.hideTournamentSelector();
					}
				}
			});
		}
	},

	editDate: function (o) {
		var jObject = $(o);
		var jContainer = jObject.parent();

		jObject.hide();

		var jEditDiv = $('<div/>').appendTo(jContainer);
		
		var jYear = $('<select/>').appendTo(jEditDiv);
		var jMonth = $('<select/>').appendTo(jEditDiv);
		var jDay = $('<select/>').appendTo(jEditDiv);
		
		var jButton = $('<input/>', {
			type: 'button',
			value: 'OK'
		}).appendTo(jEditDiv);

		var jCancelButton = $('<input/>', {
			type: 'button',
			value: 'Cancel'
		}).appendTo(jEditDiv);

		
		var i, temp, currentYear = (new Date()).getFullYear();
		
		for(i = 2007; i <= currentYear; i++) {
			temp = $('<option/>', {
				value: i,
				innerHTML: i
			}).appendTo(jYear);
		}

		for(i = 0; i <= 11; i++) {
			temp = $('<option/>', {
				value: (i < 9) ? '0' + (i + 1) : (i + 1),
				innerHTML: calendar.months[i]
			}).appendTo(jMonth);
		}

		for(i = 0;i < 31; i++) {
			temp = $('<option/>', {
				value: (i < 9) ? '0' + (i + 1) : (i + 1),
				innerHTML: i + 1
			}).appendTo(jDay);
		}

		var hideFields = function(data) {
			jButton.hide();
			jCancelButton.hide();
			jDay.fadeOut("slow");
			jMonth.fadeOut("slow");
			jYear.fadeOut(
				"slow",
				function () {
					if (data != undefined)
						jObject.html(data.date);
					jObject.fadeIn();
					jEditDiv.remove();
				}
			);
		}
		
		jButton.click(function () {
			var temp = jYear.val() + '-' + jMonth.val() + '-' + jDay.val();

			if(!calendar.isCorrect(jYear.val(),jMonth.val(), jDay.val())) {
				showError('Выбрана некорректная дата.');
				return;
			}
			
			if(temp < calendar.pipeBirthday) {
				showError('Турнир не может заканчиваться раньше, чем 23 октября 2007 года, так как это день рождения pipe-in-pipe.');
				return;
			}
			
			if(temp > calendar.today()) {				
				showError('Турнир не может быть законченным в будущем!');
				return;
			}
			$.ajax({
				url: 'proc_competition.php',
				data: {
					method: 'set_date',
					comp_id: currentTargetId,
					date: temp
				},

				dataType: 'json',

				beforeSend: function () {
					jYear.addClass('loading');
					jMonth.addClass('loading');
					jDay.addClass('loading');
					jButton.addClass('disabled');
					jCancelButton.addClass('disabled');
				},
				
				success: function (data) {
					if (data.status == 'failed') {
						showErrorJSON(data);
					} else {
						jYear.removeClass('loading');
						jYear.addClass('loaded');
						jMonth.removeClass('loading');
						jMonth.addClass('loaded');
						jDay.removeClass('loading');
						jDay.addClass('loaded');

						jButton.removeClass('disabled');
						jCancelButton.removeClass('disabled');
						hideFields(data);
					}
				},

				error: errorInAjax
			});
		});

		jCancelButton.click(function() {
			hideFields();
		});
	},

	editCoef: function (o, compId, enabled) {
		var jObject = $(o);
		jObject.slideUp('fast', function() {
			var jPanel = $('<div/>').appendTo(jObject.parent());

			var jInput = $('<input/>', {
				id: 'edit_coef_input',
				type: 'text',
				value: jObject.text()
			}).appendTo(jPanel);

			var evalButton = new Button({
				container: jPanel,
				html: 'Рассчиать по формуле',
				CSSClass: 'edit_coef_button',
				onClick: function() {
					save('evaluate_coef');
				}
			});

			var saveButton = new Button({
				html: 'Сохранить',
				CSSClass: 'edit_coef_button',
				onClick: function() {
					save('set_coef');
				}
			});

			if (enabled) {
				saveButton.appendTo(jPanel);
			} else {
				jInput.attr('disabled', 'disabled');
			}

			var cancelButton = new Button({
				container: jPanel,
				html: 'Отмена',
				CSSClass: 'edit_coef_button',
				onClick: function() {
					hideFields();
				}
			});

			var hideFields = function(newCoef) {
				if (newCoef != undefined) {
					jInput.val(newCoef);
					jObject.text(newCoef);
				}
				jPanel.slideUp('fast', function() {
					jObject.slideDown('fast');
				});
			};

			var save = function(method) {
				$.ajax({
					url: 'proc_competition.php',
					data: {
						method: method,
						comp_id: compId,
						coef: jInput.val()
					},
					dataType: 'json',

					beforeSend: function() {
						evalButton.disable();
						saveButton.disable();
						cancelButton.disable();
						jInput.attr('disabled', 'disabled');
					},

					success: function(json) {
						if (json.status != 'ok') {
							showErrorJSON(json);
							evalButton.enable();
							saveButton.enable();
							cancelButton.enable();
							if (enabled)
								jInput.removeAttr('disabled');
						} else {
							hideFields(json.coef);
						}
					}
				});
			};

			jPanel.slideDown('fast');
		});
	},

	editDesc: function (o) {
		var jObject = $(o);
		var jContainer = jObject.parent();
		var previousValue = jObject.html();

		jObject.hide();

		var jEditDiv = $('<div/>').appendTo(jContainer);

		var jInput = $('<textarea/>', {
			name: 'description',
			value: previousValue == 'Создайте описание для этого турнира!' ? '' : previousValue
		}).focus()
		.appendTo(jEditDiv);

		var jButton = $('<input/>', {
			type: 'button',
			value: 'OK'
		}).appendTo(jEditDiv)
		.css({
			margin: '2px'
		});

		var jCancelButton = $('<input/>', {
			type: 'button',
			value: 'Cancel'
		}).appendTo(jEditDiv)
		.css({
			margin: '2px'
		});

		var hideFields = function(newValue) {
			if (newValue == '')
				newValue = 'Создайте описание для этого турнира!';
			jButton.hide();
			jCancelButton.hide();
			jInput.fadeOut(
				"slow",
				function () {
					if (newValue != undefined)
						jObject.html(newValue);
					jObject.fadeIn();
					jEditDiv.remove();
				}
			);
		};

		jButton.click(function () {
			previousValue = previousValue == 'Создайте описание для этого турнира!' ? '' : previousValue;
			if (previousValue != jInput.val()) {
				$.ajax({
					url: 'proc_competition.php',
					data: {
						method: 'set_description',
						comp_id: currentTargetId,
						description: jInput.val()
					},

					beforeSend: function () {
						jInput.addClass('loading');
						jButton.addClass('disabled');
						jCancelButton.addClass('disabled');
					},

					success: function (data) {
						jInput.removeClass('loading');
						jInput.addClass('loaded');
						jButton.removeClass('disabled');
						jCancelButton.removeClass('disabled');
						hideFields(data);
					},

					error: errorInAjax
				});
			} else {
				jInput.addClass('loaded');
				hideFields();
			}
		});

		jCancelButton.click(function() {
			hideFields();
		});
	},

	create: function (leagueId) {
		var jNameInput = $('input[name=name]');
		var jDescInput = $('textarea[name=description]');

		var competitionObject = {
			name: jNameInput.val(),
			description: jDescInput.val()
		}

		if (competitionObject.name.length == 0) {
			showError('Имя турнира не может быть пустым!');
			return;
		}

		if (competitionObject.description.length == 0) {
			showError('Описание турнира не может быть пустым!');
			return;
		}

		$.ajax({
			url: 'proc_competition.php',
			data: {
				method: 'is_such_competition',
				name: competitionObject.name
			},
			dataType: 'json',

			beforeSend: function () {
				jNameInput.addClass('loading');
				jDescInput.addClass('loading');
				createCompetitionButton.disable();
			},

			success: function (data) {
				if (data.status != 'ok' || !data.result) {
					jNameInput.removeClass('loading');
					jDescInput.removeClass('loading');
					createCompetitionButton.enable();
				}
				
				if (data.status != 'ok') {
					showErrorJSON(data);
				} else if (data.result) {
					showError('Турнир с таким именем уже существует!');
				} else {
					$.ajax({
						url: 'proc_league.php',
						data: {
							method: 'add_competition',
							name: jNameInput.val(),
							description: jDescInput.val(),
							league_id: leagueId
						},
						dataType: 'json',

						success: function (data) {
							if (data.status == 'failed') {
								showErrorJSON(data);
								jNameInput.removeClass('loading');
								jDescInput.removeClass('loading');
								createCompetitionButton.enable();
							} else {
								editCompetition(data.competition_id, reloadMenu);
								// FIXME the value of the address bar is not changed
							}
						},

						error: errorInAjax
					});
				}
			},

			error: errorInAjax
		});
	},

	loadData: function (method, id, i) {
		var m = method.split('_');
		var item = (i || m[1]);

		$.ajax({
			url: 'proc_competition.php',
			data: {
				method: method,
				comp_id: id
			},

			dataType: 'html',

			beforeSend: function () {
				loading(true, $('#content_body'));
			},

			success: function (data) {
				var content = $('#content_body');
				loading(false, content);
				content.html(data);

				$('#content_menu > ul > li').removeClass('selected');
				$('#content_menu_' + item).addClass('selected');
			},

			error: errorInAjax
		});
	},

	loadProperties: function (id) {
		this.loadData('load_properties', id);
	},

	loadStructure: function (id) {
		this.loadData('load_structure', id);
	},

	loadPlayers: function (id) {
		this.loadData('load_players', id);
	},

	loadZherebjator: function (id) {
		this.loadData('load_zherebjator', id, 'players');
	},

	loadGames: function (id) {
		this.loadData('load_games', id);
	},

	loadAdmins: function (id) {
		this.loadData('load_admins', id);
	},

	loadDeleteConfirmation: function (id) {
		this.loadData('load_delete_confirmation', id);
	},

	loadMonitoring: function (id) {
		this.loadData('load_monitoring', id);
	},

	loadGamesCup: function (cupid) {
		$.ajax({
			url: 'proc_competition.php',
			data: {
				method: 'load_games_cup',
				comp_id: currentTargetId,
				cup_id: cupid
			},
			dataType: 'html',

			beforeSend: function () {
				loading(true, $('#content_body'));
			},

			success: function (data) {
				var content = $('#content_body');
				loading(false, content);
				content.html(data);

				$('#content_menu > ul > li').removeClass('selected');
				$('#content_menu_games').addClass('selected');
			},

			error: errorInAjax
		});
	},

	loadPlayersCup: function (cupid) {
		$.ajax({
			url: 'proc_competition.php',
			data: {
				method: 'load_players_cup',
				comp_id: currentTargetId,
				cup_id: cupid
			},
			dataType: 'html',

			beforeSend: function () {
				loading(true, $('#content_body'));
			},

			success: function (data) {
				var content = $('#content_body');
				loading(false, content);
				content.html(data);

				$('#content_menu > ul > li').removeClass('selected');
				$('#content_menu_players').addClass('selected');
			},

			error: errorInAjax
		});
	},

	start: function () {
		$.ajax({
			url: 'proc_competition.php',
			data: {
				method: 'start',
				comp_id: currentTargetId
			},
			dataType: 'json',

			beforeSend: function () {
				loading(true, $('#competition_status'));
			},

			success: function (data) {
				if (data.status == 'failed') {
					showErrorJSON(data);
				} else {
					editCompetition(currentTargetId);
				}
			},

			error: errorInAjax
		});
	},

	startRegistering: function () {
		$.ajax({
			url: 'proc_competition.php',
			data: {
				method: 'start_registering',
				comp_id: currentTargetId
			},
			dataType: 'json',

			beforeSend: function () {
				loading(true, $('#competition_status'));
			},

			success: function (data) {
				if (data.status == 'failed') {
					showErrorJSON(data);
				} else {
					editCompetition(currentTargetId);
				}
			},

			error: errorInAjax
		});
	},

	stop: function (date, coef) {
		var request = function(useCurrentDate) {
			if (useCurrentDate) {
				var date = new Date();
				date = date.getFullYear() + '-' + date.getMonth() + '-' + date.getDate();
			}
			$.ajax({
				url: 'proc_competition.php',
				data: {
					method: 'stop',
					comp_id: currentTargetId,
					use_current_date: useCurrentDate == undefined || useCurrentDate == false ? 0 : 1,
					date: date
				},
				dataType: 'json',

				beforeSend: function () {
					loading(true, $('#competition_status'));
				},

				success: function (data) {
					if (data.status == 'failed') {
						loading(false, $('#competition_status'));
						showErrorJSON(data);
					} else {
						editCompetition(currentTargetId);
					}
				},

				error: errorInAjax
			});
		}

		if (coef < 0) {
			showError('Коэффициент этого турнира должен быть больше нуля. Продолжение невозможно!\nУстановите нормальный коэффициент, ёлы-палы!!');
		} else if (coef == 0 && confirm('Коэффициент этого турнира равен 0.\nЕсли вы его остановите, никто не получит очков\nВы уверены, что хотите продолжить?')) {
			if (date == '0000-00-00' && confirm('Вы не можете остановить данный турнир, так как дата завершения не установлена.\nВы хотите использовать в качестве неё сегодняшнюю дату?')) {
				request(true);
			} else if (date != '0000-00-00') {
				request();
			}
		} else if (coef > 0) {
			request()
		}
	},

	restart: function () {
		$.ajax({
			url: 'proc_competition.php',
			data: {
				method: 'restart',
				comp_id: currentTargetId
			},
			dataType: 'json',

			beforeSend: function () {
				loading(true, $('#competition_status'));
			},

			success: function (data) {
				if (data.status == 'failed') {
					showErrorJSON(data);
				} else {
					editCompetition(currentTargetId);
				}
			},

			error: errorInAjax
		});
	},

	deleteСompetition: function(comp_id) {
		$.ajax({
			url: 'proc_competition.php',
			data: {
				method: 'delete_competition',
				comp_id: comp_id
			},

			dataType: 'json',

			beforeSend: function() {
				$('#delete_competition_confirm').unbind('click', deleteCompConfirm);
				$('#delete_competition_cancel').unbind('click', deleteCompCancel);
			},

			success: function(data) {
				if(data.status == 'failed') {
					showErrorJSON(data);
					$('#delete_competition_confirm').click(deleteCompConfirm);
					$('#delete_competition_cancel').click(deleteCompCancel);
				} else {
					alert('Турнир успешно удалён!');
					$('#competition' + comp_id).fadeOut();
					editLeague(data.league_id);
					//TODO change the adress
					//document.URL = 'http://cupms.pipeinpipe/main.php#league/' + data.league_id;
				}
			}
		});
	},

	deleteAdmin: function(uid, compId) {
		$.ajax({
			url: 'proc_competition.php',
			data: {
				method: 'delete_admin',
				comp_id: compId,
				uid: uid
			},
			dataType: 'json',

			beforeSend: function() {
				peopleSelector.disable();
			},

			success: function(json) {
				peopleSelector.enable();
				if (json.status != 'ok') {
					showErrorJSON(json);
				} else {
//					$('#person_' + uid).fadeOut().remove();
					competition.loadAdmins(compId);
				}
			}
		});
	},

	makeAdmin: function(compId, uid, canDelete) {
		$.ajax({
			url: 'proc_competition.php',
			data: {
				method: 'make_admin',
				comp_id: compId,
				uid: uid
			},
			dataType: 'json',

			beforeSend: function() {
				peopleSelector.disable();
			},

			success: function(json) {
				peopleSelector.enable();
				if (json.status != 'ok') {
					showErrorJSON(json);
				} else {
//					PeopleListItem({
//						targetId: compId,
//						personId: uid,
//						onClick: competition.deleteAdmin,
//						personName: json.name,
//						canDelete: canDelete
//					});
					competition.loadAdmins(compId);
				}
			}
		});
	}
};
