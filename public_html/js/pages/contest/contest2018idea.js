/**
 * Перейти к якорю
 * @param  {string} anchorId ид якоря
 */
function jumpToAnchor(anchorId) {
    var anchor = document.getElementById(anchorId);
    var anchorTopMagrin = parseInt(window.getComputedStyle(anchor, ':before').getPropertyValue('margin-top'));
    window.scrollTo(0, anchor.offsetTop + anchorTopMagrin);   
}

/**
 * Отправить новую работу на конкурс
 */
function storeContestEntity() {
    // Получить данные формы
    var file = $("#file").prop("files")[0];
    var description = $("#description").val();
    var formData = new FormData();
    formData = formDataFilter(formData);
    formData.append("description", description);
    formData.append("file", file);
    formData.append("action", "store");
    // Сохранить конкурсную работу
    $.ajax({
        url: "idea",
        dataType: "text",
        cache: false,
        contentType: false,
        processData: false,
        data: formData,
        type: "post",
        success: function(response) {
            response = JSON.parse(response);                        
            // Конкурсная работа успешно сохранена
            if (response.success) {
                // Скрыть все ошибки
                $(".form-error").addClass('hidden');
                // Показать сообщение об успешном сохранении работы
                $(".form-success").removeClass('hidden');
                // Скрыть форму
                $("#addNewEntityForm").addClass('hidden');
                // Скролл к сообщению об успешном сохранении работы
                jumpToAnchor("form");
            } else {
                // Форма не прошла валидацию
                // Скрыть все ошибки
                $(".form-error").addClass('hidden');
                // Отобразить новые ошибки
                Object.keys(response.data).forEach(function(key, index) {
                    var error = this[key][0];
                    $("#" + key + "_error").text(error).removeClass('hidden');
                }, response.data);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) { 
            // Перезагрузить страницу
            location.reload();
        }          
    });

}

// Поле прикрепления файла
$(document).on('click', '.b-promo-page_form_field .file-attach-block', function(e){
    $('.b-promo-page_form_field .file-attach-input').trigger('click');
});

$(document).on('change', '.b-promo-page_form_field .file-attach-input', function(e){
    showFileName($(this)[0]);
});

function showFileName(input) {
    $('.js-contest-file-error ').addClass('hidden');
    if (input.files && input.files[0]) {
        if (input.files[0].size > 4194304) {
            setError(t('Размер файла не должен превышать 4Mb'));
            input.value = '';
            $('.b-promo-page_form_field .file-attach-filename').text('');
            return false;
        }
        var fileName = input.files[0].name;
        $('.b-promo-page_form_field .file-attach-filename').text(fileName);
    }
}

function setError(text){
    $('.js-contest-file-error ').text(text).removeClass('hidden');
}

// Шаринг в соц сетях
(function () {
    if (window.pluso)
        if (typeof window.pluso.start == "function")
            return;
    if (window.ifpluso == undefined) {
        window.ifpluso = 1;
        var d = document, s = d.createElement('script'), g = 'getElementsByTagName';
        s.charset = 'UTF-8';
        s.async = true;
        s.src = ('https:' == window.location.protocol ? 'https' : 'http') + '://share.pluso.ru/pluso-like.js';
        var h = d[g]('body')[0];
        h.appendChild(s);
    }
})();


$(document).ready(function () {
	var $countDown = $(".b-promo-page__countdown");
	console.log($countDown.data('end'));
	var countDownDate = new Date($countDown.data('end') + ' GMT+03:00').getTime();

	var x = setInterval(function() {

		var now = new Date().getTime();

		var distance = countDownDate - now;

		var days = Math.floor(distance / (1000 * 60 * 60 * 24));
		var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
		var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
		var seconds = Math.floor((distance % (1000 * 60)) / 1000);

		$countDown.find("span").html(
			(days ? (days + ' ' + Utils.declOfNum(days, [t('день'), t('дня'), t('дней')]) + ' ') : '')
			+ (hours ? (hours + ' ' + Utils.declOfNum(hours, [t('час'), t('часа'), t('часов')]) + ' ') : '')
			+ (minutes ? (minutes + ' ' + Utils.declOfNum(minutes, [t('минута'), t('минуты'), t('минут')]) + ' ') : '')
			+ seconds + ' ' + Utils.declOfNum(seconds, [t('секунда'), t('секунды'), t('секунд')]) + ' '
		);

		$countDown.show();

		if (distance < 0) {
			clearInterval(x);
			$countDown.hide();
		}
	}, 1000);
})
