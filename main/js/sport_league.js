/* 
 * @author Innokenty Shuvalov
 */

var league = {
	loadCompetitionsPage: function(page, leagueId) {
		var container = $('#league_competitions');
		var l;
		$.ajax({
			url: '/procs/proc_sport_league.php',
			data: {
				method: 'load_competitions_page',
				page: page,
				league_id: leagueId
			},

			beforeSend: function() {
				l = loading(ge('league_competitions'), true, undefined, 100);
			},

			success: function(data) {
				l.remove();
				container.html(data);
			}
		});
	},

	loadPipemenPage: function(page, leagueId) {
		var container = $('#league_rating');
		var l;
		$.ajax({
			url: '/procs/proc_sport_league.php',
			data: {
				method: 'load_pipemen_page',
				page: page,
				league_id: leagueId
			},

			beforeSend: function() {
				l = loading(ge('league_rating'), true, undefined, 100);
			},

			success: function(data) {
				l.remove();
				container.html(data);
			}
		});
	},

	_popUps: [],

	showCompetitionPopUp: function(compId) {
		if (league._popUps[compId] != undefined) {
			var position = $('comp' + compId).offset();
			league._popUps[compId] = (new PopUp({
				x: position.left + 27,
				y: position.top - 8,
				html: ''
			}).append(league._constructPopUp(compId)))
		}
	},

	_constructPopUp: function(compId) {
		$.ajax({
			url: '/procs/proc_sport_league.php',
			data: {
				method: 'get_comp_data',
				comp_id: compId
			},

			dataType: 'json',

			success: function(json) {
				var popUp = $('<div/>').addClass('competition_pop_up');

				$('<img/>', {
					src: json.image,
					alt: json.name
				}).css({
					height: 100
				}).appendTo(popUp)

				$('<div/>').addClass('competition_pop_up_name').appendTo(popUp);

				var table = $('<table/>').addClass('competition_table').appendTo(popUp);
				$('<tbody/>').appendTo(table);
			}
		})
	}
}
