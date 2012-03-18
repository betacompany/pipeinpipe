/*
 * @author Innokenty Shuvalov
 */

var competition = {
	selectedCupId: 0,

	loadCup: function(cupId) {
        var ajax = function(method, panel) {
            $.ajax({
                url: '/procs/proc_sport_competition.php',
                data: {
                    method: method,
                    cup_id: cupId
                },

                beforeSend: function() {
                    loading(panel, true, undefined, undefined, true);
                },

                success: function(data) {
                    loading(panel, false, undefined, undefined, true);
                    panel.html(data);
                }
            });
        }

        ajax('load_cup', $('#competition_cup'));
        ajax('load_children', $('#competition_children_preview'));

        $('#cup' + competition.selectedCupId).removeClass('selected');
        $('#cup' + cupId).addClass('selected');
        competition.selectedCupId = cupId;
	},

	_highlightTracedGames: function(o) {
		var gameIds = $(o).data('traced_games');
		$('.game').css('opacity', '0.65');
		for (var i = 0; i < gameIds.length; i++) {
			$('#game' + gameIds[i]).css({
				'border-color': '#007ca7',
				'opacity': '1'
			});
		}
	},

	_downplayTracedGames: function(o) {
		var gameIds = $(o).data('traced_games');
		$('.game').css('opacity', '1');
		for (var i = 0; i < gameIds.length; i++) {
			$('#game' + gameIds[i]).css({
				'border-color': '#c7dce3'
			});
		}
	},

    _getGridCellsForPlayer: function(tableIndex, playerIndex, tableSize) {
        var result = $();
        for (var i = 1; i <= tableSize; i++) {
            result = result.add($('#grid_' + tableIndex + '_' + playerIndex + '_' + i));
            result = result.add($('#grid_' + tableIndex + '_' + i + '_' + playerIndex));
        }
        return result;
    },

	bindGridEvents: function(tableSize) {
		$('.competition_cup td, .competition_cup th').each(function() {
			var target = $(this);
            var id = target.attr('id').split('_');
			if (id.length != 4)
                return;

            //highlighting the row name
            target = target.add($('#grid_' + id[1] + '_' + id[2] + '_0'));

            //highlighting the column name
            if (id[1] != 2) {
                target = target.add($('#grid_' + id[1] + '_0_' + id[3]));
            }

            if (id[1] == 2) {
                //highlighting the second player or the matching player if the column's title is hovered
                target = target.add($('#grid_2_' + id[3] + '_0'));
                if (id[2] == 0) {
                    target = target.add(competition._getGridCellsForPlayer(id[1], id[3], tableSize))
                }
                if (id[3] == 0) {
                    target = target.add(competition._getGridCellsForPlayer(id[1], id[2], tableSize))
                }
            }

            $(this).hover(function() {
                target.toggleClass('hl');
            });
		});
	},

	showPlayOff: function(games, playoffHeight, playoffWidth, gameDivWidth, gameDivHeight) {
        const maxGameDivWidth = 230;
        const prefferedGameDivWidth = 190;

        var competitionCupBody = $('#competition_cup .body');
        var coef = (competitionCupBody.innerWidth() - 22) / playoffWidth; // paddings are 6px
        if (coef * gameDivWidth > maxGameDivWidth) {
            coef = prefferedGameDivWidth / gameDivWidth;
        }

		var playoffDiv = $('<div/>', {
			id: 'playoff',
			height: Math.round(playoffHeight * coef)
		}).appendTo(competitionCupBody);

        var options = competition._playOffConstructOptionsArray(gameDivWidth, gameDivHeight, coef);
		for (var i in games) {
			competition._playOffShowGame(games[i], playoffDiv, options);
		}
	},

    _playOffConstructOptionsArray: function (gameDivWidth, gameDivHeight, coef) {
        const scoreCoef = 1 / 10;
        const borderCoef = 1 / 60;
        const paddingCoef = 1 / 50;
        /**
         * если тут тоже поставить 10 то Гоша будет всегда влезать но зато всё будет некрасиво!!
         */
        const fontSizeCoef = 9;
        const goshaFontSizeCoef = 10;
        const gameTypeBackgroundSizeCoef = 1.9;

        var options = []
        options.coef = coef
        options.gameDivWidth = Math.round(gameDivWidth *= coef)
        options.gameDivHeight = Math.round(gameDivHeight *= coef)
        options.scoreDivWidth = Math.round(options.gameDivWidth * scoreCoef)
        options.borderWidth = Math.round(options.gameDivWidth * borderCoef)
        options.padding = Math.round(gameDivWidth * paddingCoef)
        options.nameDivWidth = gameDivWidth - options.scoreDivWidth - options.padding * 2
        options.fontSize = Math.round(options.nameDivWidth / fontSizeCoef)
        options.goshaFontSize = Math.round(options.nameDivWidth / goshaFontSizeCoef)
		options.pm1DivHeight = Math.round(options.gameDivHeight / 2 - options.padding)
		options.pm2DivHeight = Math.round(options.gameDivHeight / 2 - options.padding)
		options.popupDelta = Math.max(options.gameDivWidth / 2, 110)
        options.gameTypeBackgroundSizeCoef = gameTypeBackgroundSizeCoef
        return options
    },

    _playOffConstructGameTypeBackground: function(game) {
        var imgSrc = '/images/sport/';
        switch (game.type) {
            case competition._gameTypeTechnical:
                imgSrc += 'game_technical'; break;
            case competition._gameTypeFatality:
                imgSrc += 'game_fatality'; break;
            default:
                return;
        }
        imgSrc += '.png';

        return $('<img/>', {
                src: imgSrc
            }).addClass('sport_game_type');
    },

    _addGameTypeBackground: function (game, gameDiv, options) {
        var gameTypeBackground = competition._playOffConstructGameTypeBackground(game);
        gameTypeBackground && gameTypeBackground.load(function() {
            $(this).appendTo(gameDiv);
            var gameTypeBackgroundWidth = $(this).attr('width'),
                gameTypeBackgroundHeight = $(this).attr('height'),
                horizontalCoef = options.gameDivWidth / gameTypeBackgroundWidth,
                verticalCoef = options.gameDivHeight / gameTypeBackgroundHeight,
                coef = Math.min(horizontalCoef, verticalCoef) * options.gameTypeBackgroundSizeCoef;
            gameTypeBackgroundWidth = Math.round(gameTypeBackgroundWidth * coef);
            gameTypeBackgroundHeight = Math.round(gameTypeBackgroundHeight * coef);
            $(this).css({
                'width': gameTypeBackgroundWidth,
                'height': gameTypeBackgroundHeight,
                'margin-left': ((options.gameDivWidth - options.padding - gameTypeBackgroundWidth) / 2) + 'px',
                'margin-top': (-(options.gameDivHeight - options.padding + gameTypeBackgroundHeight) / 2) + 'px'
            })
        })
    },

	_playOffShowGame: function(game, playoffDiv, options) {
        var gameDiv = $('<div/>', {
			id: 'game' + game.id
		})
		.addClass('game round_border')
		.css({
			left: Math.round(game.left * options.coef),
			top: Math.round(game.top * options.coef),
			width: options.gameDivWidth + 'px',
			height: options.gameDivHeight + 'px',
			'border-width': options.borderWidth,
			'font-size': (game.victor && game.surnames[game.victor].length >= 13 ? options.goshaFontSize : options.fontSize) + 'px',
			'padding-left': options.padding,
			'padding-top': options.padding
		})
		.appendTo(playoffDiv);

        competition._addGameTypeBackground(game, gameDiv, options);
        competition._constructPmDiv(options, game, 1).appendTo(gameDiv);
        competition._constructPmDiv(options, game, 2).appendTo(gameDiv);

        var position = gameDiv.offset();
		competition._popUps[game.id] = (new PopUp({
			x: Math.round(position.left + options.popupDelta),
			y: position.top - 8,
			html: '',
			id: 'popup_game_' + game.id,
			recalcCoords: function (id) {
				var gida = id.split('_'),
					gid = gida[2],
					pos = $('#game'+gid).offset();
				return {
					x: Math.round(pos.left + options.popupDelta),
					y: pos.top - 8
				};
			}
		})).append(competition._constructPopUp(game));

		gameDiv
            .data('traced_games', game.tracedGames)
            .data('id', game.id).hover(function() {
                competition._popUps[$(this).data('id')].show();
            }, function() {
                competition._popUps[$(this).data('id')].hide();
            })
            .hover(function() {
                competition._highlightTracedGames(this);
            }, function() {
                competition._downplayTracedGames(this);
            });
	},

    _constructPmDiv: function(options, game, which) {
        var pmDiv = $('<div/>')
            .css({
                height: options.pm1DivHeight
            })
            .addClass(game.victor == which ? 'victor' : '');

        $('<div/>', {
            html: game.surnames[which] + '&nbsp;' + game.names[which].substring(0, 1) + '.'
        }).css({
            width: options.nameDivWidth + 'px'
        }).appendTo(pmDiv);

        $('<div/>', {
            text: game.scores[which]
        }).css({
            width: options.scoreDivWidth + 'px'
        }).appendTo(pmDiv);

        return pmDiv;
    },

	_popUps: [],

    _constructPopUpPlayer: function(gameData, which) {
        var pmLink = $('<a/>', {
            href: gameData.urls[which]
        });

        var pmDiv = $('<div/>')
            .addClass('round_border')
            .appendTo(pmLink);

        $('<img/>', {
            src: gameData.photos[which]
        })
            .appendTo(pmDiv);

        $('<div/>', {
            text: gameData.names[which] + '\n' + gameData.surnames[which]
        })
            .addClass('cup_pop_up_name')
            .appendTo(pmDiv);

        $('<div/>', {
            text: gameData.scores[which]
        })
            .addClass('cup_pop_up_score')
            .appendTo(pmDiv);

        return pmLink;
    },

	_constructPopUp: function(game) {
        var popUpDiv = $('<div/>').addClass('cup_pop_up');

        competition._constructPopUpPlayer(game, 1).appendTo(popUpDiv);
        competition._constructPopUpPlayer(game, 2).appendTo(popUpDiv);

        if (game.stage == 1) {
            var text = 'финал';
        } else if (game.stage == 3) {
            text = 'матч за III место';
        } else {
            text = '1/' + game.stage + ' финала';
        }

		$('<div/>', {
            text: text
        })
            .addClass('round_border cup_pop_up_stage_name')
            .appendTo(popUpDiv);

		return popUpDiv;
	},

	initRegistration: function(isRegistered) {
		competition._isRegistered = isRegistered;
	},

	_registeredText: 'Отменить заявку на турнир',
	_unregisteredText: 'Зарегистрироваться на турнир',
	_isRegistered: false,

	registration: function(compId) {
		$.ajax({
			url: '/procs/proc_sport_competition.php',
			data: {
				method: 'registration',
				comp_id: compId
			},

			dataType: 'json',

			success: function(json) {
				if (json.status != 'ok') {
					debug(json);
				} else {
					competition._isRegistered = !competition._isRegistered;
					$('#reg' + json.uid).fadeOut('slow', function() {
						$(this).remove();
					});
					if (json.html != '') {
						$('<div/>', {
							id: 'reg' + json.uid,
							html: json.html
						})
						.addClass('round_border')
						.hide()
						.appendTo($('#competition_registered'))
						.fadeIn('slow');
					}
				}

                registerButton.content(competition._isRegistered ? competition._unregisteredText : competition._registeredText);
			},

			error: debug
		})
	},

	loginOrRegisterPanel: function() {
		var margin = 20,

		loginOrRegisterPanel = $('#login_or_register_panel')
			.html('<p>Чтобы зарегистрироваться на турнир, необходимо</p>')
			.hide()
            .css('width', '100%'),

		options = {
			CSSClass: 'round_border',
			minOpacity: 0.7,
			css: {
				'width': 'auto',
				'background-color': '#007ca7',
				'color': 'white',
				'font-size': '1.3em',
				'padding': 9,
				'padding-top': 7,
				'text-align': 'center',
				'float': 'left',
				'margin-left': 0
			}
		},

		loginOptions = {
			html: 'Войти на сайт',
			onclick: function () {
                $('body').scrollTop();
                $('#sign_in_form input')
                    .css({
                        backgroundColor: COLOR.SECOND_LIGHT
                    })
                    .animate({
                        backgroundColor: '#ffffff'
                    }, 'slow');
                $('#sign_in_login').focus();
            }
		},

		signUpOptions = {
			html: 'Зарегистрироваться',
			href: '/sign_up?ret=' + encodeURI(window.location.pathname)
		};

		$.extend(loginOptions, options);
		$.extend(signUpOptions, options);

		new FadingButton(loginOptions).appendTo(loginOrRegisterPanel);

		$('<div/>', {
			html: 'или'
		}).css({
			'margin': margin,
			'padding-top': 6,
			'float': 'left'
		}).appendTo(loginOrRegisterPanel);

		new FadingButton(signUpOptions).appendTo(loginOrRegisterPanel);

        loginOrRegisterPanel.append('<div style="clear: both;"/>');

		loginOrRegisterPanel.slideDown('slow');
	},

    _gameTypeCommon: '0',
    _gameTypeTechnical: 't',
    _gameTypeDraw: 'd',
    _gameTypeFatality: 'f',

    _getTextForGameType: function (gameType) {
        switch (gameType) {
            case competition._gameTypeTechnical: return 'Технические победы';
            case competition._gameTypeDraw: return 'Технические ничьи';
            case competition._gameTypeFatality: return 'Победы по фаталити';
        }
    },


    showGameTypeExplanation: function(gameType) {
        var text = competition._getTextForGameType(gameType);
        if (!text) return;

        gameTypeDiv = $('<div/>')
            .addClass('sport_game_type_explanation')
            .appendTo($('#competition_cup_game_type_images'));

        $('<div/>')
            .addClass('round_border sport_game_type_' + gameType)
            .appendTo(gameTypeDiv);

        $('<span/>', {
            text: text
        })
            .appendTo(gameTypeDiv);
    }
}
