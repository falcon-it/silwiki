
function set_size_elements() {
	$(".content").height(
		$(window).height() - 
				(($("body").outerHeight() - $("body").height()) + 
				$(".tabs").outerHeight(true) + 
				($(".content").outerHeight(true) - $(".content").height()))
			);
	var ih = 0;
	$(".content .import > *:not(.hide-elements)").each(function(indx, element) { ih += $(element).outerHeight(true); });
	$(".content .import > div:last").height($(".content").height() - 
		(ih - $(".content .import > div:last").outerHeight(true)) - 
		($(".content .import").outerHeight(true) - $(".content .import").height()));
	ih = 0;
	$(".content .find > *").each(function(indx, element) { ih += $(element).outerHeight(true); });
	$(".content .find > div:last").height($(".content").height() - 
		(ih - $(".content .find > div:last").outerHeight(true)) - 
		($(".content .find").outerHeight(true) - $(".content .find").height()));
}

$(window).on('load', function () { set_size_elements(); });
$(window).on('resize', function () { set_size_elements(); });

$(document).ready(function() {
	$(".tabs > div").click(function() {
		if(!$(this).hasClass("selected")) {
			$(this).parent().children().removeClass("selected").eq($(this).index()).addClass("selected");
			$(".content").children().removeClass("selected").eq($(this).index()).addClass("selected");
			set_size_elements();
		}
	});
});
