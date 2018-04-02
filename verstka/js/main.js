
$(document).ready(function() {
	$(".tabs > div").click(function() {
		if(!$(this).hasClass("selected")) {
			$(this).parent().children().removeClass("selected").eq($(this).index()).addClass("selected");
			$(".content").children().removeClass("selected").eq($(this).index()).addClass("selected");
		}
	});
});