$(document).on('click', '.js-authorized-load-popular', function () {
    if ($(this).data('loaded') == 0) {
        loadRestPopular($(this));
    } else {
        toggleRestPopular($(this));
    }
});

function toggleRestPopular(loadButton) {
    var $popularBlock = $(".cusongslist-authorized-popular");
    $popularBlock.toggleClass('authorized-popular-rest-hidden');
    if ($popularBlock.hasClass('authorized-popular-rest-hidden')) {
        $(loadButton).text(t('Смотреть все'));
        $('html, body').animate({
            scrollTop: $('.authorized-kwork-block__popular').offset().top - 100
        }, 200);
    } else {
        $(loadButton).text(t('Скрыть популярные кворки'));
    }
}

function loadRestPopular(loadButton) {
    $('.preloader_rest_popular').show();
    $.ajax( {
        type: "GET",
        url: "/api/kwork/getrestauhorizedpopular?weightMinute=" + $(loadButton).data("weightMinute"),
        success: function( response, textStatus, xrf ) {
            if(response.result == 'success'){
                $('.cusongslist-authorized-popular .cusongsblock:last').after(response.html);
                $(loadButton).data('loaded', 1);
                toggleRestPopular(loadButton);
                $('.preloader_rest_popular').hide();
            }
        }
    } );
}