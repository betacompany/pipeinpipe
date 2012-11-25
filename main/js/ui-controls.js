/**
 * @author Artyom Grigoriev
 */
function Selector(options) {
	var defaultOptions = {
		content:[],
		editable:false,
		enabled:true,
		maxOptionsCount:5,
		onChange:function (o, value) {
		},
		onSelect:function (id) {
		},
		onSlideDown:function () {
		},
		onSlideUp:function () {
		}
	};

	$.extend(defaultOptions, options);
	options = defaultOptions;

	var ce = function (tag) {
		return document.createElement(tag);
	};

	var inputArea = ce('div');
	inputArea.className = 'ui-selector';

	var inputFieldArea = ce('div');
	inputArea.appendChild(inputFieldArea);

	var slideButton = ce('div');
	inputArea.appendChild(slideButton);

	var inputField = options.editable ? ce('input') : ce('div');
	inputFieldArea.appendChild(inputField);
	if (!options.editable) {
		$(inputField).click(function () {
			if (!returnObject.enabled) return;
			var shown = $(slideButton).data('shown');
			if (shown) {
				returnObject.hideOptions();
			} else {
				returnObject.showOptions();
			}
		});
	}

	var optionsArea = ce('div');
	optionsArea.className = 'ui-options';

	var returnObject = {
		optionsArea:optionsArea,
		slideButton:slideButton,
		inputField:inputField,

		selectedId:null,
		content:[],

		editable:options.editable,
		enabled:options.enabled,

		data:function (key, value) {
			if (value == undefined) {
				return $(this.optionsArea).data(key);
			}

			return $(this.optionsArea).data(key, value);
		},

		appendTo:function (container) {
			if (container instanceof Element) {
				container.appendChild(inputArea);
				container.appendChild(optionsArea);
			} else {
				container.append(inputArea);
				container.append(optionsArea);
			}

			return this;
		},

		setWidth:function (w) {
			$(inputArea).width(w);
			$(optionsArea).width(w);
			$(inputFieldArea).width(w - 20);

			return this;
		},

		setHeight:function (h) {
			$(inputArea).height(h);
			$(inputFieldArea).height(h);

			return this;
		},

		select:function (id) {
			var option = this.data('id_' + id);
			if (this.editable) {
				$(inputField).val(option.value);
			} else {
				$(inputField).html(option.value);
			}

			this.selectedId = option.id;

			return this;
		},

		addOption:function (option) {
			var opt = ce('div');
			opt.innerHTML = (option.html != undefined) ? option.html : option.value;

			$(opt).data('id', option.id);
			$(opt).data('value', option.value);
			this.data('id_' + option.id, option);

			$(opt).click(function () {
				if (options.editable) {
					$(inputField).val($(this).data('value'));
					$(this).data('value');
				} else {
					$(inputField).html($(this).data('value'));
				}

				returnObject.onSelect($(this).data('id'));
			});

			this.optionsArea.appendChild(opt);
			this.content.push(option);

			if (this.content.length > options.maxOptionsCount) {
				// FIXME zero initial height
				$(this.optionsArea).height(options.maxOptionsCount * $(opt).height());
				$(this.optionsArea).css('overflow-y', 'scroll');
			} else {
				$(this.optionsArea).css('height', 'auto');
				$(this.optionsArea).css('overflow-y', 'auto');
			}

			return this;
		},

		/**
		 * @brief sets the new array of options on the place of the old set
		 */
		setOptions:function (options) {
			this.content = [];
			$(this.optionsArea).children().remove();
			for (var i = 0; i < options.length; i++) {
				this.addOption(options[i]);
			}

			return this;
		},

		/**
		 * @brief hides options list if it is not hidden yet
		 */
		hideOptions:function () {
			if ($(this.slideButton).data('shown')) {
				$(this.optionsArea).slideUp("fast");
				$(this.slideButton).data('shown', false);
				options.onSlideUp();
			}

			return this;
		},

		/**
		 * @brief shows options list if it is not shown yet
		 */
		showOptions:function () {
			if (!$(this.slideButton).data('shown')) {
				$(this.optionsArea).slideDown("fast");
				$(this.slideButton).data('shown', true);
				options.onSlideDown();
			}

			return this;
		},

		/**
		 * @brief handles the changing of input field content and calls user handler
		 */
		onChange:function () {
			options.onChange(this, $(inputField).val());
			this.showOptions();

			return this;
		},

		/**
		 * @brief handles the selection of one of options and calls user handler
		 */
		onSelect:function (id) {
			options.onSelect(id);
			this.selectedId = id;
			this.hideOptions();

			return this;
		},

		/**
		 * @param [id]
		 * @brief returns the id of selected option and null if nothing is selected
		 */
		val:function (id) {
			if (id != undefined) {
				this.selectedId = id;
				return id;
			}

			return this.selectedId;
		},

		enable:function () {
			this.enabled = true;
			if (this.editable) {
				this.inputField.disabled = false;
			}

			return this;
		},

		disable:function () {
			this.enabled = false;
			if (this.editable) {
				this.inputField.disabled = true;
			}

			return this;
		},

		clear:function () {
			this.selectedId = false;
			$(inputField).val('');

			return this;
		}
	};

	if (returnObject.editable) {
		$(inputField).keyup(function () {
			if (!returnObject.enabled) return;
			returnObject.onChange();
		});

		$(inputField).blur(function () {
			if (!returnObject.enabled) return;
			returnObject.hideOptions();
		});

		$(inputField).focus(function () {
			if (!returnObject.enabled) return;
			returnObject.showOptions();
		});
	}

	if (!returnObject.enabled) {
		if (returnObject.editable) {
			inputField.disabled = true;
		}
	}

	for (var i = 0; i < options.content.length; i++) {
		returnObject.addOption(options.content[i]);
	}

	var jButton = $(slideButton);
	jButton.data('shown', false);
	$(returnObject.optionsArea).hide();

	jButton.click(function () {
		if (!returnObject.enabled) return;
		var shown = $(this).data('shown');
		if (shown) {
			returnObject.hideOptions();
		} else {
			returnObject.showOptions();
		}
	});

	return returnObject;
}

/**
 * @author Artyom Grigoriev
 */
function DynamicSelector(options) {
	// FIXME extend?
	options.content = (options.content != undefined) ? options.content : [];
	options.enabled = (options.enabled != undefined) ? options.enabled : true;
	options.maxOptionsCount = (options.maxOptionsCount != undefined) ? options.maxOptionsCount : 5;
	options.onSlideDown = (options.onSlideDown != undefined) ? options.onSlideDown : function () {
	};
	options.onSlideUp = (options.onSlideUp != undefined) ? options.onSlideUp : function () {
	};
	options.onSelect = (options.onSelect != undefined) ? options.onSelect : function () {
	};
	options.onChange = (options.onChange != undefined) ? options.onChange : function () {
	};

	var optionsArray = options.content;
	var stringArray = new Array();
	for (var i = 0; i < optionsArray.length; i++) {
		stringArray.push(optionsArray[i].value.toLowerCase());
	}

	var suffixTree = new SuffixTree(stringArray);

	var selector = new Selector({
		content:options.content,
		editable:true,
		enabled:options.enabled,
		maxOptionsCount:options.maxOptionsCount,
		onSlideDown:options.onSlideDown,
		onSlideUp:options.onSlideUp,
		onSelect:options.onSelect,

		onChange:function (o, str) {
			str = str.toLowerCase();
			var indexes = suffixTree.getPossibleIndexes(str);
			var optionsSubArray = [];
			for (var i = 0; i < indexes.length; i++) {
				optionsSubArray.push(optionsArray[indexes[i]]);
			}

			o.setOptions(optionsSubArray);
			options.onChange(str);
		}

		// TODO прописать чтобы при выделении поля ввода, содержимое поля
		// ввода и варианты на выпадающей панели выбора
		// сбрасывались и заполнялось в соответствии с тем,
		// что пользователь набрал в этом поле ранее.
		// Если пользователь ничего не набрал,
		// то просто выделять всё содержимое,
		// чтобы можно было его легко стереть.
	});

	return {
		appendTo:function (container) {
			selector.appendTo(container);

			return this;
		},

		setWidth:function (width) {
			selector.setWidth(width);

			return this;
		},

		val:function () {
			return selector.val();
		},

		select:function (id) {
			selector.select(id);

			return this;
		},

		disable:function () {
			selector.disable();

			return this;
		},

		enable:function () {
			selector.enable();

			return this;
		},

		clear:function () {
			selector.clear();

			return this;
		},

		text:function () {
			return selector.inputField.value;
		}
	}
}

/**
 * @author Innokenty Shuvalov
 * @param options
 * options are:
 * - src = '../images/delete.png'
 * - imageClass = 'fading_image'
 * - onclick: function()
 * - animationSpeed [ms] = 400
 * - minOpacity = 0.35
 */
function FadingImage(options) {
	var defOptions = {
		src:'../images/delete.png',
		CSSClass:'',
		animationSpeed:400,
		minOpacity:0.35,
		onclick:function () {
			debug("action is not defined!");
		}
	}

	$.extend(defOptions, options);
	options = defOptions;

	var jImg = $('<img/>', {
		src:options.src
	}).hover(
		function () {
			$(this).animate({opacity:1}, options.animationSpeed);
		},
		function () {
			$(this).animate({opacity:options.minOpacity}, options.animationSpeed);
		}
	)
		.addClass(options.CSSClass)
		.css({
			opacity:options.minOpacity,
			cursor:'pointer'
		})
		.click(options.onclick);

	return {
		/**
		 * Don't set containerToHover if you want this object to be visible all the time!!
		 */
		appendTo:function (сontainerToAppend, сontainerToHover) {
			jImg.appendTo(сontainerToAppend instanceof jQuery ? сontainerToAppend : $('#' + сontainerToAppend));

			if (сontainerToHover != undefined) {
				(сontainerToHover instanceof jQuery ? сontainerToHover : $('#' + сontainerToHover)).hover(
					function () {
						jImg.show();//fadeIn('fast');
					},
					function () {
						jImg.hide();//fadeOut('fast');
					}
				);
			}

			return this;
		},

		hide:function () {
			jImg().hide();

			return this;
		},

		show:function () {
			jImg().show();

			return this;
		}
	}
}

/**
 * @author Innokenty Shuvalov
 * @param options
 * options are:
 * - html = 'Кнопка'
 * - CSSClass = 'fading_image'
 * - onclick: function()
 * - animationSpeed [ms] = 400
 * - minOpacity = 0.35
 * - css
 */
function FadingButton(options) {
	var defOptions = {
		html:'Кнопка',
		animationSpeed:400,
		minOpacity:0.35,
		CSSClass:'',
		css:{},
		onclick:function () {
			debug("action is not defined!");
		}
	}
	$.extend(defOptions, options);
	options = defOptions;

	var jButton = $('<div/>')
		.addClass(options.CSSClass)
		.html('<div>' + options.html + '</div>')
		.css({
			opacity:options.minOpacity,
			cursor:'pointer'
		})
		.css(options.css)
		.click(
		options.href ?
			function () {
				window.location = options.href
			} : options.onclick
	);

	var bindHover = function () {
		jButton.hover(
			function () {
				$(this).animate({opacity:1}, options.animationSpeed);
			},
			function () {
				$(this).animate({opacity:options.minOpacity}, options.animationSpeed);
			}
		)
	}
	bindHover();

	return {
		/**
		 * Don't set containerToHover if you want this object to be visible all the time!!
		 */
		appendTo:function (сontainerToAppend, сontainerToHover) {
			jButton.appendTo(сontainerToAppend instanceof jQuery ? сontainerToAppend : $('#' + сontainerToAppend));

			if (сontainerToHover != undefined) {
				(сontainerToHover instanceof jQuery ? сontainerToHover : $('#' + сontainerToHover)).hover(
					function () {
						jButton.show();//fadeIn('fast');
					},
					function () {
						jButton.hide();//fadeOut('fast');
					}
				);
			}

			return this;
		},

		hide:function () {
			jButton.hide();
			return this;
		},

		show:function () {
			jButton.show();

			return this;
		},

		css:function (CSSoptions) {
			jButton.css(CSSoptions);

			return this;
		},

		enable:function () {
			jButton.click(options.onClick).css({
				cursor:'pointer',
				opacity:options.minOpacity
			});
			bindHover();

			return this;
		},

		disable:function () {
			jButton.unbind().css({
				cursor:'default',
				opacity:options.minOpacity
			});

			return this;
		},

		content:function (content) {
			var contentToAdd = typeof content == 'jQuery' ? content : $('<div/>').html(content);

			jButton.children().fadeOut('fast', function () {
				contentToAdd.hide();
				jButton.append(contentToAdd);
				contentToAdd.fadeIn();
			});

			return this;
		},

		click:function (fn) {
			if (fn == undefined)
				jButton.click();
			else
				jButton.unbind('click').click(fn);

			return this;
		}
	}
}

/**
 * @author Innokenty Shuvalov
 * @param options
 * options are:
 * - compId necessary
 * - parentCupId = 0 by default (it means we're creating main cup)
 * - onCancel function defining what to do if this action is canceled
 * - container an html-container which this panel will be appended to.
 * you can append it later by using the appendTo(container) function
 * - speed if you've set the container you can set the speed which
 * - will appear with. it can be 'normal' (by default), 'fast' or 'slow'
 * - callback if you've set the container you can set the callback action
 * which will happen after the panel slides down
 */
function AddCupPanel(options) {
	if (options.compId == undefined) {
		debug('comp_id in AddCupPanel is undefined!!');
		return undefined;
	}
	var defOptions = {
		parentCupId:0,
		onCancel:function () {
		}
	}

	$.extend(defOptions, options);
	options = defOptions;

	var jPanel = $('<div/>', {
		id:'create_cup_panel'
	});

	var jName = $('<input/>', {
		id:"create_cup_name",
		type:"text",
		value:options.parentCupId != 0 ? "Введите название для турнира" : "Турнир"
	}).addClass("create_cup_content");

	if (options.parentCupId != 0) {
		jName.focus(
			function () {
				if (jName.val() == 'Введите название для турнира')
					jName.val('Группа ')
						.css("color", "black");
			}
		).blur(function () {
				if (jName.val() == '') {
					jName.val('Введите название для турнира')
						.css("color", "gray");
				}
			});
	} else {
		jName.attr('disabled', 'disabled');
	}

	var jType = $('<div/>').addClass("create_cup_content");

	var typeSelector = new Selector({
		content:[
			{
				id:'playoff',
				value:'кубок по олимпийской системе'
			},
			{
				id:'one-lap',
				value:'чемпионат в один круг'
			},
			{
				id:'two-laps',
				value:'чемпионат в два круга'
			}
		]
	});
	typeSelector.setWidth(240);
	typeSelector.select(options.parentCupId == 0 ? 'playoff' : 'one-lap');
	typeSelector.appendTo(jType);

	var jSave = $('<input/>', {
		type:"button",
		value:"Создать"
	}).addClass('create_cup_content')
		.click(function () {
			cup.createCup(options.compId, options.parentCupId, jName.val(), typeSelector.val(), function () {
				jPanel.slideUp(options.speed);
			});
		});

	var jCancel = $('<input/>', {
		type:"button",
		value:"Отмена"
	}).addClass('create_cup_content')
		.click(function () {
			jPanel.slideUp(options.speed, function () {
				options.onCancel();
			});
		});

	jPanel.append(jName);
	jPanel.append(jType);
	jPanel.append(jSave);
	jPanel.append(jCancel);

	jPanel.hide();

	if (options.container != undefined) {
		// TODO if container already begins with # we need to remove it
		jPanel.appendTo($('#' + options.container));
	}

	return {
		slideDown:function () {
			jPanel.slideDown(options.speed, options.callback);
			jName.focus();

			return this;
		},

		slideUp:function () {
			jPanel.slideUp(options.speed, options.callback);

			return this;
		},

		hide:function () {
			jPanel.hide();

			return this;
		},

		show:function () {
			jPanel.show();

			return this;
		},

		appendTo:function (container) {
			jPanel.appendTo($('#' + container));

			return this;
		}
	}
}

/**
 * @author Artyom Grigoriev
 */
function CheckBox(_options) {
	var options = {
		/**
		 * @param checked boolean
		 * @param id Numeric
		 */
		onCheck:function (checked, id) {
		},
		id:0
	};

	$.extend(options, _options);

	var checkbox = ce('div');
	$(checkbox).addClass('checkbox');
	$(checkbox).click(function () {
		if ($(this).hasClass('on')) {
			$(this).removeClass('on');
			options.onCheck(false, options.id);
		} else {
			$(this).addClass('on');
			options.onCheck(true, options.id);
		}
	});

	return {
		id:options.id,
		state:false,

		appendTo:function (container) {
			container.append(checkbox);

			return this;
		},

		setState:function (value) {
			if (value != undefined) {
				if (value) {
					$(checkbox).addClass('on');
					options.onCheck(true);
				} else {
					$(checkbox).removeClass('on');
					options.onCheck(false);
				}
			}

			return this.state;
		}
	};
}

/**
 * @author Artyom Grigoriev
 * @param options
 * - onClick [function] - handler for click event
 * - container [String or jQuery] - container to append this button
 * - CSSClass - [optional]
 * - html - text on the button [optional]
 */
function Button(options) {
	var defaultOptions = {
		onClick:function () {
		},
		container:null,
		CSSClass:'',
		html:'Text'
	};

	$.extend(defaultOptions, options);
	options = defaultOptions;

	var jOuterDiv = $('<div/>')
		.addClass(options.CSSClass)
		.addClass('default_button')
		.click(options.onClick)
		.append(
		$('<div/>')
			.addClass('button_inner')
			.html(options.html)
	);

	if (options.container != null) {
		var jContainer = (options.container instanceof jQuery) ? options.container : $('#' + options.container);
		jContainer.append(jOuterDiv);
	}

	return {
		enable:function () {
			jOuterDiv.click(options.onClick);

			return this;
		},

		disable:function () {
			jOuterDiv.unbind('click', options.onClick);

			return this;
		},

		appendTo:function (container) {
			var jContainer = (container instanceof jQuery) ? container : $('#' + container);
			jContainer.append(jOuterDiv);

			return this;
		}
	}
}

function PeopleListItem(options) {
	var defaultOptions = {
		targetId:0,
		personId:0,
		personName:'undefined',
		onClick:function () {
		},
		canDelete:false
	};

	$.extend(defaultOptions, options);
	options = defaultOptions;

	var jLi = $('<li/>', {
		id:'person_' + options.personId
	}).addClass('leaf')
		.appendTo('.people')
		.fadeIn('slow');

	var jContent = $('<div/>').appendTo(jLi);

	$('<div/>', {
		text:options.personName
	}).appendTo(jContent);

	var jImageDiv = $('<div/>', {
		id:'person_' + options.personId + '_image'
	}).appendTo(jContent);

	$('<div/>').addClass('clear').appendTo(jLi);

	if (options.canDelete) {
		FadingImage({
			CSSClass:'fading_image',
			onclick:function (e) {
				options.onClick(options.personId, options.targetId);
				e.stopPropagation();
			}
		}).appendTo(jImageDiv, jLi);
	}

	peopleSelector.clear();

	return jLi;
}

function PopUp(options) {
	var defaultOptions = {
		x:690, y:200,
		html:'DefaultHTML',
		id:tm() + '_' + Math.round(Math.random() * 1000),
		delay:200
	}

	$.extend(defaultOptions, options);
	options = defaultOptions;
	if (options.recalcCoords == undefined) {
		options.recalcCoords = function (id) {
			return {x:options.x, y:options.y};
		};
	}

	var p = $('<div/>').addClass('popup_box').hide(),
		c = $('<div/>'),
		a = $('<div/>').addClass('popup_box_arrow_down');

	c.html(options.html);
	p.append(c).append(a);

	p.css({
		top:options.y - p.outerHeight() + 'px',
		left:options.x - p.outerWidth() / 2 + 'px',
		zIndex:69
	});

	a.css({
		top:p.outerHeight() - 4 + 'px',
		left:p.outerWidth() / 2 - 5 + 'px',
		zIndex:70
	});

	p.attr('id', options.id).data('over', false);

	$('body').append(p);

	var returnObject = {
		__options:options,

		position:function (x, y) {
			p.css({
				top:y - p.innerHeight() + 'px',
				left:x - p.outerWidth() / 2 + 'px'
			});

			a.css({
				top:p.outerHeight() - 4 + 'px',
				left:p.outerWidth() / 2 - 5 + 'px'
			});

			return this;
		},

		show:function () {
			//debug('popup show');
			var coords = options.recalcCoords(options.id);
			//debug(coords);
			this.position(coords.x, coords.y);
			p.fadeIn();

			return this;
		},

		hide:function () {
			var id = p.attr('id');
			setTimeout(function () {
				if ($('#' + id).data('over')) return;
				//debug('popup hide');
				$('#' + id).fadeOut().data('over', false);
			}, options.delay);

			return this;
		},

		append:function (container) {
			c.append(container);

			return this;
		}
	};

	p.hover(
		function () {
			$(this).data('over', true);
			//debug('popup over');
		},
		function () {
			$(this).data('over', false);
			//debug('popup out');
			$(this).fadeOut();
		}
	);

	return returnObject;
}

function DateSelector(options) {
	var date = options.date,
		onSelect = options.onSelect ? options.onSelect : function (date) {
		},
		anchor = options.anchor ? options.anchor : false,
		select = options.select ? options.select : false,
		dateChecked = options.dateChecked ?
			options.dateChecked :
			function (d, m, y) {
				return '';
			};

	var arr = date ? date.split('-') : false,
		today = new Date(),
		day = myParseInt(arr ? arr[2] : today.getDate()),
		month = myParseInt(arr ? arr[1] : today.getMonth() + 1), // human
		year = myParseInt(arr ? arr[0] : today.getFullYear());

	var minDate = options.minDate || {},
		maxDate = options.maxDate || {};

	var id = tm() + '_';
	jContainer = $('<div/>').addClass('ui-date-selector').attr('id', id),
		jDate = $('<div/>').addClass('date'),
		jGrid = $('<div/>').addClass('grid').hide();

	jContainer
		.append(jDate)
		.append(jGrid);

	var grid_showing = false;

	var dateSetter = function (d, m, y) {
		day = myParseInt(d);
		month = myParseInt(m);
		year = myParseInt(y);
		d = day >= 10 ? day : '0' + day;
		jDate.html(d + '&nbsp;' + common.months[month - 1].name_gen + '&nbsp;' + y);
		onSelect(dateGetter());
		if (options.hideOnSelect) {
			jGrid.fadeOut();
			grid_showing = false;
		}
	};

	var dateGetter = function () {
		var m = month >= 10 ? month : '0' + month,
			d = day >= 10 ? day : '0' + day;
		return year + '-' + m + '-' + d;
	};

	var viewed = {
		month:month,
		year:year
	};

	var dateOff = function (d, m, y) {
		m = m ? myParseInt(m) : false;
		d = d ? myParseInt(d) : false;
		//debug(d + ' ' + m + ' ' + y);
		return datecmp(d, m, y, minDate.d, minDate.m, minDate.y) >= 0 &&
			datecmp(d, m, y, maxDate.d, maxDate.m, maxDate.y) <= 0 ? '' : ' off';
	};

	var dateSelected = function (d, m, y) {
		return datecmp(d, m, y, day, month, year) == 0 ? ' selected' : '';
	}

	var showMonthGrid = function (m, y) {
		viewed.year = y;
		viewed.month = m;

		var first = (new Date(y, m - 1, 1)).getDay();
		first = (first == 0) ? 7 : first;
		var str = '';
		var days = y % 4 == 0 ? common.months[m - 1].length_leap : common.months[m - 1].length;
		str += '<table><thead><th class="m_h" colspan="7">' + common.months[m - 1].name + '&nbsp;' + y + '</th></thead><tbody>';
		for (var row = 0; row < 6; ++row) {
			str += '<tr>';
			for (var col = 1; col <= 7; ++col) {
				var d = row * 7 + col - first + 1;
				if ((d >= 1) && (d <= days)) {
					str += '<td class="d' +
						dateOff(d, m, y) +
						dateSelected(d, m, y) +
						dateChecked(d, m, y) +
						'">' + d + '</td>';
				} else {
					str += '<td></td>';
				}
			}
			str += '</tr>';
			if (d > days && row < 5) break;
		}
		str += '</tbody></table>';
		jGrid.html(str);

		$('#' + id + ' .d').not('.off').bind('click', function () {
			var d = parseInt($(this).html());
			dateSetter(d, viewed.month, viewed.year);
			//debug(dateGetter());
			if (anchor) {
				setAnchorParam('date', dateGetter());
			}
			if (select) {
				$('#' + id + ' .d').removeClass('selected');
				$(this).addClass('selected');
			}
		});

		$('#' + id + ' .m_h').bind('click', function () {
			showYearGrid(viewed.year);
		});
	};

	var showYearGrid = function (y) {
		viewed.year = y;

		var str = '<table><thead><th class="y_h" colspan="4">' + y + '</th></thead><tbody>';

		for (var row = 0; row < 3; ++row) {
			str += '<tr>';
			for (var col = 0; col < 4; ++col) {
				var m = row * 4 + col;
				str += '<td class="m' + dateOff(undefined, m + 1, y) + '" id="' + id + '' + (m + 1) + '">' + common.months[m].name_short + '</td>';
			}
			str += '</tr>';
		}

		str += '</tbody></table>';
		jGrid.html(str);

		$('#' + id + ' .m').not('.off').bind('click', function () {
			var ida = $(this).attr('id').split('_'), m = ida[1];
			showMonthGrid(m, viewed.year);
		});

		$('#' + id + ' .y_h').bind('click', function () {
			showTwentyYearsGrid(viewed.year);
		});
	};

	var showTwentyYearsGrid = function (y) {
		viewed.year = y;

		var firstYear = Math.floor(y / 20) * 20;
		var str = '<table><thead><th class="y20_h" colspan="5">' +
			(minDate.y ? Math.max(firstYear, minDate.y) : firstYear) + ' &mdash; ' +
			(maxDate.y ? Math.min(firstYear + 19, maxDate.y) : firstYear + 19) + '</th></thead><tbody>';

		for (var row = 0; row < 4; ++row) {
			str += '<tr>';
			for (var col = 0; col < 5; ++col) {
				var yr = firstYear + row * 5 + col;
				str += '<td class="y' + dateOff(undefined, undefined, yr) + '" id="' + id + '' + yr + '">' + yr + '</td>';
			}
			str += '</tr>';
		}

		str += '</tbody></table>';
		jGrid.html(str);

		$('#' + id + ' .y').not('.off').bind('click', function () {
			var ida = $(this).attr('id').split('_'), y = ida[1];
			showYearGrid(y);
		});

		$('#' + id + ' .y20_h').bind('click', function () {
			showAllGrid();
		});
	};

	var showAllGrid = function () {
		var firstYear = 1900;

		var str = '<table><thead><th class="off" colspan="5">' +
			(minDate.y ? Math.max(firstYear, minDate.y) : firstYear) + ' &mdash; ' +
			(maxDate.y ? Math.min(firstYear + 399, maxDate.y) : firstYear + 399) + '</th></thead><tbody>';

		for (var row = 0; row < 3; ++row) {
			str += '<tr>';
			for (var col = 0; col < 5; ++col) {
				var yr = firstYear + (row * 5 + col) * 20;
				str += '<td class="y20 ' + ((minDate && yr + 19 < minDate.y) || (maxDate && yr > maxDate.y) ? ' off' : '') + '" id="' + id + '' + yr + '">' + yr + ' ' + (yr + 19) + '</td>';
			}
			str += '</tr>';
		}

		str += '</tbody></table>';
		jGrid.html(str);

		$('#' + id + ' .y20').not('.off').bind('click', function () {
			var ida = $(this).attr('id').split('_'), y = ida[1];
			showTwentyYearsGrid(y);
		});
	};

	if (!date) {
		jDate.html('выберите дату');
	} else {
		debug(day);
		var dn = (day >= 10) ? day : '0' + day;
		jDate.html(dn + '&nbsp;' + common.months[month - 1].name_gen + '&nbsp;' + year);
	}

	jDate.click(function () {
		if (!grid_showing) {
			showMonthGrid(viewed.month, viewed.year);
			jGrid.fadeIn();
		} else {
			jGrid.fadeOut();
		}

		grid_showing = !grid_showing;
	});

	return {
		appendTo:function (container) {
			container.append(jContainer);
		},
		hideGrid:function () {
			jGrid.fadeOut();
			grid_showing = false;
		},
		showGrid:function () {
			showMonthGrid(viewed.month, viewed.year);
			jGrid.fadeIn();
			grid_showing = true;
		},
		val:function () {
			return dateGetter();
		},
		setBounds:function (begin, end) {
			var bd = begin.split('-'),
				ed = end.split('-');
			minDate = {y:myParseInt(bd[0]), m:myParseInt(bd[1]), d:myParseInt(bd[2])};
			maxDate = {y:myParseInt(ed[0]), m:myParseInt(ed[1]), d:myParseInt(ed[2])};
		},
		clear:function () {
			//TODO implement!!
		}
	};
}

function Timeline(options) {

	var DAY_IN_MS = 24 * 60 * 60 * 1000;

	var defaultOptions = {
		delta: 7,
		centerDate: Math.floor(new Date().getTime() / DAY_IN_MS),
		commonW: 30
	};

	$.extend(defaultOptions, options);
	options = defaultOptions;

	debug(options);

	var jContainer = $('<div/>').addClass('ui-timeline');
	var dateContainers = [];

	var convert = function (centerDate, i) {
		var ts = (centerDate + i) * DAY_IN_MS;
		return new Date(ts);
	};

	for (var i = -options.delta; i <= options.delta; ++i) {
		var date = convert(options.centerDate, i),
			d = date.getDate(),
			j = dateContainers[options.delta + i] =
					$('<div/>')
						.addClass('date day-' + i)
						.html(d)
						.data('ms', date.getTime())
						.appendTo(jContainer);
		if (i == 0) {
			j.addClass('center');
		}
	}

	jContainer.append($('<div class="month_name m1"></div>'));
	jContainer.append($('<div class="month_name m2"></div>'));

	var setCenterDay = function (day) {
		jContainer.children('.month_name.m2').hide();
		for (var i = -options.delta; i <= options.delta; ++i) {
			var date = convert(day, i),
				d = date.getDate(),
				j = dateContainers[options.delta + i]
					.html(d)
					.data('ms', date.getTime());

			if (i == -options.delta) {
				jContainer.children('.month_name.m1')
					.css({
						left: j.offset().left
					})
					.html(common.monthName(date) + "'" + new String(date.getFullYear()).substring(2));
			}

			if(d == 1 && i > -options.delta) {
				jContainer.children('.month_name.m2')
					.show()
					.css({
						left: j.offset().left
					})
					.html(common.monthName(date) + "'" + new String(date.getFullYear()).substring(2));
			}
		}
	};

	var listeners = [];

	var setCenterDayMs = function (ms, notify) {
		setCenterDay(Math.floor(ms / DAY_IN_MS));
		if (notify) {
			for (var i = 0; i < listeners.length; ++i) {
				listeners[i](ms);
			}
		}
	};

	var redraw = function (container) {
		var w = container.width(),
			dw = w / (2 * options.delta + 1);

		jContainer.children('.date')
			.width(dw - options.commonW)
			.each(function (i) {
				var x = (i - options.delta) / options.delta * 2;
				$(this).css({
					left:i * dw,
					opacity:Math.exp(-x * x / 2)
				});
			})
			.click(function () {
				var ms = $(this).data('ms');
				setCenterDayMs(ms, true);
			});

		jContainer.children('.month_name').css({
			width:dw - options.commonW + 20
		});
	};

	return {
		getByContainer: function (container) {

			redraw(container);

			$(window).resize(function () {
				redraw($(jContainer).parent());
			});

			return jContainer;
		},

		scrollTo: function (ms) {
			setCenterDayMs(ms, true);
		},

		silentScrollTo: function (ms) {
			setCenterDayMs(ms);
		},

		onChange: function (f) {
			listeners.push(f);
		}
	};
}

