
//установим высоту рамок на странице
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

$(window).on('load', function () { set_size_elements(); });//после загрузки страницы
$(window).on('resize', function () { set_size_elements(); });//при изменении размеров окна браузера

//функция, которая циклично шлёт запросы серверу
//пока тот не скопируем все данные из википедии или не вернёт ошибку
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
        else if((data.result == 'not_found') || (data.result == 'article_exsit')) {
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

$(document).ready(function() {//обработчики 
	$(".tabs > div").click(function() {
		if(!$(this).hasClass("selected")) {
			$(this).parent().children().removeClass("selected").eq($(this).index()).addClass("selected");
			$(".content").children().removeClass("selected").eq($(this).index()).addClass("selected");
			set_size_elements();
		}
	});
        $(".content .import button").click(function() { Copy($(".content .import input").val()); });
        $(".content .find button").click(function() {
            $.post("/search/", { search : $(".content .find input").val() }, function(data, textStatus, jqXHR) {
                $(".find-result").html(data.result);
            });
        });
        $(".find-result").on('click', 'a', function(event) {
            var id = $(this).parent('span').attr("data-id"),
                text = $(this).text();
            $(".find-result span.uk-text-large").each(function(indx, element) {
                var set = $(element).find('a');
                if(set.length == 0) {
                    var _text = $(element).text();
                    $(element).html("<a href=\"#\">" + _text + "</a>");
                }
            });
            $(this).parent('span').text(text);
            $.get("/article/" + id + "/", function(data, textStatus, jqXHR) {
                $('.article').html(data.article);
            });
            event.preventDefault();
        });
});
