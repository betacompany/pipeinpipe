/**
 * @author Innokenty Shuvalov
 */

sportTimeline = {
	bodyPercentage: .8,
	bodyMinSize: 755,//px
	bodyMaxSize: 1000,//px
	monthLength: 125,//px
	
	datesBarDetalization: 2,//steps
	datesBarHeight: 25,//px
	totalDatesHeight: 0,//px, will be defined later

	spaceHorizontal: 20,//px
	spaceVertical: 15,//px

	heightCollapsed: 60,//px
	heightCollapsedSecondary: 45,//px
	paddingCollapsed: 3,//px
	paddingCollapsedSecondary: 4,//px
	pictureEnabledCollapsed: true,
	statusPictureSize: 25,//px
	totalHeightCollapsed: 0,//px, will be defined later

	heightExpanded: 90,//px
	paddingExpanded: 8,//px
	pictureEnabledExpanded: true,

	lines: [],
	jTimeline: null,
	dateInterval: 0,
	startDate: '',

	scrollState: false,
	scrollSpeed: 17,//px

	animation: 200,//ms

	init: function() {
		sportTimeline.totalDatesHeight = sportTimeline.spaceVertical + sportTimeline.datesBarHeight;
		sportTimeline.totalHeightCollapsed = sportTimeline.spaceVertical + sportTimeline.heightCollapsed;
	},
	
	show: function(competitions) {
		sportTimeline.init();
		
		sportTimeline.jTimeline = $('<div/>', {
			id: 'sport_timeline'
		})
		.appendTo('#sport_timeline_container')
		.draggable({
			axis: 'x',
			cursor: 'e-resize',
			drag: function(e, ui) {}
		});

		var datesBarLength = sportTimeline.dateInterval * sportTimeline.monthLength,
			split = sportTimeline.startDate.split('-'),
			date = {
				'y': myParseInt(split[0]),
				'm': myParseInt(split[1]),
				'd': myParseInt(split[2])
			};
		for (var currOffset = 0; currOffset <= datesBarLength; currOffset += sportTimeline.monthLength) {
			sportTimeline.showMonth(date, currOffset);
		}

		var compDivs = [];
		for (var i = 0; i < competitions.length; i++) {
			compDivs[i] = sportTimeline.showCompetition(competitions[i]);
		}

		sportTimeline.rearrange(compDivs, competitions);
		var longestLine = sportTimeline.rearrange(compDivs, competitions);

		sportTimeline.jTimeline.css({
			left: ($('#sport_timeline_container').innerWidth() - longestLine - sportTimeline.monthLength)
		});

		sportTimeline.showScrollers();
	},

	showMonth: function(date, offset) {
		var monthBody = $('<div/>', {
					html: common.months[date['m'] - 1]['name'] + ' ' + date['y']
				})
				.css({
					left: offset,
					width: sportTimeline.monthLength,
					height: sportTimeline.datesBarHeight
				})
				.addClass('timeline_month')
				.appendTo(sportTimeline.jTimeline);

		date['m']++;
		if (date['m'] > 12) {
			date['m'] = 1;
			date['y']++;
		}
	},

	showCompetition: function(competition) {
		var height = competition.primary ? sportTimeline.heightCollapsed : sportTimeline.heightCollapsedSecondary,
			padding = competition.primary ? sportTimeline.paddingCollapsed : sportTimeline.paddingCollapsedSecondary,
			compBody = $('<div/>')
				.data('height', height)
				.data('id', competition.id)
				.data('padding', padding)
				.css({
					height: height
				})
				.appendTo(sportTimeline.jTimeline)
				.addClass('comp_body round_border' + (competition.primary ? ' comp_primary' : '') + (competition.status ? ' comp_active' : ''));
			
		var compLink = $('<a/>', {
				href: competition.url
			}).appendTo(compBody);

		if (sportTimeline.pictureEnabledCollapsed) {
			var compImage = $('<img/>', {
					id: 'img' + competition.id,
					alt: competition.name,
					src: competition.image
				})
				.css({
					height: height - 2 * padding,
					margin: padding
				})
				.appendTo(compLink);
		}

		var collapsed_name = '';
		for (var i = 0; i < competition.collapsed_name.length; i++) {
			collapsed_name += competition.collapsed_name[i] + '<br/>';
//			collapsed_name += '<p>' + competition.collapsed_name[i] +  '</p>';
		}

		var expanded_name = '';
		for (i = 0; i < competition.expanded_name.length; i++) {
			expanded_name += competition.expanded_name[i] + '<br/>';
//			expanded_name += '<p>' + competition.expanded_name[i] +  '</p>';
		}

		var compName = $('<div/>', {
			id: 'name' + competition.id,
			html: collapsed_name
		})
		.data('collapsed_name', collapsed_name)
		.data('expanded_name', expanded_name)
		.addClass("comp_name")
		.appendTo(compLink);

		if (competition.status_image) {
			var compStatusImage = $('<img/>', {
				id: 'status' + competition.id,
				alt: competition.name,
				src: competition.status_image
			})
			.css({
				height: 0
			})
			.addClass('sport_timeline_status')
			.appendTo(compLink);
		}

		compBody.hover(function() {
			var compBody = $(this),
				id = compBody.data('id'),
				compName = $('#name' + id),
				compImage = $('#img' + id),
				compStatus = $('#status' + id),
				heightDiff = (sportTimeline.heightExpanded - compBody.data('height')) / 2,
				widthDiff = heightDiff;

			compName.html(compName.data('expanded_name'))
			compImage.animate({
						height: sportTimeline.heightExpanded,
						margin: 0
					},
					sportTimeline.animation
				);
			compStatus.animate({
						height: sportTimeline.statusPictureSize
					},
					sportTimeline.animation
				);
			compBody
				.css({
					'z-index': 7
				})
				.animate({
						left: compBody.data('left') - widthDiff,
						top: compBody.data('top') - (compBody.data('line') + 1 == sportTimeline.lines.length ? 2 : 1) * heightDiff,
						height: sportTimeline.heightExpanded,
						padding: sportTimeline.paddingExpanded
					},
					sportTimeline.animation
				);
		}, function() {
			var compBody = $(this),
				id = compBody.data('id'),
				compName = $('#name' + id),
				compStatus = $('#status' + id),
				compImage = $('#img' + id);

			compName.html(compName.data('collapsed_name'));
			compStatus.animate({
						height: 0
					},
					sportTimeline.animation
				);
			compImage.animate({
						height: compBody.data('height') - 2 * compBody.data('padding'),
						margin: compBody.data('padding')
					},
					sportTimeline.animation
				);
			compBody
				.css({
					'z-index': 0
				})
				.animate({
						left: compBody.data('left'),
						top: compBody.data('top'),
						height: compBody.data('height'),
						padding: 0
					},
					sportTimeline.animation
				);
		});

		return compBody;
	},

	rearrange: function(compDivs, competitions) {
		sportTimeline.lines = [];

		var dateInPixels, line, top;
		for (var i = 0; i < compDivs.length; i++) {
			dateInPixels = competitions[i].date / 29.8 * sportTimeline.monthLength,
			line = sportTimeline.findLine(dateInPixels),
			top = line * sportTimeline.totalHeightCollapsed + sportTimeline.totalDatesHeight;

			compDivs[i]
				.data('line', line)
				.data('left', dateInPixels)
				.data('top', top)
				.css({
					left: dateInPixels,
					top: top
				});

			sportTimeline.lines[line] = dateInPixels + compDivs[i].width() + sportTimeline.spaceHorizontal;
		}

		var longestLine = 0;
		for (i = 0; i < sportTimeline.lines.length; i++)
			if (sportTimeline.lines[i] > longestLine)
				longestLine = sportTimeline.lines[i];

		sportTimeline.jTimeline.css({
			width: (longestLine * 1.1),
			height: (sportTimeline.lines.length * sportTimeline.totalHeightCollapsed + sportTimeline.totalDatesHeight)
		});

		return longestLine;
	},

	findLine: function(dateInPixels) {
		for (var i = 0; i < sportTimeline.lines.length; i++)
			if (dateInPixels > sportTimeline.lines[i])
				return i;
		return sportTimeline.lines.length;
	},

	scrollingEnabled: function(direction, state) {
		sportTimeline.scrollState = state;
		sportTimeline.scroll(direction);
	},

	showScrollers: function() {
		var screenWidth = $(document).width(),
			jBody = $('#body'),
			width = sportTimeline.bodyPercentage * screenWidth,
			top = sportTimeline.jTimeline.position().top,
			height = sportTimeline.jTimeline.height();

		if (width < sportTimeline.bodyMinSize)
			width = sportTimeline.bodyMinSize
		else if (width > sportTimeline.bodyMaxSize)
			width = sportTimeline.bodyMaxSize

		var scrollerWidth = (screenWidth - width) / 2;
		$('<div/>')
			.css({
				'background-image': 'url(/images/sport/scroll_left.png)'
			})
			.addClass('timeline_scroller')
			.appendTo(jBody)
			.hover(function(){
				sportTimeline.scrollingEnabled(1, true);
			}, function() {
				sportTimeline.scrollingEnabled();
			});

		$('<div/>')
			.css({
				left: screenWidth - scrollerWidth - 20,
				'background-image': 'url(/images/sport/scroll_right.png)'
			})
			.addClass('timeline_scroller')
			.appendTo(jBody)
			.hover(function(){
				sportTimeline.scrollingEnabled(-1, true);
			}, function() {
				sportTimeline.scrollingEnabled();
			});
		$('.timeline_scroller')
			.css({
				top: top,
				height: height,
				width: scrollerWidth
			});
	},

	scroll: function(direction) {
		if (sportTimeline.scrollState) {
			sportTimeline.jTimeline.css({
				left: parseInt(sportTimeline.jTimeline.css('left')) + direction * sportTimeline.scrollSpeed
			});
			setTimeout('sportTimeline.scroll(' + direction + ')', 5);
		}
	}
}
