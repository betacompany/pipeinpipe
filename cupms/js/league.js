/**
 * @author Artyom Grigoriev
 */

var league = {
	create: function() {
		var jNameInput = $('input[name=name]');
		var jDescInput = $('textarea[name=description]');
		
		var leagueObject = {
			name: jNameInput.val(),
			description: jDescInput.val(),
			formula: formulaSelector.val()
		};

		if (leagueObject.name.length == 0) {
			showError('Имя лиги не может быть пустым!');
			return;
		}

		if (leagueObject.description.length == 0) {
			showError('Описание лиги не может быть пустым!');
			return;
		}

		$.ajax({
			url: 'proc_league.php',
			data: {
				method: 'is_such_league',
				name: leagueObject.name
			},

			dataType: 'json',

			beforeSend:  function () {
				jNameInput.addClass('loading');
				jDescInput.addClass('loading');
				formulaSelector.disable();
				createLeagueButton.disable();
			},

			success: function (data) {                               
				if (data) {
					showError('Лига с таким именем уже существует!');
					jNameInput.removeClass('loading');
					jDescInput.removeClass('loading');
					formulaSelector.enable();
					createLeagueButton.enable();
				} else if(!data){
					$.ajax({
						url: 'proc_league.php',
						data: {
							method: 'add_league',
							name: leagueObject.name,
							description: leagueObject.description,
							formula: leagueObject.formula
						},
						dataType: 'json',

						success: function (data) {
							if(data.status == 'failed') {
								showErrorJSON(data);
								jNameInput.removeClass('loading');
								jDescInput.removeClass('loading');
								formulaSelector.enable();
								return;
							}

							var li = $('<li/>')
							.addClass('unfolded')
							.appendTo($('#left_column > ul'));

							var a = $('<a/>', {
								href: '#league/' + data.id,
								text: data.name
							}).appendTo(li)
							.click(function () {
								editLeague(data.id, this);
							});

							var ul = $('<ul/>', {
								display: 'block'
							}).appendTo(li);

							var li_add = $('<li/>')
							.addClass('add')
							.appendTo(ul);

							var a_add = $('<a/>', {
								href: '#league/' + data.id + '/new_competition',
								text: 'Создать турнир'
							}).click(function() {
								addCompetition(data.id, this);
							}).appendTo(li_add);

							editLeague(data.id, a);
						},

						error: errorInAjax
					});
				} else {
					showError(data.msg);
					jNameInput.removeClass('loading');
					jDescInput.removeClass('loading');
					formulaSelector.enable();
					createLeagueButton.enable();
					return;
				}
			},

			error: errorInAjax
		});
		
	},

	loadData: function (method, id) {
		var m = method.split('_');
		var item = m[1];

		$.ajax({
			url: 'proc_league.php',
			data: {
				method: method,
				league_id: id
			},

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

	loadAdmins: function (id) {
		this.loadData('load_admins', id);
	},

	deleteAdmin: function(uid, leagueId) {
		$.ajax({
			url: 'proc_league.php',
			data: {
				method: 'delete_admin',
				league_id: leagueId,
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
					league.loadAdmins(leagueId);
				}
			}
		});
	},

	makeAdmin: function(leagueId, uid, canDelete) {
		$.ajax({
			url: 'proc_league.php',
			data: {
				method: 'make_admin',
				league_id: leagueId,
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
//						targetId: leagueId,
//						personId: uid,
//						onClick: league.deleteAdmin,
//						personName: json.name,
//						canDelete: canDelete
//					});
					league.loadAdmins(leagueId);
				}
			}
		});
	},

	editName: function (o) {
		var jContainer = $(o).parent();
		var previousValue = $(o).text();

		$(o).hide();

		var input = document.createElement('input');
			input.type = 'text';
			input.name = 'name';
			input.value = previousValue;

		var button = document.createElement('input');
			button.type = 'button';
			button.name = 'OK';
			button.value = 'OK';

		var CancelButton = document.createElement('input');
			CancelButton.type = 'button';
			CancelButton.name = 'Cancel';
			CancelButton.value = 'Cancel';

		var editDiv = document.createElement('div');
			editDiv.appendChild(input);
			editDiv.appendChild(button);
			editDiv.appendChild(CancelButton);
		jContainer.append(editDiv);
		input.focus();

		jContainer.data('generated', true);

		var jInput = jContainer.contents().find('input[name=name]');
		var jButton = jContainer.contents().find('input[name=OK]');
		var jCancelButton = jContainer.contents().find('input[name=Cancel]');

		jButton.click(function () {
			$.ajax({
				url: 'proc_league.php',
				data: {
					method: 'set_name',
					league_id: currentTargetId,
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
					jButton.hide();
					jCancelButton.removeClass('disabled');
					jCancelButton.hide();
					jInput.fadeOut(
						"slow",
						function () {
							$(o).html(data);
							$(o).fadeIn();
							reloadMenu();
							$('#content_header').html(data);
							$(editDiv).remove();
						}
					);
				},

				error: errorInAjax
			});
		});

		jCancelButton.click(function () {
			jButton.hide();
			jCancelButton.hide();
			jInput.fadeOut(
				"slow",
				function () {
					$(o).fadeIn();
					$(editDiv).remove();
				}
			);
		});
	},

	editDesc: function (o) {
		// Тут просто: textarea + button
		var jContainer = $(o).parent();
		var previousValue = $(o).html();

		$(o).hide();

		var editDiv = document.createElement('div');

		var input = document.createElement('textarea');
			input.name = 'description';
			input.value = previousValue;

		var button = document.createElement('input');
			button.type = 'button';
			button.name = 'OK';
			button.value = 'OK';

		var CancelButton = document.createElement('input');
			CancelButton.type = 'button';
			CancelButton.name = 'Cancel';
			CancelButton.value = 'Cancel';

		editDiv.appendChild(input);
		editDiv.appendChild(button);
		editDiv.appendChild(CancelButton);
		jContainer.append(editDiv);
		input.focus();

		jContainer.data('generated', true);

		var jInput = jContainer.contents().find('textarea');
		var jButton = jContainer.contents().find('input[name=OK]');
		var jCancelButton = jContainer.contents().find('input[name=Cancel]');

		jButton.click(function () {
			$.ajax({
				url: 'proc_league.php',
				data: {
					method: 'set_description',
					league_id: currentTargetId,
					description: jInput.val()
				},

				beforeSend: function () {
					jInput.addClass('loading');
					jButton.addClass('disabled');
					jCancelButton.addClass('disabled');
				},

				success: function (data) {
					var newValue = data;
					if (newValue == '')
						newValue = 'Создайте описание для этой лиги!';


					jInput.removeClass('loading');
					jInput.addClass('loaded');
					jButton.removeClass('disabled');
					jCancelButton.removeClass('disabled');
					jButton.hide();
					jCancelButton.hide();
					jInput.fadeOut(
						"slow",
						function () {
							$(o).html(newValue);
							$(o).fadeIn();
							$(editDiv).remove();
						}
					);
				},

				error: errorInAjax
			});
		});

		jCancelButton.click(function() {
			jButton.hide();
			jCancelButton.hide();
			jInput.fadeOut(
				"slow",
				function () {
					$(o).fadeIn();
					$(editDiv).remove();
				}
			);
		});
	},

	editFormula: function (o, leagueId, content) {
		var jObject = $(o);
		var jPanel = $('<div/>')
		.appendTo(jObject.parent())
		.hide();
		
		var jSelector = $('<div/>', {
			id: 'formula_selector'
		}).appendTo(jPanel);

		var formulaSelector = (new Selector({
			content: content,
			onSelect: function() {
				jSaveButton.enable();
			}
		}))
		.appendTo(jSelector)
		.select(jObject.text());
		
		var jSaveButton = new Button({
			html: 'OK',
			CSSClass: 'formula_selector_button',
			onClick: function() {
				setFormula();
			},
			container: jPanel
		});
		jSaveButton.disable()

		var jCancelButton = new Button({
			html: 'Cancel',
			CSSClass: 'formula_selector_button',
			onClick: function() {
				hideFields();
			},
			container: jPanel
		});
		
		var hideFields = function(newValue) {
			if (newValue != undefined)
				jObject.text(newValue);
			jPanel.slideUp('fast', function() {
				jObject.slideDown('fast');
			});
		};
		
		var setFormula = function() {
			$.ajax({
				url: 'proc_league.php',
				data: {
					method: 'set_formula',
					league_id: leagueId,
					formula: formulaSelector.val()
				},
				dataType: 'json',

				beforeSend: function() {
					formulaSelector.disable();
					jSaveButton.disable();
					jCancelButton.disable();
				},

				success: function(json) {
					if (json.status != 'ok') {
						showErrorJSON(json);
						formulaSelector.enable();
						jSaveButton.enable();
						jCancelButton.enable();
					} else {
						hideFields(json.formula);
					}
				},

				error: errorInAjax
			});
		}
		
		jObject.slideUp('fast', function() {
			jPanel.slideDown('fast');
		});
	}
};
