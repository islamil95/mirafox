/**
 * Проверка, доступен ли вывод на эту платежную систему
 
 * @param {string} typeWithdraw
 * @returns {undefined}	 */
function checkCanWithdraw(typeWithdraw) {
	var canWithdrawData = canWithdrawJsonData;
	if (canWithdrawData[typeWithdraw].canWithdraw === true) {
		$('#purse-submit').removeClass('disabled').prop('type', 'submit');
		$('#purse-input').prop("disabled", false);
		$("#withdraw-error").empty();
	} else {
		$("#purse-submit").addClass("disabled").prop("type", "button");
		$('#purse-input').prop("disabled", true);
		$("#withdraw-error").html("<span class='color-red'>" + canWithdrawData[typeWithdraw].canWithdrawMessage + "</span>");
	}
}

/**
 * Проверяем, нужна ли доп. информация для иностранных карт
 *
 * @param typeWithdraw Тип платежной системы
 */
function checkForeignCard(typeWithdraw) {
	if (typeWithdraw === 'card3' && isNeedFillForeignSolarData) {
		$('#purse-submit').addClass('disabled').prop('type', 'button');
		$('#purse-input').prop("disabled", true);

		$('#withdraw-error').html(
			'<span class="color-red">'
				+ t('Чтобы отправить заявку на вывод, необходимо {{0}} дополнительные данные.', ['<a href="/settings#finances">' + t('указать') + '</a>'])
			+ '</span>'
		);
	}
}

/**
 *	Применение фильтров
 */

$("select[name=name-kwork]").change(function () {
    changeBalanceFilter(true, false);
});


var calendarIsClose = false;

/**
 *	Каллбек календаря onHide при закрытии календаря вызывается дважды, что-бы не дублировать запрос ajax сделал вот такую проверку
 */
function  calendarClose() {
   if(!calendarIsClose){
       changeBalanceFilter(true, true);
   }
    calendarIsClose = !calendarIsClose;
}


/**
 *	Изменяем параметры фильтра, отправляем запрос на получение новых данных
 */

var fromDate = "";
var toDate = "";
var typeOperation = ["all"];
var nameKwork = "";
var page = 1;
var typeOperationTemp;

//При получении данных о операциях обновляем ссылку экспорта согласно данным фильтра
function changeExportUrl(){
    var exportUrl = "balance/export?date_from=" + fromDate + "&to_date=" + toDate + "&type_operation=" + typeOperation + "&name_kwork=" + nameKwork;
    $(".export-userop").attr("href", exportUrl);
}

function changeBalanceFilter(changeParams, refreshKworkListFilter) {
    //Если меняются параметры фильтра, страница сбрасывается на первую
    if(changeParams){
        window.fromDate = $("#from").val();
		window.toDate = $("#to").val();
		window.nameKwork = $("select[name=name-kwork]").val();
		window.typeOperation = $("select[name=type-operation]").val();
        if (typeOperation == null) {
			typeOperation = ["all"];
        }
        if(($.inArray("order_in", typeOperation) == -1 && typeOperation[0] != "all")){
            nameKwork = "";
            $('.filter-kwork-name option:first').attr('selected','1');
            $('.js-select__single').trigger("chosen:updated");
        }
        page = 1;

        if(refreshKworkListFilter) {
            if(JSON.stringify(typeOperation) != JSON.stringify(typeOperationTemp)) {

                typeOperationTemp = typeOperation;
                $.ajax({
                    url: "/balance/filter_kwork_list",
                    type: "POST",
                    data: {date_from: fromDate, to_date: toDate, type_operation: typeOperation},
                    success: function (response) {

                        if (response.data.length != 0) {
                            $(".select__single").css("visibility", "visible");
                            $(".select__single").show();
                            $('.filter-kwork-name').empty();
                            $('.filter-kwork-name').append('<option value="">' + t("Название кворка") + '</option>');

                            $.each(response.data, function (obj, values) {
                                var choosen = "";
                                if (obj == nameKwork)
                                    choosen = " selected ";
                                $('.filter-kwork-name').append('<option ' + choosen + 'value="' + obj + '">' + values + '</option>');
                            });
                            $('.js-select__single').trigger("chosen:updated");

                            if($(".chosen-results").html() == "" && $('.chosen-container').hasClass('chosen-with-drop')){
                                $('.js-select__single').click();
                                $('.js-select__single').trigger("chosen:open");
                            }
                        } else {
                            $(".select__single").hide();
                        }

                    }
                });
            }
        }

    }

    $.ajax({
        url: "/balance/view",
        type: "POST",
        data: {
            date_from: window.fromDate,
            to_date: window.toDate,
            type_operation: window.typeOperation,
            name_kwork: window.nameKwork,
            page: window.page
        },
        success: function (data) {
            $("#operations-result").html(data);
            changeExportUrl();
        }
    });
}

// Перехват клика по навигации для ajax пагинации
$("body").on("click", ".balance-pagination ul li a", function (e) {
    e.preventDefault();
    var val =  $(this).attr("href").split("=");
	window.page = val[val.length - 1];
    changeBalanceFilter(false, false);
});


