var signUp = {

	prooved: {
		login: false,
		password2: false,
		name: false,
		surname: false,
		email: false
	},

	counters: {
		login: 0,
		password2: 0,
		name: 0,
		surname: 0,
		email: 0
	},

	proofInput: function (input, callback, hard) {
		var name = $(input).attr('name'),
			shname = name.substr(name.lastIndexOf('_') + 1),
			value = $(input).val();

		this.counters[shname]++;
		if (shname.match(/password/) || shname.match(/name/)) hard = true;
		if (this.counters[shname] < 5 && (hard == undefined || !hard)) return;

		debug(shname + ' ' + value);

		$(input).parent().parent().children('.notify').show();

		var result = false;
		if (shname == 'login' || shname == 'email') {
			$.ajax({
				url: '/procs/proc_sign_up.php',
				data: {
					method: 'proof_input',
					field_name: shname,
					field_value: value
				},
				dataType: 'json',

				success: function (json) {
					var result = (json.result == 'true');

					callback({
						result: result,
						name: shname
					});

					$(input).parent().parent().children('.notify').hide();
				}
			});
		} else if (shname == 'password2') {
			var pass1 = $('input[name=sign_up_password1]').val(),
				pass2 = $('input[name=sign_up_password2]').val();

			if (pass1 == pass2) result = true;
			if (pass1 == '') result = false;

			callback({
				result: result,
				name: shname
			});

			pass1.parent().parent().children('.notify').hide();
			pass2.parent().parent().children('.notify').hide();
		} else if (shname == 'name' || shname == 'surname') {
			result = ($('input[name=sign_up_' + shname + ']').val().length > 0);
			callback({
				result: result,
				name: shname
			});
			$(input).parent().parent().children('.notify').hide();
		}
	},

	proofAll: function () {
		var ok = true;
		for (var key in signUp.prooved) {
			if (!signUp.prooved[key]) {
				ok = false;
				$('input[type=sign_up_'+key+']').removeClass('wrong').addClass('ok');
				break;
			}
		}

		if (ok) {
			ge('sign_up_button').disabled = false;
		} else {
			ge('sign_up_button').disabled = true;
		}
	},

	proofResult: function (proof) {
		signUp.prooved[proof.name] = proof.result;
		debug(proof);
		if (proof.result) {
			$('.elem input[name=sign_up_'+proof.name+']').removeClass('wrong').addClass('ok');
			if (proof.name.match(/password/)) {
				$('.elem input[name=sign_up_password1]').removeClass('wrong').addClass('ok');
				$('.elem input[name=sign_up_password2]').removeClass('wrong').addClass('ok');
			}
		} else {
			$('.elem input[name=sign_up_'+proof.name+']').removeClass('ok').addClass('wrong');
			if (proof.name.match(/password/)) {
				$('.elem input[name=sign_up_password1]').removeClass('ok').addClass('wrong');
				$('.elem input[name=sign_up_password2]').removeClass('ok').addClass('wrong');
			}
		}
		this.counters[proof.name] = 0;
	},

	initDynamicSelectors: function () {
		var countrySelector = new DynamicSelector({
			content: [
				{id: 1, value: 'Россия'},
				{id: 0, value: 'другая...'}
			]
		});

		countrySelector.setWidth($('.elem input').width() + 4);
		countrySelector.appendTo($('#country'));

		var citySelector = new DynamicSelector({
			content: [
				{id: 1, value: 'Санкт-Петербург'},
				{id: 0, value: 'другой...'}
			]
		});

		citySelector.setWidth($('.elem input').width() + 4);
		citySelector.appendTo($('#city'));
	},

	initParams: function () {
		for (var key in getAnchorParams()) {
			var value = getAnchorParam(key);
			if (value != '' && value != null) {
				$('input[name=sign_up_'+key+']').val(value);
			}
		}
	},

	socialOver: function () {
		$(this).animate({opacity: 1}, 'fast');
	},

	socialOut: function () {
		$(this).animate({opacity: .5}, 'fast');
	},

	socialClick: function () {
		VK.Auth.login(signUp.vkAuthInfo);
	},

	initSocial: function () {
		$('#social_icons .elem > div').hover(
			signUp.socialOver,
			signUp.socialOut
		).click(
			signUp.socialClick
		);
	},

	vkAuthInfo: function (response) {
		if (response.session) {
			$.ajax({
				url: '/procs/proc_main.php',
				data: {
					method: 'login_vk'
				},
				dataType: 'json',

				success: function (json) {
					if (json == null) return;
					if (json.status == 'failed') {

					} else if (json.status == 'success') {
						$('.vk_icon_large').height(80);
						$('#social_icons').height(80);
						$('.vk_icon_large .social_id').html(response.session.mid);
						$('input[name=sign_up_vkid]').val(response.session.mid);

						$('#social_icons .elem > div')
							.unbind('mouseenter', signUp.socialOver)
							.unbind('mouseleave', signUp.socialOut)
							.unbind('click', signUp.socialClick)
							.css('opacity', 1);
					} else if (json.status == 'full_success') {
						window.location.reload();
					}
				},

				error: function () {
					signUp.vkError();
				}
			});
		} else {
			signUp.vkError();
		}
	},

	vkError: function () {

	},

	handleError: function (id, error) {
		var s = id.split('_'), inp;
		if (s.length > 1) {
			inp = $('input[name=sign_up_'+s[1]+']');
			if (s[1] == 'vkid') {
				inp = $('#social_icons');
			}
			offset = inp.offset().top;
				
			if (offset != null) {
				offset -= $('.right_column').offset().top;
				$('#error_box').css({
					marginTop: offset
				});
			}
		}

		$('#error_box').html(error.text);
		if (inp != undefined) inp.removeClass('ok').addClass('wrong');
		if (s.length > 1 && s[1] == 'password1') {
			$('input[name=sign_up_password2]').removeClass('ok').addClass('wrong');
		}
	}
}

$(document).ready(function () {
	$('.elem').append(
		$('<div/>').addClass('notify').hide()
	).find('input').keyup(function () {
		signUp.proofInput(this, function (proof) {
			signUp.proofResult(proof);
			signUp.proofAll();
		});
	}).blur(function () {
		signUp.proofInput(this, function (proof) {
			signUp.proofResult(proof);
			signUp.proofAll();
		}, true);
	});

	signUp.initDynamicSelectors();
	signUp.initSocial();
	signUp.initParams();

	$('#sign_up_button')
		.mouseover(signUp.proofAll)
		.click(function () {
			ge('sign_up_button').disabled = true;
			ge('sign_up').submit();
		});
});

