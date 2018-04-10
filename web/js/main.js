
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

function Copy(query) {
    var state = {};
    
    state.callback = function(data, textStatus, jqXHR) {
        if(data.result == 'ok') {
            if(data.exit) {
                $("#progress-id .uk-progress-bar").css("width", "100%").text("Успешно");
                $("#process-result").removeClass("hide-elements").find(".import-result > div").html(data.message);
                setTimeout(function() {
                    $("#table-result > tbody").append(data.table);
                    state.exit();
                }, 5000);
            }
            else {
                $("#progress-id .uk-progress-bar").css("width", data.process + "%").text(data.process + "%");
                state.post();
            }
        }
        else if(data.result == 'not_found') {
            $("#progress-id .uk-progress-bar").css("width", "100%").text("Успешно");
            $("#process-result").removeClass("hide-elements").find(".import-result > div").html(data.message);
            setTimeout(function() { state.exit(); }, 5000);
        }
        else {
            $("#progress-id .uk-progress-bar").css("width", "100%").text("Ошибка!!!");
            setTimeout(function() { state.exit(); }, 3000);
        }
    };
    
    state.post = function() {
        $.post("/copy/", { query : state.query }, state.callback);
    };
    
    state.init = function() {
        $("#progress-id").removeClass("hide-elements").find(".uk-progress-bar").css("width", 0).text("Запуск...");
        $(".content .import button").attr("disabled", true);
        state.post();
    };
    
   state.exit = function() {
        $(".content .import button").attr("disabled", false);
        $("#process-result").addClass("hide-elements");
        $("#progress-id").addClass("hide-elements");
    }
    
    state.query = query;
    state.init();
}

$(document).ready(function() {
	$(".tabs > div").click(function() {
		if(!$(this).hasClass("selected")) {
			$(this).parent().children().removeClass("selected").eq($(this).index()).addClass("selected");
			$(".content").children().removeClass("selected").eq($(this).index()).addClass("selected");
			set_size_elements();
		}
	});
        $(".content .import button").click(function() {
            Copy($(".content .import input").val());
        });
});
