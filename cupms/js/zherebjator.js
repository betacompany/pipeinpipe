var zh = {

	_compId: 0,
	getRegistered: function () {
		$.ajax({
			url: 'proc_zh.php',
			data: {
				method: 'get_registered',
				comp_id: zh._compId
			},
			dataType: 'json',
			success: function (json) {
				debug(json);
				zh.drawRegistered(json.response);
			}
		});
	},

	drawRegistered: function (a) {
		$('#reg_table > tbody').html('');
		var c = a.length;
		for (var i in a) {
			var reg = a[i];
			debug(reg);
			$('#reg_table > tbody').append(
				$('<tr/>')
					.html('\
					<td>'+c+'</td>\
					<td>'+reg.user.id+'</td>\
					<td>'+reg.user.name+'</td>\
					<td>'+reg.user.surname+'</td>\
					<td>'+reg.player.id+'</td>\
					<td>'+reg.player.name+'</td>\
					<td>'+reg.player.surname+'</td>\
					<td id="c_'+c+'"></td>\
					')
			);
			var del = new FadingImage({
				CSSClass: 'fading_image',
				onclick: function(e) {
					var p = $(e.target).parent().parent().find('td');
					zh.remove($(p.get(1)).text(), $(p.get(4)).text());
					e.stopPropagation();
				}
			}).appendTo($('#c_'+c), $('#c_'+c).parent());
			c--;
		}
	},

	_selected: false,
	selected: function (id, ds) {
		this._selected = id;
		this._text = ds.text();
		$('#selected').html(zh._text + ' (' + (zh._selected ? zh._selected : 'новый') + ')');
	},

	register: function () {
		if (!zh._selected) {
			var name = zh._text.split(' ')[0];
			var surname = zh._text.split(' ')[1];
			var ok = confirm('Вы действительно хотите зарегистрировать нового пайпмена:\nимя='+name+', фамилия='+surname+'?');
			if (ok) {
				$.ajax({
					url: 'proc_zh.php',
					data: {
						method: 'register_new',
						comp_id: zh._compId,
						name: name,
						surname: surname
					},
					dataType: 'json',
					success: function (json) {
						zh.getRegistered();
						peopleSelector.clear();
						zh.selected(false, peopleSelector);
					}
				});
			}
		} else {
			$.ajax({
				url: 'proc_zh.php',
				data: {
					method: 'register',
					comp_id: zh._compId,
					pmid: zh._selected
				},
				dataType: 'json',
				success: function (json) {
					zh.getRegistered();
					peopleSelector.clear();
					zh.selected(false, peopleSelector);
				}
			});
		}
	},

	remove: function (uid, pmid) {
		var ok = confirm('Вы действительно хотите удалить uid='+uid+', pmid='+pmid);
		if (ok) {
			$.ajax({
				url: 'proc_zh.php',
				data: {
					method: 'remove',
					comp_id: zh._compId,
					pmid: pmid,
					usid: uid
				},
				dataType: 'json',
				success: function (json) {
					zh.getRegistered();
					peopleSelector.clear();
					zh.selected(false, peopleSelector);
				}
			});
		}
	}
};
