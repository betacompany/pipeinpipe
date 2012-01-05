var selectedColor;

function setActive(div){
	setPassive($("#"+selectedColor));
	selectedColor = div.attr("id");
	div.css("top", "1px").css("left", "1px").css("border-width", "1px");
}

function setPassive(div){
	div.css("top", "2px").css("left", "2px").css("border-width", "0px");
}

$(document).ready(function (){
	var colorBlocks = $(".color");
	var MY_COLORS = {
		A: '#FCA7A7',
		B: '#E06507',
		C: '#F6DE86',
		D: '#FDAE79',
		E: '#B9CD5C',
		F: '#FDB03E',
		G: '#BEE871'
	};

	for(var key in COLOR){
		var id = COLOR[key].substr(1);
		$("#color_table").html($("#color_table").html()+"<div class='colorContainer'><div id='" + id + "' class='color'></div></div>")
		$("#" + id).css("background-color", COLOR[key]);
	}

	for(var key in MY_COLORS){
		var id = MY_COLORS[key].substr(1);
		$("#color_table").html($("#color_table").html()+"<div class='colorContainer'><div id='" + id + "' class='color'></div></div>")
		$("#" + id).css("background-color", MY_COLORS[key]);
	}

	for (var i=0; i<4;){
		$("#form_" + i).html($("#form_" + i).html() + "<div id='form_"+ ++i +"' class='form'></div>");
	}

	$(".color").click(function() {setActive($(this));});
	$(".form").click(function(e) {e.stopPropagation();$(this).css("background-color", "#"+selectedColor);});
});


