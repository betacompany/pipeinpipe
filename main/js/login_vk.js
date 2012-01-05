// social webs sign in
$(document).ready(function () {
	var host = location.host,
		first = host.substring(0, host.indexOf('.')),
		mobile = first == 'mobile' || first == 'm-dev' || first == 'm';

	try {
		VK.init({
			apiId: 1969436
		});

		function authInfo(response) {
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

						} else if (json.status == 'success' && !mobile) {
							try {
								VK.Api.call(
									'getProfiles',
									{
										format: 'JSON',
										uids: response.session.mid,
										fields: 'uid,first_name,last_name,bdate'
									},
									function (data) {
										var user = data.response[0];
										$.ajax({
											url: '/procs/proc_main.php',
											data: {
												method: 'get_users',
												vkid: user.uid,
												name: user.first_name,
												surname: user.last_name,
												birthdate: user.bdate
											},

											success: function (html) {
												if (html != "ololo") {
													$('#social_login_bar').html(html);
												} else {
													$.ajax({
														url: '/procs/proc_main.php',
														data: {
															method: 'quick_register'
														},
														dataType: 'json',

														success: function(json) {
															if (!json.status) {
																window.location = '/sign_up';
																return;
															}
															if (json.status == 'failed') {
																window.location = '/sign_up';
																return;
															}
															if (json.status == 'ok') {
																window.location.reload();
															}
														}
													});
												}
												
											},

											error: showError
										});
									}
								);
							} catch (e) {

							}
						} else if (json.status == 'full_success') {
							window.location.reload();
						}
					},

					error: function () {
						alert('error');
					}
				});
			} else {
				alert('not auth');
			}
		}

		$('#vk_login').click(function () {
			VK.Auth.login(authInfo);
		}).hover(
			function () {
				$(this).animate({opacity: 1}, 300);
			},
			function () {
				$(this).animate({opacity: .5}, 300);
			}
		);
	} catch (e) {
		
	}
});