/**
 * @author Artyom Grigoriev
 */

/*
 * schema of pseudo-pathes:
 *	#league
 *		/new					- addLeague(anchor);
 *		/[league_id]
 *			/.					- editLeague(league_id, anchor);
 *			/new_competition	- addCompetition(league_id, anchor);
 *			/admins
 *				/.				- editLeague(league_id, anchor); league.loadAdmins(league_id);
 *				/add			- ???
 *	#competition
 *		/[competition_id]
 *			/.					- editCompetition(competition_id, anchor)
 *			/structure			- editCompetition(competition_id, anchor); competition.loadStrusture(competition_id)
 *			/players			- editCompetition(competition_id, anchor); competition.loadPlayers(competition_id)
 *			/zherebjator
 *			/games
 *				/.				- editCompetition(competition_id, anchor); competition.loadGames(competition_id)
 *				/[cup_id]		- ???
 *			/admins
 *				/.				- ???
 *				/add			- ???
 *	#players
 *		/.						- editPlayers(anchor)
 *		/[player_id]			- ???
 */

$(document).ready(function () {
	var url = document.URL;
	var sharpIndex = url.indexOf('#', 0);
	
	if (sharpIndex >= 0) {
		var anchor = url.substring(sharpIndex + 1, url.length);
		var path = anchor.split('/');
		var selector, j;

		if (path.length > 1) {
			selector = 'a[href=#' + path[0] + '/' + path[1] + ']';
			j = $(selector);
		} else {
			selector = 'a[href=#' + path[0] + ']';
			j = $(selector);
		}

		if (path[0] != '') {
			if (path[0] == 'competition') {
				listOpen(j.parent('li').parent('ul').parent('li'));
				editCompetition(path[1], j);
				if (path.length == 3) {
					if (path[2] == 'structure') {
						competition.loadStructure(path[1]);
					} else if (path[2] == 'players') {
						competition.loadPlayers(path[1]);
					} else if (path[2] == 'zherebjator') {
						competition.loadZherebjator(path[1]);
					} else if (path[2] == 'games') {
						competition.loadGames(path[1]);
					}
				}
			} else if (path[0] == 'league') {
				if (path[1] == 'new') {
					addLeague(j);
				} else {
					listOpen(j.parent('li'));
					if (path.length == 3) {
						if (path[2] == 'new_competition') {
							addCompetition(path[1], $(selector));
						} else if (path[2] == 'admins') {
							editLeague(path[1], j);
							league.loadAdmins(path[1]);
						}
					} else {
						editLeague(path[1], j);
					}
				}
			} else if (path[0] == 'players') {
				editPlayers(j);
			}
		}
	}

});