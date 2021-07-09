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

$(document).on('click', '.b-promo-page_form_field .file-attach-block', function(e){
    $('.b-promo-page_form_field .file-attach-input').trigger('click');
});

$(document).on('change', '.b-promo-page_form_field .file-attach-input', function(e){
    showFileName($(this)[0]);
});

function showFileName(input) {
    if (input.files && input.files[0]) {
        if (input.files[0].size > config.files.maxSizeReal) {
            setError(t('Размер файла не должен превышать {{0}} МБ', [config.files.maxSize]));
            input.value = '';
            return false;
        }
        var fileName = input.files[0].name;
        var len = fileName.length;
        var symb3 = fileName.substr(len - 3, len).toLowerCase();
        var symb4 = fileName.substr(len - 4, len).toLowerCase();
        var allow = false;
        if($.inArray(symb3, ["doc"]) != -1) {
            allow = true;
        }else if($.inArray(symb4, ["docx"]) != -1) {
            allow = true;
        }
        if (!allow) {
            setError(t('Недопустимое расширение файла.'));
            input.value = '';
            return false;
        }
        $('.b-promo-page_form_field .file-attach-filename').text(fileName);
    }
}

function setError(text){
    $('.js-contest-file-error ').text(text).removeClass('hidden');
}
$(document).on('submit', '#promo_file_form', function(e){
    if ($('.b-promo-page_form_field .file-attach-input')[0].files.length == 0) {
        setError(t('Выберите файл для загрузки.'));
        e.preventDefault();
        return false;
    }
});