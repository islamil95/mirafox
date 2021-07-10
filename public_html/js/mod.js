$("document").ready(function () {
    $(".rowRenderButtonDiv").click(function (e) {
       // console.log($(e.currentTarget).prev().html());
       $(this).parent('div').prev().toggle();
        $(this).children('div').toggleClass('rowRenderButtonTop');
    });
});


