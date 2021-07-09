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
        if($.inArray(symb3, ["jpg", "gif", "png"]) != -1) {
            allow = true;
        }else if($.inArray(symb4, ["jpeg"]) != -1) {
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

$(document).on('click', '.promo-birthday-card .overlay', function(){
    var card = $(this).closest('.promo-birthday-card');
    var cardContent = card.find('.popup-content').html();
    var content = $('.common-popup-content');
    content.find('.card-popup-content').html(cardContent);
    birthday_init_share_social(card.data('id'));
    show_popup(content.html(), 'birthday-popup-content');
});

function loadCards() {
    var container = $('.promo-birthday-cards');
    var total = container.data('total');
    var lastId = container.find('.promo-birthday-card').last().data('id');
    $('.loadKworks').addClass('onload');
    $.ajax({
        method: 'post',
        dataType: 'json',
        data: {
            action: 'loadCards',
            lastId: lastId
        },
        success: function(response) {
            $('.loadKworks').removeClass('onload');
            if (response.html.length > 0) {
                $(response.html).insertAfter(container.find('.promo-birthday-card').last());
            }
            if (container.find('.promo-birthday-card').size() >= total) {
                $('.loadKworks').remove();
            }
        }
    });
}

function birthdaySendForm() {
    var form = $('#promo_file_form');
    var video = form.find('input[name=video]').val();
    var file = form.find('input[name=fileInput]').val();
    var text = form.find('textarea[name=text]').val();
    if (!video && !file && !text) {
        form.find('.form-error').removeClass('hidden');
    } else {
        $('#promo_file_form').submit();
    }
}

function birthday_init_share_social(id) {
    var url = CANONICAL_ORIGIN_URL + '?card=' + id;
    if (USER_ID) {
        url += '&ref=' + USER_ID;
    }
    share = Ya.share2('birthday-tut-laykayut', {
        content: {
            url: url
        },
        theme: {
            services: 'facebook,vkontakte,twitter,odnoklassniki,gplus',
            bare: true
        }
    });
}

$(document).ready(function(){
    $('.autoload .promo-birthday-card .overlay').click();
});