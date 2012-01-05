/**
 * @author Artom Grigoriev
 */

var main = {

	photobg: {
		randomize: function (onSuccess) {
			//debug('[main.js] randomization started');
			api.request({
				handler: 'photobg',
				method: 'get',
				success: function (json) {
					//debug(json);
					if (onSuccess != undefined) onSuccess();					
					var index = Math.floor(json.images.length * Math.random()), i = 0;

					while (json.images[index].url == getCookie('photobg_url') && i < 10) {
						index = Math.floor(json.images.length * Math.random());
						i++;
					}

					var image = json.images[index],
						j = $('<div/>')
								.addClass('photobg')
								.css('max-width', image.w + 'px')
								.hide()
								.append(
									$('<img/>').attr('src', image.url)
								);

					if (image.right_side == '1') j.addClass('right');
					
					//debug(image);
					$('#layout').append(j);
					j.fadeIn();

					setCookie('photobg_url', image.url, 1);
				}
			});
		}
	},

	showErrorText: function (text) {
		$('#error_box').html(text).slideDown().delay(5000).slideUp();
	}
};

$(main.photobg.randomize);