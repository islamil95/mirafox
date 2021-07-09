function fox_jumpMenu(targ, selObj, restore) {
    eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");
    if (restore) {
        selObj.selectedIndex = 0;
    }
}

$(function () {
    if (nextpage * items_per_page >= total) {
        var requestBlockJs = $('.request_block-js');
        $('.loadKworks').remove();
        if (requestBlockJs.length) {
            requestBlockJs.removeClass('hidden');
        }
    }

    //смена сортировки
    $('.search-filter-sort .js-sort-by').on('change', function () {
        window.location.href = $(this).val();
    });
});