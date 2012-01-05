/**
 * @author Artyom Grigoriev
 */

var life = {
	__selected_blog: 0,
	__selected_tag: 0,
	__selected_post: 0,
	__posts_from: 0,
	__settings: {
		limit: 10
	},
	__disabled: false,

	showBlog: function (blogId) {
		$('#blog_' + this.__selected_blog).removeClass('selected');
		$('#tag_' + this.__selected_tag).removeClass('selected');
		$('#blog_' + blogId).addClass('selected');

		life.__selected_blog = blogId;
		life.__selected_tag =  0;
		life.__posts_from = 0;
		life.__disabled = false;
		life.loadPosts();
	},

	showTag: function (tagId) {
		$('#blog_' + this.__selected_blog).removeClass('selected');
		$('#tag_' + this.__selected_tag).removeClass('selected');
		$('#tag_' + tagId).addClass('selected');

		life.__selected_blog = 0;
		life.__selected_tag = tagId;
		life.__posts_from = 0;
		life.__disabled = false;
		life.loadPosts();
	},

	showAllPosts: function () {
		this.showBlog(0);
	},

	initBlog: function () {
		if (getAnchorParam('blog') != null) {
			$('#stream_content').html('');
			life.showBlog(getAnchorParam('blog'));
		} else if (getAnchorParam('tag') != null) {
			$('#stream_content').html('');
			life.showTag(getAnchorParam('tag'));
		} else {
			life.__posts_from += life.__settings.limit;
		}
	},

	loadPosts: function (success) {
		if (life.__disabled) return;

		var load;

		$.ajax({
			url: '/procs/proc_life.php',
			data: {
				method: 'load_posts',
				blog_id: life.__selected_blog,
				tag_id: life.__selected_tag,
				from: life.__posts_from,
				limit: life.__settings.limit,
				description: (life.__posts_from == 0 && life.__selected_blog != 0) ? '1' : '0'
			},
			dataType: 'html',

			beforeSend: function () {
				load = {
					target: life.__selected_blog + '_' + life.__selected_tag
				};
				life.loading(true);
				life.__posts_from += life.__settings.limit;
			},

			success: function (html) {
				life.loading(false);
				if (life.__selected_blog + '_' + life.__selected_tag != load.target) return;
				if (success != undefined) success();

				if (html == '') {
					if (life.__posts_from == life.__settings.limit) {
						$('#stream_content').html('<center>Таких постов пока нет</center>');
					}
					life.__disabled = true;
					return;
				}

				if (life.__posts_from == life.__settings.limit) {
					$('#stream_content').html(html);
				} else {
					$('#stream_content').append(html);
				}
			}
		});
	},

	loadPost: function (postId) {
		var load;
		life.__selected_post = postId;

		$.ajax({
			url: '/procs/proc_life.php',
			data: {
				method: 'load_post',
				post_id: postId
			},
			dataType: 'html',

			beforeSend: function () {
				load = {
					target: postId
				};
				$('#post_' + postId).find('.sub').hide();
				life.loading(true);
			},

			success: function (html) {
				life.loading(false);
				if (load.target != life.__selected_post) return;
				$('#post_' + postId).find('.body').html(html);
			}
		});

		return false;
	},

	prepareLoadingBlog: function () {
		$(window).scroll(function () {
			var x = $('#footer').offset().top - $(window).scrollTop() - window.innerHeight;
			//debug(x);
			if (x < window.innerHeight && x >= 0) {
				$('#stream_end').height(50);
				life.loadingEnd(true);
				life.loadPosts(function () {
					life.loadingEnd(false);
				});
			}
		});
	},

	loading: function (enable) {
		if (enable) {
			$('#stream_loading').fadeIn();
		} else {
			$('#stream_loading').fadeOut();
		}
	},

	loadingEnd: function (enable) {
		if (enable) {
			$('#stream_end').fadeIn();
		} else {
			$('#stream_end').fadeOut();
		}
	},

	showComments: function (type) {
		if (!type || type == 'all') {
			$('tr.row').fadeIn();
			$('.option').removeClass('selected');
			$('#type_all').addClass('selected');
		} else {
			$('tr.row').not('.'+type).fadeOut();
			$('tr.row.'+type).fadeIn();
			$('.option').removeClass('selected');
			$('#type_'+type).addClass('selected');
		}
	},

	feed: {
		loading: null,
		dates: {},

		init: function () {
			$('#feed .event .title').click(function () {
				$('#feed .event .body').slideToggle();
			});
			$.ajax({
				url: '/procs/proc_life.php',
				data: {
					method: 'get_dates'
				},
				dataType: 'json',
				success: function (json) {
					if (!json || !json.status || json.status != 'ok') return;
					life.feed.dates = json.dates;
					var dates = getKeys(life.feed.dates);
					dates.sort();
					var firstDate = dates[0],
						lastDate = dates[dates.length - 1];
					ds.setBounds(firstDate, lastDate);
					ds.showGrid();
				}
			});
		},

		dateChecked: function (d, m, y) {
			var date = '';
			date += y + '-';
			date += m < 10 ? '0'+m+'-' : m+'-';
			date += d < 10 ? '0'+d : d;
			debug(date);
			if (life.feed.dates[date] > 0) return ' checked';
			return '';
		},
		
		selectHandler: function (date) {
			life.feed.loading = loading(ge('stream_content'), true, undefined, 100);
			debug('sel: ' + date);
			$.ajax({
				url: '/procs/proc_life.php',
				data: {
					method: 'load_date',
					date: date
				},
				cache: false,

				success: function (html) {
					$('#stream_content').html(html);
					life.feed.loading.hide();
				}
			});
		}
	},

	blog: {
		check: function () {
			var title_len = $('input[name=post_title]').val().length;
			if (title_len == 0) {
				main.showErrorText('Пустой заголовок!');
				return false;
			}
			if (title_len > 100) {
				main.showErrorText('Слишком длинный заголовок!');
				return false;
			}

			var short_len = $('textarea[name=post_short_source]').val().length;
			if (short_len == 0) {
				main.showErrorText('Пустая выдержка!');
				return false;
			}

//			var full_len = $('textarea[name=post_full_source]').val().length;
//			if (full_len == 0) {
//				main.showErrorText('Пустой текст поста!');
//				return false;
//			}

			return true;
		},

		post: function () {
			if (life.blog.check()) {
				$('#blog_editor form').submit();
				$('#editor_button').unbind('click');
			}
		},

		removePost: function (postId) {
			var ok = confirm('Вы действительно хотите удалить этот пост?');
			if (ok) {
				$.ajax({
					url: '/procs/proc_life.php',
					data: {
						method: 'remove_post',
						post_id: postId
					},
					dataType: 'json',

					success: function (json) {
						if (!json || !json.status || json.status != 'ok') {
							main.showErrorText('Не удалось :(');
						} else {
							window.location = '/life/blog';
						}
					}
				});
			}
		}
	}

};

if (document.URL.match(/blog/)) {
	if (document.URL.match(/new/) || document.URL.match(/edit/)) {
		$(function () {
			var bd = $('#blog_editor .body textarea');
			bd.keyup(function () {
				$('#blog_editor .subbody textarea').val(
					content.cutString(bd.val(), 200, 250)
				);
			});
			$('#editor_button').bind('click', life.blog.post);
		});
	} else {
		$(life.initBlog);
		$(life.prepareLoadingBlog);
	}
} else if (document.URL.match(/comments/)) {
	$(function () {
		life.showComments(getAnchorParam('type'));
	});
} else if (document.URL.match(/life/)) {
	$(function () {
		life.feed.init();
	});
}
