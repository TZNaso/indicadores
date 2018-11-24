$(document).ready(function () {
	var btnMenuLateral = $("#btnMenuLateral");
	var MainMenu = $("#MainMenu");
	var mainContent = $("#mainContent");
	var mainWidth = mainContent.css('width');

	btnMenuLateral.css({'margin-left':MainMenu.css('width'), display:'block'})
	btnMenuLateral.click(function() {
		MainMenu.toggle( "slow", function() {
			if (MainMenu.css('display') =='none') {
				btnMenuLateral.css({'margin-left':0});
				btnMenuLateral.text(">>");
				mainContent.css({'margin-left':btnMenuLateral.css('width')});
				mainContent.css({width:'95%'});
			} else {
				btnMenuLateral.css({'margin-left':MainMenu.css('width')});
				mainContent.css({'margin-left':MainMenu.css('width')});
				btnMenuLateral.text("<<");
				mainContent.css({width:mainWidth});
			}
		});
	});
});