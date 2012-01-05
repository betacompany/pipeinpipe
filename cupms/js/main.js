/**
 * @author Artyom Grigoriev
 */

//
var currentTargetType = '';
var currentTargetId = 0;

function errorInAjax(request, status, errorThrown) {
	if (status == 'parseerror') {
		showError('Неправильный тип ответа.');
		return;
	}

    showError('Произошла ошибка загрузки страницы.');
}

/**
 * @param start
 * @param o
 */
function loading(start, o) {
	if (start) {
		o.children().remove();
		o.height(100);
		o.css('text-align', 'center');
		var img = document.createElement('img');
		img.src = 'images/loadingc.gif';
		img.style.marginTop = '60px';
		o.append(img);
	} else {
		o.css('height', 'auto');
		o.css('text-align', 'left');
		o.children().remove();
	}
}

var jSelected = null;      //it's jQuery object for League or Competition <a> tag

function editPlayers(e) {
	$.ajax({
		url: 'proc_players.php',
		data: {
			method: 'main_page'
		},

		beforeSend: function () {
			loading(true, $('#main'));
		},

		success: function (data) {
			loading(false, $('#main'));

			$('#main').html(data);

			if (jSelected != null) jSelected.removeClass('selected');

			$(e).addClass('selected');

			$('#save').click(player.create);
		},

		error: errorInAjax
	});
}

function editLeague(id, e) {
	$.ajax({
		url: 'proc_league.php',
		data: {
			method: 'main_page',
			league_id: id
		},

		beforeSend: function () {
			loading(true, $('#main'));
		},

		success: function (data) {
			loading(false, $('#main'));
                        
			$('#main').html(data);

			if (jSelected != null) jSelected.removeClass('selected');

			$(e).addClass('selected');
			jSelected = $(e);
			listOpen($(e).parent('li'));//when choosing league, its competitions slides down

			currentTargetType = 'league';
			currentTargetId = id;
		},

		error: errorInAjax
	});
}

function editCompetition(id, e) {
	$.ajax({
		url: 'proc_competition.php',
		data: {
			method: 'main_page',
			comp_id: id
		},

		beforeSend: function () {
			loading(true, $('#main'));
		},

		success: function (data) {
			loading(false, $('#main'));

			$('#main').html(data);

			if (jSelected != null) jSelected.removeClass('selected');

			if (e instanceof Function) {
				e();
			} else if (e != undefined) {
				$(e).addClass('selected');
				jSelected = $(e);
			}

			currentTargetType = 'competition';
			currentTargetId = id;
		},

		error: errorInAjax
	});
}

function addLeague(e) {
	$.ajax({
		url: 'proc_league.php',
		data: {
			method: 'add_league_page'
		},

		beforeSend: function () {
			loading(true, $('#main'));
		},

		success: function (data) {
			loading(false, $('#main'));

			$('#main').html(data);

			if (jSelected != null) jSelected.removeClass('selected');

			$(e).addClass('selected');
			jSelected = $(e);
			listOpen($(e).parent('li'));//when choosing league, it's competitions slide down

			currentTargetType = 'league';
			currentTargetId = 0;
		},
		
		error: errorInAjax
	});
}

function addCompetition(league_id, e) {
	$.ajax({
		url: 'proc_competition.php',
		data: {
			method: 'add_competition_page',
			league_id: league_id
		},

		beforeSend: function () {
			loading(true, $('#main'));
		},

		success: function (data) {
			loading(false, $('#main'));

			$('#main').html(data);

			if (jSelected != null) jSelected.removeClass('selected');

			$(e).addClass('selected');
			jSelected = $(e);

			currentTargetType = 'league';
			currentTargetId = league_id;
		},
		
		error: errorInAjax
	});
}


function loadAdmins(e) {
	$.ajax({
		url: 'proc_main.php',
		data: {
			method: 'load_admins'
		},

		beforeSend: function () {
			loading(true, $('#main'));
		},

		success: function (data) {
			loading(false, $('#main'));

			$('#main').html(data);

			if (e != undefined) {
				if (jSelected != null) jSelected.removeClass('selected');

				$(e).addClass('selected');
				jSelected = $(e);
			}

			currentTargetType = '';
			currentTargetId = 0;
		},

		error: errorInAjax
	});
}

/**
 * This function reloads side menu
 * @author Andrew Solozobov
 */
function reloadMenu() {
	var unfolded = new Array();
	$.ajax({
		url: 'proc_main.php',
		data: {
			method: 'side_menu'
		},

		beforeSend: function () {
			$('#left_column > ul > li.unfolded').each(
				function (index, value){
					unfolded[unfolded.length] = $(value).attr('id');
				}
			);

			loading(true, $('#left_column'));
		},

		success: function (data) {
			loading(false, $('#left_column'));
			$('#left_column').html(data);

			if (currentTargetType != null) jSelected=$('#'+currentTargetType+currentTargetId+' > a').addClass('selected');
			li = $('#left_column > ul > li.folded');
			for (i = 0; i < unfolded.length; i++){
				li.filter('[id='+unfolded[i]+']').removeClass('folded').addClass('unfolded');
			}
			
		},

		error: errorInAjax
	});
}

/**
 * Slides toggle unordered list
 * @param <jQuery object> li - <li> that may hide another <ul> inside
 * @author Andrew Solozobov
 */
function listSlideToggle(li) {
	if (li.children('ul').size() > 0){
		li.children('ul').slideToggle('slow');
		if(li.hasClass('folded')){
			li.removeClass('folded').addClass('unfolded');
		}
		else{
			li.removeClass('unfolded').addClass('folded');
		}
	}
}


/**
 * function tries to open given list if it's possible
 * @param <jQuery object> li - <li> that may hide another <ul> inside
 * @author Andrew Solozobov
 */
function listOpen(li){
	if (li.children('ul').size() > 0){
		li.children('ul').slideDown('slow');
		if(li.hasClass('folded')){
			li.removeClass('folded');
			li.addClass('unfolded');
		}
	}
}

function deleteAdmin(uid) {
	alert("this action is not supported.");
}

function makeAdmin(uid, canDelete) {
	$.ajax({
		url: 'proc_main.php',
		data: {
			method: 'make_admin',
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
//				PeopleListItem({
//					personId: uid,
//					onClick: deleteAdmin,
//					personName: json.name,
//					canDelete: canDelete
//				});
				loadAdmins();
			}
		}
	});
}

function debug(msg) {
	try {
		console.debug(msg);
	} catch (e) {}
}
