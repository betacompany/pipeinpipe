/**
 * @author Innokenty Shuvalov
 */

var player = {
	fillById : function (pmid) {
		var nameField = $("#name");
		var surnameField = $("#surname");
		var emailField = $("#email");
		var countryField = $("#country");
		var cityField = $("#city");
		var descriptionField = $("#description");

		$.ajax({
			url: 'proc_players.php',
			data: {
				method: 'get_by_id',
				pmid : pmid
			},

			dataType: 'json',

			beforeSend: function () {
				player.loading();
			},

			success: function (pman) {
				nameField.val(pman.name);
				surnameField.val(pman.surname);
				countryField.val(pman.country);
				cityField.val(pman.city);
				emailField.val(pman.email);
				descriptionField.val(pman.description);
				genSelector.select(pman.gender);

				if (pman.userId > 0) {
					userSelector.select(pman.userId);
				} else {
					userSelector.val('');
					//TODO change userSelector's content
				}

				player.loaded();

				player.newPlayer = false;
			},

			error: function() {
				player.loaded();
				errorInAjax();
			}
		});
	},

	saveChanges: function () {
		var nameField = $("#name");
		var surnameField = $("#surname");
		var emailField = $("#email");
		var countryField = $("#country");
		var cityField = $("#city");
		var descriptionField = $("#description");

		$.ajax({
			url: 'proc_players.php',
			data: {
				method: 'save_changes',
				pmid: peopleSelector.val(),
				name: nameField.val(),
				surname: surnameField.val(),
				email: emailField.val(),
				country: countryField.val(),
				city: cityField.val(),
				description: descriptionField.val(),

				gender: genSelector.val(),
				user_id: userSelector.val()
			},

			dataType: 'json',

			beforeSend: function () {
				player.loading();
			},

			success: function (json) {
				player.loaded();
				if (json.status != 'ok') {
					showErrorJSON(json);
				}
			},

			error: function() {
				player.loaded();
				errorInAjax();
			}
		});
	},

	create : function() {
		var nameField = $("#name");
		var surnameField = $("#surname");
		var emailField = $("#email");
		var countryField = $("#country");
		var cityField = $("#city");
		var descriptionField = $("#description");

		$.ajax({
			url: 'proc_players.php',
			data: {
				method: 'create',
				name: nameField.val(),
				surname: surnameField.val(),
				email: emailField.val(),
				country: countryField.val(),
				city: cityField.val(),

				gender: genSelector.val(),
				description: descriptionField.val(),
				user_id: userSelector.val()
			},

			beforeSend : function() {
				player.loading();
			},

			success : function(json) {
				player.loaded();
				if (json.status != 'ok') {
					showErrorXML(json);
				} else {
					alert("новый пайп-мен успешно создан!");
					player.clearFields();
				}
			},

			error: function() {
				player.loaded();
				errorInAjax();
			}
		});
	},

	loading : function() {
		var nameField = $("#name");
		var surnameField = $("#surname");
		var emailField = $("#email");
		var countryField = $("#country");
		var cityField = $("#city");
		var descriptionField = $("#description");

		nameField.addClass('loading');
		surnameField.addClass('loading');
		emailField.addClass('loading');
		countryField.addClass('loading');
		cityField.addClass('loading');
		descriptionField.addClass('loading');

		userSelector.disable();
		playerSaveButton.disable();
		newPlayerButton.disable();
		genSelector.disable();
	},

	loaded : function() {
		var nameField = $("#name");
		var surnameField = $("#surname");
		var emailField = $("#email");
		var countryField = $("#country");
		var cityField = $("#city");
		var descriptionField = $("#description");

		nameField.removeClass('loading');
		surnameField.removeClass('loading');
		emailField.removeClass('loading');
		countryField.removeClass('loading');
		cityField.removeClass('loading');
		descriptionField.removeClass('loading');

		userSelector.enable();
		playerSaveButton.enable();
		newPlayerButton.enable();
		genSelector.enable();
	},

	clearFields : function() {
		var nameField = $("#name");
		var surnameField = $("#surname");
		var emailField = $("#email");
		var countryField = $("#country");
		var cityField = $("#city");
		var descriptionField = $("#description");

		nameField.val('');
		surnameField.val('');
		countryField.val('');
		cityField.val('');
		emailField.val('');
		descriptionField.val('');
		genSelector.select('m');

		player.newPlayer = true;
	},

	newPlayer: true,

	go: function () {
		if (player.newPlayer) {
			player.create();
		} else {
			player.saveChanges();
		}
	}
};