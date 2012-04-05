var cup = {
	createCup: function(compId, parentCupId, name, type, onSuccess) {
		if (parentCupId != 0 && ( (name.length == 0) || (name == 'Введите название для турнира')) )
			alert('Имя турнира не может быть пустым!');
		else {
			$.ajax({
				url: 'proc_cup.php',
				data: {
					method: 'is_correct_name',
					comp_id: compId,
					name: name
				},

				dataType: 'json',

				beforeSend: function() {
					// TODO unbind events
				},

				success: function(data) {
					if (!data.result) {
						alert('Подтурнир с таким именем уже существует в этом турнире!');
						// TODO bind events
					} else {
						$.ajax({
							url: 'proc_cup.php',
							data: {
								method: 'is_correct_type',
								type: type
							},

							dataType: 'json',

							success: function(data) {
								if (!data.result) {
									alert('Тип турнира введён неверно!');
									// TODO bind events
								} else {
									$.ajax({
										url: 'proc_cup.php',
										data: {
											method: 'create',
											type: type,
											name: name,
											comp_id: compId,
											parent_cup_id: parentCupId
										},

										dataType: 'json',

										success: function(response) {
											if (response.status == 'failed') {
												showErrorJSON(response);
												// TODO bind events
											} else {
												onSuccess();
												competition.loadStructure(compId);
											}
										}
									});
								}
							}
						});
					}
				}
			});
		}
	},

	/**
	 * Removes cup with such id
	 * Before removing it requires approvement
	 * @param o
	 * @param cup_id
	 */
	remove: function (cup_id, isTopLevelCup, o) {
		if (confirm('Вы что, действительно хотите удалить этот ' + (isTopLevelCup ? '' : 'под') + 'турнир?!')) {
			$.ajax({
				url: 'proc_cup.php',
				data: {
					method: 'remove',
					cup_id: cup_id
				},

				dataType: 'json',

				beforeSend: function () {

				},

				success: function (data) {
					if (data.status == 'failed') {
						showErrorJSON(data);
					} else {
//						alert('Турнир успешно удалён!');
						if(o == undefined)
							o = 'cup_' + cup_id;
						$('#' + o).fadeOut('normal', function() {
							if ($(this).parent().children().length == 1) {
								$(this).parent().parent().removeClass('unfolded').addClass('leaf');
								$(this).parent().remove();
							}
						});
					}
				}
			});
		}
	},

	editName: function (o, cup_id, options) {
		var jContainer = $(o);

        var jEditDiv = $('<div/>')
            .addClass('cup_edit')
            .hide()
            .insertAfter(jContainer);

		var jPreviousNameDiv = $('.cup_name', jContainer);
		var jPreviousMultDiv = $('.cup_mult', jContainer);
        var jPreviousSubCupsMultDivs = $('> ul .cup_mult', jContainer.parent());

        if (options.nameEditable) {
            var previousName = jPreviousNameDiv.text().trim(),
                nameObject = cup._constructStructureDetailsInput(jEditDiv, {
                    previousValue: previousName,
                    label: 'Имя турнира',
                    inputWidth: 150
                });
        }
        if (options.cupMultEditable) {
            var previousCupMult = jPreviousMultDiv.text().trim(),
                cupMultObject = cup._constructStructureDetailsInput(jEditDiv, {
                    previousValue: previousCupMult,
                    label: 'Мультипликатор для турнира 0 < x < 8',
                    inputWidth: 40
                });
        }
        if (options.subCupsMultEditable) {
            var previousSubCupsMult = jPreviousSubCupsMultDivs.first().text(),
                subCupMultObject = cup._constructStructureDetailsInput(jEditDiv, {
                    previousValue: previousSubCupsMult,
                    label: 'Мультипликатор для всех подтурниров 0 < x < 8',
                    inputWidth: 40
                });
        }
        if (options.cupMultEditable && options.subCupsMultEditable) {
            $('<div/>', {
                text: 'Не забудьте, что сумма этих мультипликаторов должна быть равна 8!'
            })
                .addClass('cup_notification')
                .appendTo(jEditDiv);
        }

		var buttonWidth = 69;
		var jOkButton = $('<input/>', {
			type: 'button',
			value: 'OK',
			width: buttonWidth
		})
            .appendTo(jEditDiv)
            .css({
                'margin-right': '6px',
                'margin-top': '6px'
            });

        var jCancelButton = $('<input/>', {
            type: 'button',
            value: 'Cancel',
            width: buttonWidth
        })
            .appendTo(jEditDiv);

        jEditDiv.children().each(function() {
			$(this)
                .css({
                    'float': 'none'
                })
                .children('input').each(function() {
                    $(this).css({
                        'margin-left': '8px'
                    });
                });
		});

		var animationSpeed = "fast";
		var hideAll = function(name, mult, subCupsMult) {
			jPreviousNameDiv.html(name);
			jPreviousMultDiv.html(mult);
            jPreviousSubCupsMultDivs.html(subCupsMult);
			jEditDiv.fadeOut(
				animationSpeed,
				function () {
					jContainer.fadeIn(animationSpeed);
					jEditDiv.remove();
				}
			);
		};

		var hideAllLoaded = function(cupName, mult, subCupsMult) {
			nameObject && nameObject.loaded();
            subCupMultObject && subCupMultObject.loaded();
            cupMultObject && cupMultObject.loaded();
            jEditDiv.addClass('loaded');
			hideAll(cupName, mult, subCupsMult);
		}

		var sendData = function(newName, newCupMult, newSubCupsMult) {
			$.ajax({
				url: 'proc_cup.php',
				data: {
					method: 'set_name_and_mult',
					cup_id: cup_id,
					name: newName,
					cup_mult: newCupMult,
					sub_cups_mult: newSubCupsMult
				},
				beforeSend: function () {
					jEditDiv.addClass('loading');
					jOkButton.addClass('disabled');
					jCancelButton.addClass('disabled');
				},
				dataType: 'json',
				success: function (data) {
					jEditDiv.removeClass('loading');
					if (data.status == undefined || data.status == 'failed') {
						showErrorJSON(data);
					} else {
						hideAllLoaded(newName, newCupMult, newSubCupsMult);
					}
				},
				error: errorInAjax
			});
		}

		jOkButton.click(function () {
            var newName = nameObject ? nameObject.getValue() : undefined;
            var newCupMult = cupMultObject ? cupMultObject.getValue() : undefined;
            var newSubCupsMult = subCupMultObject ? subCupMultObject.getValue() : undefined;
			if ((newName && newName !== previousName) ||
                (newCupMult && newCupMult !== previousCupMult) ||
                (newSubCupsMult && newSubCupsMult !== previousSubCupsMult)) {

				sendData(newName, newCupMult, newSubCupsMult);
			} else {
				hideAllLoaded();
			}
		});

		jCancelButton.click(function () {
			hideAll()
		});

		jContainer.fadeOut(animationSpeed, function() {
			jEditDiv.fadeIn(animationSpeed);
			if (nameObject) {
                nameObject.focus();
            } else if (cupMultObject) {
                cupMultObject.focus();
            } else if (subCupMultObject) {
                subCupMultObject.focus();
            }
		});
	},

    _constructStructureDetailsInput: function(jBlockToAppend, options) {
        var jDiv = $('<div/>', {
			text: options.label//'Мультипликатор для всех подтурниров 0 < x < 8'
		}).appendTo(jBlockToAppend);

		var jInput = $('<input/>', {
			type: 'text',
			value: options.previousValue,
			width: options.inputWidth
		}).appendTo(jDiv);

        return {
            loaded: function() {
                jInput.addClass('loaded');
            },
            getValue: function() {
                var value = jInput.val();
                return value ? value : undefined;
            },
            focus: function() {
                jInput.focus();
            }
        }
    },

	addPlayer: function(cup_id, pmid) {
		$.ajax({
			url: 'proc_cup.php',
			data: {
				method: 'add_player',
				cup_id: cup_id,
				pmid: pmid
			},

			beforeSend: function() {
				peopleSelector.disable();
			},

			dataType: 'json',

			success: function(data) {
				peopleSelector.enable();

				if (data.status == 'failed') {
					showErrorJSON(data);
				} else {
//					PeopleListItem({
//						targetId: cup_id,
//						personId: pmid,
//						personName: data.name,
//						onClick: cup.removePlayer
//					});
					competition.loadPlayersCup(cup_id);
				}
			}
		});
	},

	removePlayer: function(pmid, cup_id) {
		$.ajax({
			url: 'proc_cup.php',
			data: {
				method: 'remove_player',
				cup_id: cup_id,
				pmid: pmid
			},

			dataType: 'json',

			success: function(data) {
				if (data.status == 'failed') {
					showErrorJSON(data);
				} else {
//					$('#person_' + pmid).fadeOut('slow');
					competition.loadPlayersCup(cup_id);
				}
			}
		});
	},

	createPlayoffGame: function (game) {
		$.ajax({
			url: 'proc_cup.php',
			data: {
				method: 'edit_game',
				cup_id: game.cup_id,
				parent_game_id: game.parent_id,
				stage: game.stage,
				is_left: game.is_left
			},

			dataType: 'json',

			success: function (json) {
				if (json.status != 'ok') {
					showErrorJSON(json);
				} else {
					competition.loadGamesCup(game.cup_id);
				}
			},

			error: errorInAjax
		});
	},
	
	editPlayoffGame: function (game_id) {
		$.ajax({
			url: 'proc_cup.php',
			data: {
				method: 'get_game_data',
				game_id: game_id
			},

			beforeSend: function () {
//				loading(true, $('.playoff'));
			},

			dataType: 'json',

			success: function (data) {
				if (data.status != 'ok') {
					showErrorJSON(data);
				} else {
					if (data.pmid1 != 0)
						playerSelector1.select(data.pmid1);
					else
						playerSelector1.clear();

					if (data.pmid2 != 0)
						playerSelector2.select(data.pmid2);
					else
						playerSelector2.clear();

					var jScoreField1 = $('#score_1').val(data.score1);
					var jScoreField2 = $('#score_2').val(data.score2);

					$('#save').click(function () {
						$(this).unbind('click');
						cup.savePlayOffGame(game_id,
											jScoreField1.val(),
											jScoreField2.val(),
											playerSelector1.val(),
											playerSelector2.val());
					});

//					loading(false, $('.playoff'));
					$('#edit_match_panel').slideDown('fast');
				}
			},

			error: errorInAjax
		});
	},

    _gameTypeCommon: '0',
    _gameTypeTechnical: 't',
    _gameTypeDraw: 'd',
    _gameTypeFatality: 'f',
    _unusualGameTypes: null,
    _initUnusualGameTypes: function() {
        cup._unusualGameTypes = [cup._gameTypeDraw, cup._gameTypeFatality, cup._gameTypeTechnical];
    },

    _getScoresForGivenValues: function(scores, maxPossibleScore) {
        if (!cup._unusualGameTypes) {
            cup._initUnusualGameTypes();
        }

        for (var i in scores) {
            var type = scores[i];
            if ($.inArray(type, cup._unusualGameTypes) != -1) {
                return {
                    score1: i == 0 || type == cup._gameTypeDraw ? maxPossibleScore : 0,
                    score2: i == 1 || type == cup._gameTypeDraw ? maxPossibleScore : 0,
                    type: type
                }
            }
        }

        return {
            score1: scores[0],
            score2: scores[1],
            type: cup._gameTypeCommon
        }
    },

	savePlayOffGame: function(game_id, score1, score2, pmid1, pmid2) {
        var data = {
            method: 'edit_game',
            game_id: game_id,
            pmid1: pmid1,
            pmid2: pmid2
        };
        $.extend(data, cup._getScoresForGivenValues([score1, score2], 10));

        $.ajax({
			url: 'proc_cup.php',
			data: data,

			beforeSend: function() {
			//	loading(true, $('.playoff'));
			},

			dataType: 'json',

			success: function(data) {
			//	loading(false, $('.playoff'));
				if (data.status == 'ok') {
					cup.hideEditingPanel();
					var gameDiv = $('#game_' + game_id),
                        name1 = !data.name1 ? 'не задан' : data.name1,
                        name2 = !data.name2 ? 'не задан' : data.name2,
                        score1 = data.score1,
                        score2 = data.score2;
					$('> div:first-child > div:first-child', gameDiv).text(name1);
					$('> div:first-child > div:last-child', gameDiv).text(score1);
                    $('> div:last-child > div:first-child', gameDiv).text(name2);
                    $('> div:last-child > div:last-child', gameDiv).text(score2);
				} else {
					showErrorJSON(data);
				}
			}
		});
	},

	saveRegGame: function(which, gameId, jScoreBox1, jScoreBox2, cupId, pmid1, pmid2) {
        var data = {
            method: 'edit_game'
        };

        $.extend(data,
            gameId ? {game_id: gameId} : {
                cup_id: cupId,
                pmid1: pmid1,
                pmid2: pmid2
            }
        );

        $.extend(data, cup._getScoresForGivenValues([jScoreBox1.val(), jScoreBox2.val()], 5));

        $.ajax({
			url: 'proc_cup.php',
			data: data,

			beforeSend: function() {
				jScoreBox1.addClass('loading');
				jScoreBox2.addClass('loading');
			},

			dataType: 'json',

			success: function(data) {
				jScoreBox1.removeClass('loading');
				jScoreBox2.removeClass('loading');

				if (data.status != 'ok') {
					showErrorJSON(data);
				}
			}
		});
	},

	hideEditingPanel: function() {
		$('#edit_match_panel').slideUp('fast');
		$('#save').unbind();
	},

	editRegularGame: function(input, which, gameId, cupId, pmid1, pmid2) {
		var jScoreBoxCurrent = $(input),
			jTd = jScoreBoxCurrent.parent(),
			jScoreBox1 = jTd.find('input:first-child'),
			jScoreBox2 = jTd.find('input:last-child'),
			prevScore1 = jScoreBox1.val(),
			prevScore2 = jScoreBox2.val();

			jScoreBox2.css('text-align', 'right');
			jScoreBoxCurrent
				.css('border', '1px solid #000')
				.focusout(function() {
					jScoreBoxCurrent.css('border', 'none').unbind('focusout');
					jScoreBox2.css('text-align', 'left');

					var newScore1 = jScoreBox1.val();
					var newScore2 = jScoreBox2.val();
					if ((prevScore1 != newScore1 || prevScore2 != newScore2) && newScore1 != '' && newScore2 != '') {
						if (prevScore1 != '' && prevScore2 != '') {
							cup.saveRegGame(which, gameId, jScoreBox1, jScoreBox2);
						} else {
							cup.saveRegGame(which, null, jScoreBox1, jScoreBox2, cupId, pmid1, pmid2)
						}
					}
				});
	},
	
	scoreRegExp: '((1|2|3|4|5|6|7|8|9|0)*)|f|t|F|T',
	
	isValidScore: function(score) {
		var match = score.match(cup.scoreRegExp);
		return match[0] == score;
	},

	recalcResultTable: function(cupId) {
		$.ajax({
			url: 'proc_cup.php',
			data: {
				method: 'recalc_result_table',
				cup_id: cupId
			},

			dataType: 'json',

			success: function(json) {
				if (json.status != 'ok')
					showErrorJSON(json);
				else {
					competition.loadGamesCup(json.cup_id);
				}
			}
		});
	},

	createStages: function(cupId) {
		$.ajax({
			url: 'proc_cup.php',
			data: {
				method: 'create_stages',
				max_stage: $('#maxstage').val(),
				cup_id: cupId
			},

			dataType: 'json',

			success: function(json) {
				competition.loadGamesCup(cupId);
			}
		});
	}
}
