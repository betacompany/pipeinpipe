var mainMenu = {
	/**
	 * contains all main menu functions and properties
	 */

	//colors
	passiveColor: '',
	activeColor: '',
	openedColor: '',

	initColors: function () {
		this.passiveColor = COLOR.FIRST;
		this.activeColor = COLOR.THIRD;
		this.openedColor = COLOR.SECOND;
	},

	// menu
	openedMenuId: null,//Id of menu whos content is shown to user
	openedSubmenuId: null,//Id of submenu whos content is shown to user
	selectedMenuId: null,//Id of menu under user's cursor
	selectedSubmenuId: null,//Id of submenu under user's cursor

	selectOpened: function (){
		this.selectMenu($("#"+this.openedMenuId));
		this.selectSubmenu($("#"+this.openedSubmenuId));
	},

	//@param jQueryObject menu (NOT NULL)
	selectMenu: function (menu){
		if (menu == null) return;

		//WARNING!!! Think twice before changing this method!
		//We select openedSubmenu, if we are on opened menu
		if (this.openedMenuId == menu.attr("id")){
			this.selectSubmenu($("#"+this.openedSubmenuId));
		}
		else{//we close all submenus otherwise
			this.selectSubmenu(null);
		}
		if (this.selectedMenuId != menu.attr("id")){//not to open twice
			var prevMenuId = this.selectedMenuId;//local variable previously selecteded menu id
			this.selectedMenuId = menu.attr("id");//this is the global variable for courrently selected menu id
			var menuToSelectId = menu.attr("id");//this is local variable for menu id that must be selected in current thread

			var color = this.activeColor;//color of menu item to be opened
			//color of menu_item depends on it's .opented status
			if (menuToSelectId==this.openedMenuId){
				color = this.openedColor;
			}

			//if we select the menu that must be selected
			if (this.selectedMenuId == menuToSelectId){
				menu.animate({//setting active color for menu item
					"backgroundColor": color}, 100, "linear", function(){
						//setting passive color for menu item
						if (mainMenu.selectedMenuId != prevMenuId){
							$("#"+prevMenuId).animate({
								"backgroundColor": mainMenu.passiveColor}, 100, "linear"
							);
						}
					}
				);
			}

			//animating submenu panels and detach_bar
			$("#menu_detach_bar").animate({//setting menu_detach_bar active color
				"backgroundColor": color}, 100, "linear", function(){
				//if we are not closing the submenu that must be opened now
				if(mainMenu.selectedMenuId != prevMenuId){
					$("#submenu_" + (prevMenuId.split('_'))[1]).slideUp(100, function (){
						//selecting opened submenu
						//setting colors of submenus
						$("#"+mainMenu.selectedSubmenuId).css({"backgroundColor": mainMenu.passiveColor});//setting selectedSubmenu passive
						$("#"+mainMenu.openedSubmenuId).css({"backgroundColor": mainMenu.openedColor});//setting openedSubmenu active
						//saving id of selected submenu
						mainMenu.selectedSubmenuId = mainMenu.openedSubmenuId;
						//if we are opening the submenu that must be opened now
						if (menuToSelectId == mainMenu.selectedMenuId){
							$("#submenu_"+(menuToSelectId.split('_')[1])).slideDown(100);
						}
					});
				}
			});
		}
	},

	//@param jQueryObject submenu
	selectSubmenu: function (submenu){
		//WARNING!!! Think twice before changing this method!
		//comments here are all the same as in selectMenu(menu) method
		//submenu=null means that we want to unselect all submenus
		if (submenu==null){
			var prevSubmenuId = this.selectedSubmenuId;
			this.selectedSubmenuId = null;
			$("#"+prevSubmenuId).animate({//setting passive color for submenu item
				"backgroundColor": mainMenu.passiveColor}, 100, "linear"
			);
		}
		else if (this.selectedSubmenuId != submenu.attr("id")){
			var prevSubmenuId = this.selectedSubmenuId;
			this.selectedSubmenuId = submenu.attr("id");
			var submenuToSelectId = submenu.attr("id");

			var color = this.activeColor;//color of submenu item to be opened
			//color of menu_item depends on it's .opented status
			if (this.selectedMenuId == this.openedMenuId){
				color = this.openedColor;
			}

			//if submenuToSelect is defined and if the submenu, that must be selected, or its menu haven't changed
			if ((submenuToSelectId != undefined)&&(this.selectedSubmenuId == submenuToSelectId)&&(this.selectedMenuId==$("#"+submenuToSelectId).parent().parent().attr("id").split("sub")[1])){
				$("#"+submenuToSelectId).animate({//setting active color for submenu item
					"backgroundColor": color}, 100, "linear", function(){
						//if we are not unselecting currently selected submenu item
						if (mainMenu.selectedSubmenuId != prevSubmenuId){
							$("#"+prevSubmenuId).animate({//setting passive color for submenu item
								"backgroundColor": mainMenu.passiveColor}, 100, "linear"
							);
						}
					}
				);
			}
			else {
				//we are not unselecting currently selected submenu item
				if (this.selectedSubmenuId != prevSubmenuId){
					$("#"+prevSubmenuId).animate({//setting passive color for submenu item
						"backgroundColor": mainMenu.passiveColor}, 100, "linear"
					);
				}
			}
		}
	},

	alignSubmenus: function () {
		// center align of submenus
		var cl = $('#submenu_container').offset().left, // absolute position
			cw = $('#submenu_container').innerWidth(),
			items = ['index', 'sport', 'media', 'life'/*, 'forum'*/];

		for (var i = 0; i < items.length; i++) {
			var w = $('#submenu_' + items[i]).innerWidth(),
				l = $('#menu_' + items[i]).offset().left, // absolute position
				mw = $('#menu_' + items[i]).outerWidth(),
				left = (l - cl + mw / 2) - w / 2;

			left = (left < 0) ? 0 : left;
			(cl + left + w < cl + cw) ?
				$('#submenu_' + items[i]).css('left', left) :
				$('#submenu_' + items[i]).css('right', 0);
		}
	}
	
};

$(document).ready(function () {
	//setting menu variables
	mainMenu.initColors();
	mainMenu.openedMenuId = $('#menu_container a div.opened').attr('id');
	mainMenu.openedSubmenuId = $('#submenu_container div a div.opened').attr('id');
	mainMenu.selectedMenuId = mainMenu.openedMenuId;
	mainMenu.selectedSubmenuId = mainMenu.openedSubmenuId;

	//to select opened menu item, when user fastly leaves the page
	$(document).mouseout(function (e) {
		if('menu'==$(e.fromElement).attr('id')){
			mainMenu.selectOpened();
		}
	});
	//to select opened menu item, when user leaves menu
	$('body').mouseover(function () {
		mainMenu.selectOpened();
	});
	//to prevent document->mouseout rising while cursor is on menu
	$("#menu").mouseout(function (e){
		e.stopPropagation();
	});
	//to prevent body->mouseover rising while cursor is on menu
	$("#menu").mouseover(function (e){
		e.stopPropagation();
	});
	$("#menu_container a div.menu_item").mouseover(function () {mainMenu.selectMenu($(this))});
	$("#submenu_container a div.submenu_item").mouseover(function () {mainMenu.selectSubmenu($(this))});
});

$(document).ready(mainMenu.alignSubmenus);
$(window).resize(mainMenu.alignSubmenus);