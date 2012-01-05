var profile = {

	save: function (key, value) {
		disabled[key] = true;
		$.ajax({
			url: '/procs/proc_main.php',
			data: {
				method: 'profile_update',
				key: key,
				value: value
			},
			dataType: 'json',
			cache: false,
			success: function (json) {
				disabled[key] = false;
				if (json.status && json.status == 'ok') {
					$('#input_'+key).val(json.value);
					$('#button_'+key).fadeOut();
					values[key] = json.value;
				}
			}
		});
	},

	photo: {
		__x: 0,
		__y: 0,
		__w: 100,
		__h: 100,

		init: function () {
			if ($('#photo_container img').attr('src').match(/default/)) {
				main.showErrorText('Сначала загрузите фотографию');
				return;
			}
			$('#photo_container').height(
				$('#photo_container img').height() + 4
			);
			$('#photo_border')
				.fadeIn()
				.draggable({
					containment: 'parent',
					stop: function () {
						var offset = $('#photo_border').offset(),
							poffset = $('#photo_container').offset();
						profile.photo.__x = offset.left - poffset.left;
						profile.photo.__y = offset.top - poffset.top;
					}
				});
			$('#photo_border')
				.resizable({
					minWidth: 100,
					minHeight: 100
				});
			$('#photo_mini').addClass('checked');
			$('#photo_save').fadeIn().one('click', null, function () {
				profile.photo.save();
			});
			if ($('#photo_container').offset().top < $(window).scrollTop()) {
				window.scrollTo(0, $('#photo_container').offset().top);
			}
		},

		disable: function () {
			$('#photo_mini').removeClass('checked');
			$('#photo_save').fadeOut();
			$('#photo_border').fadeOut();
		},

		save: function () {
			$.ajax({
				url: '/procs/proc_main.php',
				data: {
					method: 'miniature',
					x: profile.photo.__x,
					y: profile.photo.__y,
					w: profile.photo.__w,
					h: profile.photo.__h
				},
				dataType: 'json',
				success: function (json) {
					if (json.status && json.status == 'ok') {
						$('#img_small').attr('src', json.small);
						$('#img_supersmall').attr('src', json.supersmall);
						$('#photo_save').fadeOut();
						profile.photo.disable();
					}
				}
			});
		}
	}

};
